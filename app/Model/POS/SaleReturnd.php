<?php

namespace App\Model\POS;

// use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class SaleReturnd extends BaseModel
{
    protected $table = 'pos_sales_return_d';

    public $timestamps = false;

    protected $fillable = [
        'return_bill_no',
        'product_id',
        'branch_id',
        'product_quantity',
        'product_cost_price',
        'product_sales_price',
        'total_amount',
        'product_barcode',
        'product_system_barcode',
        'payment_month',
        'warranty',
        'service_warranty',
        'compresser_warranty',
        'return_reason',
        'return_description',
    ];

    public function product()
    {
        return $this->belongsTo('App\Model\POS\Product', 'product_id', 'id');
    }

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }
}
