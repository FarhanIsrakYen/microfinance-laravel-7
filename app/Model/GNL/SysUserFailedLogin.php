<?php

namespace App\Model\GNL;

use Illuminate\Database\Eloquent\Model;

class SysUserFailedLogin extends Model {

	protected $table = 'gnl_sys_user_failed_login';
	protected $primaryKey = 'id';
	protected $fillable = [
		'username',
		'password',
		'attempt_time',
		'ip_address',
		'browser_address',
		'http_user_agent',
	];
}
