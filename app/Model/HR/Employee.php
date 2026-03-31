<?php

namespace App\Model\HR;

use App\BaseModel;

class Employee extends BaseModel
{

    protected $table = 'hr_employees';
    protected $fillable = [
        'company_id',
        'branch_id',
        'emp_code',
        'employee_no',
        'emp_name',
        'emp_father_name',
        'emp_mother_name',
        'designation_id',
        'department_id',
        'emp_gender',
        'emp_email',
        'emp_phone',
        'emp_present_addr',
        'emp_parmanent_addr',
        'emp_dob',
        'emp_id_type',
        'emp_national_id',
        'emp_description',
        'created_by',
		'updated_by'
    ];

    public function branch()
    {
        return $this->belongsTo('App\Model\GNL\Branch', 'branch_id', 'id');
    }

    public function company()
    {
        return $this->belongsTo('App\Model\GNL\Company', 'company_id', 'id');
    }

    public function designation()
    {
        return $this->belongsTo('App\Model\HR\EmployeeDesignation', 'designation_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo('App\Model\HR\EmpDepartment', 'department_id', 'id');
    }

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
