<?php

namespace App\Model\GNL;
use Illuminate\Database\Eloquent\Model;


class HOIG extends Model
{

    protected $table = 'query_db_ho_ig';

    protected $fillable = [
        'table_name',
    ];

    public $timestamps = false;

}
