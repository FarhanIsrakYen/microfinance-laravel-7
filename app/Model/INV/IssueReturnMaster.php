<?php

namespace App\Model\INV;

use App\BaseModel;

class IssueReturnMaster extends BaseModel
{
    protected $table = 'inv_issues_r_m';

    protected $fillable = [
        'bill_no', 
        'return_date', 
        'branch_from', 
        'branch_to', 
        'total_quantity',
        'company_id', 
        'is_active', 
        'is_delete', 
        'created_at', 
        'created_by', 
        'updated_at', 
        'updated_by'
    ];

    public function branchFrom()
    {
        return $this->belongsTo('App\Model\GNL\Branch', 'branch_from', 'id');
    }

    public function branchTo()
    {
        return $this->belongsTo('App\Model\GNL\Branch', 'branch_to', 'id');
    }

    public function company()
    {
        return $this->belongsTo('App\Model\GNL\Company', 'company_id', 'id');
    }

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }
}
