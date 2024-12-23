<?php

namespace App\Repositories\Common;

use App\Models\Common\Credit;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class CreditRepository extends BaseRepository
{
    /**
     * @var Tournament
     */
    protected Credit $credit;

    /**
     * @param Credit $credit
     */
    public function __construct(Credit $credit)
    {
        $this->credit = $credit;
        parent::__construct($credit);
    }
    
}
