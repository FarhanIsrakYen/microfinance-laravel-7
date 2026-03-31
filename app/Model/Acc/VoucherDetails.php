<?php

namespace App\Model\Acc;

// use Illuminate\Database\Eloquent\Model;

use App\BaseModel;

class VoucherDetails extends BaseModel
{
    protected $table = 'acc_voucher_details';

    protected $fillable = [
        // 'branch_id',
        'voucher_id',
        'debit_acc',
        'credit_acc',
        'amount',

        'local_narration',
        'ft_from',
        'ft_to',
        'ft_target_acc',

    ];

    public $timestamps = false;

    public function branch_to()
    {
        return $this->belongsTo('App\Model\GNL\Branchs', 'ft_to', 'id');
    }
    public function branch_from()
    {
        return $this->belongsTo('App\Model\GNL\Branchs', 'ft_from', 'id');
    }

    public function targetAcc()
    {
        return $this->belongsTo('App\Model\Acc\Ledger', 'ft_target_acc', 'id');
    }
    public function LedgerDebit()
    {
        return $this->belongsTo('App\Model\Acc\Ledger', 'debit_acc', 'id');
    }
    public function LedgerCredit()
    {
        return $this->belongsTo('App\Model\Acc\Ledger', 'credit_acc', 'id');
    }

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }
}
