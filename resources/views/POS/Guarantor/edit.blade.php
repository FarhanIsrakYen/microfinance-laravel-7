@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\HtmlService as HTML;
?>

<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator"
novalidate="true" autocomplete="off">
    @csrf
    <div class="row">
        <div class="col-lg-9 offset-3 mb-2">
            <!-- Html View Load  -->
            {!! HTML::forCompanyFeild($GuarantorData->company_id) !!}
        </div>
    </div>
    <div class="row">
        <div class="col-lg-8 offset-lg-3">
            {!! HTML::forBranchFeild(true,'branch_id','branch_id',$GuarantorData->branch_id) !!}
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6">

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar" for="selCustID">Customer Name</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2"
                             name="customer_id" id="customer_id"
                            required data-error="Please select Customer">
                            <option value="" selected="selected">Select Customer</option>
                            @foreach ($CustomerData as $Row)
                            <option value="{{$Row->customer_no}}" @if($Row->customer_no == $GuarantorData->customer_id) selected @endif>{{$Row->customer_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Gurantor Name</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round" id="gr_name"
                            name="gr_name" placeholder="Enter Gurantor Name"
                            required data-error="Please enter Gurantor name."
                            value="{{$GuarantorData->gr_name}}">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Father's Name</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round" id="gr_father_name"
                            name="gr_father_name" placeholder="Enter Father's Name"
                            value="{{$GuarantorData->gr_father_name}}">
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Mother's Name</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round" id="gr_mother_name"
                            name="gr_mother_name" placeholder="Enter Mother's Name"
                            value="{{$GuarantorData->gr_mother_name}}">
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Date of Birth</label>
                <div class="col-lg-7">
                    <div class="input-group ">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="icon wb-calendar round" aria-hidden="true"></i>
                            </span>
                        </div>

                        <input type="text" class="form-control round datepicker" id="gr_dob"
                            name="gr_dob" placeholder="DD-MM-YYYY" autocomplete="off"
                            value="<?= (!empty($GuarantorData->gr_dob)) ? date('d-m-Y', strtotime($GuarantorData->gr_dob)) : '' ?>"
                        >
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Marital Status</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <div class="radio-custom radio-primary">
                            <input type="radio" name="gr_marital_status" value="Single"
                                class="MarritalStatus"
                                {{ $GuarantorData->gr_marital_status == 'Single' ? 'checked' : ''}}>
                            <label for="g1">Single &nbsp &nbsp </label>
                        </div>
                        <div class="radio-custom radio-primary">
                            <input type="radio" name="gr_marital_status" value="Married"
                                class="MarritalStatus"
                                {{ $GuarantorData->gr_marital_status == 'Married' ? 'checked' : ''}}>
                            <label for="g2">Married &nbsp &nbsp </label>
                        </div>
                        <div class="radio-custom radio-primary">
                            <input type="radio" name="gr_marital_status" value="Divorced"
                                class="MarritalStatus"
                                {{ $GuarantorData->gr_marital_status == 'Divorced' ? 'checked' : ''}}>
                            <label for="g3">Divorced &nbsp &nbsp</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center" id="spouse" style="display: none;">
                <label class="col-lg-4 input-title ">Spouse Name</label>
                <div class="col-lg-7">
                    <div class="input-group ">
                        <input type="text" class="form-control round" id="txtGrSpouse"
                            name="gr_spouse_name" placeholder="Enter Spouse Name"
                            value="{{$GuarantorData->gr_spouse_name}}">
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Email</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="email" class="form-control round" id="gr_email" name="gr_email"
                            onblur="fnCheckDuplicate(
                                '{{base64_encode('pos_guarantors')}}',
                                this.name+'&&is_delete',
                                this.value+'&&0',
                                '{{url('/ajaxCheckDuplicate')}}',
                                this.id,
                                'txtCodeError',
                                'Email',
                                '{{$GuarantorData->gr_email}}');">
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Mobile</label>
                <div class="col-lg-7">
                    <div class="input-group ">
                        <input type="text" class="form-control round textNumber" id="gr_mobile"
                            name="gr_mobile" placeholder="Mobile Number (01*********)" pattern="[01][0-9]{10}"  required
                            data-error="Please enter mobile number (01*********)" minlength="11" maxlength="11"
                            onblur="fnCheckDuplicate(
                                '{{base64_encode('pos_guarantors')}}',
                                this.name+'&&is_delete',
                                this.value+'&&0',
                                '{{url('/ajaxCheckDuplicate')}}',
                                this.id,
                                'txtCodeError2',
                                'mobile number',
                                '{{$GuarantorData->id}}');"
                         value="{{$GuarantorData->gr_mobile}}">
                    </div>
                    <div class="help-block with-errors is-invalid" id="txtCodeError2"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">
                    NID/Smart Card/&nbsp Passport/Driving License/&nbsp Birth Certificate
                </label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <div class="radio-custom radio-primary">
                            <input type="radio" class="identification" id="n1" name="gr_id_type"
                            value="nid" {{ $GuarantorData->gr_id_type == 'nid' ? 'checked' : ''}}>
                            <label for="n1">NID &nbsp &nbsp</label>
                        </div>
                        <div class="radio-custom radio-primary">
                            <input type="radio" class="identification" id="n2" name="gr_id_type"
                            value="smartCard" {{ $GuarantorData->gr_id_type == 'smartCard' ? 'checked' : ''}}>
                            <label for="n2">Smart Card &nbsp &nbsp</label>
                        </div>
                        <div class="radio-custom radio-primary">
                            <input type="radio" class="identification" id="n3" name="gr_id_type"
                            value="passport" {{ $GuarantorData->gr_id_type == 'passport' ? 'checked' : ''}}>
                            <label for="n3">Passport &nbsp &nbsp</label>
                        </div>
                        <div class="radio-custom radio-primary">
                            <input type="radio" class="identification" id="n4" name="gr_id_type"
                            value="drivingLicense" {{ $GuarantorData->gr_id_type == 'drivingLicense' ? 'checked' : ''}}>
                            <label for="n4">Driving License &nbsp &nbsp</label>
                        </div>
                        <div class="radio-custom radio-primary">
                            <input type="radio" class="identification" id="n5" name="gr_id_type"
                            value="birthCertificate" {{ $GuarantorData->gr_id_type == 'birthCertificate' ? 'checked' : ''}}>
                            <label for="n5">Birth Certificate &nbsp &nbsp</label>
                        </div>
                        <div class="input-group mt-4">
                            <input type="text" class="form-control round textNumber identificationInput" name="gr_nid"
                                id="gr_nid" value="{{$GuarantorData->gr_nid}}" required data-error="Fill Up this form">
                        </div>
                        <div class="help-block with-errors is-invalid" id="errMsg"></div>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Yearly Income</label>
                <div class="col-lg-7">
                    <div class="input-group ">
                        <input type="text" class="form-control round" id="gr_yearly_income"
                            name="gr_yearly_income" placeholder="Enter Yearly Income"
                            value="{{$GuarantorData->gr_yearly_income}}">
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Relation</label>
                <div class="col-lg-7">
                    <div class="input-group ">
                        <input type="text" class="form-control round" id="gr_relation_with"
                            name="gr_relation_with" placeholder="Enter Relation"
                            value="{{$GuarantorData->gr_relation_with}}">
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Description</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <textarea class="form-control round" id="gr_desc" name="gr_desc" rows="1"
                            placeholder="Enter Description">{{$GuarantorData->gr_desc}}</textarea>
                    </div>
                </div>
            </div>

        </div>

        <!-- Present Address Start -->
        <div class="col-lg-6">
            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Present Address</label>
                <div class="col-lg-7">
                </div>
            </div>
            <div class="form-row align-items-center">
                <div class="col-lg-6 form-group">
                    <div class="input-title">Select Division</div>
                    <div class="input-group">
                      <select class="form-control clsSelect2" required data-error="Please enter Division name."
                           name="gr_pre_division_id"
                          id="gr_pre_division_id"
                          onchange="fnAjaxSelectBox(
                                          'gr_pre_district_id',
                                          this.value,
                              '{{base64_encode('gnl_districts')}}',
                              '{{base64_encode('division_id')}}',
                              '{{base64_encode('id,district_name')}}',
                              '{{url('/ajaxSelectBox')}}'
                                      ); GetSelectedText('checkbox_addr');">
                          <option value="" >Select Division</option>
                          @foreach ($DivisionData as $Row)
                          <option value="{{$Row->id}}"
                            {{ ($GuarantorData->gr_pre_division_id == $Row->id) ? 'selected="selected"' : '' }}>{{$Row->division_name}}</option>
                          @endforeach
                      </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>

                <div class="col-lg-6 form-group">
                    <div class="input-title">Select District</div>
                    <div class="input-group">
                      <select class="form-control clsSelect2" required data-error="Please enter District name."
                           name="gr_pre_district_id"
                          id="gr_pre_district_id"
                          onchange="fnAjaxSelectBox(
                                          'gr_pre_upazila_id',
                                          this.value,
                              '{{base64_encode('gnl_upazilas')}}',
                              '{{base64_encode('district_id')}}',
                              '{{base64_encode('id,upazila_name')}}',
                              '{{url('/ajaxSelectBox')}}'
                                      ); GetSelectedText('checkbox_addr');">
                          <option value="" selected="selected">Select District</option>
                      </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row align-items-center">
                <div class="col-lg-6 form-group">
                    <div class="input-title">Select Upazila</div>
                    <div class="input-group">
                      <select class="form-control clsSelect2" required data-error="Please enter Upazila name."
                           name="gr_pre_upazila_id"
                          id="gr_pre_upazila_id"  onchange="fnAjaxSelectBox(
                                          'gr_pre_union_id',
                                          this.value,
                              '{{base64_encode('gnl_unions')}}',
                              '{{base64_encode('upazila_id')}}',
                              '{{base64_encode('id,union_name')}}',
                              '{{url('/ajaxSelectBox')}}'
                                      ); GetSelectedText('checkbox_addr');">
                          <option value="" selected="selected">Select Upazila</option>
                      </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>

                <div class="col-lg-6 form-group">
                    <div class="input-title">Select Union</div>
                    <div class="input-group">
                      <select class="form-control clsSelect2" required data-error="Please enter Union name."
                           name="gr_pre_union_id"
                          id="gr_pre_union_id" onchange="fnAjaxSelectBox(
                                          'gr_pre_village_id',
                                          this.value,
                              '{{base64_encode('gnl_villages')}}',
                              '{{base64_encode('union_id')}}',
                              '{{base64_encode('id,village_name')}}',
                              '{{url('/ajaxSelectBox')}}'
                                      ); GetSelectedText('checkbox_addr');">
                          <option value="" selected="selected">Select Union</option>
                      </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row align-items-center">
                <div class="col-lg-6 form-group">
                    <div class="input-title">Select Village</div>
                    <div class="input-group">
                      <select class="form-control clsSelect2"
                           name="gr_pre_village_id"
                          id="gr_pre_village_id"
                          onchange="GetSelectedText('checkbox_addr');">
                          <option value="">Select Village</option>
                      </select>
                    </div>
                </div>

                <div class="col-lg-6 form-group">
                    <div class="input-group">
                      <textarea class="form-control" id="gr_pre_remarks" name="gr_pre_remarks"
                          rows="2" placeholder="Enter Remarks">{{$GuarantorData->gr_pre_remarks}}</textarea>
                    </div>
                </div>
            </div>
            <!-- <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Division</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2"
                             name="gr_pre_division_id"
                            id="gr_pre_division_id"
                            onchange="fnAjaxSelectBox(
                                            'gr_pre_district_id',
                                            this.value,
                                '{{base64_encode('gnl_districts')}}',
                                '{{base64_encode('division_id')}}',
                                '{{base64_encode('id,district_name')}}',
                                '{{url('/ajaxSelectBox')}}'
                                        ); GetSelectedText('checkbox_addr');">
                            <option value="">Select Division</option>
                            @foreach ($DivisionData as $Row)
                            <option value="{{$Row->id}}"
                                @if($GuarantorData->gr_pre_division_id == $Row->id) selected @endif >
                                {{$Row->division_name}}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">District</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2"
                             name="gr_pre_district_id"
                            id="gr_pre_district_id"
                            onchange="fnAjaxSelectBox(
                                            'gr_pre_upazila_id',
                                            this.value,
                                '{{base64_encode('gnl_upazilas')}}',
                                '{{base64_encode('district_id')}}',
                                '{{base64_encode('id,upazila_name')}}',
                                '{{url('/ajaxSelectBox')}}'
                                        ); GetSelectedText('checkbox_addr');">
                            <option value="">Select District</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Upazila</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2"
                             name="gr_pre_upazila_id"
                            id="gr_pre_upazila_id"  onchange="fnAjaxSelectBox(
                                            'gr_pre_union_id',
                                            this.value,
                                '{{base64_encode('gnl_unions')}}',
                                '{{base64_encode('upazila_id')}}',
                                '{{base64_encode('id,union_name')}}',
                                '{{url('/ajaxSelectBox')}}'
                                        ); GetSelectedText('checkbox_addr');">
                            <option value="">Select Upazila</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Union</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2"
                             name="gr_pre_union_id"
                            id="gr_pre_union_id" onchange="fnAjaxSelectBox(
                                            'gr_pre_village_id',
                                            this.value,
                                '{{base64_encode('gnl_villages')}}',
                                '{{base64_encode('union_id')}}',
                                '{{base64_encode('id,village_name')}}',
                                '{{url('/ajaxSelectBox')}}'
                                        ); GetSelectedText('checkbox_addr');">
                            <option value="" selected="selected">Select Union</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Village</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2"
                             name="gr_pre_village_id"
                            id="gr_pre_village_id"
                            onchange="GetSelectedText('checkbox_addr');">
                            <option value="">Select Village</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Enter Remarks</label>
                <div class="col-lg-7">
                    <textarea class="form-control round" id="gr_pre_remarks" name="gr_pre_remarks"
                        rows="1" placeholder="Enter Remarks">{{$GuarantorData->gr_pre_remarks}}
                    </textarea>
                </div>
            </div> -->
            <!-- Present Address End -->

            <!-- Permanent Address Start -->
            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title">Same As Present Address</label>
                <div class="col-lg-7 form-group">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="input-group">
                                    <input type="checkbox" id="checkbox_addr" onclick="GetSelectedText(this.id);">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Permanent Address</label>
                <div class="col-lg-7">
                </div>
            </div>
            <div class="form-row align-items-center">
                <div class="col-lg-6 form-group">
                    <div class="input-title">Select Division</div>
                    <div class="input-group">
                      <select class="form-control clsSelect2" required data-error="Please enter Division name."
                           name="gr_par_division_id"
                          id="gr_par_division_id"
                          onchange="fnAjaxSelectBox(
                                          'gr_par_district_id',
                                          this.value,
                              '{{base64_encode('gnl_districts')}}',
                              '{{base64_encode('division_id')}}',
                              '{{base64_encode('id,district_name')}}',
                              '{{url('/ajaxSelectBox')}}'
                                      );">
                          <option value="" selected="selected">Select Division</option>
                          @foreach ($DivisionData as $Row)
                          <option value="{{$Row->id}}"
                              @if($GuarantorData->gr_par_division_id == $Row->id) selected @endif >
                              {{$Row->division_name}}
                          </option>
                          @endforeach
                      </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>

                <div class="col-lg-6 form-group">
                    <div class="input-title">Select District</div>
                    <div class="input-group">
                      <select class="form-control clsSelect2" required data-error="Please enter District name."
                           name="gr_par_district_id"
                          id="gr_par_district_id"
                          onchange="fnAjaxSelectBox(
                                          'gr_par_upazila_id',
                                          this.value,
                              '{{base64_encode('gnl_upazilas')}}',
                              '{{base64_encode('district_id')}}',
                              '{{base64_encode('id,upazila_name')}}',
                              '{{url('/ajaxSelectBox')}}'
                                      );">
                          <option value="" selected="selected">Select District</option>
                      </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row align-items-center">
                <div class="col-lg-6 form-group">
                    <div class="input-title">Select Upazila</div>
                    <div class="input-group">
                      <select class="form-control clsSelect2" required data-error="Please enter Upazila name."
                           name="gr_par_upazila_id"
                          id="gr_par_upazila_id" onchange="fnAjaxSelectBox(
                                          'gr_par_union_id',
                                          this.value,
                              '{{base64_encode('gnl_unions')}}',
                              '{{base64_encode('upazila_id')}}',
                              '{{base64_encode('id,union_name')}}',
                              '{{url('/ajaxSelectBox')}}'
                                      );">
                          <option value="" selected="selected">Select Upazila</option>
                      </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>

                <div class="col-lg-6 form-group">
                    <div class="input-title">Select Union</div>
                    <div class="input-group">
                      <select class="form-control clsSelect2" required data-error="Please enter Union name."
                           name="gr_par_union_id"
                          id="gr_par_union_id" onchange="fnAjaxSelectBox(
                                          'gr_par_village_id',
                                          this.value,
                              '{{base64_encode('gnl_villages')}}',
                              '{{base64_encode('union_id')}}',
                              '{{base64_encode('id,village_name')}}',
                              '{{url('/ajaxSelectBox')}}'
                                      );">
                          <option value="" selected="selected">Select Union</option>
                      </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row align-items-center">
                <div class="col-lg-6 form-group">
                    <div class="input-title">Select Village</div>
                    <div class="input-group">
                      <select class="form-control clsSelect2"
                           name="gr_par_village_id"
                          id="gr_par_village_id">
                          <option value="" selected="selected">Select Village</option>
                      </select>
                    </div>
                </div>

                <div class="col-lg-6 form-group">
                    <div class="input-group">
                      <textarea class="form-control" id="gr_par_remarks" name="gr_par_remarks"
                          rows="2" placeholder="Enter Remarks">{{$GuarantorData->gr_par_remarks}}</textarea>
                    </div>
                </div>
            </div>
            <!-- <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Division</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2"
                             name="gr_par_division_id"
                            id="gr_par_division_id"
                            onchange="fnAjaxSelectBox(
                                            'gr_par_district_id',
                                            this.value,
                                '{{base64_encode('gnl_districts')}}',
                                '{{base64_encode('division_id')}}',
                                '{{base64_encode('id,district_name')}}',
                                '{{url('/ajaxSelectBox')}}'
                                        );">
                            <option value="" selected="selected">Select Division</option>
                            @foreach ($DivisionData as $Row)
                            <option value="{{$Row->id}}"
                                @if($GuarantorData->gr_par_division_id == $Row->id) selected @endif >
                                {{$Row->division_name}}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">District</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2"
                             name="gr_par_district_id"
                            id="gr_par_district_id"
                            onchange="fnAjaxSelectBox(
                                            'gr_par_upazila_id',
                                            this.value,
                                '{{base64_encode('gnl_upazilas')}}',
                                '{{base64_encode('district_id')}}',
                                '{{base64_encode('id,upazila_name')}}',
                                '{{url('/ajaxSelectBox')}}'
                                        );">
                            <option value="" selected="selected">Select District</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Upazila</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2"
                             name="gr_par_upazila_id"
                            id="gr_par_upazila_id" onchange="fnAjaxSelectBox(
                                            'gr_par_union_id',
                                            this.value,
                                '{{base64_encode('gnl_unions')}}',
                                '{{base64_encode('upazila_id')}}',
                                '{{base64_encode('id,union_name')}}',
                                '{{url('/ajaxSelectBox')}}'
                                        );">
                            <option value="" selected="selected">Select Upazila</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Union</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2"
                             name="gr_par_union_id"
                            id="gr_par_union_id" onchange="fnAjaxSelectBox(
                                            'gr_par_village_id',
                                            this.value,
                                '{{base64_encode('gnl_villages')}}',
                                '{{base64_encode('union_id')}}',
                                '{{base64_encode('id,village_name')}}',
                                '{{url('/ajaxSelectBox')}}'
                                        );">
                            <option value="" selected="selected">Select Union</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Village</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2"
                             name="gr_par_village_id"
                            id="gr_par_village_id">
                            <option value="" selected="selected">Select Village</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Enter Remarks</label>
                <div class="col-lg-7">
                    <textarea class="form-control round" id="gr_par_remarks" name="gr_par_remarks"
                        rows="1" placeholder="Enter Remarks">{{$GuarantorData->gr_par_remarks}}
                    </textarea>
                </div>
            </div> -->
            <!-- Parmanent Address Start -->

        </div>
    </div>
    <div class="row align-items-center">
        <div class="col-lg-12">
            <div class="form-group d-flex justify-content-center">
                <div class="example example-buttons">
                    <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                    <button type="submit" class="btn btn-primary btn-round"id="btnSubmit">Update</button>
                    <!-- <a href="#"><button type="button" class="btn btn-warning btn-round">Next</button></a> -->
                </div>
            </div>
        </div>
    </div>
