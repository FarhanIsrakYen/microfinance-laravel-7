<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;

class MemberEducation extends Model
{
    protected $table = 'mfn_member_educational_levels';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 
        'status'
    ];


}
