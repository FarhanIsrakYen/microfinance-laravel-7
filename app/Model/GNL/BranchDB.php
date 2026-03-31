<?php

namespace App\Model\GNL;
use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class BranchDB extends Model
{

    protected $table = 'query_db_branch';
    protected $fillable = [
        
        'table_name',
       
       
    ];

    public $timestamps = false;

}
