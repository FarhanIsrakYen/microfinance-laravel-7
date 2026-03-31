<?php

namespace App\Model\HR;

use App\BaseModel;

class EmployeeExperience extends BaseModel
{

    protected $table = 'hr_emp_exp_info';
    protected $fillable = [
        'empId',
        'name',
        'type',
        'location',
        'fullAddress',
        'designation',
        'departmentOrProject',
        'jobResponsibility',
        'areaOfExp',
        'firstWorkingDate',
        'lastWorkingDate',
        'jobDuration',
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
