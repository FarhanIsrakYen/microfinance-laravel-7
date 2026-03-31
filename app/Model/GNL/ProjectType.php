<?php

namespace App\Model\GNL;

use App\BaseModel;

class ProjectType extends BaseModel
{

    protected $table = 'gnl_project_types';
    protected $fillable = [
        'group_id',
        'company_id',
        'project_id',
        'project_type_name',
        'project_type_code',
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

    public function project()
    {
        //    return $this->('App\Phone');
        return $this->belongsTo('App\Model\GNL\Project', 'project_id', 'id');
    }

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
