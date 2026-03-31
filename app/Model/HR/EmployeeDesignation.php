<?php

namespace App\Model\HR;

use App\BaseModel;

class EmployeeDesignation extends BaseModel
{

    protected $table = 'hr_designations';
    protected $fillable = [
        'name',
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
