<?php

namespace App\Model\GNL;

use App\BaseModel;

class GovtHoliday extends BaseModel
{
    protected $table = 'hr_holidays_govt';
    protected $fillable = [
        'company_id',
        'gh_title',
        'gh_date',
        'gh_description',
        'efft_start_date',
        'efft_end_date',
        'created_by',
		'updated_by'
    ];

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }
}
