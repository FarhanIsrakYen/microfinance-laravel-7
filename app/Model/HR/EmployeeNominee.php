<?php

namespace App\Model\HR;

use App\BaseModel;

class EmployeeNominee extends BaseModel
{

    protected $table = 'hr_emp_nominee_info';
    protected $fillable = [
        'empId',
        'name',
        'address',
        'relation',
        'percentage',
        'mobile',
        'nidNo',
        'nomineeImage',
        'signatureImage',
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
