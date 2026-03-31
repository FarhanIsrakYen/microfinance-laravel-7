<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;

class MonthEndSavings extends Model
{
    protected $table = 'mfn_month_end_savings';

 
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
        'created_at', 
        'updated_at', 
        'created_by',
        'updated_by',
        
    ];


}
