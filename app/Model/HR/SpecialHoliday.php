<?php

namespace App\Model\HR;

use App\BaseModel;

class SpecialHoliday extends BaseModel
{
    protected $table = 'hr_holidays_special';
    protected $fillable = [
        'company_id',
        'branch_id',
        'sh_title',
        'sh_app_for',
        'sh_date_from',
        'sh_date_to',
        'sh_description',
        'created_by',
		'updated_by'
    ];

    public function company()
    {
        return $this->belongsTo('App\Model\GNL\Company', 'company_id', 'id');
    }
    public function branch()
    {
        return $this->belongsTo('App\Model\GNL\Branch', 'branch_id', 'id');
    }

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }
}
