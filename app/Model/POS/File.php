<?php

namespace App\Model\POS;
use App\BaseModel;

class File extends BaseModel
{
    protected $table = 'gnl_files';
    protected $fillable = [
        'file_name',
        'file_size',
        'file_type',
        'file_url',
        'created_by',
		'updated_by'
    ];

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
