<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;

class AutoVoucherConfig extends Model
{
    protected $table = 'mfn_auto_voucher_config';
    public $timestamps = false;
    protected $fillable = [
        'componentId',
        'headFor',
        'loanProductId',
        'savingsProductId',
        'principalLedgerId',
        'interestLedgerId',
        'interestProvisionLedgerId',
    ];
}
