<?php

namespace App\Model\Acc;

use App\BaseModel;

class MisConfig extends BaseModel {
	// table name 
	protected $table = 'acc_mis_configuration';

	protected $fillable = [
		'mis_name',
		'table_field_name',
		'sales_type',
		'supplier_id',
		'company_id',
		'branch_id',
		'created_by',
		'updated_by'
	];

	public function salestype() {
		return $this->belongsTo('App\Model\Acc\MisType', 'sales_type', 'id');
	}
	public function supplier() {
		return $this->belongsTo('App\Model\POS\Supplier', 'supplier_id', 'id');
	}

	/* Here Insert Created By & Update By */
	public static function boot() {
		parent::boot();
	}
}
