<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;

class SavingProvisions extends Model
{
    protected $table = 'mfn_savings_provision';

    protected $fillable = [
        'provisionCode', 
        'amount', 
        'provisionDate', 
        'created_by', 
        'updated_by', 
        'created_at', 
        'updated_at', 
        'is_delete'
    ];
}
