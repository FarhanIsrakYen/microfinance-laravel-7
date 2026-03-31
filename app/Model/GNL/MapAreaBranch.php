<?php

namespace App\Model\GNL;

// use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class MapAreaBranch extends BaseModel
{
    protected $table='gnl_map_area_branch';


    protected $fillable = [
        'company_id',
        'area_id',
        'branch_id'
    ];

    public $timestamps = false;
    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }
}
