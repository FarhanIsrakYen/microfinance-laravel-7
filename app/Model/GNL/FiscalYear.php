<?php

namespace App\Model\GNL;

use App\BaseModel;

class FiscalYear extends BaseModel
{

    protected $table = 'gnl_fiscal_year';
    protected $fillable = [
        'company_id',
        'fy_name',
        'fy_start_date',
        'fy_end_date',
        'created_by',
		'updated_by'
    ];

    public function company()
    {
        //    return $this->('App\Phone');
        return $this->belongsTo('App\Model\GNL\Company', 'company_id', 'id');
    }

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
