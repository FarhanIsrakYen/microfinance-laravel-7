<?php

namespace App\Model\POS;

// use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class OrderMaster extends BaseModel
{
    protected $table = 'pos_orders_m';
    // public $timestamps = false;
    protected $fillable = [
        'company_id',
        'order_no',
        'order_date',
        'delivery_date',
        'order_from',
        'order_to',
        'total_quantity',
        'remarks',
        'is_approve',
        'is_delivered',
        'is_completed',
        'is_active',
        'is_delete',
        'delivery_place',
        'created_by',
        'updated_by'
    ];

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }
}
