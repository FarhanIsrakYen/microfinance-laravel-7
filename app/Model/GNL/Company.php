<?php

namespace App\Model\GNL;
use App\BaseModel;

class Company extends BaseModel
{

    protected $table = 'gnl_companies';
    protected $fillable = [
        'group_id',
        'comp_name',
        'comp_code',
        'comp_email',
        'comp_phone',
        'comp_addr',
        'comp_web_add',
        'fy_start_date',
        'fy_end_date',
        'comp_logo',
        'created_by',
        'updated_by',
        'module_arr'

    ];

    public function group()
    {

        return $this->belongsTo('App\Model\GNL\Group', 'group_id', 'id')->where('is_delete', 0);

    }

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
