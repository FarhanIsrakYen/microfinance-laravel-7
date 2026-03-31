<?php

namespace App\Model\INV;

// use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class PurchaseReturnDetails extends BaseModel {

	protected $table = 'inv_purchases_r_d';

	public $timestamps = false;

	protected $fillable = [
		'pr_bill_no', 
		'product_id', 
		'product_quantity', 
		'unit_cost_price', 
		'total_cost_price'
	];

	public function product() {
		return $this->belongsTo('App\Model\INV\Product', 'product_id', 'id');
	}

	/* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
