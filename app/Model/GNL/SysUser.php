<?php

namespace App\Model\GNL;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\HasApiTokens;

class SysUser extends Authenticatable
{
    // use Notifiable;
    use HasApiTokens, Notifiable;

    protected $table = 'gnl_sys_users';

    protected $fillable = [
        'sys_user_role_id',
        'company_id',
        'full_name',
        'username',
        'password',
        'email',
        'contact_no',
        'designation',
        'department',
        'user_image',
        'signature_image',
        'last_login_ip',
        'last_login_time',
        'ip_address',
        'branch_id',
        'employee_id',
        'created_by',
        'updated_by'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'last_login_time' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();

        // // create a event to happen on saving
        // static::saving(function ($model) {
        //     $model->created_by = Auth::id();
        // });

        // // create a event to happen on updating
        // static::updating(function ($model) {
        //     $model->updated_by = Auth::id();
        // });

        // create a event to happen on saving
        static::saving(function ($model) {

            if(in_array('created_by', $model->fillable)){
                $model->created_by = Auth::id();
            }

            if ($model->table != "db_query_log")
            {
                $dblogData = array();

                $dblogData['table_name'] = $model->table;
                $dblogData['fillable'] = implode(',', $model->fillable);
                $dblogData['attributes'] = implode(',', array_keys($model->attributes));
                $dblogData['attr_values'] = implode(',', $model->attributes);
                $dblogData['operation_type'] = "insert";

                session()->put('dblogData', $dblogData);
            }
        });

        // create a event to happen on updating
        static::updating(function ($model) {

            if(in_array('updated_by', $model->fillable)){
                $model->updated_by = Auth::id();
            }

            if ($model->table != "db_query_log")
            {
                $dblogData = array();

                $dblogData['table_name'] = $model->table;
                $dblogData['fillable'] = implode(',', $model->fillable);
                $dblogData['attributes'] = implode(',', array_keys($model->attributes));
                $dblogData['attr_values'] = implode(',', $model->attributes);

                if ($model->is_delete == 1) {
                    $dblogData['operation_type'] = "delete";
                }
                else{
                    $dblogData['operation_type'] = "update";
                }

                session(['dblogData' => $dblogData]);
            }
        });

    }
}
