<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;


class MfnDayEnd extends Model
{
    //
    protected $table = 'mfn_day_end';
    protected $fillable = [
        'date',
        'branchId',
        'isActive',
        'loanDisbursementAmount',
        'loanCollectionAmount',
        'loanDueAmount',
        'savingsDepositAmount',
        'savingsWithdrawAmount',
        
    ];


   
}
