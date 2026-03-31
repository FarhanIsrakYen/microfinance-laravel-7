<?php

namespace App\Model\INV;

use App\BaseModel;

class PGroup extends BaseModel
{

    protected $table = 'inv_p_groups';
    protected $fillable = [
        'company_id',
        'group_name',
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
