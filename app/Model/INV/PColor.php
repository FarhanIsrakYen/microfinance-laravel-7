<?php

namespace App\Model\INV;

use App\BaseModel;

class PColor extends BaseModel
{

    protected $table = 'inv_p_colors';
    protected $fillable = [
        'company_id',
        'prod_group_id',
        'prod_cat_id',
        'prod_sub_cat_id',
        'prod_brand_id',
        'prod_model_id',
        'prod_size_id',
        'color_name',
        'branch_id',
        'created_by',
		'updated_by'
    ];

    public function company()
    {
        return $this->belongsTo('App\Model\GNL\Company', 'company_id', 'id');
    }

    public function pgroup()
    {
        return $this->belongsTo('App\Model\INV\PGroup', 'prod_group_id', 'id');
    }

    public function pcategory()
    {
        return $this->belongsTo('App\Model\INV\PCategory', 'prod_cat_id', 'id');
    }

    public function psubCategoty()
    {
        return $this->belongsTo('App\Model\INV\PSubCategory', 'prod_sub_cat_id', 'id');
    }

    public function pbrand()
    {
        return $this->belongsTo('App\Model\INV\PBrand', 'prod_brand_id', 'id');
    }

    public function pmodel()
    {
        return $this->belongsTo('App\Model\INV\PModel', 'prod_model_id', 'id');
    }

    public function psize()
    {
        return $this->belongsTo('App\Model\INV\PSize', 'prod_size_id', 'id');
    }

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
