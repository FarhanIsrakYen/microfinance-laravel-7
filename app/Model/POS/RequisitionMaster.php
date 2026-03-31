<?php

namespace App\Model\POS;

// use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class RequisitionMaster extends BaseModel
{
    // pos_requisitions_m
    protected $table = 'pos_requisitions_m';
    // public $timestamps = false;
    protected $fillable = [
        'company_id',
        'requisition_no',
        'branch_from',
        'branch_to',
        'supplier_id',
        'total_quantity',
        'requisition_date',
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
        return $this->belongsTo('App\Model\POS\Supplier', 'supplier_id', 'id');
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
