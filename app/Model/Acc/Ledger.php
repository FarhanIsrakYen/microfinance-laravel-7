<?php

namespace App\Model\Acc;

// use Illuminate\Database\Eloquent\Model;

use App\BaseModel;

class Ledger extends BaseModel
{
    //
	protected $table = 'acc_account_ledger';

	protected $fillable = [
		'name',
		'code',
		'sys_code',
		'description',
		'acc_type_id',
		'order_by',
        'parent_id',
        'is_group_head',
        'level',
        'company_id',
		'branch_arr',
		'project_arr',
		'level',
		'created_by',
		'updated_by'

	];

	

	public function account_type() {
		return $this->belongsTo('App\Model\Acc\AccountType', 'acc_type_id', 'id');
	}
	public function parent() {
		return $this->belongsTo('App\Model\Acc\Ledger', 'parent_id', 'id');
	}
	

	/* Here Insert Created By & Update By */
	public static function boot() {
		parent::boot();
	}
}
