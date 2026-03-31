@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\HtmlService as HTML;
?>
<div class="page">
    <div class="page-header">
        <h4 class="">View Guarantor</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('pos') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0)">Customer</a></li>
            <li class="breadcrumb-item"><a href="{{ url('pos/guarantor') }}">Guarantor</a></li>
            <li class="breadcrumb-item active">View</li>
        </ol>
    </div>
    <div class="page-content">
        <div class="panel">
            <div class="panel-body">
                <form class="form-horizontal"  data-toggle="validator" novalidate="true">
                    <div class="row">
                        <div class="col-lg-9 offset-3 mb-2">
                            <!-- Html View Load  -->
                            {!! HTML::forCompanyFeild($GuarantorData->company_id,'disabled') !!}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">

                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title RequiredStar" for="selCustID">Customer Name</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <select class="form-control clsSelect2"
                                             name="customer_id" id="customer_id" disabled>
                                            @foreach ($CustomerData as $Row)
                                            <option value="{{$Row->id}}" @if($Row->id == $GuarantorData->customer_id) selected @endif>{{$Row->customer_name}}</option>
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
                                            value="{{$GuarantorData->gr_name}}" readonly>
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
                                            value="{{$GuarantorData->gr_father_name}}" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title">Mother's Name</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="gr_mother_name"
                                            name="gr_mother_name" placeholder="N/A"
                                            value="{{$GuarantorData->gr_mother_name}}" readonly>
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
                                            value="{{$GuarantorData->gr_dob}}" readonly>
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
                                        <input type="text" class="form-control round" id="gr_spouse_name"
                                            name="gr_spouse_name" placeholder="N/A"
                                            value="{{$GuarantorData->gr_spouse_name}}" readonly>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                            </div>

                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">Email</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <input type="email" class="form-control round" id="gr_email" name="gr_email"
                                            placeholder="Enter Email"
                                         value="{{$GuarantorData->gr_email}}" readonly>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                    <div class="help-block with-errors is-invalid" id="txtCodeError"></div>
                                </div>
                            </div>

                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">Mobile</label>
                                <div class="col-lg-7">
                                    <div class="input-group ">
                                        <input type="Number" class="form-control round" id="gr_mobile"
                                            name="gr_mobile" placeholder="Enter Mobile Number" 
                                         value="{{$GuarantorData->gr_mobile}}" readonly>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                    <div class="help-block with-errors is-invalid" id="txtCodeError2"></div>
                                </div>
                            </div>

                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">National ID</label>
                                <div class="col-lg-7">
                                    <div class="input-group ">
                                        <input type="text" class="form-control round" id="gr_nid" name="gr_nid"
                                            placeholder="Enter National ID"
                                            value="{{$GuarantorData->gr_nid}}" readonly>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                            </div>

                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title">Yearly Income</label>
                                <div class="col-lg-7">
                                    <div class="input-group ">
                                        <input type="text" class="form-control round" id="gr_yearly_income"
                                            name="gr_yearly_income" placeholder="N/A"
                                            value="{{$GuarantorData->gr_yearly_income}}" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title">Relation</label>
                                <div class="col-lg-7">
                                    <div class="input-group ">
                                        <input type="text" class="form-control round" id="gr_relation_with"
                                            name="gr_relation_with" placeholder="N/A"
                                            value="{{$GuarantorData->gr_relation_with}}" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title">Description</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <textarea class="form-control round" id="gr_desc" name="gr_desc" rows="1"
                                            placeholder="N/A" readonly>{{$GuarantorData->gr_desc}}</textarea>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Present Address Start -->
                        <div class="col-lg-6">
                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title">Present Address</label>
                                <div class="col-lg-7">
                                </div>
                            </div>
                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title">Division</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <select class="form-control clsSelect2"
                                             name="gr_pre_division_id"
                                            id="gr_pre_division_id" disabled>
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
                                            id="gr_pre_district_id" disabled>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title">Upazila</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <select class="form-control clsSelect2"
                                             name="gr_pre_upazila_id"
                                            id="gr_pre_upazila_id" disabled>
                                            <option value="">Select Upazila</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title">Union</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <select class="form-control clsSelect2"
                                             name="gr_pre_union_id"
                                            id="gr_pre_union_id" disabled>
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
                                            id="gr_pre_village_id" disabled>
                                        </select>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                            </div>
                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title">Enter Remarks</label>
                                <div class="col-lg-7">
                                    <textarea class="form-control round" id="gr_pre_remarks" name="gr_pre_remarks"
                                        rows="1" placeholder="Enter Remarks" readonly>{{$GuarantorData->gr_pre_remarks}}  
                                    </textarea>
                                </div>
                            </div>
                            <!-- Present Address End -->

                            <!-- Parmanent Address Start -->

                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title">Parmanent Address</label>
                                <div class="col-lg-7">
                                </div>
                            </div>
                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title">Division</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <select class="form-control clsSelect2"
                                             name="gr_par_division_id"
                                            id="gr_par_division_id" disabled>
                                            @foreach ($DivisionData as $Row)
                                            <option value="{{$Row->id}}" 
                                                @if($GuarantorData->gr_par_division_id == $Row->id) selected @endif >
                                                {{$Row->division_name}}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title">District</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <select class="form-control clsSelect2"
                                             name="gr_par_district_id"
                                            id="gr_par_district_id" disabled>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title">Upazila</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <select class="form-control clsSelect2"
                                             name="gr_par_upazila_id"
                                            id="gr_par_upazila_id" disabled>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title">Union</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <select class="form-control clsSelect2"
                                             name="gr_par_union_id"
                                            id="gr_par_union_id" disabled>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title">Village</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <select class="form-control clsSelect2"
                                             name="gr_par_village_id"
                                            id="gr_par_village_id" disabled>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title">Enter Remarks</label>
                                <div class="col-lg-7">
                                    <textarea class="form-control round" id="txtGrParRemarks" name="gr_par_remarks"
                                        rows="1" placeholder="Enter Remarks" readonly>{{$GuarantorData->gr_par_remarks}}  
                                    </textarea>
                                </div>
                            </div>
                            <!-- Parmanent Address Start -->

                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-lg-12">
                            <div class="form-group d-flex justify-content-center">
                                <div class="example example-buttons">
                                    <a href="{{ url('pos/guarantor') }}" class="btn btn-default btn-round">Back</a>
                                    <!-- <a href="#"><button type="button" class="btn btn-warning btn-round">Next</button></a> -->
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
<!-- End Page -->

<script type="text/javascript">
// -----If Married Radio Button is clicked, 
//        show Spouse Name Div  -------//
$(document).ready(function() {

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
</script>

<script>

    $(document).ready(function() {

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

                $('#gr_par_division_id').trigger('change');
                $('#gr_par_district_id').trigger('change');
                $('#gr_par_upazila_id').trigger('change');
                $('#gr_par_union_id').trigger('change');
                $('#gr_par_village_id').trigger('change');
            }
    }

    // Disable radio button
    $(':radio:not(:checked)').attr('disabled', true);

</script>

@endsection