</form>
<!-- End Page -->

<script>

    $(document).ready(function() {
        $('#btnSubmit').removeAttr("disabled");
        $('#btnSubmit').removeClass("disabled");

        // fnAjaxSelectBox(
        //                 'customer_id',
        //                 $('#branch_id').val(),
        //                 '{{base64_encode('pos_customers')}}',
        //                 '{{base64_encode('branch_id')}}',
        //                 '{{base64_encode('customer_no,customer_name')}}',
        //                 '{{url('/ajaxSelectBox')}}',
        //                 '{{ $GuarantorData->customer_id}}'
        //             );

        // Load Present Address values
        fnAjaxSelectBox(
            'gr_pre_district_id',
            '{{ $GuarantorData->gr_pre_division_id }}',
            '{{base64_encode("gnl_districts")}}',
            '{{base64_encode("division_id")}}',
            '{{base64_encode("id,district_name")}}',
            '{{url("/ajaxSelectBox")}}',
            '{{ $GuarantorData->gr_pre_district_id}}'
        );
        fnAjaxSelectBox(
            'gr_pre_upazila_id',
            '{{ $GuarantorData->gr_pre_district_id }}',
            '{{base64_encode("gnl_upazilas")}}',
            '{{base64_encode("district_id")}}',
            '{{base64_encode("id,upazila_name")}}',
            '{{url("/ajaxSelectBox")}}',
            '{{ $GuarantorData->gr_pre_upazila_id}}'
        );

        fnAjaxSelectBox(
            'gr_pre_union_id',
            '{{ $GuarantorData->gr_pre_upazila_id }}',
            '{{base64_encode("gnl_unions")}}',
            '{{base64_encode("upazila_id")}}',
            '{{base64_encode("id,union_name")}}',
            '{{url("/ajaxSelectBox")}}',
            '{{ $GuarantorData->gr_pre_union_id}}'
        );
        fnAjaxSelectBox(
            'gr_pre_village_id',
            '{{ $GuarantorData->gr_pre_union_id }}',
            '{{base64_encode("gnl_villages")}}',
            '{{base64_encode("union_id")}}',
            '{{base64_encode("id,village_name")}}',
            '{{url("/ajaxSelectBox")}}',
            '{{ $GuarantorData->gr_pre_village_id}}'
        );

        // Load Parmanent Address values
        fnAjaxSelectBox(
            'gr_par_district_id',
            '{{ $GuarantorData->gr_par_division_id }}',
            '{{base64_encode("gnl_districts")}}',
            '{{base64_encode("division_id")}}',
            '{{base64_encode("id,district_name")}}',
            '{{url("/ajaxSelectBox")}}',
            '{{ $GuarantorData->gr_par_district_id}}'
        );
        fnAjaxSelectBox(
            'gr_par_upazila_id',
            '{{ $GuarantorData->gr_par_district_id }}',
            '{{base64_encode("gnl_upazilas")}}',
            '{{base64_encode("district_id")}}',
            '{{base64_encode("id,upazila_name")}}',
            '{{url("/ajaxSelectBox")}}',
            '{{ $GuarantorData->gr_par_upazila_id}}'
        );

        fnAjaxSelectBox(
            'gr_par_union_id',
            '{{ $GuarantorData->gr_par_upazila_id }}',
            '{{base64_encode("gnl_unions")}}',
            '{{base64_encode("upazila_id")}}',
            '{{base64_encode("id,union_name")}}',
            '{{url("/ajaxSelectBox")}}',
            '{{ $GuarantorData->gr_par_union_id}}'
        );
        fnAjaxSelectBox(
            'gr_par_village_id',
            '{{ $GuarantorData->gr_par_union_id }}',
            '{{base64_encode("gnl_villages")}}',
            '{{base64_encode("union_id")}}',
            '{{base64_encode("id,village_name")}}',
            '{{url("/ajaxSelectBox")}}',
            '{{ $GuarantorData->gr_par_village_id}}'
        );

        $('#branch_id').change(function() {


            fnAjaxSelectBox(
                            'customer_id',
                            this.value,
                            '{{base64_encode('pos_customers')}}',
                            '{{base64_encode('branch_id')}}',
                            '{{base64_encode('customer_no,customer_name')}}',
                            '{{url('/ajaxSelectBox')}}'
                        );


        });

        if ($('.MarritalStatus').is(':checked')){
            var selIdTxt = $('.MarritalStatus:checked').val();
            $('.MarritalStatus').each(function() {

                if (selIdTxt == "Married") {
                    $('#spouse').show();
                } else {
                    $('#spouse').hide();
                }
            });
        }

        $(".MarritalStatus").click(function() {
            var selIdTxt = $(this).val();
            $('.MarritalStatus').each(function() {

                if (selIdTxt == "Married") {
                    $('#spouse').show();
                } else {
                    $('#spouse').hide();
                }
            });
        });
    });

    function GetSelectedText(id) {

        if ($('#' + id).is(':checked')) {

            $('#gr_par_division_id').val($('#gr_pre_division_id').val());
            $('#gr_par_division_id').trigger('change');

            fnAjaxSelectBox(
                'gr_par_district_id',
                $('#gr_par_division_id').val(),
                '{{base64_encode('gnl_districts')}}',
                '{{base64_encode('division_id')}}',
                '{{base64_encode('id,district_name')}}',
                '{{url('/ajaxSelectBox')}}',
                $('#gr_pre_district_id').val()
            );
            fnAjaxSelectBox(
                'gr_par_upazila_id',
                $('#gr_pre_district_id').val(),
                '{{base64_encode('gnl_upazilas')}}',
                '{{base64_encode('district_id')}}',
                '{{base64_encode('id,upazila_name')}}',
                '{{url('/ajaxSelectBox')}}',
                $('#gr_pre_upazila_id').val()
            );
            fnAjaxSelectBox(
                'gr_par_union_id',
                $('#gr_pre_upazila_id').val(),
                '{{base64_encode('gnl_unions')}}',
                '{{base64_encode('upazila_id')}}',
                '{{base64_encode('id,union_name')}}',
                '{{url('/ajaxSelectBox')}}',
                $('#gr_pre_union_id').val()
            );
            fnAjaxSelectBox(
                'gr_par_village_id',
                $('#gr_pre_union_id').val(),
                '{{base64_encode('gnl_villages')}}',
                '{{base64_encode('union_id')}}',
                '{{base64_encode('id,village_name')}}',
                '{{url('/ajaxSelectBox')}}',
                $('#gr_pre_village_id').val()
            );
            $('#gr_par_remarks').val($('#gr_pre_remarks').val());
        } else {
                $('#gr_par_division_id').val('');
                $('#gr_par_district_id').val('');
                $('#gr_par_upazila_id').val('');
                $('#gr_par_union_id').val('');
                $('#gr_par_village_id').val('');
                // $('#par_remarks').val('');

                // $('#gr_par_division_id').trigger('change');
                // $('#gr_par_district_id').trigger('change');
                // $('#gr_par_upazila_id').trigger('change');
                // $('#gr_par_union_id').trigger('change');
                // $('#gr_par_village_id').trigger('change');
            }
    }

    $('form').submit(function (event) {
        // event.preventDefault();
        $(this).find(':submit').attr('disabled', 'disabled');
        // $(this).submit();
    });

    var grId = "{{ $GuarantorData->id }}";

    if ($('.identification').is(':checked')){
        var idTxt = $('.identification:checked').val();

        // Natinal ID Validation
        if(idTxt === 'nid'){
            $('.identificationInput').attr("placeholder", "Enter NID No");
            $("#errMsg,#errMsgSC,#errMsgPP,#errMsgDL,#errMsgBR").prop('id', 'errMsgNID');
            $(".identificationInput").on("input", function(event){
                $(this).addClass('textNumber');
                var nidNo = $(this).val();
                if (nidNo.length > 0) {
                    if (nidNo.length != 13) {
                        if (nidNo.length != 17) {
                            $("#errMsgNID").html("Invalid NID! NID must be of 13 or 17 Digits");
                            $('#btnSubmit').attr('disabled', 'disabled');
                            $('#gr_nid').css("border-color","red");
                        }else if ( nidNo.length == 17 ){
                            $("#errMsgNID").html('');

                            // // // Duplicate Check
                        var query = $(this).val();
                        var forWhich = $(this).attr("name");
                        var tableName = btoa('pos_guarantors');

                        var columnName = $(this).attr("name")+'&&is_delete';
                        var columnValue = $(this).val()+'&&0';
                        var url_text = "{{url('/ajaxCheckDuplicate')}}";
                        var fieldID = $(this).attr("id");
                        var updateID = '{{ $GuarantorData->id }}';

                        fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                        'errMsgNID', 'NID', updateID);

                        if($('#'+ fieldID).val() !== ''){
                            $("#errMsgNID").html('');
                            $('#btnSubmit').removeAttr("disabled");
                            $('#btnSubmit').removeClass("disabled");
                            $('#gr_nid').css('border-color','#e4eaec');
                        }

                            // $.ajax({
                            //     type: "get",
                            //     url: "{{route('ajaxCheckDuplicate')}}",
                            //     data: {query: query, tableName: tableName, forWhich: forWhich},
                            //     dataType: "json",
                            //     success: function (data) {
                            //         if (data.exists) {
                            //             if (data.rowID != grId) {
                            //                 $('#btnSubmit').attr('disabled', 'disabled');
                            //                 $('#errMsgNID').html('Please enter unique NID');
                            //                 $('#gr_nid').css("border-color","red");
                            //             }
                            //             else {
                            //                 $('#btnSubmit').removeAttr("disabled");
                            //                 $('#btnSubmit').removeClass("disabled");
                            //                 $('#gr_nid').css('border-color','#e4eaec');
                            //             }
                            //         }
                            //         else {
                            //             $("#errMsgNID").html('');
                            //             $('#btnSubmit').removeAttr("disabled");
                            //             $('#btnSubmit').removeClass("disabled");
                            //             $('#gr_nid').css('border-color','#e4eaec');
                            //         }
                            //     },
                            // });
                        }
                    } else if ( nidNo.length == 13 ){
                        $("#errMsgNID").html('');

                            // // // Duplicate Check
                            var query = $(this).val();
                        var forWhich = $(this).attr("name");
                        var tableName = btoa('pos_guarantors');

                        var columnName = $(this).attr("name")+'&&is_delete';
                        var columnValue = $(this).val()+'&&0';
                        var url_text = "{{url('/ajaxCheckDuplicate')}}";
                        var fieldID = $(this).attr("id");
                        var updateID = '{{ $GuarantorData->id }}';

                        fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                        'errMsgNID', 'NID', updateID);

                        if($('#'+ fieldID).val() !== ''){
                            $('#btnSubmit').removeAttr("disabled");
                            $('#btnSubmit').removeClass("disabled");
                            $('#gr_nid').css('border-color','#e4eaec');
                        }


                        // $.ajax({
                        //     type: "get",
                        //     url: "{{route('ajaxCheckDuplicate')}}",
                        //     data: {query: query, tableName: tableName, forWhich: forWhich},
                        //     dataType: "json",
                        //     success: function (data) {
                        //         if (data.exists) {
                        //             if (data.rowID != grId) {
                        //                 $('#btnSubmit').attr('disabled', 'disabled');
                        //                 $('#errMsgNID').html('Please enter unique NID');
                        //                 $('#gr_nid').css("border-color","red");
                        //             }
                        //             else {
                        //                 $('#btnSubmit').removeAttr("disabled");
                        //                 $('#btnSubmit').removeClass("disabled");
                        //                 $('#gr_nid').css('border-color','#e4eaec');
                        //             }
                        //         }
                        //         else {
                        //             $('#btnSubmit').removeAttr("disabled");
                        //             $('#btnSubmit').removeClass("disabled");
                        //             $('#gr_nid').css('border-color','#e4eaec');
                        //         }
                        //     },
                        // });
                    }
                }
                else {
                    $("#errMsgNID").html('');
                }
            });
        }
        else if(idTxt === 'smartCard'){
            $(this).attr("placeholder", "Enter Smart Card No");
            $("#errMsg,#errMsgNID,#errMsgPP,#errMsgDL,#errMsgBR").prop('id', 'errMsgSC');
            $(".identificationInput").on("input", function(event){
                $(this).addClass('textNumber');
                var cardNo = $(this).val();
                if (cardNo.length > 0) {
                    if(cardNo.length != 10) {
                        $("#errMsgSC").html("Not a valid 10-digit Smart Card Number");
                        $('#btnSubmit').attr('disabled', 'disabled');
                        $('#gr_nid').css("border-color","red");
                    }
                    else {
                        $("#errMsgSC").html('');


                        // // // Duplicate Check
                        var query = $(this).val();
                        var forWhich = $(this).attr("name");
                        var tableName = btoa('pos_guarantors');

                        var columnName = $(this).attr("name")+'&&is_delete';
                        var columnValue = $(this).val()+'&&0';
                        var url_text = "{{url('/ajaxCheckDuplicate')}}";
                        var fieldID = $(this).attr("id");
                        var updateID = '{{ $GuarantorData->id }}';

                        fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                        'errMsgSC', 'smart card no.', updateID);

                        if($('#'+ fieldID).val() !== ''){
                            $("#errMsgSC").html('');
                            $('#btnSubmit').removeAttr("disabled");
                            $('#btnSubmit').removeClass("disabled");
                            $('#gr_nid').css('border-color','#e4eaec');
                        }


                        // $.ajax({
                        //     type: "get",
                        //     url: "{{route('ajaxCheckDuplicate')}}",
                        //     data: {query: query, tableName: tableName, forWhich: forWhich},
                        //     dataType: "json",
                        //     success: function (data) {
                        //         if (data.exists) {
                        //             if (data.rowID != grId) {
                        //                 $('#btnSubmit').attr('disabled', 'disabled');
                        //                 $('#errMsgSC').html('Please enter unique Card No');
                        //                 $('#gr_nid').css("border-color","red");
                        //             }
                        //             else {
                        //                 $("#errMsgSC").html('');
                        //                 $('#btnSubmit').removeAttr("disabled");
                        //                 $('#btnSubmit').removeClass("disabled");
                        //                 $('#gr_nid').css('border-color','#e4eaec');
                        //             }
                        //         }
                        //         else {
                        //             $("#errMsgSC").html('');
                        //             $('#btnSubmit').removeAttr("disabled");
                        //             $('#btnSubmit').removeClass("disabled");
                        //             $('#gr_nid').css('border-color','#e4eaec');
                        //         }
                        //     },
                        // });
                    }
                }
                else {
                    $("#errMsgSC").html('');
                }
            });
        }
        else if(idTxt === 'passport'){
            $('.identificationInput').attr("placeholder", "Enter passport No");
            $("#errMsg,#errMsgNID,#errMsgSC,#errMsgDL,#errMsgBR").prop('id', 'errMsgPP');
            $(".identificationInput").on("input", function(event){
                $(this).addClass('textNumber');
                var passportNo = $(this).val();
                if(passportNo.length != 0) {
                    if (passportNo.length != 9) {
                        $("#btnSubmit").attr("disabled", "disabled");
                        $("#errMsgPP").html("Not a valid 9-digit Passport Number");
                        $('#gr_nid').css("border-color","red");
                    } else if ( passportNo.length == 9 ){
                            $("#errMsgPP").html('');

                            // // // Duplicate Check
                        var query = $(this).val();
                        var forWhich = $(this).attr("name");
                        var tableName = btoa('pos_guarantors');

                        var columnName = $(this).attr("name")+'&&is_delete';
                        var columnValue = $(this).val()+'&&0';
                        var url_text = "{{url('/ajaxCheckDuplicate')}}";
                        var fieldID = $(this).attr("id");
                        var updateID = '{{ $GuarantorData->id }}';

                        fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                        'errMsgPP', 'passport no.', updateID);

                        if($('#'+ fieldID).val() !== ''){
                            $("#errMsgPP").html('');
                            $('#btnSubmit').removeAttr("disabled");
                            $('#btnSubmit').removeClass("disabled");
                            $('#gr_nid').css('border-color','#e4eaec');
                        }


                            // $.ajax({
                            //     type: "get",
                            //     url: "{{route('ajaxCheckDuplicate')}}",
                            //     data: {query: query, tableName: tableName, forWhich: forWhich},
                            //     dataType: "json",
                            //     success: function (data) {
                            //         if (data.exists) {
                            //             if (data.rowID != grId) {
                            //                 $('#btnSubmit').attr('disabled', 'disabled');
                            //                 $('#errMsgPP').html('Please enter unique Passport No');
                            //                 $('#gr_nid').css("border-color","red");
                            //             }
                            //             else {
                            //                 $("#errMsgPP").html('');
                            //                 $('#btnSubmit').removeAttr("disabled");
                            //                 $('#btnSubmit').removeClass("disabled");
                            //                 $('#gr_nid').css('border-color','#e4eaec');
                            //             }
                            //         }
                            //         else {
                            //             $("#errMsgPP").html('');
                            //             $('#btnSubmit').removeAttr("disabled");
                            //             $('#btnSubmit').removeClass("disabled");
                            //             $('#gr_nid').css('border-color','#e4eaec');
                            //         }
                            //     },
                            // });
                        }
                }
                else {
                    $("#errMsgPP").html('');
                }
            });

        }
        else if(idTxt === 'drivingLicense'){
            $('.identificationInput').attr("placeholder", "Enter Driving License No");
            $("#errMsg,#errMsgNID,#errMsgSC,#errMsgPP,#errMsgBR").prop('id', 'errMsgDL');
            $(".identificationInput").on("input", function(event){
                $(this).removeClass('textNumber');
                var licenceNo = $(this).val();
                if(licenceNo.length != 0) {
                    if (licenceNo.length != 15) {
                        $("#btnSubmit").attr("disabled", "disabled");
                        $("#errMsgDL").html("Not a valid 15-digit Driving Licence Number");
                        $('#gr_nid').css("border-color","red");
                    } else if ( licenceNo.length == 15 ){
                            $("#errMsgDL").html('');

                            // // // Duplicate Check
                            var query = $(this).val();
                            var forWhich = $(this).attr("name");
                            var tableName = btoa('pos_guarantors');

                            var columnName = $(this).attr("name")+'&&is_delete';
                            var columnValue = $(this).val()+'&&0';
                            var url_text = "{{url('/ajaxCheckDuplicate')}}";
                            var fieldID = $(this).attr("id");
                            var updateID = '{{ $GuarantorData->id }}';

                            fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                            'errMsgDL', 'driving license no.', updateID);

                            if($('#'+ fieldID).val() !== ''){
                                $("#errMsgDL").html('');
                                $('#btnSubmit').removeAttr("disabled");
                                $('#btnSubmit').removeClass("disabled");
                                $('#gr_nid').css('border-color','#e4eaec');
                            }


                            // $.ajax({
                            //     type: "get",
                            //     url: "{{route('ajaxCheckDuplicate')}}",
                            //     data: {query: query, tableName: tableName, forWhich: forWhich},
                            //     dataType: "json",
                            //     success: function (data) {
                            //         if (data.exists) {
                            //             if (data.rowID != grId) {
                            //                 $('#btnSubmit').attr('disabled', 'disabled');
                            //                 $('#errMsgDL').html('Please enter unique Driving Licence No');
                            //                 $('#gr_nid').css("border-color","red");
                            //             }
                            //             else {
                            //                 $("#errMsgDL").html('');
                            //                 $('#btnSubmit').removeAttr("disabled");
                            //                 $('#btnSubmit').removeClass("disabled");
                            //                 $('#gr_nid').css('border-color','#e4eaec');
                            //             }
                            //         }
                            //         else {
                            //             $("#errMsgDL").html('');
                            //             $('#btnSubmit').removeAttr("disabled");
                            //             $('#btnSubmit').removeClass("disabled");
                            //             $('#gr_nid').css('border-color','#e4eaec');
                            //         }
                            //     },
                            // });
                        }
                }
                else {
                    $("#errMsgDL").html('');
                }
            });
        }
        else if(idTxt === 'birthCertificate'){
            $('.identificationInput').attr("placeholder", "Enter Birth Registration No");
            $("#errMsg,#errMsgNID,#errMsgSC,#errMsgPP,#errMsgDL").prop('id', 'errMsgBR');
            $(".identificationInput").on("input", function(event){
                $(this).addClass('textNumber');
                var brNo = $(this).val();
                if(brNo.length != 0) {
                    if (brNo.length != 17) {
                        $("#btnSubmit").attr("disabled", "disabled");
                        $("#errMsgBR").html("Not a valid 17-digit Birth Registration Number");
                        $('#gr_nid').css("border-color","red");
                    } else if ( brNo.length == 17 ){
                            $("#errMsgBR").html('');

                            // // // Duplicate Check
                            var query = $(this).val();
                            var forWhich = $(this).attr("name");
                            var tableName = btoa('pos_guarantors');

                            var columnName = $(this).attr("name")+'&&is_delete';
                            var columnValue = $(this).val()+'&&0';
                            var url_text = "{{url('/ajaxCheckDuplicate')}}";
                            var fieldID = $(this).attr("id");
                            var updateID = '{{ $GuarantorData->id }}';

                            fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                            'errMsgBR', 'birth registration no.', updateID);

                            if($('#'+ fieldID).val() !== ''){
                                $("#errMsgBR").html('');
                                $('#btnSubmit').removeAttr("disabled");
                                $('#btnSubmit').removeClass("disabled");
                                $('#gr_nid').css('border-color','#e4eaec');
                            }


                            // $.ajax({
                            //     type: "get",
                            //     url: "{{route('ajaxCheckDuplicate')}}",
                            //     data: {query: query, tableName: tableName, forWhich: forWhich},
                            //     dataType: "json",
                            //     success: function (data) {
                            //         if (data.exists) {
                            //             if (data.rowID != grId) {
                            //                 $('#btnSubmit').attr('disabled', 'disabled');
                            //                 $('#errMsgBR').html('Please enter unique Birth Registration No');
                            //                 $('#gr_nid').css("border-color","red");
                            //             }
                            //             else {
                            //                 $("#errMsgBR").html('');
                            //                 $('#btnSubmit').removeAttr("disabled");
                            //                 $('#btnSubmit').removeClass("disabled");
                            //                 $('#gr_nid').css('border-color','#e4eaec');
                            //             }
                            //         }
                            //         else {
                            //             $("#errMsgBR").html('');
                            //             $('#btnSubmit').removeAttr("disabled");
                            //             $('#btnSubmit').removeClass("disabled");
                            //             $('#gr_nid').css('border-color','#e4eaec');
                            //         }
                            //     },
                            // });
                        }
                }
                else {
                    $("#errMsgBR").html('');
                }
            });
        }
    }

