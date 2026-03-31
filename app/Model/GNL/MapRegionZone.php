<?php

namespace App\Model\GNL;

// use Illuminate\Database\Eloquent\Model;

use App\BaseModel;

class MapRegionZone extends BaseModel
{
   protected $table='gnl_map_region_zone';
    protected $fillable = [
        'company_id',
        'region_id',
        'zone_id'
    ];

    public $timestamps = false;
    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }
}
