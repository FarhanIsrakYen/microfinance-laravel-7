<?php

namespace App\Model\MFn;

use Illuminate\Database\Eloquent\Model;

class LoanWaiver extends Model
{
    protected $table = 'mfn_loan_waivers';

    protected $fillable = [
        'loanId',
        'samityId',
        'branchId',
        'waiverDate',
        'amount',
        'principalAmount',
        'interestAmount',
        'note',
        'isWithServiceCharge',
        'collectionIds',
        // 'created_at',
        // 'updated_at',
        'created_by',
        // 'updated_by',
        // 'is_delete'
    ];
}
