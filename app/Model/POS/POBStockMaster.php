<?php

namespace App\Model\POS;

use App\BaseModel;

class POBStockMaster extends BaseModel
{
    protected $table = 'pos_ob_stock_m';

    protected $fillable = [
        'company_id',
        'branch_id',
        'ob_no',
        'opening_date',
        'total_product',
        'total_quantity',
        'total_amount',
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

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }
}
