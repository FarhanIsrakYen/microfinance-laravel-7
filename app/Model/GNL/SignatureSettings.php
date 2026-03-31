<?php

namespace App\Model\GNL;

use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class SignatureSettings extends BaseModel
{
    protected $table = 'gnl_signature_setting';

    protected $fillable = [
        'module_id',
        'title', 
        'signatorDesignationId', 
        'signatorEmployeeId', 
        'applicableFor', 
        'positionOrder', 
        'status', 
        'created_at', 
        'updated_at', 
        'created_by', 
        'updated_by', 
        'is_delete'
    ];


     public function designation()
    {
        return $this->belongsTo('App\Model\GNL\EmpDesignation', 'signatorDesignationId', 'id');
    }

    public function employee()
    {
        return $this->belongsTo('App\Model\HR\Employee', 'signatorEmployeeId', 'id');
    }

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
