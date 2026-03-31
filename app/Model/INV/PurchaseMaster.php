<?php

namespace App\Model\INV;

use App\BaseModel;

class PurchaseMaster extends BaseModel {
	protected $table = 'inv_purchases_m';

	protected $fillable = [
		'branch_id', 
		'bill_no', 
		'purchase_date', 
		'delivery_no', 
		'order_no', 
		'total_quantity', 
		'total_ordered_quantity', 
		'total_received_quantity', 
		'supplier_id', 
		'invoice_no', 
		'delivery_to', 
		'total_amount', 
		'discount_rate', 
		'discount_amount', 
		'ta_after_discount', 
		'vat_rate', 
		'vat_amount', 
		'total_payable_amount', 
		'paid_amount', 
		'due_amount', 
		'contact_person', 
		'remarks', 
		'is_completed', 
		'company_id', 
		'is_active', 
		'is_delete', 
		'created_at', 
		'created_by', 
		'updated_at', 
		'updated_by'
	];

	public function branch() {
		return $this->belongsTo('App\Model\GNL\Branch', 'branch_to', 'id');
	}
	public function company() {
		return $this->belongsTo('App\Model\GNL\Company', 'company_id', 'id');
	}
	public function supplier() {
		return $this->belongsTo('App\Model\INV\Supplier', 'supplier_id', 'id');
	}

	/* Here Insert Created By & Update By */
	public static function boot() {
		parent::boot();
	}
}
