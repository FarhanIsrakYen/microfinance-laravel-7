<?php

namespace App\Model\POS;

use App\BaseModel;

class POBDueSaleMaster extends BaseModel
{
    protected $table = 'pos_ob_duesales_m';

    protected $fillable = [
        'ob_no',
        'company_id',
        'branch_id',
        'opening_date',
        'total_customer',
        'total_sales_amount',
        'total_collection',
        'total_due_amount',
        'is_active',
        'is_delete',
        'created_by',
		'updated_by'
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

}
