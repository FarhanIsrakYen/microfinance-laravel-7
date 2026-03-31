<?php

namespace App\Model\Acc;

use App\BaseModel;

class AccDayEnd extends BaseModel
{
    protected $table = 'acc_day_end';
    protected $fillable = [

        'branch_date',
        'start_date',
        'end_date',
        'branch_id',
        'fiscal_year_id',
        'company_id',
        'is_active',
        'created_by',
        'updated_by',

    ];

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
