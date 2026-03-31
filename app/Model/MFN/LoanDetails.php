<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;

class LoanDetails extends Model
{
    protected $table = 'mfn_loan_details';
    protected $primaryKey = 'loanId';
}
