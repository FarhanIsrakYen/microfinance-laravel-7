<?php

namespace App\Model\POS;

use App\BaseModel;

class Customer extends BaseModel
{
    protected $table = 'pos_customers';
    protected $fillable = [
        'customer_no',
        'branch_id',
        'customer_type',
        'company_id',
        'customer_name',
        'cus_gender',
        'mother_name',
        'father_name',
        'yearly_income',
        'district_name',
        'marital_status',
        'spouse_name',
        'customer_email',
        'customer_mobile',
        'customer_id_type',
        'customer_nid',
        'pre_division_id',
        'pre_district_id',
        'pre_upazila_id',
        'pre_union_id',
        'pre_village_id',
        'pre_remarks',
        'par_division_id',
        'par_district_id',
        'par_upazila_id',
        'par_union_id',
        'par_village_id',
        'par_remarks',
        'customer_dob',
        'samity_id',
        'customer_desc',
        'customer_image',
        'created_by',
		'updated_by'

    ];

    public function branch()
    {
        return $this->belongsTo('App\Model\GNL\Branch', 'branch_id', 'id');
    }

    public function company()
    {
        return $this->belongsTo('App\Model\GNL\Company', 'company_id', 'id');
    }
    public function predivision()
    {

        return $this->belongsTo('App\Model\GNL\Division', 'pre_division_id', 'id');
    }
    public function pardivision()
    {

        return $this->belongsTo('App\Model\GNL\Division', 'par_division_id', 'id');
    }
    public function predistrict()
    {

        return $this->belongsTo('App\Model\GNL\District', 'pre_district_id', 'id');
    }
    public function pardistrict()
    {

        return $this->belongsTo('App\Model\GNL\District', 'par_district_id', 'id');
    }
    public function preupazila()
    {

        return $this->belongsTo('App\Model\GNL\Upazila', 'pre_upazila_id', 'id');
    }
    public function parupazila()
    {

        return $this->belongsTo('App\Model\GNL\Upazila', 'par_upazila_id', 'id');
    }
    public function preunion()
    {

        return $this->belongsTo('App\Model\GNL\Union', 'pre_union_id', 'id');
    }
    public function parunion()
    {

        return $this->belongsTo('App\Model\GNL\Union', 'par_union_id', 'id');
    }
    public function previllage()
    {

        return $this->belongsTo('App\Model\GNL\Village', 'pre_village_id', 'id');
    }
    public function parvillage()
    {

        return $this->belongsTo('App\Model\GNL\Village', 'par_village_id', 'id');
    }


    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }
}
