<?php

namespace App\Model\POS;

use App\BaseModel;

class DayEnd extends BaseModel
{
    //
    protected $table = 'pos_day_end';
    protected $fillable = [
        'dayend_no',
        'branch_id',
        'branch_code',
        'branch_date',
        'day_start_date',
        'day_end_date',
        'next_working_day',
        'total_customer',
        'total_product_quantity',
        'total_sales_amount',
        'total_collection',
        'total_purchases',
        'total_due',
        'company_id',
        'is_active',
        'created_by',
		'updated_by'

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
