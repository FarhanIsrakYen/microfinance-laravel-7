<?php

namespace App\Model\INV;

// use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class MonthEnd extends BaseModel
{
    protected $table = 'inv_month_end';

    protected $fillable = [
        'monthend_no', 
        'branch_id', 
        'branch_code', 
        'month_date', 
        'month_start_date', 
        'month_end_date', 
        'total_working_day', 
        'total_current_month_customer', 
        'total_customer', 
        'total_current_month_product_quantity', 
        'total_product_quantity', 
        'total_current_month_purchases', 
        'total_purchases',
        'total_current_month_balance', 
        'total_balance', 
        'company_id', 
        'is_active', 
        'is_delete',
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
