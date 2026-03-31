<?php

namespace App\Model\INV;

use App\BaseModel;

class PSubCategory extends BaseModel
{

    protected $table = 'inv_p_subcategories';
    protected $fillable = [
        'company_id',
        'prod_group_id',
        'prod_cat_id',
        'sub_cat_name',
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

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
