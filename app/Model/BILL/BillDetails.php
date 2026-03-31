<?php

namespace App\Model\BILL;

// use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class BillDetails extends BaseModel {

	protected $table = 'bill_cash_d';

	public $timestamps = false;

	protected $fillable = [
		'bill_no',
		'product_id',
		'product_type',
		'branch_id',
		'product_serial_no',
		'product_quantity',
		'product_unit_price',
		'product_sales_price',
		'total_amount'
	];

	/* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
