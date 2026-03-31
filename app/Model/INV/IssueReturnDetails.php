<?php

namespace App\Model\INV;

use App\BaseModel;

class IssueReturnDetails extends BaseModel
{
    protected $table = 'inv_issues_r_d';

    public $timestamps = false;

    protected $fillable = [
        'ir_bill_no',
        'branch_from',
        'product_id',
        'product_quantity'
    ];

    // public function branch() {
    //     return $this->belongsTo('App\Model\GNL\Branch', 'branch_id', 'id');
    // }

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
