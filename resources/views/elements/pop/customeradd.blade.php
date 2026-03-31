<?php
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
?>

<div class="container">
    <div class="row">
        <div class="modal fade" id="modalCustForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
             aria-hidden="true">

            <div class="modal-dialog modal-dialog-centered modal-lg" role="document" style="max-width:80%;">

                <div class="modal-content">
                    <div class="modal-header text-center">
                        <h4 class="modal-title font-weight-bold text-center">Customer Entry</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body mx-3">

                        <form enctype="multipart/form-data" method="post" action="" data-toggle="validator" novalidate="true">
                            @csrf
                            <input type="hidden" id="csrf" value="{{ csrf_token() }}">
                            <div class="row">
                                <div class="col-lg-8 offset-lg-3">
                                    <!-- Html View Load  -->
                                    <input type="hidden" name="company_id" id="company_id2" value="{{ Common::getCompanyId() }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-8 offset-lg-3">
                                    {!! HTML::forBranchFeild(true,'branch_id','branch_id2',null,'','Branch') !!}
                                </div>
                            </div>

                            <div class="row">
                                <!--Form Left-->
                                <div class="col-lg-6">

                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title RequiredStar">Customer Type</label>
                                        <div class="col-lg-7 form-group">
                                            <div class="input-group ">
                                                <div class="radio-custom radio-primary">
                                                    <input type="radio" id="radio1" name="customer_type" value="1"
                                                        onclick="cashFunction(this.value);" checked="checked">
                                                    <label for="radio1">CASH</label>
                                                </div>
                                                <div class="radio-custom radio-primary" style="margin-left: 20px!important;">
                                                    <input type="radio" id="radio2" value="2" name="customer_type"
                                                        onclick="cashFunction(this.value);">
                                                    <label for="radio2">INSTALLMENT</label>
                                                </div>
                                            </div>
                                            <div class="help-block with-errors is-invalid"></div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title RequiredStar" for="customer_no">Customer
                                            Code</label>
                                        <div class="col-lg-7 form-group">
                                            <div class="input-group ">
                                                <input type="text" class="form-control round" name="customer_no"
                                                    id="customer_no" placeholder="Enter Coustomer ID" required
                                                    data-error="Please enter Customer id."
                                                    onblur="fnCheckDuplicate(
                                                        '{{base64_encode('pos_customers')}}',
                                                        this.name+'&&is_delete',
                                                        this.value+'&&0',
                                                        '{{url('/ajaxCheckDuplicate')}}',
                                                        this.id,
                                                        'txtCodeError',
                                                        'customer code');">
                                            </div>
                                            <div class="help-block with-errors is-invalid" id="txtCodeError"></div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title RequiredStar">Customer Name</label>
                                        <div class="col-lg-7 form-group">
                                            <div class="input-group ">
                                                <input type="text" class="form-control round" id="customer_name"
                                                    name="customer_name" placeholder="Enter Customer Name" required
                                                    data-error="Please enter Customer name.">
                                            </div>
                                            <div class="help-block with-errors is-invalid"></div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title">Father's Name</label>
                                        <div class="col-lg-7 form-group">
                                            <div class="input-group ">
                                                <input type="text" class="form-control round" name="father_name"
                                                    id="father_name" placeholder="Enter Father's Name">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title">Mother Name</label>
                                        <div class="col-lg-7 form-group">
                                            <div class="input-group ">
                                                <input type="text" class="form-control round" name="mother_name"
                                                    id="mother_name" placeholder="Enter Mother Name">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title" for="marital_status">Marital Status</label>
                                        <div class="col-lg-7 form-group">
                                            <div class="input-group ">

                                                <div class="radio-custom radio-primary">
                                                    <input type="radio" id="m1" value="Single" name="marital_status"
                                                        onclick="SingSFunction()" checked="">
                                                    <label for="m1">Single </label>
                                                </div>

                                                <div class="radio-custom radio-primary" onclick="statusFunction()"
                                                    style="margin-left: 20px!important;">
                                                    <input type="radio" id="m2" value="Married" name="marital_status">
                                                    <label for="m2">Married </label>


                                                </div>
                                                <div class="radio-custom radio-primary" style="margin-left: 20px!important;">
                                                    <input type="radio" id="m3" name="marital_status" onclick="DivoSFunction()"
                                                        value="Divorced">
                                                    <label for="m3">Divorced</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- RequireInput class add for onclick back end required add ,Mstatus id add for jquery hide and show  -->
                                    <div class="form-row align-items-center" id="Mstatus" style="display:none;">
                                        <label class="col-lg-4 input-title">Spouse Name</label>
                                        <div class="col-lg-7 form-group">

                                            <div class="input-group">
                                                <input type="text" class="form-control round" name="spouse_name"
                                                    id="spouse_name" placeholder="Enter Spouse Name">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title RequiredStar">National ID</label>
                                        <div class="col-lg-7 form-group">
                                            <div class="input-group">
                                                <input type="number" class="form-control round" name="customer_nid"
                                                    placeholder="Enter National ID" required
                                                    data-error="Please enter National ID">
                                            </div>
                                            <div class="help-block with-errors is-invalid"></div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title RequiredStar">Mobile</label>
                                        <div class="col-lg-7 form-group">
                                            <div class="input-group ">
                                                <input type="Number" class="form-control round" name="customer_mobile" id="customer_mobile" placeholder="Enter  Mobile Number" required data-error="Please enter mobile">
                                            </div>
                                            <div class="help-block with-errors is-invalid"></div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title">Email</label>
                                        <div class="col-lg-7 form-group">
                                            <div class="input-group ">
                                                <input type="email" class="form-control round" id="customer_email"
                                                    name="customer_email" placeholder="Enter Email"
                                                    data-error="Please enter correct email.">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title">Date of Birth</label>
                                        <div class="col-lg-7 form-group">
                                            <div class="input-group ">
                                                <div class="input-group-prepend ">
                                                    <span class="input-group-text ">
                                                        <i class="icon wb-calendar round" aria-hidden="true"></i>
                                                    </span>
                                                </div>
                                                <input type="text" name="customer_dob" id="customer_dob"
                                                    class="form-control round datepicker-custom" autocomplete="off"
                                                    placeholder="DD/MM/YYYY">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row form-group align-items-center">
                                        <label class="col-lg-4 input-title " for="cus_gender">Gender</label>
                                        <div class="col-lg-7">
                                            <div class="input-group ">
                                                <div class="radio-custom radio-primary">
                                                    <input type="radio" id="g1" name="cus_gender" value="male" checked="">
                                                    <label for="g1">Male &nbsp &nbsp </label>
                                                </div>
                                                <div class="radio-custom radio-primary">
                                                    <input type="radio" id="g2" name="cus_gender" value="female">
                                                    <label for="g2">Female &nbsp &nbsp </label>
                                                </div>
                                                <div class="radio-custom radio-primary">
                                                    <input type="radio" id="g3" name="cus_gender" value="others">
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
                                            <label class="input-title">Division</label>
                                            <div class="input-group">
                                                <select class="form-control RequireInput cls-select-2" name="pre_division_id" id="pre_division_id" style="width: 100%"
                                                onchange="fnAjaxSelectBox(
                                                            'pre_district_id',
                                                            this.value,
                                                '{{base64_encode('gnl_districts')}}',
                                                '{{base64_encode('division_id')}}',
                                                '{{base64_encode('id,district_name')}}',
                                                '{{url('/ajaxSelectBox')}}'
                                                        ); GetSelectedText('checkbox_addr');">

                                                    <option value="">Select Division</option>
                                                    @foreach ($DivData as $Row)
                                                    <option value="{{$Row->id}}">{{$Row->division_name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="help-block with-errors is-invalid"></div>
                                        </div>

                                        <div class="col-lg-6 form-group">
                                            <label class="input-title">District</label>
                                            <div class="input-group">
                                                <select class="form-control RequireInput cls-select-2" name="pre_district_id" style="width: 100%"
                                                    id="pre_district_id" onchange="fnAjaxSelectBox(
                                                            'pre_upazila_id',
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

                                    <div class="form-row align-items-center">
                                        <div class="col-lg-6 form-group">
                                            <label class="input-title">Upazila</label>
                                            <div class="input-group">
                                                <select class="form-control RequireInput cls-select-2" name="pre_upazila_id" style="width: 100%"
                                                    id="pre_upazila_id" onchange="fnAjaxSelectBox(
                                        'pre_union_id',
                                        this.value,
                            '{{base64_encode('gnl_unions')}}',
                            '{{base64_encode('upazila_id')}}',
                            '{{base64_encode('id,union_name')}}',
                            '{{url('/ajaxSelectBox')}}'
                                    ); GetSelectedText('checkbox_addr');">
                                                    <option value="">Select upazila</option>

                                                </select>
                                            </div>
                                            <div class="help-block with-errors is-invalid"></div>
                                        </div>

                                        <div class="col-lg-6 form-group">
                                            <label class="input-title">Union</label>
                                            <div class="input-group">
                                                <select class="form-control RequireInput cls-select-2" name="pre_union_id" style="width: 100%"
                                                    id="pre_union_id" onchange="fnAjaxSelectBox(
                                'pre_village_id',
                                this.value,
                    '{{base64_encode('gnl_villages')}}',
                    '{{base64_encode('union_id')}}',
                    '{{base64_encode('id,village_name')}}',
                    '{{url('/ajaxSelectBox')}}'
                            ); GetSelectedText('checkbox_addr');">
                                                    <option value="">Select Union</option>

                                                </select>
                                            </div>
                                            <div class="help-block with-errors is-invalid"></div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <div class="col-lg-6 form-group">
                                            <label class="input-title">Village</label>
                                            <div class="input-group">
                                                <select class="form-control  cls-select-2" name="pre_village_id"
                                                    id="pre_village_id" onchange="GetSelectedText('checkbox_addr');" style="width: 100%">
                                                    <option value="">Select Village</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-6 form-group">
                                            <div class="input-group">
                                                <textarea class="form-control " name="pre_remarks" id="pre_remarks" rows="2"
                                                    placeholder="Enter Remark"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <label class="col-lg-6 input-title" for="checkbox_addr">Same As Present Address</label>
                                        <div class="col-lg-6 form-group">
                                            <input type="checkbox" id="checkbox_addr" onclick="GetSelectedText(this.id);">
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title " for="parAddress">Parmanent Address:<span
                                                class="RequireText"></span></label>
                                    </div>
                                    <div class="form-row align-items-center">
                                        <div class="col-lg-6 form-group">
                                            <label class="input-title">Division</label>
                                            <div class="input-group">
                                                <select class="form-control RequireInput cls-select-2" name="par_division_id" style="width: 100%"
                                                    id="par_division_id" onchange="fnAjaxSelectBox(
                                'par_district_id',
                                this.value,
                    '{{base64_encode('gnl_districts')}}',
                    '{{base64_encode('division_id')}}',
                    '{{base64_encode('id,district_name')}}',
                    '{{url('/ajaxSelectBox')}}'
                            );">
                                                    <option value="">Select Division</option>
                                                    @foreach ($DivData as $Row)
                                                    <option value="{{$Row->id}}">{{$Row->division_name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="help-block with-errors is-invalid"></div>
                                        </div>

                                        <div class="col-lg-6 form-group">
                                            <label class="input-title">District</label>
                                            <div class="input-group">
                                                <select class="form-control RequireInput cls-select-2" name="par_district_id" style="width: 100%"
                                                    id="par_district_id" onchange="fnAjaxSelectBox(
                                'par_upazila_id',
                                this.value,
                                '{{base64_encode('gnl_upazilas')}}',
                                '{{base64_encode('district_id')}}',
                                '{{base64_encode('id,upazila_name')}}',
                                '{{url('/ajaxSelectBox')}}'
                                );">
                                                    <option value="">Select District</option>

                                                </select>
                                            </div>
                                            <div class="help-block with-errors is-invalid"></div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <div class="col-lg-6 form-group">
                                            <label class="input-title">Upazila</label>
                                            <div class="input-group">
                                                <select class="form-control RequireInput cls-select-2" name="par_upazila_id" style="width: 100%"
                                                    id="par_upazila_id" onchange="fnAjaxSelectBox(
                                'par_union_id',
                                this.value,
                                '{{base64_encode('gnl_unions')}}',
                                '{{base64_encode('upazila_id')}}',
                                '{{base64_encode('id,union_name')}}',
                                '{{url('/ajaxSelectBox')}}'
                                );">

                                                    <option value="">Select Upazila</option>

                                                </select>
                                            </div>
                                            <div class="help-block with-errors is-invalid"></div>
                                        </div>

                                        <div class="col-lg-6 form-group">
                                            <label class="input-title">Union</label>
                                            <div class="input-group">
                                                <select class="form-control RequireInput cls-select-2" name="par_union_id" style="width: 100%"
                                                    id="par_union_id" onchange="fnAjaxSelectBox(
                                'par_village_id',
                                this.value,
                                '{{base64_encode('gnl_villages')}}',
                                '{{base64_encode('union_id')}}',
                                '{{base64_encode('id,village_name')}}',
                                '{{url('/ajaxSelectBox')}}'
                                );">

                                                    <option value="">Select Union</option>

                                                </select>
                                            </div>
                                            <div class="help-block with-errors is-invalid"></div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <div class="col-lg-6 form-group">
                                            <label class="input-title">Village</label>
                                            <div class="input-group">
                                                <select class="form-control cls-select-2" name="par_village_id"
                                                    id="par_village_id" style="width: 100%">
                                                    <option value="">Select Village</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-6 form-group">
                                            <div class="input-group">
                                                <textarea class="form-control" name="par_remarks" id="par_remarks" rows="2"
                                                    placeholder="Enter Remark"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title">Yearly Income</label>
                                        <div class="col-lg-8 form-group">
                                            <div class="input-group ">
                                                <input type="text" class="form-control round" id="yearly_income"
                                                    name="yearly_income" placeholder="Your yearly Income">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title">Customer Picture</label>
                                        <div class="col-lg-8 form-group">
                                            <div class="input-group input-group-file" data-plugin="inputGroupFile">
                                                <input type="text" class="form-control round">
                                                <div class="input-group-append">
                                                    <span class="btn btn-success btn-file">
                                                        <i class="icon wb-upload" aria-hidden="true"></i>
                                                        <input type="file" id="customer_image" name="customer_image" onchange="validate_fileupload(this.id);">
                                                    </span>
                                                </div>
                                            </div>
                                            <span style="font-size: 14px; color: green;">(Maximum file size 1 Mb)</span>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title">Description</label>
                                        <div class="col-lg-8 form-group">
                                            <div class="input-group">
                                                <textarea class="form-control " name="customer_desc" id="customer_desc" rows="2"
                                                    placeholder="Enter Description"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </form>

                    </div>
                    <div class="modal-footer d-flex justify-content-center">
                        <div class="row align-items-center">
                            <div class="col-lg-12">
                                <div class="form-group d-flex justify-content-center">
                                    <div class="example example-buttons">
                                        <a href="javascript:void(0)" class="btn btn-default btn-round"
                                            data-dismiss="modal">Back</a>
                                        <a href="javascript:void(0)" class="btn btn-primary btn-round"
                                            id="submitButtonSupPOP">Save</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
            <div class="modal-footer d-flex justify-content-center">
                <div class="row align-items-center">
                    <div class="col-lg-12">
                        <div class="form-group d-flex justify-content-center">
                            <div class="example example-buttons">
                                <a href="javascript:void(0)" class="btn btn-default btn-round"
                                    data-dismiss="modal">Back</a>
                                <a href="javascript:void(0)" class="btn btn-primary btn-round"
                                    id="submitButtonSupPOP">Save</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>




