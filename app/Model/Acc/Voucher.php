<?php

namespace App\Model\Acc;

use App\BaseModel;

class Voucher extends BaseModel
{
    protected $table = 'acc_voucher';

    protected $fillable = [
        'voucher_type_id',
        'project_id',
        'project_type_id',
        'voucher_code',
        'voucher_date',
        'module_id',
        'total_amount',
        'global_narration',
        'reference_id',
        'v_generate_type',
        'prep_by',
        'auth_by',
        'appr_by',
        'ft_id',
        'ft_from',
        'company_id',
        'branch_id',
        'created_by',
        'updated_by',
        'voucher_status'
    ];

    public function UserName()
    {
        return $this->belongsTo('App\Model\GNL\SysUser', 'prep_by', 'id');
    }
    public function voucherType()
    {
        return $this->belongsTo('App\Model\Acc\VoucherType', 'voucher_type_id', 'id');
    }
    public function projectType()
    {
        return $this->belongsTo('App\Model\GNL\ProjectType', 'project_type_id', 'id');
    }
    public function project()
    {
        return $this->belongsTo('App\Model\GNL\Project', 'project_id', 'id');
    }

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
