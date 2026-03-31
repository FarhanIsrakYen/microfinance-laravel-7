<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;


class MfnYearEnd extends Model
{
    //
    protected $table = 'mfn_year_end';
    protected $fillable = [
        'date',
        'fiscal_year_id',
        'start_date',
        'end_date',
        'company_id',
        'branch_id',
        'is_active',
        'is_delete',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by'        
    ];
    
   
}
