<?php

namespace App\Model\GNL;

use App\BaseModel;

class SysUserRole extends BaseModel {

	protected $table = 'gnl_sys_user_roles';
	protected $primaryKey = 'id';
	protected $fillable = [
		'parent_id',
		'role_name',
		'order_by',
		'modules',
		'menus',
		'permissions',
		'serialize_module',
		'serialize_menu',
		'serialize_permission',
		'created_by',
		'updated_by'
	];

	//    public function SysUser()
	// {
	//     return $this->hasMany(SysUser::class, 'sys_user_role_id');
	// }

	/* Here Insert Created By & Update By */
	public static function boot() {
		parent::boot();
	}
}
