<?php

namespace App\Model\GNL;

use Illuminate\Database\Eloquent\Model;

class DBQueryLog extends Model
{
    protected $table = 'db_query_log';

    protected $fillable = [
    	'company_id', 
    	'branch_id', 
    	'table_name', 
    	'fillable', 
    	'attributes',
        'attr_values',
    	'operation_type',
    	'query_sql'
    ];
}
