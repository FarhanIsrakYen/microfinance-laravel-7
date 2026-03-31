<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;

class LoanAdjustments extends Model
{
    protected $table = 'mfn_loan_adjustments';

    protected $fillable = [
        'loanId', 
        'memberId', 
        'samityId', 
        'branchId', 
        'adjustmentAmount', 
        'adjustmentDetails', 
        'date', 
        'collectionId', 
        'withdrawIds', 
        'status',
        // 'created_at', 
        // 'updated_at', 
        'created_by', 
        // 'updated_by', 
        // 'is_delete'
    ];
}
