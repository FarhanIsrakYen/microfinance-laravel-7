<?php

namespace App\Model\GNL;

use App\BaseModel;

class Branch extends BaseModel
{

    protected $table = 'gnl_branchs';
    protected $fillable = [
        'group_id',
        'company_id',
        'project_id',
        'project_type_id',
        'branch_name',
        'branch_code',
        'branch_email',
        'branch_phone',
        'branch_addr',
        'contact_person',
        'branch_opening_date',
        'soft_start_date',
        'acc_start_date',
        'mfn_start_date',
        'fam_start_date',
        'inv_start_date',
        'proc_start_date',
        'bill_start_date',
        'hr_start_date',
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

    public function projectType()
    {
        //    return $this->('App\Phone');
        return $this->belongsTo('App\Model\GNL\ProjectType', 'project_type_id', 'id');
    }

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
