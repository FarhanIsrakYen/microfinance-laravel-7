<?php

namespace App\Model\POS;

use App\BaseModel;

class PurchaseReturnMaster extends BaseModel {
	
	protected $table = 'pos_purchases_r_m';

	protected $fillable = [
		'company_id', 
		'bill_no', 
		'supplier_id', 
		'branch_id', 
		'return_date', 
		'total_quantity', 
		'total_amount', 
		'is_active', 
		'is_delete', 
		'created_at', 
		'created_by', 
		'updated_at', 
		'updated_by'
	];

	public function company() {
		return $this->belongsTo('App\Model\GNL\Company', 'company_id', 'id');
	}

	public function supplier() {
		return $this->belongsTo('App\Model\POS\Supplier', 'supplier_id', 'id');
	}

	public function branch() {
		return $this->belongsTo('App\Model\GNL\Branch', 'branch_id', 'id');
	}

	/* Here Insert Created By & Update By */
	public static function boot() {
		parent::boot();
	}
}
