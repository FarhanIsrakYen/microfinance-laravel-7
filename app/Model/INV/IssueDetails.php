<?php

namespace App\Model\INV;

// use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class IssueDetails extends BaseModel
{
    protected $table = 'inv_issues_d';

    public $timestamps = false;

    protected $fillable = [
        'branch_to',
        'issue_bill_no',
        'product_id',
        'product_quantity'
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
        return $this->belongsTo('App\Model\INV\Product', 'product_id', 'id');
    }

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
