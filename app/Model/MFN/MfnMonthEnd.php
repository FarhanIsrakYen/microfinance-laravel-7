<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;


class MfnMonthEnd extends Model
{
    //
    protected $table = 'mfn_month_end';
    protected $fillable = [
        'date',
        'branchId',
        
    ];


   
}
