<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;

class MemberProfession extends Model
{
    protected $table = 'mfn_member_professions';

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
