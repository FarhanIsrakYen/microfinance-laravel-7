<?php

namespace App\Model\INV;

// use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class POBStockDetails extends BaseModel
{
    protected $table = 'inv_ob_stock_d';

    protected $fillable = [
        'ob_no',
        'branch_id',
        'product_id',
        'product_quantity'
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
        return $this->belongsTo('App\Model\INV\Product', 'product_id', 'id');
    }

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }
}
