<?php

namespace App\Model\POS;

use App\BaseModel;

class SaleReturnm extends BaseModel
{
    protected $table = 'pos_sales_return_m';

    protected $fillable = [
        'company_id',
        'branch_id',
        'return_bill_no',
        'return_date',
        'total_return_quantity',
        'total_return_amount',
        'sales_id',
        'sales_bill_no',
        'sales_type',
        'sales_date',
        'total_sales_payable_amount',
        'sales_paid_amount',
        'sales_due_amount',
        'sales_payment_system_id',
        'payment_month',
        'payable_return_amount',
        'fiscal_year_id',
        'customer_id',
        'customer_barcode',
        'employee_id',
        'is_active',
        'is_delete',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

    public function branch()
    {
        return $this->belongsTo('App\Model\GNL\Branch', 'branch_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo('App\Model\HR\Employee', 'employee_id', 'employee_no');
    }
}
