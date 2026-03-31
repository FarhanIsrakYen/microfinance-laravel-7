<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;

class ShareAccount extends Model
{
    protected $table = 'mfn_share_accounts';

    protected $fillable = [
        'memberId', 
        'samityId', 
        'branchId', 
        'unitPrice', 
        'numberOfShare', 
        'totalPrice',
        'purchaseDate', 
        'status',
        'closingDate',
        'created_at',
        'updated_at',
        'created_by', 
        'updated_by', 
        'is_delete'
    ]; 

    
}
