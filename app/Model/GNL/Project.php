<?php

namespace App\Model\GNL;

use App\BaseModel;

class Project extends BaseModel
{
    protected $table = 'gnl_projects';
    protected $fillable = [
        'group_id',
        'company_id',
        'project_code',
        'project_name',
        'created_by',
		'updated_by'
    ];

    public function group()
    {
        //    return $this->('App\Phone');
        return $this->belongsTo('App\Model\GNL\Group', 'group_id', 'id');
    }

    public function company()
    {
        //    return $this->('App\Phone');
        return $this->belongsTo('App\Model\GNL\Company', 'company_id', 'id');
    }

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
