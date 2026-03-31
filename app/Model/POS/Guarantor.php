<?php

namespace App\Model\POS;

use App\BaseModel;

class Guarantor extends BaseModel
{
    protected $table = 'pos_guarantors';
    protected $fillable = [
        'guarantor_no',
        'company_id',
        'branch_id',
        'customer_id',
        'gr_name',
        'gr_father_name',
        'gr_mother_name',
        'gr_spouse_name',
        'gr_email',
        'gr_mobile',
        'gr_pre_division_id',
        'gr_pre_district_id',
        'gr_pre_upazila_id',
        'gr_pre_union_id',
        'gr_pre_village_id',
        'gr_pre_remarks',
        'gr_par_division_id',
        'gr_par_district_id',
        'gr_par_upazila_id',
        'gr_par_union_id',
        'gr_par_village_id',
        'gr_par_remarks',
        'gr_relation_with',
        'gr_dob',
        'gr_id_type',
        'gr_nid',
        'gr_marital_status',
        'gr_yearly_income',
        'gr_desc',
        'created_by',
		'updated_by'
    ];

    public function company()
    {
        return $this->belongsTo('App\Model\GNL\Company', 'company_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo('App\Model\POS\Customer', 'customer_id', 'customer_no');
    }

    public function division()
    {
        return $this->belongsTo('App\Model\GNL\Division', 'division_id', 'id');
    }

    public function district()
    {
        return $this->belongsTo('App\Model\GNL\District', 'district_id', 'id');
    }
    public function upazila()
    {
        return $this->belongsTo('App\Model\GNL\Upazila', 'upazila_id', 'id');
    }

    public function union()
    {
        return $this->belongsTo('App\Model\GNL\Union', 'union_id', 'id');
    }
    public function village()
    {
        return $this->belongsTo('App\Model\GNL\Village', 'village_id', 'id');
    }
    public function predivision()
    {

        return $this->belongsTo('App\Model\GNL\Division', 'gr_pre_division_id', 'id');
    }
    public function pardivision()
    {

        return $this->belongsTo('App\Model\GNL\Division', 'gr_par_division_id', 'id');
    }
    public function predistrict()
    {

        return $this->belongsTo('App\Model\GNL\District', 'gr_pre_district_id', 'id');
    }
    public function pardistrict()
    {

        return $this->belongsTo('App\Model\GNL\District', 'gr_par_district_id', 'id');
    }
    public function preupazila()
    {

        return $this->belongsTo('App\Model\GNL\Upazila', 'gr_pre_upazila_id', 'id');
    }
    public function parupazila()
    {

        return $this->belongsTo('App\Model\GNL\Upazila', 'gr_par_upazila_id', 'id');
    }
    public function preunion()
    {

        return $this->belongsTo('App\Model\GNL\Union', 'gr_pre_union_id', 'id');
    }
    public function parunion()
    {

        return $this->belongsTo('App\Model\GNL\Union', 'gr_par_union_id', 'id');
    }
    public function previllage()
    {

        return $this->belongsTo('App\Model\GNL\Village', 'gr_pre_village_id', 'id');
    }
    public function parvillage()
    {

        return $this->belongsTo('App\Model\GNL\Village', 'gr_par_village_id', 'id');
    }

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
