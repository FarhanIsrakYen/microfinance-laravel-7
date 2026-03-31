<?php

namespace App\Model\INV;

use App\BaseModel;

class UsesDetails extends BaseModel {

	protected $table = 'inv_use_d';

	public $timestamps = false;

	protected $fillable = [
		'uses_bill_no', 
		'product_id', 
		'branch_id',
		'product_serial_no', 
		'product_quantity'
	];

	/* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
