<?php

namespace App\Model\INV;

// use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class EmployeeRequisitionMaster extends BaseModel
{
    // pos_requisitions_m
    protected $table = 'inv_requisitions_emp_m';
    // public $timestamps = false;
    protected $fillable = [
        'company_id',
        'requisition_no',
        'emp_from',
        'branch_id',
        'supplier_id',
        'total_quantity',
        'requisition_date',
        'requisition_for',
        'dept_id',
        'room_id',
        'remarks',
        'is_approve',
        'is_active',
        'is_delete',
        'is_complete',
        'is_ordered',
        'created_by',
        'updated_by',

    ];

    public function supplier()
    {
        return $this->belongsTo('App\Model\INV\Supplier', 'supplier_id', 'id');
    }

    public function branchfrom()
    {
        return $this->belongsTo('App\Model\GNL\Branch', 'branch_from', 'id');
    }

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }
}
