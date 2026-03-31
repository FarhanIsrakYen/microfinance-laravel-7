<?php

namespace App\Model\GNL;

use App\BaseModel;

class Village extends BaseModel
{

    protected $table = 'gnl_villages';
    protected $fillable = [
        'division_id',
        'district_id',
        'upazila_id',
        'union_id',
        'village_name',
        'created_by',
		'updated_by'
    ];

    public function division()
    {

        return $this->belongsTo('App\Model\GNL\Division', 'division_id', 'id');
    }

    public function district()
    {

        return $this->belongsTo(District::class);
    }

    public function upazila()
    {

        return $this->belongsTo(Upazila::class);
    }

    public function union()
    {

        return $this->belongsTo(Union::class);
    }

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
