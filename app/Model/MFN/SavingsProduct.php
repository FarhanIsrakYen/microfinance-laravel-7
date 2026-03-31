<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;

class SavingsProduct extends Model
{
    protected $table = 'mfn_savings_product';

    protected $filable = [
        'name',
         'shortName', 
         'productCode', 
         'productTypeId', 
         'effectiveDate', 
         'minimumSavingsBalance', 
         'collectionFrequencyId',
         'interestCalculationMethodId',
         'interestAvgMethodPeriodId', 
         'generateInterestProbation',
         'isMultipleSavingsAllowed', 
         'isNomineeRequired', 
         'isClosingChargeApplicable',
         'closingCharge',
         'isPartialWithdrawAllowed',
         'isPartialInterestWithdrawAllowed',
         'isDueMemberGettingInterest',
         'onClosingInterestEditable',
         'isMandatoryOnMemberAdmission',
         'status'
     ]; 


}
