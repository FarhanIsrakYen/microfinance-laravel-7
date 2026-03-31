<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;
use App\Jobs\SendMailJob;
use DB;

class SavingsClosing extends Model
{
    protected $table = 'mfn_savings_closings';

    protected $fillable = [
        'accountId',
        'memberId',
        'samityId',
        'branchId',
        'withdrawId',
        'closingDate',
        'closingAmount',
        'isFromMemberClosing',
        'is_delete',
    ];
}
