<?php

namespace App\Model\GNL;

use App\BaseModel;

class SysModule extends BaseModel {

	protected $table = 'gnl_sys_modules';
	protected $fillable = [
		'module_name', 'module_short_name', 'module_icon', 'route_link', 'is_active', 'is_delete', 'updated_at', 'updated_by', 'created_at', 'created_by',
	];

	/* Here Insert Created By & Update By */
	public static function boot() {
		parent::boot();
	}

}
