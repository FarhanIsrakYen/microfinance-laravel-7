<?php
namespace App\Model\Acc;

use App\BaseModel;

class AutoVoucherConfig extends BaseModel
{
     // table name 
	protected $table = 'acc_auto_voucher_config';

	protected $fillable = [
		'config_id',
		'sales_type',
		'voucher_type',
		'amount_type',
		'ledger_id',
        'ledger_code',
        'local_narration',
        'mis_config_id',
        'mis_config_name',
		'table_field_name',
		'supplier_id',
        'company_id',
		'branch_id',
		'created_by',
		'updated_by'

	];

	public function ledger() {
		return $this->belongsTo('App\Model\Acc\Ledger', 'ledger_code', 'code');
	}
	
	public function salestype() {
		return $this->belongsTo('App\Model\Acc\MisType', 'sales_type', 'id');
	}
	
	public function vouchertype() {
		return $this->belongsTo('App\Model\Acc\VoucherType', 'voucher_type', 'id');
	}

	/* Here Insert Created By & Update By */
	public static function boot() {
		parent::boot();
	}
}
