<?php

namespace App\Model\BILL;

use App\BaseModel;

class Product extends BaseModel
{

    protected $table = 'bill_products';
    protected $fillable = [
        'id',
        'company_id',
        'supplier_id',
        'prod_cat_id',
        'product_name',
        'sale_price',
        'prod_image',
        'prod_vat',
        'prod_desc',
        'created_by',
		'updated_by'
    ];

    public function supplier()
    {
        return $this->belongsTo('App\Model\BILL\Supplier', 'supplier_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo('App\Model\BILL\PCategory', 'prod_cat_id', 'id');
    }
    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
