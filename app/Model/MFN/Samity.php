<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;

class Samity extends Model
{
    protected $table = 'mfn_samity';

    protected $fillable = [
        'name', 
        'samityCode', 
        'branchId', 
        'openingDate', 
        'samityType', 
        'samityDay', 
        'maxActiveMember', 
        'workingAreaId', 
        'fieldOfficerEmpId', 
        'registrationNo', 
        'samityTime',
        'isTransferable',
        'latitude', 
        'longtitude', 
        'closingDate',
        'isOpening',
        'is_delete'
    ];

   /// $fieldOfficerName = DB::table('hr_employees')->where('id', $somityName->fieldOfficerEmpId)->first()->emp_name;
    public function hrEmployee()
    {
        return $this->belongsTo('App\Model\HR\Employee', 'fieldOfficerEmpId', 'id');
    }
}
