<?php

namespace App\Model\POS;

// use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class POBStockDetails extends BaseModel
{
    protected $table = 'pos_ob_stock_d';

    protected $fillable = [
        'ob_no',
        'branch_id',
        'product_id',
        'product_quantity',
        'unit_cost_price',
        'total_cost_amount',
    ];
    public $timestamps = false;

    public function company()
    {
        return $this->belongsTo('App\Model\GNL\Company', 'company_id', 'id');
    }
    public function branch()
    {
        return $this->belongsTo('App\Model\GNL\Branch', 'branch_id', 'id');
    }
    public function product()
    {
        return $this->belongsTo('App\Model\POS\Product', 'product_id', 'id');
    }

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }
}
