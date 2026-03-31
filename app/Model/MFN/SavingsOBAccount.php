<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;

class SavingsOBAccount extends Model
{
    protected $table      = 'mfn_savings_opening_balance';
    protected $primaryKey = 'accountId';
    protected $fillable   = [
        'accountId',
        'memberId',
        'samityId',
        'branchId',
        'depositAmount',
        'interestAmount',
        'withdrawAmount',
        'openingBalance',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',

    ];

    public function member()
    {
        return $this->belongsTo('App\Model\MFN\Member', 'memberId', 'id');
    }
}
