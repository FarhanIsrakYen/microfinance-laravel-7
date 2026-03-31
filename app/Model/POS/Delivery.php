<?php

namespace App\Model\POS;

// use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class Delivery extends BaseModel
{
    protected $table = 'pos_deliveries';

    protected $fillable = [
    	'company_id',
    	'branch_id',
    	'order_id',
    	'requisition_id',
    	'delivery_no',
    	'is_active',
		'is_delete',
		'created_by',
		'updated_by'
	];
	
	public $timestamps = false;

	/* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }
}
