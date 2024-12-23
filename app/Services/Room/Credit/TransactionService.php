<?php

namespace App\Services\Room\Credit;

use App\Repositories\Room\Credit\TransactionRepository;
use App\Services\BaseService;
use App\Models\Room\Credit\Transaction;

class TransactionService extends BaseService
{
    /**
     * @var TransactionRepository
     */
    protected TransactionRepository $transaction;

    /**
     * @param TransactionRepository $tournament
     */
    public function __construct(TransactionRepository $transaction)
    {
        $this->transaction = $transaction;
        parent::__construct($this->transaction);
    }

}
