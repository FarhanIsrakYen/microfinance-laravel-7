<?php

namespace App\Model\INV;

// use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class OrderDetails extends BaseModel
{
    protected $table = 'inv_orders_d';

    public $timestamps = false;

    protected $fillable = [
        'order_no',
        'requisition_no',
        'requisition_date',
        'product_id',
        'product_quantity',
        'requisition_branch_from',
    ];

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }
}
