<?php

namespace App\Model\HR;

use App\BaseModel;

class Bank extends BaseModel
{

    protected $table = 'hr_banks';
    protected $fillable = [
        'name',
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
