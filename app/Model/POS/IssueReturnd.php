<?php

namespace App\Model\POS;

// use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class IssueReturnd extends BaseModel
{
    protected $table = 'pos_issues_r_d';

    public $timestamps = false;

    protected $fillable = [
        'ir_bill_no',
        'branch_from',
        'product_id',
        'product_quantity',
        'unit_cost_price',
        'total_cost_amount',
    ];

    // public function branch() {
    //     return $this->belongsTo('App\Model\GNL\Branch', 'branch_id', 'id');
    // }

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
