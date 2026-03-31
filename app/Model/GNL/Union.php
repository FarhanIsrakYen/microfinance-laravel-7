<?php

namespace App\Model\GNL;

use App\BaseModel;

class Union extends BaseModel
{

    protected $table = 'gnl_unions';
    protected $fillable = [
        'division_id',
        'district_id',
        'upazila_id',
        'union_name',
        'created_by',
		'updated_by'
    ];

    public function division()
    {

        return $this->belongsTo('App\Model\GNL\Division', 'division_id', 'id');
    }

    public function district()
    {

        return $this->belongsTo('App\Model\GNL\District', 'district_id', 'id');
    }

    public function upazila()
    {

        return $this->belongsTo('App\Model\GNL\Upazila', 'upazila_id', 'id');
    }

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
