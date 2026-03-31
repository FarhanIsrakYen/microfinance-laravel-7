<?php

namespace App\Model\HR;

use App\BaseModel;

class EmployeeEducation extends BaseModel
{

    protected $table = 'hr_emp_edu_info';
    protected $fillable = [
        'empId',
        'certificateTitle',
        'groupName',
        'instituteName',
        'boardOrInstituteName',
        'resultSystem',
        'cgpaOrGrade',
        'outOf',
        'passingYear',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'is_delete',
    ];

   
    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
