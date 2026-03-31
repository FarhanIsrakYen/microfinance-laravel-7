<?php

namespace App\Model\Acc;

use App\BaseModel;

class VoucherType extends BaseModel {
	//
	//
	protected $table = 'acc_voucher_type';

	protected $fillable = [
		'name',
		'title_name',
		'short_name',
		'company_id',
		'branch_id',
		'created_by',
		'updated_by'
	];

	/* Here Insert Created By & Update By */
	public static function boot() {
		parent::boot();
	}
}
