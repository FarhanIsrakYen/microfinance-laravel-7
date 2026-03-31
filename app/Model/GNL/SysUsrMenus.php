<?php

namespace App\Model\GNL;

use App\BaseModel;

class SysUsrMenus extends BaseModel
{

    protected $table = 'gnl_sys_menus';
    protected $fillable = [
        'module_id', 'parent_menu_id', 'menu_name', 'route_link', 'page_title', 'controller',
        'action', 'menu_link', 'menu_sort_name', 'menu_icon', 'order_by', 'remarks',
        'is_active', 'is_delete', 'updated_at', 'created_at',
        'created_by',
		'updated_by'
    ];

    public function SysModule()
    {
        return $this->belongsTo(SysModule::class, 'module_id', 'id');
    }

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
