<?php

namespace App\Model\HR;

use App\BaseModel;

class EmployeeReference extends BaseModel
{

    protected $table = 'hr_emp_ref_info';
    protected $fillable = [
        'empId',
        'name',
        'occupation',
        'companyName',
        'designation',
        'workingAddress',
        'nidNo',
        'relation',
        'mobile',
        'phone',
        'email',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

   
    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
