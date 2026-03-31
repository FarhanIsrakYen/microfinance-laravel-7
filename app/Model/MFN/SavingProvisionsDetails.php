<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;

class SavingProvisionsDetails extends Model
{
    protected $table = 'mfn_savings_provision_details';
    
	public $timestamps = false;

    protected $fillable = [
        'provisionId', 
        'branchId', 
        'samityId', 
        'accountId', 
        'principalAmount', 
        'provisionAmount', 
        'dateFrom', 
        'dateTo'
    ];
}
