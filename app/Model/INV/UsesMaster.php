<?php

namespace App\Model\INV;

use App\BaseModel;

class UsesMaster extends BaseModel {
	protected $table = 'inv_use_m';

	protected $fillable = [
		'uses_bill_no',
		'is_opening',
		'uses_date',
		'company_id',
		'branch_id',
		'employee_no',
		'department_id',
		'requisition_for',
        'room_id',
		'total_quantity',
		'fiscal_year_id',
		'is_active',
		'is_delete',
		'created_at',
		'created_by',
		'updated_at',
		'updated_by'
	];

	public function branch() {
		return $this->belongsTo('App\Model\GNL\Branch', 'branch_id', 'id');
	}

	public function employee() {
		return $this->belongsTo('App\Model\HR\Employee', 'employee_no', 'employee_no');
	}

	public function department() {
		return $this->belongsTo('App\Model\HR\EmpDepartment', 'department_id', 'id');
	}

	public function room() {
		return $this->belongsTo('App\Model\HR\Room', 'room_id', 'id');
	}

	/* Here Insert Created By & Update By */
	public static function boot() {
		parent::boot();
	}
}
