<?php

namespace App\Model\GNL;

use Illuminate\Database\Eloquent\Model;

class SysUserHistory extends Model {

	protected $table = 'gnl_sys_user_historys';

	protected $primaryKey = 'id';
	
	protected $fillable = [
		'sys_username',
		'sys_user_id',
		'sys_user_role_id',
		'login_time',
		'logout_time',
		'ip_address',
		'browser_address',
		'http_user_agent',
		'session_key'
	];
}
