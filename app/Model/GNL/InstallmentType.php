<?php

namespace App\Model\GNL;

use App\BaseModel;

class InstallmentType extends BaseModel {
	protected $table = 'gnl_installment_type';

	public $timestamps = false;

	protected $fillable = [
		'name',
		'is_active',
	];

	/* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
