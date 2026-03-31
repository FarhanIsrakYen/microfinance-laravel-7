<?php

namespace App\Model\BILL;

use App\BaseModel;

class AgreementMaster extends BaseModel {
	protected $table = 'bill_agreement_m';

	protected $fillable = [
		'branch_id',
		'agreement_no',
		'agreement_date',
		'agreement_end_date',
		'agreement_type',
		'service_start_date',
		'no_of_branch',
		'customer_id',
		'sales_by',
		'service_by',
		'total_license_fee',
		'total_service_fee',
		'total_amount',
		'company_id',
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
	public function salesBy() {
		return $this->belongsTo('App\Model\HR\Employee', 'sales_by', 'id');
	}
	public function serviceBy() {
		return $this->belongsTo('App\Model\HR\Employee', 'service_by', 'id');
	}

	/* Here Insert Created By & Update By */
	public static function boot() {
		parent::boot();
	}
}
