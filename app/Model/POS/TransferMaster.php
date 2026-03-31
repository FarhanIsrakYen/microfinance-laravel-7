<?php

namespace App\Model\POS;

use App\BaseModel;

class TransferMaster extends BaseModel {
	protected $table = 'pos_transfers_m';

	protected $fillable = [
		'company_id',
		'bill_no',
		'order_no',
		'transfer_date',
		'branch_from',
		'branch_to',
		'total_quantity',
		'total_amount',
		'created_by',
		'updated_by'
	];

	public function company() {
		return $this->belongsTo('App\Model\GNL\Company', 'company_id', 'id');
	}
	public function branchFrom() {
		return $this->belongsTo('App\Model\GNL\Branch', 'branch_from', 'id');
	}
	public function branchTo() {
		return $this->belongsTo('App\Model\GNL\Branch', 'branch_to', 'id');
	}

	/* Here Insert Created By & Update By */
	public static function boot() {
		parent::boot();
	}
}
