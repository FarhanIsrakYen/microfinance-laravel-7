<?php

namespace App\Model\BILL;

use App\BaseModel;

class Package extends BaseModel
{

    protected $table = 'bill_packages';
    protected $fillable = [
        'id',
        'company_id',
        'package_name',
        'package_products',
        'package_price',
        'created_by',
		'updated_by'
    ];
    // public function product()
    // {
    //     return $this->belongsTo('App\Model\BILL\Product', 'product_id', 'id');
    // }
    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
