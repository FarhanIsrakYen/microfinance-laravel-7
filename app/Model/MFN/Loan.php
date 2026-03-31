<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $table = 'mfn_loans';


    public function loanProduct()
    {
        return $this->belongsTo('App\Model\MFN\LoanProduct', 'productId', 'id');
    }
    public function interestRate()
    {
        return $this->belongsTo('App\Model\MFN\LoanProductInterestRate', 'interestRateId', 'id');
    }

}
