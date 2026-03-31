<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;

class MemberDetails extends Model
{
    protected $table = 'mfn_member_details';
    protected $primaryKey = 'memberId';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'memberId', 
        'surName', 
        'dateOfBirth', 
        'maritalStatusId', 
        'educationLevelId', 
        'fatherName', 
        'motherName', 
        'sonName', 
        'spouseName', 
        'nationalityId', 
        'mobileNo', 
        'formApplicationNo', 
        'firstEvidenceTypeId', 
        'firstEvidence', 
        'firstEvidenceIssuerCountryId', 
        'secondEvidenceTypeId', 
        'secondEvidence', 
        'secondEvidenceIssuerCountryId', 
        'secondEvidenceValidTill', 
        'admissionFee', 
        'admissionNo', 
        'preDivisionId', 
        'preDistrictId', 
        'preUpazilaId', 
        'preUnionId', 
        'preVillageId', 
        'preStreetHolding', 
        'familyContactNumber', 
        'perDivisionId', 
        'perDistrictId', 
        'perUpazilaId', 
        'perUnionId', 
        'perVillageId', 
        'perStreetHolding', 
        'professionId', 
        'religionId', 
        'numberOfFamilyMember', 
        'yearlyIncome', 
        'landArea', 
        'note', 
        'fixedAssetDescription',
        'isOpening', 
        'created_by', 
        'updated_by'
    ];
    //$educationalLevels = DB::table('mfn_member_educational_levels')->where('id', $memberDetails->educationLevelId)->first()->name;
    public function memberEducation()
    {
        return $this->belongsTo('App\Model\MFN\MemberEducation', 'educationLevelId', 'id');
    }
    public function memberProfession()
    {
        return $this->belongsTo('App\Model\MFN\MemberProfession', 'professionId', 'id');
    }

      // present address
      //$previllage = DB::table('gnl_villages')->where('id', $memberDetails->preVillageId)->first()->village_name;
      public function preVillage()
    {
        return $this->belongsTo('App\Model\GNL\Village', 'preVillageId', 'id');
    }
      //$preunion = DB::table('gnl_unions')->where('id', $memberDetails->preUnionId)->first()->union_name;
    public function preUnion()
    {
        return $this->belongsTo('App\Model\GNL\Union', 'preUnionId', 'id');
    }
     // $preupazila = DB::table('gnl_upazilas')->where('id', $memberDetails->preUpazilaId)->first()->upazila_name;
    public function preUpazila()
    {
        return $this->belongsTo('App\Model\GNL\Upazila', 'preUpazilaId', 'id');
    }
      //$predistrict = DB::table('gnl_districts')->where('id', $memberDetails->preDistrictId)->first()->district_name;
    public function preDistrict()
    {
        return $this->belongsTo('App\Model\GNL\District', 'preDistrictId', 'id');
    }
     // $predivision = DB::table('gnl_divisions')->where('id', $memberDetails->preDivisionId)->first()->division_name;
      public function preDivision()
    {
        return $this->belongsTo('App\Model\GNL\Division', 'preDivisionId', 'id');
    }
      // Parmanent address
    //   $pervillage = DB::table('gnl_villages')->where('id', $memberDetails->perVillageId)->first()->village_name;
    //   $perunion = DB::table('gnl_unions')->where('id', $memberDetails->perUnionId)->first()->union_name;
    //   $perupazila = DB::table('gnl_upazilas')->where('id', $memberDetails->perUpazilaId)->first()->upazila_name;
    //   $perdistrict = DB::table('gnl_districts')->where('id', $memberDetails->perDistrictId)->first()->district_name;
    //   $perdivision = DB::table('gnl_divisions')->where('id', $memberDetails->perDivisionId)->first()->division_name;

      public function perVillage()
      {
          return $this->belongsTo('App\Model\GNL\Village', 'perVillageId', 'id');
      }
        //$perunion = DB::table('gnl_unions')->where('id', $memberDetails->perUnionId)->first()->union_name;
      public function perUnion()
      {
          return $this->belongsTo('App\Model\GNL\Union', 'perUnionId', 'id');
      }
       // $perupazila = DB::table('gnl_upazilas')->where('id', $memberDetails->perUpazilaId)->first()->upazila_name;
      public function perUpazila()
      {
          return $this->belongsTo('App\Model\GNL\Upazila', 'perUpazilaId', 'id');
      }
        //$perdistrict = DB::table('gnl_districts')->where('id', $memberDetails->perDistrictId)->first()->district_name;
      public function perDistrict()
      {
          return $this->belongsTo('App\Model\GNL\District', 'perDistrictId', 'id');
      }
       // $perdivision = DB::table('gnl_divisions')->where('id', $memberDetails->perDivisionId)->first()->division_name;
        public function perDivision()
      {
          return $this->belongsTo('App\Model\GNL\Division', 'perDivisionId', 'id');
      } 
     
    

}
