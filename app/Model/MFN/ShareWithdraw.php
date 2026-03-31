<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;

class ShareWithdraw extends Model
{
    protected $table = 'mfn_share_withdraws';

    protected $fillable = [
        'accountId',
        'memberId', 
        'samityId', 
        'branchId', 
        'numberOfShare', 
        'unitPrice', 
        'totalPrice',
        'withdrawDate', 
        'transactionTypeId',
        'ledgerId',
        'chequeNo',
        'closingDate',
        'created_at',
        'updated_at',
        'created_by', 
        'updated_by', 
        'is_delete'
    ]; 

    
}
