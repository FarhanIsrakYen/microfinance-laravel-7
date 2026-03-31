<?php

namespace App\Model\GNL;
use Illuminate\Database\Eloquent\Model;


class HODB extends Model
{

    protected $table = 'query_db_ho';
    protected $fillable = [
        'table_name',
       
    ];

    public $timestamps = false;

}
