<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;

class SavingsWithdraw extends Model
{
    protected $table = 'mfn_savings_withdraw';

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
        'created_by'
    ];

    public function depositeType()
    {
        return $this->belongsTo('App\Model\MFN\SavingsTransactionType', 'transactionTypeId', 'id');
    }
}
