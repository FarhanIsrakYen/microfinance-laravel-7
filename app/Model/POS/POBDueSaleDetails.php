<?php

namespace App\Model\POS;

// use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class POBDueSaleDetails extends BaseModel
{
    protected $table = 'pos_ob_duesales_d';

    protected $fillable = [
        'ob_no',
        // 'company_id',
        'branch_id',
        // 'opening_date',
        'customer_id',
        // 'customer_name',
        // 'customer_no',
        'sales_bill_no',
        'sales_products',
        'sales_amount',
        'collection_amount',
        'due_amount',
        'installment_amount',
        'installment_month',
        'installment_type',
        'sales_date',
        'last_collection_date',
        // 'is_active',
        // 'is_delete',
    ];
    public $timestamps = false;

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
