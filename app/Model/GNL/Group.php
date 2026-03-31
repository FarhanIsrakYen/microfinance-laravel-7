<?php

namespace App\Model\GNL;
use App\BaseModel;

class Group extends BaseModel
{
    protected $table = 'gnl_groups';
    protected $fillable = [
        'group_name',
        'group_email',
        'group_phone',
        'group_addr',
        'group_web_add',
        'group_logo',
        'created_by',
		'updated_by'
    ];

    public function company()
    {
        //    return $this->('App\Phone');
        return $this->hasMany(Company::class)->where('is_delete', 0);
    }

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
