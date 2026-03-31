<?php

namespace App\Model\Acc;

use App\BaseModel;

class AccountType extends BaseModel {
	//
	protected $table = 'acc_account_type';

	protected $fillable = [
		'name',
		'code',
		'description',
		'parent_id',
		'is_parent',
		'company_id',
		'branch_id',
		'created_by',
		'updated_by'
		
	];

	public function GrandParent() {
		return $this->belongsTo('App\Model\Acc\AccountType', 'parent_id', 'id');
	}

	/* Here Insert Created By & Update By */
	public static function boot() {
		parent::boot();
	}
}
