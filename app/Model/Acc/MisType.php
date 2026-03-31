<?php

namespace App\Model\Acc;

use App\BaseModel;

class MisType extends BaseModel {
	protected $table = 'acc_mis_type';

	protected $fillable = [
		'name',
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
