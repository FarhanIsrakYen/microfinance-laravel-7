<?php

namespace App\Model\BILL;

use App\BaseModel;

class AgreementDetails extends BaseModel {
	protected $table = 'bill_agreement_d';

	protected $fillable = [
		'agreement_no',
		'product_id',
		'product_type',
		'license_fee_ho',
		'license_fee_br',
		'license_fee',
		'service_fee_ho',
		'service_fee_br',
		'service_fee',
		'total_amount',
	];

	/* Here Insert Created By & Update By */
	public static function boot() {
		parent::boot();
	}
	public $timestamps = false;
}
