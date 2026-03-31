<?php

namespace App\Model\POS;

// use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class Barcode extends BaseModel
{
    protected $table = 'pos_p_barcodes';
    protected $fillable = [
    	'product_id',
        'product_barcode',
        'qtn_barcode',
        'bar_counter',
        'bar_counter_array'
    ];

    public $timestamps = false;

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }
}
