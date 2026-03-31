<?php

namespace App\Model\HR;

use App\BaseModel;

class BankBranch extends BaseModel
{

    protected $table = 'hr_bank_branches';
    protected $fillable = [
        'bankId',
        'name',
        'address',
        'created_at', 
        'updated_at', 
        'created_by', 
        'updated_by', 
        'is_delete'
    ];

 
    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
