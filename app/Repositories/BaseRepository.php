<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Log;

/**
 * Class BaseRepository
 */
class BaseRepository implements Repository
{
    /**
     * @var
     */
    protected $model;

    /**
     * BaseRepository constructor.
     * @param $model
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * @return string
     */
    public function eloquentModel(): string
    {
        return $this->model::class;
    }

    /**
     * @param array|null $select
     * @param array|null $where
     * @param array|null $whereNot
     * @param array|null $with
     * @param null $orderBy
     * @param null $groupBy
     * @param int|null $limit
     * @return \Illuminate\Support\Collection
     */
    public function all(
        array $select = null,
        array $where = null,
        array $whereNot = null,
        array $with = null,
        $orderBy = null,
        $groupBy = null,
        int $limit = null,
        bool $paginate = false
    ) {
        $query = $this->model;

        /**
         * @desc Chain where clauses
         */
        if ($select !== null && is_array($select)) {
            foreach ($select as $value) {
                $query = $query->addSelect($value);
            }
        }

        /**
         * @desc Chain where clauses
         */
        if ($where !== null && is_array($where)) {
            foreach ($where as $key => $value) {

                // supports operators other than default equals operator; ex:  ['id' => ['>', 9]] <-- where id > 9
                if( is_array($value))
                    $query = $query->where($key, array_shift($value), array_shift($value));

                // default equals operator
                else
                    $query = $query->where($key, '=', $value);
            }
        }

        /**
         * @desc Chain where no clauses
         */
        if ($whereNot !== null && is_array($whereNot)) {
            foreach ($where as $key => $value) {
                $query = $query->where($key, '!=', $value);
            }
        }

        /**
         * @desc Chain eager loaded relationships
         */
        if ($with !== null && is_array($with)) {
            foreach ($with as $value) {
                $query = $query->with($value);
            }
        }
       
        /**
         * @desc Chain order by clauses
         */
        if ($orderBy !== null) {
            switch (gettype($orderBy)) {
                case 'string':
                    $query = $query->orderBy($orderBy);
                    break;
                case 'array':
                    foreach ($orderBy as $key => $value) {
                        $query = $query->orderBy($key, $value);
                    }
                    break;
            }
        }
        /**
         * @desc Chain order by clauses
         */
        if ($groupBy !== null) {
            switch (gettype($groupBy)) {
                case 'string':
                    $query = $query->groupBy($groupBy);
                    break;
                case 'array':
                    foreach ($groupBy as $value) {
                        $query = $query->groupBy($value);
                    }
                    break;
            }
        }

        /**
         * @desc Chain eager loaded relationships
         */
        if ($limit !== null && is_int($limit)) {
            $query = $query->limit($limit);
        }

        if( $paginate === false ){
            return $query->get();
        }

        return $query->paginate($limit);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(
        array $data
    ) {
        return $this->model->create(
            $data
        );
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function insertOrIgnore(
        array $data
    ) {
        return $this->model->insertOrIgnore(
            $data
        );
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function insertGetId(
        array $data
    ) {
        return $this->model->insertGetId(
            $data
        );
    }

    /**
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(
        int $id,
        array $data
    ): bool {
        try {
            $this->model->where(
                'id', $id
            )->update($data);

            return true;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        return false;
    }

    /**
     * @param int $id
     * @param string $column
     * @return bool
     */
    public function increment(
        int $id,
        string $column
    ): bool {
        try {
            $this->model->where(
                'id', $id
            )->update([
                $column => DB::raw($column.' + 1'),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        return false;
    }

    /**
     * @param int $id
     */
    public function delete(
        int $id
    ): void {
        $this->model->where(
            'id', '=', $id
        )->delete();
    }

    /**
     * @param int $id
     */
    public function softDelete(
        int $id
    ): void {
        $this->model->where(
            'id', '=', $id
        )->delete();
    }

    /**
     * @param int $id
     */
    public function hardDelete(
        int $id
    ): void {
        $this->model->where(
            'id', '=', $id
        )->forceDelete();
    }

    /**
     * @param array $where
     * @param array|null $with
     * @param null $orderBy
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|mixed|object|null
     */
    public function show(
        array $where,
        array $with = null,
              $orderBy = null
    ) {
        $query = $this->model;

        /**
         * @desc Chain where clauses
         */
        if (is_array($where)) {
            foreach ($where as $key => $value) {
                $query = $query->where($key, '=', $value);
            }
        }

        /**
         * @desc Chain eager loaded relationships
         */
        if (is_array($with)) {
            foreach ($with as $value) {
                $query = $query->with($value);
            }
        }

        /**
         * @desc Chain order by clauses
         */
        if ($orderBy !== null) {
            switch (gettype($orderBy)) {
                case 'string':
                    $query = $query->orderBy($orderBy);
                    break;
                case 'array':
                    foreach ($orderBy as $key => $value) {
                        $query = $query->orderBy($key, $value);
                    }
                    break;
            }
        }

        return $query->first();
    }

    /**
     * @param array $select
     * @param array $where
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|mixed|object|null
     */
    public function showByName(
        array $select = null,
        array $where = null
    ) {
        $query = $this->model;

        /**
         * @desc Chain where clauses
         */
        if (is_array($select)) {
            $columns = implode(',', $select);
            $query = $query->select($select);
        }

        /**
         * @desc Chain where clauses
         */
        if (is_array($where)) {
            foreach ($where as $key => $value) {
                $query = $query->where($key, '=', $value);
            }
        }

        return $query->first();
    }

    /**
     * @param array|null $where
     * @param array|null $with
     * @param null $orderBy
     * @return mixed
     */
    public function count(
        array $where = null,
        array $with = null,
              $orderBy = null
    ) {
        $query = $this->model;

        /**
         * @desc Chain where clauses
         */
        if (is_array($where)) {
            foreach ($where as $key => $value) {
                $query = $query->where($key, '=', $value);
            }
        }

        /**
         * @desc Chain eager loaded relationships
         */
        if (is_array($with)) {
            foreach ($with as $value) {
                $query = $query->with($value);
            }
        }

        /**
         * @desc Chain order by clauses
         */
        if ($orderBy !== null) {
            switch (gettype($orderBy)) {
                case 'string':
                    $query = $query->orderBy($orderBy);
                    break;
                case 'array':
                    foreach ($orderBy as $key => $value) {
                        $query = $query->orderBy($key, $value);
                    }
                    break;
            }
        }

        return $query->count();
    }
}
