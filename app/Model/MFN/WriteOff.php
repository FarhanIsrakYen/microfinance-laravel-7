<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;

class WriteOff extends Model
{
    protected $table = 'mfn_loan_writeoffs';

    protected $fillable = [
        'loanId',
        'samityId',
        'branchId',
        'writeOffDate',
        'amount',
        'principalAmount',
        'interestAmount',
        'note',
        'collectionIds',
        // 'created_at',
        // 'updated_at',
        'created_by',
        // 'updated_by',
        // 'is_delete'
    ];






}
