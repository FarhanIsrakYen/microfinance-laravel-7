<?php

namespace App\Model\POS;

// use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class RequisitionDetails extends BaseModel
{
    protected $table = 'pos_requisitions_d';
    public $timestamps = false;

    protected $fillable = [
        'requisition_no',
        'product_id',
        'branch_from',
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
