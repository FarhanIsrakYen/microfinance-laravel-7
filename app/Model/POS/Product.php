<?php

namespace App\Model\POS;

use App\BaseModel;

class Product extends BaseModel
{

    protected $table = 'pos_products';
    protected $fillable = [
        'id',
        'company_id',
        'supplier_id',
        'prod_cat_id',
        'prod_brand_id',
        'prod_size_id',
        'product_name',
        'cost_price',
        'sale_price',
        'prod_image',
        'min_stock',
        'prod_vat',
        'prod_barcode',
        'barcode_image',
        'sys_barcode',
        'prod_group_id',
        'prod_sub_cat_id',
        'prod_model_id',
        'prod_color_id',
        'prod_uom_id',
        'warranty',
        'service_warranty',
        'compresse_warranty',
        'prod_desc',
        'created_by',
		'updated_by'
    ];

    public function supplier()
    {
        return $this->belongsTo('App\Model\POS\Supplier', 'supplier_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo('App\Model\POS\PCategory', 'prod_cat_id', 'id');
    }

    public function subcategory()
    {
        return $this->belongsTo('App\Model\POS\PSubCategory', 'prod_sub_cat_id', 'id');
    }

    public function brand()
    {
        return $this->belongsTo('App\Model\POS\PBrand', 'prod_brand_id', 'id');
    }

    public function size()
    {
        return $this->belongsTo('App\Model\POS\PSize', 'prod_size_id', 'id');
    }

    public function pgroup()
    {
        return $this->belongsTo('App\Model\POS\PGroup', 'prod_group_id', 'id');
    }

    public function model()
    {
        return $this->belongsTo('App\Model\POS\PModel', 'prod_model_id', 'id');
    }

    public function color()
    {
        return $this->belongsTo('App\Model\POS\PColor', 'prod_color_id', 'id');
    }

    public function uom()
    {
        return $this->belongsTo('App\Model\POS\PUOM', 'prod_uom_id', 'id');
    }

    // public function company() {
    //     return $this->belongsTo('App\Model\GNL\Company', 'company_id', 'id');
    // }

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
