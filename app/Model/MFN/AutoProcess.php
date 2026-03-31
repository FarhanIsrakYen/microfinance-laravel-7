<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;

class AutoProcess extends Model
{
    protected $table = 'mfn_auto_processes';

    protected $fillable = [
        'samityId', 
        'date', 
        'presentMembers', 
        'isCompleted', 
    ];

   
}
