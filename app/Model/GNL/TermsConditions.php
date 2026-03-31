<?php

namespace App\Model\GNL;

use App\BaseModel;

class TermsConditions extends BaseModel
{

    protected $table = 'gnl_terms_conditions';
    protected $fillable = [
        'company_id',
        'tc_name',
        'tc_remarks',
        'created_by',
		'updated_by'
    ];

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
