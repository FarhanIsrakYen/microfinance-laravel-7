<?php

namespace App\Model\HR;

use App\BaseModel;

class Room extends BaseModel
{

    protected $table = 'hr_rooms';
    protected $fillable = [
        'company_id',
        'dept_id',
        'room_name',
        'room_code',
        'created_by',
		'updated_by'
    ];

    public function department()
    {
        return $this->belongsTo('App\Model\HR\EmpDepartment', 'dept_id', 'id');
    }
    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
