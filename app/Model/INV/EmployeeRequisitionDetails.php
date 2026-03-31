<?php

namespace App\Model\INV;

// use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class EmployeeRequisitionDetails extends BaseModel
{
    protected $table = 'inv_requisitions_emp_d';
    public $timestamps = false;

    protected $fillable = [
        'requisition_no',
        'product_id',
        'product_quantity',
        'total_product_quantity',
        'is_complete',
        'is_ordered'
    ];

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }
}
