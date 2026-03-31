<?php

namespace App\Model\GNL;

use App\BaseModel;

class CompanyHoliday extends BaseModel
{
    protected $table = 'hr_holidays_comp';
    protected $fillable = [
        'company_id',
        'ch_title',
        'ch_date',
        'ch_day',
        'ch_description',
        'ch_eff_date',
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
