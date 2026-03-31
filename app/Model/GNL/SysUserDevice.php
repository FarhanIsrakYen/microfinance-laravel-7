<?php

namespace App\Model\GNL;

use Illuminate\Database\Eloquent\Model;

class SysUserDevice extends Model {

	protected $table = 'gnl_sys_user_devices';
	protected $primaryKey = 'id';
	protected $fillable = [
		'sys_username',
		'sys_user_id',
		'sys_user_role_id',
		'device_name',
		'access_token',
		'ip_address',
		'browser_address',
		'http_user_agent',
	];
}
