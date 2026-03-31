<?php

namespace App\Model\MFN;

use App\Jobs\SendMailJob;
use DB;
use Illuminate\Database\Eloquent\Model;

class SavingsAccount extends Model
{
    protected $table = 'mfn_savings_accounts';

    protected $fillable = [
        'accountCode',
        'memberId',
        'branchId',
        'savingsProductId',
        'autoProcessAmount',
        'interestRate', 'matureDate',
        'samityId',
        'created_by',
        'created_at',
        'openingDate',
        'closingDate',
        'mriCode',
    ];

    public function savingsProduct()
    {
        return $this->belongsTo('App\Model\MFN\SavingsProduct', 'savingsProductId', 'id');
    }
    public function member()
    {
        return $this->belongsTo('App\Model\MFN\Member', 'memberId', 'id');
    }
}
