<?php

namespace App\Model\POS;

use App\BaseModel;
use App\Model\GNL\Company;
use App\Model\POS\PBrand;
use App\Model\POS\PCategory;
use App\Model\POS\PGroup;
use App\Model\POS\PSubCategory;

class PModel extends BaseModel
{
    protected $table = 'pos_p_models';

    protected $fillable = [
        'company_id',
        'prod_group_id',
        'prod_cat_id',
        'prod_sub_cat_id',
        'prod_brand_id',
        'model_name',
        'created_by',
		'updated_by'
    ];

    public function company()
    {
        return $this->belongsTo('App\Model\GNL\Company', 'company_id', 'id');
    }
    public function pgroup()
    {
        return $this->belongsTo(PGroup::class, 'prod_group_id', 'id');
    }

    public function pcategory()
    {
        return $this->belongsTo(PCategory::class, 'prod_cat_id', 'id');
    }

    public function psubcategory()
    {
        return $this->belongsTo(PSubCategory::class, 'prod_sub_cat_id', 'id');
    }

    public function pbrand()
    {
        return $this->belongsTo(PBrand::class, 'prod_brand_id', 'id');
    }

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }
}
