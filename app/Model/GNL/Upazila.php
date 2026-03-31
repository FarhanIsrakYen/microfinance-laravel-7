<?php

namespace App\Model\GNL;

use App\BaseModel;

class Upazila extends BaseModel
{

    protected $table = 'gnl_upazilas';
    protected $fillable = [
        'division_id',
        'district_id',
        'upazila_name',
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

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
