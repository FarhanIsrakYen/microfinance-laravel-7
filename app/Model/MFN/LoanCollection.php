<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;

class LoanCollection extends Model
{
    protected $table = 'mfn_loan_collections';

    protected $fillable = [
        'loanId', 
        'memberId', 
        'samityId', 
        'branchId', 
        'collectionDate', 
        'amount', 
        'principalAmount', 
        'interestAmount', 
        'paymentType', 
        'ledgerId', 
        'chequeNo', 
        // 'created_at', 
        // 'updated_at', 
        'created_by', 
        // 'updated_by', 
        // 'isAuthorized', 
        'isFromAutoProcess',
        'is_delete'
    ];
}
