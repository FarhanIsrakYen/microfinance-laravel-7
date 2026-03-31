<?php

namespace App\Model\POS;

use App\BaseModel;

class Collection extends BaseModel {
	
	protected $table = 'pos_collections';

	protected $fillable = [
		'company_id', 
		'branch_id', 
		'sales_bill_no', 
		'collection_amount', 
		'collection_date', 
		'cash_price', 
		'principal_amount', 
		'installment_profit', 
		'payment_system_id', 
		'customer_id', 
		'employee_id', 
		'fiscal_year_id', 
		'collection_no',
		'is_active', 
		'is_delete', 
		'created_at', 
		'created_by', 
		'updated_at', 
		'updated_by',
		'sales_type'
	];

	public function customer() {
		return $this->belongsTo('App\Model\POS\Customer', 'customer_id', 'customer_no');
	}

	// public function branch() {
	// 	return $this->belongsTo('App\Model\GNL\Branch', 'branch_id', 'id');
	// }

	/* Here Insert Created By & Update By */
	public static function boot() {
		parent::boot();
	}
}
