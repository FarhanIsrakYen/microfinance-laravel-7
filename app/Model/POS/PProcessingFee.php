<?php

namespace App\Model\POS;

// use Illuminate\Database\Eloquent\Model;

use App\BaseModel;

class PProcessingFee extends BaseModel
{
    protected $table = 'pos_processing_fee';

    protected $fillable = [
        'company_id', 'amount', 'is_active', 'is_delete',
    ];

    public function company()
    {
        return $this->belongsTo('App\Model\GNL\Company', 'company_id', 'id');
    }

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }
}
