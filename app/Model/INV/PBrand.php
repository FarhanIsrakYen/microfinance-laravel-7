<?php

namespace App\Model\INV;

use App\BaseModel;

class PBrand extends BaseModel
{
    protected $table = 'inv_p_brands';

    protected $fillable = [
        'company_id',
        'prod_group_id',
        'prod_cat_id',
        'prod_sub_cat_id',
        'brand_name',
        'branch_id',
        'created_by',
		'updated_by'
    ];

    public function company()
    {
        return $this->belongsTo('App\Model\GNL\Company', 'company_id', 'id');
    }

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }
}
