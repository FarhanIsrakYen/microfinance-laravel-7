<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;

class LoanGuarantor extends Model
{
    protected $table = 'mfn_loan_guarantors';

    protected $fillable = [
        'loanId',
        'name',
        'relation',
        'address',
        'phone',
        'guarantorNo',
        'created_at',
        'created_by',
        'updated_by',
    ];
}
