@extends('Layouts.erp_master')

@section('content')
<div class="page">
    <div class="page-header">
        <h4 class="">View Customer </h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{url('/pos')}}">Home</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0)">Customer</a></li>
            <li class="breadcrumb-item"><a href="{{url('/pos/customer')}}"> Customer List</a></li>
            <li class="breadcrumb-item active">View</li>
        </ol>
    </div>
    <div class="page-content">
        <div class="panel">
            <div class="panel-body">

                <form enctype="multipart/form-data" method="" class="form-horizontal" data-toggle="validator"
                    novalidate="true">
                    @csrf
                    <div class="row">
                        <div class="col-lg-8 offset-lg-3">
                            <!-- Html View Load  -->
                            {!! HTML::forCompanyFeild($CustomerData->company_id,'disabled') !!}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-8 offset-lg-3">
                            {!! HTML::forBranchFeild(true,'','',$CustomerData->branch_id,'disabled') !!}
                        </div>
                    </div>

                    <div class="row">
                        <!--Form Left-->
                        <div class="col-lg-6">

                            <div class="form-row align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">Customer Type</label>
                                <div class="col-lg-7 form-group">
                                    <div class="input-group">
                                        <div class="radio-custom radio-primary">
                                            <input type="radio" id="radio1" name="customer_type" value="1"
                                                onclick="cashFunction(this.value);"
                                                {{ ($CustomerData->customer_type == 1) ? 'checked' : '' }}
                                                checked="checked">
                                            <label for="radio1">CASH</label>
                                        </div>
                                        <div class="radio-custom radio-primary" style="margin-left: 20px!important;">
                                            <input type="radio" id="radio2" value="2" name="customer_type"
                                                onclick="cashFunction(this.value);"
                                                {{ ($CustomerData->customer_type == 2) ? 'checked' : '' }}>
                                            <label for="radio2">INSTALLMENT</label>
                                        </div>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                            </div>

                            <div class="form-row align-items-center">
                                <label class="col-lg-4 input-title RequiredStar" for="customer_code">
                                    Customer Code
                                </label>
                                <div class="col-lg-7 form-group">
                                    <div class="input-group">
                                        <input type="text" class="form-control round" name="customer_code"
                                            id="customer_code" value="{{$CustomerData->customer_code}}"
                                            placeholder="Enter Coustomer ID" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">Customer Name</label>
                                <div class="col-lg-7 form-group">
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="customer_name"
                                            name="customer_name" placeholder="Enter Customer Name"
                                            value="{{$CustomerData->customer_name}}" readonly>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>

                                </div>
                            </div>

                            <div class="form-row align-items-center">
                                <label class="col-lg-4 input-title">Father's Name</label>
                                <div class="col-lg-7 form-group">
                                    <div class="input-group">
                                        <input type="text" class="form-control round" name="father_name"
                                            id="father_name" placeholder="Enter Father's Name"
                                            value="{{$CustomerData->father_name}}" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row align-items-center">
                                <label class="col-lg-4 input-title">Mother Name</label>
                                <div class="col-lg-7 form-group">
                                    <div class="input-group">
                                        <input type="text" class="form-control round" name="mother_name"
                                            id="mother_name" placeholder="Enter Mother Name"
                                            value="{{$CustomerData->mother_name}}" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row align-items-center">
                                <label class="col-lg-4 input-title">Marital Status</label>
                                <div class="col-lg-7 form-group">
                                    <div class="input-group">
                                        <div class="radio-custom radio-primary">
                                            <input type="radio" id="m1" value="Single" name="marital_status" class="MarritalStatus"
                                                {{ ($CustomerData->marital_status == "Single") ? 'checked' : '' }}>
                                            <label for="m1">Single </label>
                                        </div>

                                        <div class="radio-custom radio-primary"
                                            style="margin-left: 20px!important;">
                                            <input type="radio" id="m2" value="Married" name="marital_status" class="MarritalStatus"
                                                {{ ($CustomerData->marital_status == "Married") ? 'checked' : '' }}>
                                            <label for="m2">Married </label>


                                        </div>
                                        <div class="radio-custom radio-primary" style="margin-left: 20px!important;">
                                            <input type="radio" id="m3" name="marital_status" class="MarritalStatus"
                                                {{ ($CustomerData->marital_status == "Divorced") ? 'checked' : '' }}
                                                value="Divorced">
                                            <label for="m3">Divorced</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row align-items-center" id="Mstatus" style="display:none;">
                                <label class="col-lg-4 input-title">Spouse Name</label>
                                <div class="col-lg-7 form-group">
                                    <div class="input-group">
                                        <input type="text" class="form-control round" name="spouse_name"
                                            id="spouse_name" value="{{$CustomerData->spouse_name}}"
                                            placeholder="Enter Spouse Name" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">National ID</label>
                                <div class="col-lg-7 form-group">
                                    <div class="input-group">
                                        <input type="number" class="form-control round" name="customer_nid"
                                            id="customer_nid" value="{{$CustomerData->customer_nid}}"
                                            placeholder="Enter National ID" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">Mobile</label>
                                <div class="col-lg-7 form-group">
                                    <div class="input-group">
                                        <input type="Number" class="form-control round" name="customer_mobile"
                                            id="customer_mobile" value="{{$CustomerData->customer_mobile}}"
                                            placeholder="Enter  Mobile Number" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row align-items-center">
                                <label class="col-lg-4 input-title">Email</label>
                                <div class="col-lg-7 form-group">
                                    <div class="input-group">
                                        <input type="email" class="form-control round" id="customer_email"
                                            name="customer_email" id="customer_email" name="customer_email"
                                            value="{{$CustomerData->customer_email}}" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row align-items-center">
                                <label class="col-lg-4 input-title">Date of Birth</label>
                                <div class="col-lg-7 form-group">
                                    <div class="input-group">
                                        <div class="input-group-prepend ">
                                            <span class="input-group-text ">
                                                <i class="icon wb-calendar round" aria-hidden="true"></i>
                                            </span>
                                        </div>
                                        <input type="text" name="customer_dob" id="customer_dob"
                                            value="{{$CustomerData->customer_dob}}"
                                            class="form-control round datepicker-custom" autocomplete="off"
                                            placeholder="DD/MM/YYYY" disabled>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row form-group align-items-center">
                              <label class="col-lg-4 input-title " for="cus_gender">Gender</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <div class="radio-custom radio-primary">
                                            <input type="radio" id="g1" name="cus_gender" value="male" {{ $CustomerData->cus_gender == 'male' ? 'checked' : ''}} >
                                            <label for="g1">Male  &nbsp &nbsp  </label>
                                        </div>
                                        <div class="radio-custom radio-primary">
                                            <input type="radio" id="g2" name="cus_gender" value="female" {{ $CustomerData->cus_gender == 'female' ? 'checked' : ''}}>
                                            <label for="g2">Female &nbsp &nbsp </label>
                                        </div>
                                        <div class="radio-custom radio-primary">
                                            <input type="radio" id="g3" name="cus_gender" value="others" {{ $CustomerData->cus_gender == 'others' ? 'checked' : ''}}>
                                            <label for="g3">Others &nbsp &nbsp</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!--Form Right-->
                        <div class="col-lg-6">

                            <div class="form-row align-items-center">
                                <label class="col-lg-4 input-title">Present Address:<span
                                        class="RequireText"></span></label>
                            </div>

                            <div class="form-row align-items-center">
                                <div class="col-lg-6 form-group">
                                    <div class="input-group">
                                        <select class="form-control RequireInput clsSelect2"
                                             name="pre_division_id"
                                            id="pre_division_id" disabled>

                                            <option value="">Select Division</option>
                                            @foreach ($DivData as $Row)
                                            <option value="{{$Row->id}}"
                                                {{ ($CustomerData->pre_division_id == $Row->id) ? 'selected="selected"' : '' }}>
                                                {{$Row->division_name}}</option>
                                            @endforeach
                                        </select>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-6 form-group">
                                    <div class="input-group">
                                        <select class="form-control RequireInput clsSelect2"
                                             name="pre_district_id"
                                            id="pre_district_id" disabled>
                                            <option value="">Select District</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row align-items-center">
                                <div class="col-lg-6 form-group">
                                    <div class="input-group">
                                        <select class="form-control RequireInput clsSelect2"
                                             name="pre_upazila_id"
                                            id="pre_upazila_id" disabled>
                                            <option value="">Select upazila</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-6 form-group">
                                    <div class="input-group">
                                        <select class="form-control RequireInput clsSelect2"
                                             name="pre_union_id" id="pre_union_id" disabled>
                                            <option value="">Select Union</option>
                                        </select>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                            </div>

                            <div class="form-row align-items-center">
                                <div class="col-lg-6 form-group">
                                    <div class="input-group">
                                        <select class="form-control clsSelect2"
                                             name="pre_village_id"
                                            id="pre_village_id" disabled>
                                            <option value="">Select Village</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-6 form-group">
                                    <div class="input-group">
                                        <textarea class="form-control" name="pre_remarks" id="pre_remarks"
                                            rows="2" placeholder="N/A" readonly>{{$CustomerData->pre_remarks}}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row align-items-center">
                                <label class="col-lg-6 input-title">Same As Present Address</label>
                                <div class="col-lg-6 form-group">
                                    <input type="checkbox" id="checkbox_addr">
                                </div>
                            </div>

                            <div class="form-row align-items-center">
                                <label class="col-lg-4 input-title" for="parAddress">Parmanent Address:<span
                                        class="RequireText"></span></label>
                            </div>

                            <div class="form-row align-items-center">
                                <div class="col-lg-6 form-group">
                                    <div class="input-group">
                                        <select class="form-control RequireInput clsSelect2"
                                             name="par_division_id"
                                            id="par_division_id" disabled>
                                            <option value="">Select Division</option>
                                            @foreach ($DivData as $Row)
                                            <option value="{{$Row->id}}"
                                                {{ ($CustomerData->par_division_id == $Row->id) ? 'selected="selected"' : '' }}>
                                                {{$Row->division_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>

                                <div class="col-lg-6 form-group">
                                    <div class="input-group">
                                        <select class="form-control RequireInput clsSelect2"
                                             name="par_district_id"
                                            id="par_district_id" disabled>
                                            <option value="">Select District</option>

                                        </select>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                            </div>

                            <div class="form-row align-items-center">
                                <div class="col-lg-6 form-group">
                                    <div class="input-group">
                                        <select class="form-control RequireInput clsSelect2"
                                             name="par_upazila_id"
                                            id="par_upazila_id" disabled>
                                            <option value="">Select Upazila</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-6 form-group">
                                    <div class="input-group">
                                        <select class="form-control RequireInput clsSelect2"
                                             name="par_union_id" id="par_union_id"
                                            disabled>
                                            <option value="">Select Union</option>

                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row align-items-center">
                                <div class="col-lg-6 form-group">
                                    <div class="input-group">
                                        <select class="form-control  clsSelect2"
                                             name="par_village_id"
                                            id="par_village_id" disabled>
                                            <option value="">Select Village</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-6 form-group">
                                    <div class="input-group">
                                        <textarea class="form-control" name="par_remarks" id="par_remarks"
                                            rows="2" placeholder="N/A" readonly>{{$CustomerData->par_remarks}}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row align-items-center">
                                <label class="col-lg-4 input-title">Yearly Income</label>
                                <div class="col-lg-8 form-group">
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="yearly_income"
                                            name="yearly_income" value="{{$CustomerData->yearly_income}}"
                                            placeholder='N/A' readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row align-items-center">
                                <label class="col-lg-4 input-title">Customer Picture</label>
                                <div class="col-lg-6 form-group">
                                    <div class="input-group input-group-file" data-plugin="inputGroupFile">
                                        <input type="text" class="form-control round">
                                        <div class="input-group-append">
                                            <span class="btn btn-success btn-file">
                                                <i class="icon wb-upload" aria-hidden="true"></i>
                                                <input type="file" id="customer_image" name="customer_image" multiple=""
                                                    value="{{$CustomerData->customer_image}}">
                                            </span>
                                        </div>
                                    </div>
                                    @if(!empty($CustomerData->customer_image))
                                        @if(file_exists($CustomerData->customer_image))
                                        <img src="{{ Storage::disk('public')->get('uploads/customer/. $CustomerData->id . '/'.$CustomerData->customer_image') }}" style="height: 32PX; width: 32PX;">
                                        <!-- <img src="{{ asset('/uploads/customer/' . $CustomerData->id . '/'.$CustomerData->customer_image) }}" style="height: 32PX; width: 32PX;"> -->
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <div class="form-row align-items-center">
                                <label class="col-lg-4 input-title">Description</label>
                                <div class="col-lg-8 form-group">
                                    <div class="input-group">
                                        <textarea class="form-control" name="customer_desc" id="customer_desc"
                                            rows="2"
                                            placeholder="N/A" readonly>{{$CustomerData->customer_desc}}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group d-flex justify-content-center">
                                <div class="example example-buttons">
                                    <a href="{{url('/pos/customer')}}"><button type="button"
                                            class="btn btn-default btn-round">Back</button></a>
                                    <button type="submit" class="btn btn-primary btn-round">Update</button>
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

<!-- frontend required field check -->
<script>

    $(document).ready(function() {

        if ($('.MarritalStatus').is(':checked')){
            var idTxt = $('.MarritalStatus:checked').val();
            if(idTxt === 'Married'){
                    $("#Mstatus").show();
                }
            else
                $("#Mstatus").hide();
        }

        $(".MarritalStatus").click(function() {
            var selIdTxt = $(this).val();

            $( '.MarritalStatus' ).each(function() {

                if(selIdTxt === 'Married'){
                    $("#Mstatus").show();
                }
                else
                $("#Mstatus").hide();
            });
        });

        // Load Present Address values
        fnAjaxSelectBox(
            'pre_district_id',
            '{{ $CustomerData->pre_division_id }}',
            '{{base64_encode("gnl_districts")}}',
            '{{base64_encode("division_id")}}',
            '{{base64_encode("id,district_name")}}',
            '{{url("/ajaxSelectBox")}}',
            '{{ $CustomerData->pre_district_id}}'
        );
        fnAjaxSelectBox(
            'pre_upazila_id',
            '{{ $CustomerData->pre_district_id }}',
            '{{base64_encode("gnl_upazilas")}}',
            '{{base64_encode("district_id")}}',
            '{{base64_encode("id,upazila_name")}}',
            '{{url("/ajaxSelectBox")}}',
            '{{ $CustomerData->pre_upazila_id}}'
        );

        fnAjaxSelectBox(
            'pre_union_id',
            '{{ $CustomerData->pre_upazila_id }}',
            '{{base64_encode("gnl_unions")}}',
            '{{base64_encode("upazila_id")}}',
            '{{base64_encode("id,union_name")}}',
            '{{url("/ajaxSelectBox")}}',
            '{{ $CustomerData->pre_union_id}}'
        );
        fnAjaxSelectBox(
            'pre_village_id',
            '{{ $CustomerData->pre_union_id }}',
            '{{base64_encode("gnl_villages")}}',
            '{{base64_encode("union_id")}}',
            '{{base64_encode("id,village_name")}}',
            '{{url("/ajaxSelectBox")}}',
            '{{ $CustomerData->pre_village_id}}'
        );

        // Load Parmanent Address values
        fnAjaxSelectBox(
            'par_district_id',
            '{{ $CustomerData->par_division_id }}',
            '{{base64_encode("gnl_districts")}}',
            '{{base64_encode("division_id")}}',
            '{{base64_encode("id,district_name")}}',
            '{{url("/ajaxSelectBox")}}',
            '{{ $CustomerData->par_district_id}}'
        );
        fnAjaxSelectBox(
            'par_upazila_id',
            '{{ $CustomerData->par_district_id }}',
            '{{base64_encode("gnl_upazilas")}}',
            '{{base64_encode("district_id")}}',
            '{{base64_encode("id,upazila_name")}}',
            '{{url("/ajaxSelectBox")}}',
            '{{ $CustomerData->par_upazila_id}}'
        );

        fnAjaxSelectBox(
            'par_union_id',
            '{{ $CustomerData->par_upazila_id }}',
            '{{base64_encode("gnl_unions")}}',
            '{{base64_encode("upazila_id")}}',
            '{{base64_encode("id,union_name")}}',
            '{{url("/ajaxSelectBox")}}',
            '{{ $CustomerData->par_union_id}}'
        );
        fnAjaxSelectBox(
            'par_village_id',
            '{{ $CustomerData->par_union_id }}',
            '{{base64_encode("gnl_villages")}}',
            '{{base64_encode("union_id")}}',
            '{{base64_encode("id,village_name")}}',
            '{{url("/ajaxSelectBox")}}',
            '{{ $CustomerData->par_village_id}}'
        );

    });

    cashFunction('{{$CustomerData->customer_type}}');
    // Change required field for installment customer
    function cashFunction(CustomerType) {
        if (CustomerType == "1") {
            $('.RequireInput').prop('required', false);
            $('.RequireText').html('');
        } else if (CustomerType == "2") {
            $('.RequireInput').prop('required', true);
            $('.RequireText').html('&nbsp; <span class="red-800">*</span>');
        }
    }


    // For Same as Present Address Checkbox
    function GetSelectedText(id) {

        if ($('#' + id).is(':checked')) {

                $('#par_division_id').val($('#pre_division_id').val());
                // $('#par_division_id').trigger('change');

                fnAjaxSelectBox(
                    'par_district_id',
                    $('#par_division_id').val(),
                    '{{base64_encode('gnl_districts')}}',
                    '{{base64_encode('division_id')}}',
                    '{{base64_encode('id,district_name')}}',
                    '{{url('/ajaxSelectBox')}}',
                    $('#pre_district_id').val()
                );
                fnAjaxSelectBox(
                    'par_upazila_id',
                    $('#pre_district_id').val(),
                    '{{base64_encode('gnl_upazilas')}}',
                    '{{base64_encode('district_id')}}',
                    '{{base64_encode('id,upazila_name')}}',
                    '{{url('/ajaxSelectBox')}}',
                    $('#pre_upazila_id').val()
                );
                fnAjaxSelectBox(
                    'par_union_id',
                    $('#pre_upazila_id').val(),
                    '{{base64_encode('gnl_unions')}}',
                    '{{base64_encode('upazila_id')}}',
                    '{{base64_encode('id,union_name')}}',
                    '{{url('/ajaxSelectBox')}}',
                    $('#pre_union_id').val()
                );
                fnAjaxSelectBox(
                    'par_village_id',
                    $('#pre_union_id').val(),
                    '{{base64_encode('gnl_villages')}}',
                    '{{base64_encode('union_id')}}',
                    '{{base64_encode('id,village_name')}}',
                    '{{url('/ajaxSelectBox')}}',
                    $('#pre_village_id').val()
                );

                $('#par_remarks').val($('#pre_remarks').val());
            } else {
                    $('#par_division_id').val('');
                    $('#par_district_id').val('');
                    $('#par_upazila_id').val('');
                    $('#par_union_id').val('');
                    $('#par_village_id').val('');
                    // $('#par_remarks').val('');

                    // $('#par_division_id').trigger('change');
                    // $('#par_district_id').trigger('change');
                    // $('#par_upazila_id').trigger('change');
                    // $('#par_union_id').trigger('change');
                    // $('#par_village_id').trigger('change');
                }
        }

</script>
@endsection
