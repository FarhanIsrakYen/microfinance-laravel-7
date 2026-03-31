<?php

namespace App\Model\BILL;

use App\BaseModel;

class Collection extends BaseModel {

	protected $table = 'bill_collections';

	protected $fillable = [
		'company_id',
		'branch_id',
		'bill_no',
		'collection_amount',
		'collection_date',
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
		'updated_by'
	];

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
