<?php

namespace App\Model\Acc;

use App\BaseModel;

class OpeningBalanceDetails extends BaseModel
{
    //
    //
    protected $table = 'acc_ob_d';
    public $timestamps = false;

    protected $fillable = [
        'ob_no',
        'ledger_id',
        'branch_id',

        'debit_amount',
        'credit_amount',
        'balance_amount',
        'cash_debit',
        'cash_credit',
        'bank_debit',
        'bank_credit',
        'jv_debit',
        'jv_credit',
        'ft_debit',
        'ft_credit',

    ];

    public function ledger()
    {
        return $this->belongsTo('App\Model\Acc\Ledger', 'ledger_id', 'id');
    }
    // public function account_type() {
    //     return $this->belongsTo('App\Model\Acc\AccountType', 'acc_type_id', 'id');
    // }

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }
}
