<?php

namespace App\Model\POS;

// use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class Issued extends BaseModel
{
    protected $table = 'pos_issues_d';

    public $timestamps = false;

    protected $fillable = [
        'branch_to',
        'issue_bill_no',
        'product_id',
        'product_quantity',
        'unit_cost_price',
        'total_cost_amount',
    ];

    public function branchFrom()
    {
        return $this->belongsTo('App\Model\GNL\Branch', 'branch_from', 'id');
    }
    public function branchTo()
    {
        return $this->belongsTo('App\Model\GNL\Branch', 'branch_to', 'id');
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
