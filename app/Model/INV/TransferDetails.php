<?php

namespace App\Model\INV;

// use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class TransferDetails extends BaseModel {
	protected $table = 'inv_transfers_d';

	protected $fillable = [
		'transfer_bill_no',
		'product_id',
		'product_quantity',
		'branch_from',
		'branch_to'
	];
	
	public $timestamps = false;

	public function product() {
		return $this->belongsTo('App\Model\INV\Product', 'product_id', 'id');
	}

	/* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }
}