<script>
// <!-- frontend required field check ,add Star in the top of the label-->
cashFunction('1');

function cashFunction(CustomerType) {

    if (CustomerType == "1") {

        $('.RequireInput').prop('required', false);
        $('.RequireText').html('');

    } else if (CustomerType == "2") {

        // change Submit to Next Button for Installment Customer
        $("#CustomerBtn").html("Next");

        // change Required Field for Installment Customer
        $('.RequireInput').prop('required', true);
        $('.RequireText').html('&nbsp; <span class="red-800">*</span>');

    }
}

/* Marital Status radio Button show and hide */
function statusFunction() {
    $("#Mstatus").show();
}

function SingSFunction() {
    $("#Mstatus").hide();
    $('#spouse_name').val("");
}

function DivoSFunction() {
    $("#Mstatus").hide();
    $('#spouse_name').val("");
}

/* Check Box Selected for Parmanent Address */
function GetSelectedText(id) {

    if ($('#' + id).is(':checked')) {

        $('#par_division_id').val($('#pre_division_id').val());
        $('#par_division_id').trigger('change');

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
        // $('#par_division_id').val($('#pre_division_id').val());
        // $('#par_district_id').val($('#pre_district_id').val());
        //  $('#par_upazila_id').val($('#pre_upazila_id').val());
        //  $('#par_union_id').val($('#pre_union_id').val());
        // $('#par_village_id').val($('#pre_village_id').val());
        $('#par_remarks').val($('#pre_remarks').val());
    } else {
        $('#par_division_id').val('');
        $('#par_district_id').val('');
        $('#par_upazila_id').val('');
        $('#par_union_id').val('');
        $('#par_village_id').val('');
        // $('#par_remarks').val('');
    }
}


