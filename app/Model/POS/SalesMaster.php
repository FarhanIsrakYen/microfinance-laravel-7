<?php

namespace App\Model\POS;

use App\BaseModel;

class SalesMaster extends BaseModel {
	protected $table = 'pos_sales_m';

	protected $fillable = [
		'sales_bill_no',
		'is_opening',
		'sales_type',
		'sales_date',
		'company_id',
		'branch_id',
		'customer_id',
		'employee_id',
		'installment_date',
		'customer_barcode',
		'vat_chalan_no',
		'customer_order_no',
		'total_quantity',
		'total_amount',
		'discount_rate',
		'discount_amount',
		'ta_after_vat',
		'ta_after_discount',
		'vat_rate',
		'vat_amount',
		'total_payable_amount',
		'paid_amount',
		'due_amount',
		'payment_system_id',
		'inst_package_id',
		'installment_month',
		'installment_type',
		'installment_rate',
		'installment_amount',
		'instalment_actual',
		'instalment_extra',
		'service_charge',
		'fiscal_year_id',
		'cash_price',
		'principal_amount',
		'installment_profit',
		'customer_mobile',
		'customer_nid',
		'is_complete',
		'complete_date',
		'is_active',
		'is_delete',
		'created_at',
		'created_by',
		'updated_at',
		'updated_by',
		'total_cost_amount'
	];

	public function branch() {
		return $this->belongsTo('App\Model\GNL\Branch', 'branch_id', 'id');
	}
	public function customer() {
		return $this->belongsTo('App\Model\POS\Customer', 'customer_id', 'customer_no');
	}
	public function employee() {
		return $this->belongsTo('App\Model\HR\Employee', 'employee_id', 'employee_no');
	}

	public function inst_package() {
		return $this->belongsTo('App\Model\POS\PInstallmentPackage', 'inst_package_id', 'id');
	}

	public function inst_type() {
		return $this->belongsTo('App\Model\GNL\InstallmentType', 'installment_type', 'id');
	}

	/* Here Insert Created By & Update By */
	public static function boot() {
		parent::boot();
	}
}
