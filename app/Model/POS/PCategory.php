<?php

namespace App\Model\POS;

use App\BaseModel;
use App\Model\POS\PGroup;

class PCategory extends BaseModel
{

    protected $table = 'pos_p_categories';
    protected $fillable = [
        'company_id',
        'prod_group_id',
        'cat_name',
        'created_by',
		'updated_by'
    ];

    public function pgroup()
    {
        return $this->belongsTo(PGroup::class, 'prod_group_id', 'id');
    }

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