/* Pop Up Supplier Start */
$(document).ready(function() {

    $('#branch_id2').find('clsSelect2').removeClass('clsSelect2').addClass('cls-select-2');

    $('#submitButtonSupPOP').click(function() {

        var customer_name = $('#customer_name').val();

        var customer_type = $("input[name='customer_type']:checked").val();

        var customer_no = $('#customer_no').val();
        // father_name
        var father_name = $('#father_name').val();
        // mother_name
        var mother_name = $('#mother_name').val();
        // marital_status REDIO
        var marital_status = $('#marital_status').val();

        // spouse_name
        var spouse_name = $('#spouse_name').val();

        // customer_nid
        var customer_nid = $('#customer_nid').val();

        // customer_mobile
        var customer_mobile = $('#customer_mobile').val();

        // customer_email
        var customer_email = $('#customer_email').val();

        // customer_dob
        var customer_dob = $('#customer_dob').val();

        // cus_gender
        var cus_gender = $('#cus_gender').val();

        // pre_division_id
        var pre_division_id = $('#pre_division_id').val();

        // pre_district_id
        var pre_district_id = $('#pre_district_id').val();

        // pre_upazila_id
        var pre_upazila_id = $('#pre_upazila_id').val();

        // pre_union_id
        var pre_union_id = $('#pre_union_id').val();

        // pre_village_id
        var pre_village_id = $('#pre_village_id').val();

        // pre_remarks
        var pre_remarks = $('#pre_remarks').val();

        // par_division_id
        var par_division_id = $('#par_division_id').val();

        // par_district_id
        var par_district_id = $('#par_district_id').val();

        // par_upazila_id
        var par_upazila_id = $('#par_upazila_id').val();

        // par_union_id
        var par_union_id = $('#par_union_id').val();

        // par_village_id
        var par_village_id = $('#par_village_id').val();

        // par_remarks
        var par_remarks = $('#par_remarks').val();

        // yearly_income
        var yearly_income = $('#yearly_income').val();

        // customer_ipar_remarksmage
        var customer_ipar_remarksmage = $('#customer_ipar_remarksmage').val();

        // customer_desc
        var customer_desc = $('#customer_desc').val();


        // console.log(customer_email)
        // console.log(customer_mobile)


        if (customer_name != "" && customer_email != "" && customer_mobile != "") {

            $.ajax({
                url: "{{ url('pos/popUpCustomerData') }}",
                type: "POST",
                data: {
                    _token: $("#csrf").val(),
                    type: 1,
                    customer_name :customer_name,
                    customer_type :customer_type,
                    customer_no :customer_no,
                    father_name :father_name,
                    mother_name :mother_name,
                    marital_status :marital_status,
                    spouse_name :spouse_name,
                    customer_nid :customer_nid,
                    customer_mobile :customer_mobile,
                    customer_email :customer_email,
                    customer_dob :customer_dob,
                    cus_gender :cus_gender,
                    pre_division_id :pre_division_id,
                    pre_district_id :pre_district_id,
                    pre_upazila_id :pre_upazila_id,
                    pre_union_id :pre_union_id,
                    pre_village_id :pre_village_id,
                    pre_remarks :pre_remarks,
                    par_division_id :par_division_id,
                    par_district_id :par_district_id,
                    par_upazila_id :par_upazila_id,
                    par_union_id :par_union_id,
                    par_village_id :par_village_id,
                    par_remarks :par_remarks,
                    yearly_income :yearly_income,
                    customer_ipar_remarksmage :customer_ipar_remarksmage,
                    customer_desc :customer_desc,
                },
                cache: false,
                success: function(dataResult) {

                    var dataResult = JSON.parse(dataResult);
                    if (dataResult.statusCode == 200) {

                        $('#modalCustForm').modal('toggle');
                        $("#supModalFormId").trigger("reset");
                        swal("Successfully Inserted!", "", "success");
                    } else if (dataResult.statusCode == 201) {

                        swal("Unsuccessfully to Insert!", "", "error");
                    }

                }
            });
        } else {
            swal({
                icon: 'error',
                title: 'Oops...',
                text: 'Please fillup all fields!',
            });
        }
    });
    /* Supplier Type */
    $('#supplier_type').change(function() {

        if ($(this).val() == '2') {
            $('#comissionIDinput').show();
        } else {
            $('#comissionIDinput').hide();
        }
    });
});
/* Pop Up Supplier End */

    function validate_fileupload(id) {
        var myFile = $('#' + id).prop('files');
        var filetype = myFile[0].type;
        var filesize = myFile[0].size / (1024 * 1024);  // in mb

        var errorFlag = false;

        if(filesize > 1){
            errorFlag = true;
        }

        if(filetype == 'image/jpeg'
            || filetype == 'image/jpg'
            || filetype == 'image/png'
            || filetype == 'image/bmp'
            || filetype == 'image/gif')
        {
            errorFlag = false;
        }
        else{
            errorFlag = true;
        }

        if(errorFlag === true){
            $('#' + id).val('');
            swal({
                icon: 'error',
                title: 'Error',
                text: 'File size must be equal or less than 1 mb & file type is image. !!',
            });
        }
    }
</script>
