<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;

class SavingsDeposit extends Model
{
    protected $table = 'mfn_savings_deposit';

    protected $fillable = [
        'accountId', 
        'memberId', 
        'samityId', 
        'branchId', 
        'primaryProductId', 
        'savingsProductId', 
        'amount', 
        'date', 
        'transactionTypeId', 
        'ledgerId', 
        'chequeNo', 
        'created_at', 
        'created_by',
        'isFromAutoProcess',
    ];

    public function depositeType()
    {
        return $this->belongsTo('App\Model\MFN\SavingsTransactionType', 'transactionTypeId', 'id');
    }

}
