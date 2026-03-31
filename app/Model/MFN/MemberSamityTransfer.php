<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;

class MemberSamityTransfer extends Model
{
    protected $table = 'mfn_member_samity_transfers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'memberId', 
        'branchId', 
        'date', 
        'oldSamityId', 
        'newSamityId', 
        'transferData', 
        'note',
        'created_by', 
        'updated_by', 
        'created_at', 
        'updated_at',
        'is_delete'
    ];
}
