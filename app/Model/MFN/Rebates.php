<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;

class Rebates extends Model
{
    protected $table = 'mfn_loan_rebates';

    protected $fillable = [
        'loanId',
        'samityId',
        'branchId',
        'rebateDate',
        'rebateAmount',
        'note',
        'collection_ids',
        // 'created_at',
        // 'updated_at',
        'created_by', 
        // 'updated_by',
        // 'is_delete'
    ];

}
