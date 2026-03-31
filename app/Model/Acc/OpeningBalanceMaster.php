<?php

namespace App\Model\Acc;

use App\BaseModel;
// use Illuminate\Database\Eloquent\Model;

class OpeningBalanceMaster extends BaseModel
{

    protected $table = 'acc_ob_m';
    // public $timestamps = false;

    protected $fillable = [
        'is_year_end',
        'company_id',
        'ob_no',
        'project_id',
        'project_type_id',
        'branch_id',
        'fiscal_year_id',
        'opening_date',
        'total_debit_amount',
        'total_credit_amount',
        'total_balance',
        'total_cash_debit',
        'total_cash_dredit',
        'total_bank_debit',
        'total_bank_credit',
        'total_jv_debit',
        'total_jv_credit',
        'total_ft_debit',
        'total_ft_credit',
        'created_by',
		'updated_by'
    ];
    public function company()
    {
        return $this->belongsTo('App\Model\GNL\Company', 'company_id', 'id');
    }
    public function branch()
    {
        return $this->belongsTo('App\Model\GNL\Branch', 'branch_id', 'id');
    }
    public function project()
    {
        return $this->belongsTo('App\Model\GNL\Project', 'project_id', 'id');
    }
    public function projectType()
    {
        return $this->belongsTo('App\Model\GNL\ProjectType', 'project_type_id', 'id');
    }
    public function account_type()
    {
        return $this->belongsTo('App\Model\Acc\AccountType', 'acc_type_id', 'id');
    }

    /* Here Insert Created By & Update By */
    // public static function boot() {
    //     parent::boot();
    // }

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
