<?php

namespace App\Model\BILL;

use App\BaseModel;

class PCategory extends BaseModel
{

    protected $table = 'bill_p_categories';
    protected $fillable = [
        'company_id',
        'cat_name',
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
