<?php

namespace App\Model\POS;

use App\BaseModel;

class PInstallmentPackage extends BaseModel
{
    protected $table = 'pos_inst_packages';

    protected $fillable = [
        'company_id',
        'prod_inst_month',
        'prod_inst_profit',
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
