<?php

namespace App\Model\GNL;

// use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class MapZoneArea extends BaseModel
{
    protected $table='gnl_map_zone_area';
    protected $fillable = [
        'company_id',
        'zone_id',
        'area_id'
    ];

    public $timestamps = false;
    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }
}
