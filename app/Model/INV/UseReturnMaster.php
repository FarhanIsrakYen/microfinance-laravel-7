<?php

namespace App\Model\INV;

use App\BaseModel;

class UseReturnMaster extends BaseModel
{
    protected $table = 'inv_use_return_m';

    protected $fillable = [
        'company_id', 
        'branch_id', 
        'return_bill_no', 
        'return_date', 
        'total_return_quantity', 
        'uses_bill_no',
        'uses_date', 
        'fiscal_year_id',
        'employee_id', 
        'is_active', 
        'is_delete', 
        'created_at', 
        'created_by', 
        'updated_at', 
        'updated_by'
    ];

    public function branch() {
      return $this->belongsTo('App\Model\GNL\Branch', 'branch_id', 'id');
    }
    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }
}
