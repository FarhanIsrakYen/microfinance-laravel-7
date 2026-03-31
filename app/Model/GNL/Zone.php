<?php

namespace App\Model\GNL;

use App\BaseModel;

class Zone extends BaseModel
{

    protected $table = 'gnl_zones';
    
    protected $fillable = [
        'zone_name',
        'zone_code',
        'company_id',
        'created_by',
        'updated_by',
        'area_arr',
        'branch_arr'
    ];

    public function company()
    {

        return $this->belongsTo('App\Model\GNL\Company', 'company_id', 'id');
    }

    // public function branch()
    // {

    //     return $this->belongsTo(Area::class);
    // }

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
