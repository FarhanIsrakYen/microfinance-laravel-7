<?php

namespace App\Model\INV;

use App\BaseModel;

class PUOM extends BaseModel
{
    protected $table = 'inv_p_uoms';

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
