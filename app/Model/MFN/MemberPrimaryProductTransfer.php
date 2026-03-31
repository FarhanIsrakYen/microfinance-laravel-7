<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;

class MemberPrimaryProductTransfer extends Model
{
    protected $table = 'mfn_member_primary_product_transfers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'memberId', 
        'branchId', 
        'oldProductId', 
        'newProductId', 
        'transferDate', 
        'transferData', 
        'note',
        'created_by', 
        'updated_by', 
        'created_at', 
        'updated_at',
        'is_delete'
    ];


    //   public function loanProduct()
    // {
    //     return $this->belongsTo('App\Model\MFN\LoanProduct', 'primaryProductId', 'id');
    // }
}
