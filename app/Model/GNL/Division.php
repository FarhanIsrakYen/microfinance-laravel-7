<?php

namespace App\Model\GNL;

use App\BaseModel;

class Division extends BaseModel
{

    protected $table = 'gnl_divisions';
    protected $fillable = [
        'division_name',
        'short_name',
        'created_by',
		'updated_by'
    ];

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
