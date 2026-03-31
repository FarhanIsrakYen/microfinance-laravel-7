<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;

class LoanProduct extends Model
{
    protected $table = 'mfn_loan_products';
    protected $fillable = [
        'name', 
        'shortName', 
        'productCode', 
        'productTypeId', 
        'productCategoryId', 
        'fundingOrgId', 
        'pksfFundId', 
        'startDate', 
        'isPrimaryProduct', 
        'minLoanAmount', 
        'avgLoanAmount',
        'maxLoanAmount',
        'yearsEligibleWriteOff', 
        'isInsuranceApplicable', 
        'insuranceCalculationMethodId',
        'insurancePercentage',
        'fixedInsuranceAmount',
        'mandatorySavingsPercantage',
        'isMultipleLoanAllowed',
        'repaymentInfo',
        'formFee',
        'additionalFee',
        'additionalFreeForFirstTime',
        'status',
        'is_delete'
    ];
}
