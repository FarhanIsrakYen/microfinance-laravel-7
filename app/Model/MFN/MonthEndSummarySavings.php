<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;

class MonthEndSummarySavings extends Model
{
    protected $table = 'mfn_month_end_savings';
    public $timestamps = false;
    protected $fillable = [
        'date',
        'branchId',
        'loanProductId',
        'savingsProductId',
        'gender',
        'openingBalance',
        'deposit',
        'deposit_pt',
        'withdraw',
        'withdraw_pt',
        'withdraw_adjustment',
        'closingBalance',
        
    ];
}
