<?php

namespace App\Model\HR;

use App\BaseModel;

class EmpDepartment extends BaseModel
{

    protected $table = 'hr_departments';
    protected $fillable = [
        'company_id',
        'dept_name',
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
