<?php

namespace App\Model\GNL;

use App\BaseModel;

class Notice extends BaseModel
{
    protected $table = 'gnl_notice';

    protected $fillable = [
        'notice_title',
        'notice_period',
        'start_time',
        'end_time',
        'notice_body',
        'branch_id',
        'is_active',
        'is_delete',
        'created_by',
		'updated_by'
    ];

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
