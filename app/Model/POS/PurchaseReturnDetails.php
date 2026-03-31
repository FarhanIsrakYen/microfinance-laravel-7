<?php

namespace App\Model\POS;

// use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class PurchaseReturnDetails extends BaseModel {

	protected $table = 'pos_purchases_r_d';

	public $timestamps = false;

	protected $fillable = [
		'pr_bill_no', 
		'product_id', 
		'product_quantity', 
		'unit_cost_price', 
		'total_cost_price'
	];

	public function product() {
		return $this->belongsTo('App\Model\POS\Product', 'product_id', 'id');
	}

	/* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
