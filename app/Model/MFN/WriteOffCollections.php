<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;

class WriteOffCollections extends Model
{
    protected $table = 'mfn_loan_writeoff_collections';

    protected $fillable = [
        'collectionId',
        'loanId', 
        'memberId', 
        'samityId', 
        'branchId', 
        'amount', 
        'date', 
        // 'created_at', 
        // 'updated_at', 
        'created_by', 
        'updated_by', 
        // 'is_delete'
    ];
}
