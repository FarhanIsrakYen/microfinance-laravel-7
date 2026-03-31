<?php

namespace App\Model\Acc;

use App\BaseModel;

class AccYearEnd extends BaseModel {
	//

	protected $table = 'acc_year_end';
	protected $fillable = [

		'date',
		'start_date',
		'end_date',
		'fiscal_year_id',
		'branch_id',
		'company_id',
		'total_day',
		'is_active',
		'created_by',
		'updated_by'

	];

	public function branch() {
		return $this->belongsTo('App\Model\GNL\Branch', 'branch_id', 'id');
	}

	/* Here Insert Created By & Update By */
	public static function boot() {
		parent::boot();
	}
}
