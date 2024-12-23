<?php

namespace App\Repositories\Room\Credit;

use App\Models\Room\Credit\Transaction;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class TransactionRepository extends BaseRepository
{
    /**
     * @var Transaction
     */
    protected Transaction $transaction;

    /**
     * @param Transaction $transaction
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
        parent::__construct($transaction);
    }
    
}
