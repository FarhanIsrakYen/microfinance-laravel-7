<?php

namespace App\Model\POS;

// use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class SalesDetails extends BaseModel {

	protected $table = 'pos_sales_d';

	public $timestamps = false;

	protected $fillable = [
		'sales_bill_no', 
		'product_id', 
		'branch_id',
		'product_serial_no', 
		'product_quantity', 
		'product_cost_price',
		'product_unit_price', 
		'total_sales_price',
		'product_barcode', 
		'product_system_barcode',
		'total_cost_price', 
		'warranty', 
		'service_warranty', 
		'compresser_warranty', 
		'cash_price', 
		'principal_amount', 
		'installment_profit'
	];

	/* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
