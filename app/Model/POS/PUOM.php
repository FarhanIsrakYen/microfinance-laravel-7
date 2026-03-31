<?php

namespace App\Model\POS;

use App\BaseModel;

class PUOM extends BaseModel
{
    protected $table = 'pos_p_uoms';

    protected $fillable = [
        'company_id',
        'uom_name',
        'branch_id',
        'created_by',
		'updated_by'
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
