<?php

namespace App\Model\INV;

use App\BaseModel;

class PurchaseDetails extends BaseModel {

	protected $table = 'inv_purchases_d';

	public $timestamps = false;

	protected $fillable = [
		'purchase_bill_no', 
		'product_id', 
		'unit_cost_price', 
		'product_quantity', 
		'ordered_quantity', 
		'received_quantity', 
		'total_cost_price', 
		'is_completed'
	];

	/* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }
}
