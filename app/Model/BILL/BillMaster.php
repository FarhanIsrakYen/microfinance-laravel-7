<?php

namespace App\Model\BILL;

use App\BaseModel;

class BillMaster extends BaseModel {
	protected $table = 'bill_cash_m';

	protected $fillable = [
		'bill_no',
		'agreement_no',
		'is_opening',
		'bill_date',
		'bill_month',
		'company_id',
		'branch_id',
		'customer_id',
		'employee_id',
		'customer_order_no',
		'total_quantity',
		'total_amount',
		'discount_rate',
		'discount_amount',
		'ta_after_discount',
		'vat_rate',
		'vat_amount',
		'gross_total',
		'service_charge',
		'fiscal_year_id',
		'is_active',
		'is_delete',
		'created_at',
		'created_by',
		'updated_at',
		'updated_by'
	];

	public function branch() {
		return $this->belongsTo('App\Model\GNL\Branch', 'branch_id', 'id');
	}
	public function customer() {
		return $this->belongsTo('App\Model\BILL\Customer', 'customer_id', 'customer_no');
	}
	public function employee() {
		return $this->belongsTo('App\Model\HR\Employee', 'employee_id', 'employee_no');
	}


	/* Here Insert Created By & Update By */
	public static function boot() {
		parent::boot();
	}
}