$(".identification").click(function() {
    $("#errMsg,#errMsgNID,#errMsgSC,#errMsgPP,#errMsgDL,#errMsgBR").html('');
    $('#gr_nid').val('');
    var selIdTxt = $(this).val();

    if ("{{ $GuarantorData->gr_id_type }}" == selIdTxt) {
        $('.identificationInput').val("{{ $GuarantorData->gr_nid }}");
    }

    $( '.identificationInput' ).each(function() {

        if(selIdTxt === 'nid'){
            $(this).attr("placeholder", "Enter NID No");
            $("#errMsg,#errMsgSC,#errMsgPP,#errMsgDL,#errMsgBR").prop('id', 'errMsgNID');
            $(".identificationInput").on("input", function(event){
                $(this).addClass('textNumber');
                var nidNo = $(this).val();
                if (nidNo.length != 0) {
                    if (nidNo.length != 13) {
                        if (nidNo.length != 17) {
                            $("#errMsgNID").html("Invalid NID! NID must be of 13 or 17 Digits");
                            $('#btnSubmit').attr('disabled', 'disabled');
                        }else if ( nidNo.length == 17 ){
                            $("#errMsgNID").html('');

                            // // // Duplicate Check
                            var query = $(this).val();
                            var forWhich = $(this).attr("name");
                            var tableName = btoa('pos_guarantors');

                            var columnName = $(this).attr("name")+'&&is_delete';
                            var columnValue = $(this).val()+'&&0';
                            var url_text = "{{url('/ajaxCheckDuplicate')}}";
                            var fieldID = $(this).attr("id");
                            var updateID = '{{ $GuarantorData->id }}';

                            fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                            'errMsgNID', 'NID', updateID);

                            if($('#'+ fieldID).val() !== ''){
                                $("#errMsgNID").html('');
                                $('#btnSubmit').removeAttr("disabled");
                                $('#btnSubmit').removeClass("disabled");
                                $('#gr_nid').css('border-color','#e4eaec');
                            }


                            // $.ajax({
                            //     type: "get",
                            //     url: "{{route('ajaxCheckDuplicate')}}",
                            //     data: {query: query, tableName: tableName, forWhich: forWhich},
                            //     dataType: "json",
                            //     success: function (data) {
                            //         if (data.exists) {
                            //             if (data.rowID != grId) {
                            //                 $('#btnSubmit').attr('disabled', 'disabled');
                            //                 $('#errMsgNID').html('Please enter unique NID');
                            //                 $('#gr_nid').css("border-color","red");
                            //             }
                            //             else {
                            //                 $("#errMsgNID").html('');
                            //                 $('#btnSubmit').removeAttr("disabled");
                            //                 $('#btnSubmit').removeClass("disabled");
                            //                 $('#gr_nid').css('border-color','#e4eaec');
                            //             }
                            //         }
                            //         else {
                            //             $("#errMsgNID").html('');
                            //             $('#btnSubmit').removeAttr("disabled");
                            //             $('#btnSubmit').removeClass("disabled");
                            //             $('#gr_nid').css('border-color','#e4eaec');
                            //         }
                            //     },
                            // });

                        }
                    } else if ( nidNo.length == 13 ){
                        $("#errMsgNID").html('');

                        // // // Duplicate Check
                        var query = $(this).val();
                        var forWhich = $(this).attr("name");
                        var tableName = btoa('pos_guarantors');

                        var columnName = $(this).attr("name")+'&&is_delete';
                        var columnValue = $(this).val()+'&&0';
                        var url_text = "{{url('/ajaxCheckDuplicate')}}";
                        var fieldID = $(this).attr("id");
                        var updateID = '{{ $GuarantorData->id }}';

                        fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                        'errMsgNID', 'NID', updateID);

                        if($('#'+ fieldID).val() !== ''){
                            $("#errMsgNID").html('');
                            $('#btnSubmit').removeAttr("disabled");
                            $('#btnSubmit').removeClass("disabled");
                            $('#gr_nid').css('border-color','#e4eaec');
                        }

                        // $.ajax({
                        //     type: "get",
                        //     url: "{{route('ajaxCheckDuplicate')}}",
                        //     data: {query: query, tableName: tableName, forWhich: forWhich},
                        //     dataType: "json",
                        //     success: function (data) {
                        //         if (data.exists) {
                        //             if (data.rowID != grId) {
                        //                 $('#btnSubmit').attr('disabled', 'disabled');
                        //                 $('#errMsgNID').html('Please enter unique NID');
                        //                 $('#gr_nid').css("border-color","red");
                        //             }
                        //             else {
                        //                 $("#errMsgNID").html('');
                        //                 $('#btnSubmit').removeAttr("disabled");
                        //                 $('#btnSubmit').removeClass("disabled");
                        //                 $('#gr_nid').css('border-color','#e4eaec');
                        //             }
                        //         }
                        //         else {
                        //             $("#errMsgNID").html('');
                        //             $('#btnSubmit').removeAttr("disabled");
                        //             $('#btnSubmit').removeClass("disabled");
                        //             $('#gr_nid').css('border-color','#e4eaec');
                        //         }
                        //     },
                        // });
                    }
                }
                else {
                    $("#errMsgNID").html('');
                }
            });
        }
        else if(selIdTxt === 'smartCard'){
            $(this).attr("placeholder", "Enter Smart Card No");
            $("#errMsg,#errMsgNID,#errMsgPP,#errMsgDL,#errMsgBR").prop('id', 'errMsgSC');
            $(".identificationInput").on("input", function(event){
                $(this).addClass('textNumber');
                var cardNo = $(this).val();
                if (cardNo.length > 0) {
                    if(cardNo.length != 10) {
                        $("#errMsgSC").html("Not a valid 10-digit Smart Card Number");
                        $('#btnSubmit').attr('disabled', 'disabled');
                        $('#gr_nid').css("border-color","red");
                    }else if ( cardNo.length == 10 ){
                        $("#errMsgSC").html('');

                        // // // Duplicate Check
                        var query = $(this).val();
                        var forWhich = $(this).attr("name");
                        var tableName = btoa('pos_guarantors');

                        var columnName = $(this).attr("name")+'&&is_delete';
                        var columnValue = $(this).val()+'&&0';
                        var url_text = "{{url('/ajaxCheckDuplicate')}}";
                        var fieldID = $(this).attr("id");
                        var updateID = '{{ $GuarantorData->id }}';

                        fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                        'errMsgSC', 'smart card no.', updateID);

                        if($('#'+ fieldID).val() !== ''){
                            $("#errMsgSC").html('');
                            $('#btnSubmit').removeAttr("disabled");
                            $('#btnSubmit').removeClass("disabled");
                            $('#gr_nid').css('border-color','#e4eaec');
                        }


                        // $.ajax({
                        //     type: "get",
                        //     url: "{{route('ajaxCheckDuplicate')}}",
                        //     data: {query: query, tableName: tableName, forWhich: forWhich},
                        //     dataType: "json",
                        //     success: function (data) {
                        //         if (data.exists) {
                        //             if (data.rowID != grId) {
                        //                 $('#btnSubmit').attr('disabled', 'disabled');
                        //                 $('#errMsgSC').html('Please enter unique Card No');
                        //                 $('#gr_nid').css("border-color","red");
                        //             }
                        //             else {
                        //                 $("#errMsgSC").html('');
                        //                 $('#btnSubmit').removeAttr("disabled");
                        //                 $('#btnSubmit').removeClass("disabled");
                        //                 $('#gr_nid').css('border-color','#e4eaec');
                        //             }
                        //         }
                        //         else {
                        //             $("#errMsgSC").html('');
                        //             $('#btnSubmit').removeAttr("disabled");
                        //             $('#btnSubmit').removeClass("disabled");
                        //             $('#gr_nid').css('border-color','#e4eaec');
                        //         }
                        //     },
                        // });
                    }
                }
                else {
                    $("#errMsgSC").html('');
                }
            });
        }
        else if(selIdTxt === 'passport'){
            $(this).attr("placeholder", "Enter passport No");
            $("#errMsg,#errMsgNID,#errMsgSC,#errMsgDL,#errMsgBR").prop('id', 'errMsgPP');
            $(".identificationInput").on("input", function(event){
                $(this).addClass('textNumber');
                var passportNo = $(this).val();
                if(passportNo.length != 0) {
                    if (passportNo.length != 9) {
                        $("#btnSubmit").attr("disabled", "disabled");
                        $("#errMsgPP").html("Not a valid 9-digit Passport Number");
                        $('#gr_nid').css("border-color","red");
                    } else if ( passportNo.length == 9 ){
                        $("#errMsgPP").html('');

                        // // // Duplicate Check
                        var query = $(this).val();
                        var forWhich = $(this).attr("name");
                        var tableName = btoa('pos_guarantors');

                        var columnName = $(this).attr("name")+'&&is_delete';
                        var columnValue = $(this).val()+'&&0';
                        var url_text = "{{url('/ajaxCheckDuplicate')}}";
                        var fieldID = $(this).attr("id");
                        var updateID = '{{ $GuarantorData->id }}';

                        fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                        'errMsgPP', 'passport no.', updateID);

                        if($('#'+ fieldID).val() !== ''){
                            $("#errMsgPP").html('');
                            $('#btnSubmit').removeAttr("disabled");
                            $('#btnSubmit').removeClass("disabled");
                            $('#gr_nid').css('border-color','#e4eaec');
                        }


                        // $.ajax({
                        //     type: "get",
                        //     url: "{{route('ajaxCheckDuplicate')}}",
                        //     data: {query: query, tableName: tableName, forWhich: forWhich},
                        //     dataType: "json",
                        //     success: function (data) {
                        //         if (data.exists) {
                        //             if (data.rowID != grId) {
                        //                 $('#btnSubmit').attr('disabled', 'disabled');
                        //                 $('#errMsgPP').html('Please enter unique Passport No');
                        //                 $('#gr_nid').css("border-color","red");
                        //             }
                        //             else {
                        //                 $("#errMsgPP").html('');
                        //                 $('#btnSubmit').removeAttr("disabled");
                        //                 $('#btnSubmit').removeClass("disabled");
                        //                 $('#gr_nid').css('border-color','#e4eaec');
                        //             }
                        //         }
                        //         else {
                        //             $("#errMsgPP").html('');
                        //             $('#btnSubmit').removeAttr("disabled");
                        //             $('#btnSubmit').removeClass("disabled");
                        //             $('#gr_nid').css('border-color','#e4eaec');
                        //         }
                        //     },
                        // });
                    }
                }
                else {
                    $("#errMsgPP").html('');
                }
            });

        }
        else if(selIdTxt === 'drivingLicense'){
            $(this).attr("placeholder", "Enter Driving License No");
            $("#errMsg,#errMsgNID,#errMsgSC,#errMsgPP,#errMsgBR").prop('id', 'errMsgDL');
            $(".identificationInput").on("input", function(event){
                $(this).removeClass('textNumber');
                var licenceNo = $(this).val();
                if(licenceNo.length != 0) {
                    if (licenceNo.length != 15) {
                        $("#btnSubmit").attr("disabled", "disabled");
                        $("#errMsgDL").html("Not a valid 15-digit Driving Licence Number");
                        $('#gr_nid').css("border-color","red");
                    } else if ( licenceNo.length == 15 ){
                        $("#errMsgDL").html('');

                        // // // Duplicate Check
                        var query = $(this).val();
                        var forWhich = $(this).attr("name");
                        var tableName = btoa('pos_guarantors');

                        var columnName = $(this).attr("name")+'&&is_delete';
                        var columnValue = $(this).val()+'&&0';
                        var url_text = "{{url('/ajaxCheckDuplicate')}}";
                        var fieldID = $(this).attr("id");
                        var updateID = '{{ $GuarantorData->id }}';

                        fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                        'errMsgDL', 'driving license no.', updateID);

                        if($('#'+ fieldID).val() !== ''){
                            $("#errMsgDL").html('');
                            $('#btnSubmit').removeAttr("disabled");
                            $('#btnSubmit').removeClass("disabled");
                            $('#gr_nid').css('border-color','#e4eaec');
                        }


                        // $.ajax({
                        //     type: "get",
                        //     url: "{{route('ajaxCheckDuplicate')}}",
                        //     data: {query: query, tableName: tableName, forWhich: forWhich},
                        //     dataType: "json",
                        //     success: function (data) {
                        //         if (data.exists) {
                        //             if (data.rowID != grId) {
                        //                 $('#btnSubmit').attr('disabled', 'disabled');
                        //                 $('#errMsgDL').html('Please enter unique Driving Licence No');
                        //                 $('#gr_nid').css("border-color","red");
                        //             }
                        //             else {
                        //                 $("#errMsgDL").html('');
                        //                 $('#btnSubmit').removeAttr("disabled");
                        //                 $('#btnSubmit').removeClass("disabled");
                        //                 $('#gr_nid').css('border-color','#e4eaec');
                        //             }
                        //         }
                        //         else {
                        //             $("#errMsgDL").html('');
                        //             $('#btnSubmit').removeAttr("disabled");
                        //             $('#btnSubmit').removeClass("disabled");
                        //             $('#gr_nid').css('border-color','#e4eaec');
                        //         }
                        //     },
                        // });
                    }
                }
                else {
                    $("#errMsgDL").html('');
                }
            });
        }
        else if(selIdTxt === 'birthCertificate'){
            $(this).attr("placeholder", "Enter Birth Registration No");
            $("#errMsg,#errMsgNID,#errMsgSC,#errMsgPP,#errMsgDL").prop('id', 'errMsgBR');
            $(".identificationInput").on("input", function(event){
                $(this).addClass('textNumber');
                var brNo = $(this).val();
                if(brNo.length != 0) {
                    if (brNo.length != 17) {
                        $("#btnSubmit").attr("disabled", "disabled");
                        $("#errMsgBR").html("Not a valid 17-digit Birth Registration Number");
                        $('#gr_nid').css("border-color","red");
                    }
                    else if ( brNo.length == 17 ){
                        $("#errMsgBR").html('');

                        // // // Duplicate Check
                        var query = $(this).val();
                        var forWhich = $(this).attr("name");
                        var tableName = btoa('pos_guarantors');

                        var columnName = $(this).attr("name")+'&&is_delete';
                        var columnValue = $(this).val()+'&&0';
                        var url_text = "{{url('/ajaxCheckDuplicate')}}";
                        var fieldID = $(this).attr("id");
                        var updateID = '{{ $GuarantorData->id }}';

                        fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                        'errMsgBR', 'birth registration no.', updateID);

                        if($('#'+ fieldID).val() !== ''){
                            $("#errMsgBR").html('');
                            $('#btnSubmit').removeAttr("disabled");
                            $('#btnSubmit').removeClass("disabled");
                            $('#gr_nid').css('border-color','#e4eaec');
                        }


                        // $.ajax({
                        //     type: "get",
                        //     url: "{{route('ajaxCheckDuplicate')}}",
                        //     data: {query: query, tableName: tableName, forWhich: forWhich},
                        //     dataType: "json",
                        //     success: function (data) {
                        //         if (data.exists) {
                        //             if (data.rowID != grId) {
                        //                 $('#btnSubmit').attr('disabled', 'disabled');
                        //                 $('#errMsgBR').html('Please enter unique Birth Registration No');
                        //                 $('#gr_nid').css("border-color","red");
                        //             }
                        //             else {
                        //                 $("#errMsgBR").html('');
                        //                 $('#btnSubmit').removeAttr("disabled");
                        //                 $('#btnSubmit').removeClass("disabled");
                        //                 $('#gr_nid').css('border-color','#e4eaec');
                        //             }
                        //         }
                        //         else {
                        //             $("#errMsgBR").html('');
                        //             $('#btnSubmit').removeAttr("disabled");
                        //             $('#btnSubmit').removeClass("disabled");
                        //             $('#gr_nid').css('border-color','#e4eaec');
                        //         }
                        //     },
                        // });
                    }
                }
                else {
                    $("#errMsgBR").html('');
                }
            });
        }

    });
});

</script>

@endsection
