<?php

namespace App\Repositories\User;

use App\Models\User\User;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class UserRepository extends BaseRepository
{
    /**
     * @var User
     */
    protected User $user;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        parent::__construct($user);
    }

    /**
     * @param string $role
     * @return mixed
     */
    public function getUserByRole(string $role,string $keyword): mixed
    {
        if(!empty($keyword))
            return $this->user::role($role)->where('email','like','%'.$keyword.'%')->paginate(100);

        return $this->user::role($role)->paginate(100);
    }
    /* get users by ids */
    public function getUserByIds($ids): mixed
    {
        if(!empty($ids))
            return $this->user::whereIn('id',$ids)->get();

        return null;
    }
}
