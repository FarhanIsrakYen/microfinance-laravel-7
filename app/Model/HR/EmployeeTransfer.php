<?php

namespace App\Model\HR;

use App\BaseModel;

class EmployeeTransfer extends BaseModel
{

    protected $table = 'hr_employee_transfer';

    protected $fillable = [
        'employee_no',
        'company_id',
        'branch_from',
        'branch_to',
        'transfer_date',
        
        'is_active',
        'is_delete',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',

        'is_approved',
        'approved_by'
    ];

    // /* Here Insert Created By & Update By */
    // public static function boot()
    // {
    //     parent::boot();
    // }
}
