<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $table = 'mfn_members';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 
        'memberCode', 
        'mraCode', 
        'branchId', 
        'samityId', 
        'primaryProductId', 
        'admissionDate', 
        'gender', 
        'closingDate', 
        'created_by', 
        'updated_by'
    ];

    //$somityName = DB::table('mfn_samity')->where('id', $member->samityId)->select('id', 'name', 'fieldOfficerEmpId')->first();
    public function samity()
    {
        return $this->belongsTo('App\Model\MFN\Samity', 'samityId', 'id');
    }
      // $primaryProduct = DB::table('mfn_loan_products')->where('id', $member->primaryProductId)->select('name','shortName')->first();

      public function loanProduct()
    {
        return $this->belongsTo('App\Model\MFN\LoanProduct', 'primaryProductId', 'id');
    }
}
