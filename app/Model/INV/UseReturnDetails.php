<?php

namespace App\Model\INV;

use App\BaseModel;

class UseReturnDetails extends BaseModel
{
    protected $table = 'inv_use_return_d';

    public $timestamps = false;

    protected $fillable = [
        'return_bill_no',
        'product_id',
        'branch_id',
        'product_quantity',
        'return_reason',
        'return_description',
    ];

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
