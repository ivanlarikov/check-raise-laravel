<?php
namespace App\Services\Common;

use App\Repositories\Common\CreditRepository;
use App\Services\BaseService;
use App\Models\Common\Credit;

class CreditService extends BaseService
{
    /**
     * @var CreditRepository
     */
    protected CreditRepository $credit;

    /**
     * @param CreditRepository $user
     */
    public function __construct(CreditRepository $credit)
    {
        $this->credit = $credit;
        parent::__construct($this->credit);
    }

}
