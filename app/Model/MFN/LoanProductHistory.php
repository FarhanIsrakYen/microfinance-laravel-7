<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;

class LoanProductHistory extends Model
{
    protected $table = 'mfn_loan_products_history';
    public $timestamps = false;

    protected $fillable = [
        'productId',
        'content',
        'effectiveTo',
        'created_by',
        'created_at'
    ];
}
