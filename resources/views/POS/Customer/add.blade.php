@extends('Layouts.erp_master')
@section('content')
<?php
use App\Services\HtmlService as HTML;
use App\Services\CommonService as Common;
?>

<form enctype="multipart/form-data" method="post" data-toggle="validator" novalidate="true" autocomplete="off">
    @csrf
    <div class="row">
        <div class="col-lg-8 offset-lg-3">
            <!-- Html View Load  -->
            {!! HTML::forCompanyFeild() !!}
        </div>
    </div>
    <div class="row">
        <div class="col-lg-8 offset-lg-3">
            {!! HTML::forBranchFeild(true) !!}
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
                            <input type="radio" id="radio1" name="customer_type" value="1" class="cType">
                            <label for="radio1">CASH</label>
                        </div>
                        <div class="radio-custom radio-primary" style="margin-left: 20px!important;">
                            <input type="radio" id="radio2" value="2" name="customer_type" class="cType" checked>
                            <label for="radio2">INSTALLMENT</label>
                        </div>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title" for="customer_no">Customer
                    Code</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" name="customer_no" id="customer_no"
                              readonly value="{{Common::generateCustomerNo(Common::getBranchId())}}">
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Customer Name</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" id="customer_name" name="customer_name"
                            placeholder="Enter Customer Name" required data-error="Please enter Customer name.">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title">Father's Name</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" name="father_name" id="father_name"
                            placeholder="Enter Father's Name">
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title">Mother Name</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" name="mother_name" id="mother_name"
                            placeholder="Enter Mother Name">
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title" for="marital_status">Marital Status</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">

                        <div class="radio-custom radio-primary">
                            <input type="radio" id="m1" value="Single" name="marital_status" class="mStatus" checked>
                            <label for="m1">Single </label>
                        </div>

                        <div class="radio-custom radio-primary" style="margin-left: 20px!important;">
                            <input type="radio" id="m2" value="Married" name="marital_status" class="mStatus">
                            <label for="m2">Married </label>


                        </div>
                        <div class="radio-custom radio-primary" style="margin-left: 20px!important;">
                            <input type="radio" id="m3" name="marital_status" class="mStatus" value="Divorced">
                            <label for="m3">Divorced</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RequireInput class add for onclick back end required add ,Mstatus id add for jquery hide and show  -->
            <div class="form-row align-items-center" id="Married" style="display:none;">
                <label class="col-lg-4 input-title">Spouse Name</label>
                <div class="col-lg-7 form-group">

                    <div class="input-group">
                        <input type="text" class="form-control round" name="spouse_name" id="spouse_name"
                            placeholder="Enter Spouse Name">
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center" id="onlyInstallment">
                <label class="col-lg-4 input-title">
                    NID/Smart Card/&nbsp Passport/Driving License/&nbsp Birth Certificate
                    <span class="RequireText"></span>
                </label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <div class="radio-custom radio-primary">
                            <input type="radio" class="identification" id="n1" name="customer_id_type" value="nid"
                                checked>
                            <label for="n1">NID &nbsp &nbsp</label>
                        </div>
                        <div class="radio-custom radio-primary">
                            <input type="radio" class="identification" id="n2" name="customer_id_type"
                                value="smartCard">
                            <label for="n2">Smart Card &nbsp &nbsp</label>
                        </div>
                        <div class="radio-custom radio-primary">
                            <input type="radio" class="identification" id="n3" name="customer_id_type" value="passport">
                            <label for="n3">Passport &nbsp &nbsp</label>
                        </div>
                        <div class="radio-custom radio-primary">
                            <input type="radio" class="identification" id="n4" name="customer_id_type"
                                value="drivingLicense">
                            <label for="n4">Driving License &nbsp &nbsp</label>
                        </div>
                        <div class="radio-custom radio-primary">
                            <input type="radio" class="identification" id="n5" name="customer_id_type"
                                value="birthCertificate">
                            <label for="n5">Birth Certificate &nbsp &nbsp</label>
                        </div>
                        <div class="input-group mt-4">
                            <input type="text" class="form-control round identificationInput RequireInput textNumber"
                                name="customer_nid" id="customer_nid" placeholder="Enter NID No" autocomplete="off"
                                data-error="Fill Up this field">
                        </div>
                        <div class="help-block with-errors is-invalid errMsgNid" id="errMsg"></div>
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Mobile</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <input type="text" pattern="[01][0-9]{10}" class="form-control round textNumber"
                            name="customer_mobile" id="customer_mobile" placeholder="Mobile Number (01*********)"
                            required data-error="Please enter mobile number (01*********)" minlength="11" maxlength="11"
                            onblur="fnCheckDuplicate(
                                '{{base64_encode('pos_customers')}}',
                                this.name+'&&is_delete',
                                this.value+'&&0',
                                '{{url('/ajaxCheckDuplicate')}}',
                                this.id,
                                'errMsgPhone',
                                'mobile number');">
                    </div>
                    <div class="help-block with-errors is-invalid" id="errMsgPhone"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title">Email</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <input type="email" class="form-control round" id="customer_email" name="customer_email"
                            placeholder="Enter Email" data-error="Please enter correct email.">
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title">Date of Birth</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="icon wb-calendar round" aria-hidden="true"></i>
                            </span>
                        </div>
                        <input type="text" name="customer_dob" id="customer_dob"
                            class="form-control round datepicker-custom" autocomplete="off" placeholder="DD-MM-YYYY">
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title " for="cus_gender">Gender</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <div class="radio-custom radio-primary">
                            <input type="radio" id="g1" name="cus_gender" value="male" checked>
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
                <label class="col-lg-4 input-title">Present Address:<span class="RequireText"></span></label>
            </div>
            <div class="form-row align-items-center">
                <div class="col-lg-6 form-group">
                    <div class="input-title">Select Division</div>
                    <div class="input-group">
                        <select class="form-control RequireInput clsSelect2" name="pre_division_id" id="pre_division_id"
                            onchange="fnAjaxSelectBox(
                              'pre_district_id',
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
                    <div class="input-title">Select District</div>
                    <div class="input-group">
                        <select class="form-control RequireInput clsSelect2" name="pre_district_id" id="pre_district_id"
                            onchange="fnAjaxSelectBox(
                              'pre_upazila_id',
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
                    <div class="input-title">Select Upazila</div>
                    <div class="input-group">
                        <select class="form-control RequireInput clsSelect2" name="pre_upazila_id" id="pre_upazila_id"
                            onchange="fnAjaxSelectBox(
                                     'pre_union_id',
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
                    <div class="input-title">Select Union</div>
                    <div class="input-group">
                        <select class="form-control RequireInput clsSelect2" name="pre_union_id" id="pre_union_id"
                            onchange="fnAjaxSelectBox(
                            'pre_village_id',
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
                    <div class="input-title">Select Village</div>
                    <div class="input-group">
                        <select class="form-control  clsSelect2" name="pre_village_id" id="pre_village_id">
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

            <div class="form-row form-group align-items-center">
                <label class="col-lg-6 input-title" for="checkbox_addr">Same As Present Address</label>
                <div class="col-lg-6 input-group checkbox-custom checkbox-primary">
                    <input type="checkbox" id="checkbox_addr">
                    <label></label>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title " for="parAddress">Permanent Address:<span
                        class="RequireText"></span></label>
            </div>
            <div class="form-row align-items-center">
                <div class="col-lg-6 form-group">
                    <div class="input-title">Select Division</div>
                    <div class="input-group">
                        <select class="form-control RequireInput clsSelect2" name="par_division_id" id="par_division_id"
                            onchange="fnAjaxSelectBox(
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
                    <div class="input-title">Select District</div>
                    <div class="input-group">
                        <select class="form-control RequireInput clsSelect2" name="par_district_id" id="par_district_id"
                            onchange="fnAjaxSelectBox(
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
                    <div class="input-title">Select Upazila</div>
                    <div class="input-group">
                        <select class="form-control RequireInput clsSelect2" name="par_upazila_id" id="par_upazila_id"
                            onchange="fnAjaxSelectBox(
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
                    <div class="input-title">Select Union</div>
                    <div class="input-group">
                        <select class="form-control RequireInput clsSelect2" name="par_union_id" id="par_union_id"
                            onchange="fnAjaxSelectBox(
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
                    <div class="input-title">Select Village</div>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="par_village_id" id="par_village_id">
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
                    <div class="input-group">
                        <input type="text" class="form-control round" id="yearly_income" name="yearly_income"
                            placeholder="Enter yearly Income">
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title">Customer Picture</label>
                <div class="col-lg-8 form-group">
                    <div class="input-group input-group-file" data-plugin="inputGroupFile">
                        <input type="text" class="form-control round" placeholder="Upload Picture">
                        <div class="input-group-append">
                            <span class="btn btn-success btn-file" style="height: 30px">
                                <i class="icon wb-upload" aria-hidden="true"></i>
                                <input type="file" id="customer_image" name="customer_image"
                                    onchange="validate_fileupload(this.id);">
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

    <div class="row">
        <div class="col-lg-12">
            <div class="form-group d-flex justify-content-center">
                <div class="example example-buttons">
                    <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                    <button type="submit" class="btn btn-primary btn-round" id="btnSubmit">Save</button>
                    <!-- <a href="#"><button type="button" class="btn btn-warning btn-round">Next</button></a> -->
                </div>
            </div>
        </div>
    </div>
</form>

<script>
function cashFunction(CustomerType) {
    if (CustomerType == 1) {
        $('.RequireInput').prop('required', false);
        $('.RequireText').html('');

        $('#onlyInstallment').hide();

    } else if (CustomerType == 2) {
        // change Submit to Next Button for Installment Customer
        $("#CustomerBtn").html("Next");

        // change Required Field for Installment Customer
        $('.RequireInput').prop('required', true);
        $('.RequireText').html('&nbsp; <span class="red-800">*</span>');

        $('#onlyInstallment').show();
    }
}

$(document).ready(function() {

    // <!-- frontend required field check ,add Star in the top of the label-->

    if ($('.cType').is(':checked')) {
        var custType = $('.cType:checked').val();
        cashFunction(custType);

    }

    $(".cType").click(function() {
        var custType = $(this).val();
        cashFunction(custType);
    });


    /* Marital Status radio Button show and hide */
    if ($('.mStatus').is(':checked')) {
        var idTxt = $('.mStatus:checked').val();
        if (idTxt == "Married") {
            $('#Married').show();
        } else {
            $('#Married').hide();
        }

    }

    $(".mStatus").click(function() {
        var idTxt = $(this).val();
        if (idTxt == "Married") {
            $('#Married').show();
        } else {
            $('#Married').hide();
        }
    });


    /* Check Box Selected for Parmanent Address */
    $("#checkbox_addr").click(function() {
        if ($('#checkbox_addr').is(':checked')) {
            $('#par_division_id').val($('#pre_division_id').val());
            $('#par_division_id').trigger('change');

            fnAjaxSelectBox(
                'par_district_id',
                $('#par_division_id').val(),
                '{{base64_encode("gnl_districts")}}',
                '{{base64_encode("division_id")}}',
                '{{base64_encode("id,district_name")}}',
                '{{url("/ajaxSelectBox")}}',
                $('#pre_district_id').val()
            );

            fnAjaxSelectBox(
                'par_upazila_id',
                $('#pre_district_id').val(),
                '{{base64_encode("gnl_upazilas")}}',
                '{{base64_encode("district_id")}}',
                '{{base64_encode("id,upazila_name")}}',
                '{{url("/ajaxSelectBox")}}',
                $('#pre_upazila_id').val()
            );

            fnAjaxSelectBox(
                'par_union_id',
                $('#pre_upazila_id').val(),
                '{{base64_encode("gnl_unions")}}',
                '{{base64_encode("upazila_id")}}',
                '{{base64_encode("id,union_name")}}',
                '{{url("/ajaxSelectBox")}}',
                $('#pre_union_id').val()
            );
            fnAjaxSelectBox(
                'par_village_id',
                $('#pre_union_id').val(),
                '{{base64_encode("gnl_villages")}}',
                '{{base64_encode("union_id")}}',
                '{{base64_encode("id,village_name")}}',
                '{{url("/ajaxSelectBox")}}',
                $('#pre_village_id').val()
            );
            $('#par_remarks').val($('#pre_remarks').val());
        }
    });


    if ($('.identification').is(':checked')) {
        var idTxt = $('.identification:checked').val();

        // Natinal ID Validation
        if (idTxt === 'nid') {
            $(this).attr("placeholder", "Enter NID No");
            $("#errMsg,#errMsgSC,#errMsgPP,#errMsgDL,#errMsgBR").prop('id', 'errMsgNID');
            $(".identificationInput").on("input", function(event) {
                $(this).addClass('textNumber');
                var nidNo = $(this).val();

                if (nidNo.length > 0) {
                    if (nidNo.length != 13) {

                        if (nidNo.length != 17) {

                            $("#errMsgNID").html("Invalid NID! NID must be of 13 or 17 Digits").show();
                            $('#customer_nid').css("border-color", "red");
                            $('#btnSubmit').attr("disabled", "disabled");

                        } else if (nidNo.length == 17) {
                            $("#errMsgNID").html('');
                            $('#customer_nid').css('border-color', '#e4eaec');

                            // // // Duplicate Check
                            var query = $(this).val();
                            var forWhich = $(this).attr("name");
                            var tableName = btoa('pos_customers');

                            var columnName = $(this).attr("name") + '&&is_delete';
                            var columnValue = $(this).val() + '&&0';
                            var url_text = "{{url('/ajaxCheckDuplicate')}}";
                            var fieldID = $(this).attr("id");
                            var updateID = null;

                            fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                                'errMsgNID', 'NID', updateID);

                            if ($('#' + fieldID).val() !== '') {
                                var numberOfEmptyFields = $('form').find(
                                        'input[required],select[required]')
                                    .filter(function() {
                                        return $(this).val() === "";
                                    }).length;
                                if (numberOfEmptyFields == 0) {
                                    $('#btnSubmit').removeClass("disabled");
                                }
                                $('#btnSubmit').removeAttr("disabled");
                                $(this).css('border-color', '#e4eaec');
                            }

                            // $.ajax({
                            //     type: "get",
                            //     url: "{{route('ajaxCheckDuplicate')}}",
                            //     data: {query: query, tableName: tableName, forWhich: forWhich},
                            //     dataType: "json",
                            //     success: function (data) {
                            //         if (data.exists) {
                            //             $('#btnSubmit').attr("disabled","disabled");
                            //             $('#errMsgNID').html('Please enter unique NID');
                            //             $('#customer_nid').css("border-color","red");
                            //         }
                            //         else {
                            //             var numberOfEmptyFields = $('form').find('input[required],select[required]')
                            //                                     .filter(function () {
                            //                                         return $(this).val() === "";
                            //                                     }).length;
                            //             if (numberOfEmptyFields == 0) {
                            //                 $('#btnSubmit').removeClass("disabled");
                            //             }
                            //             $('#btnSubmit').removeAttr("disabled");
                            //             $(this).css('border-color','#e4eaec');
                            //         }
                            //     },
                            // });
                        }
                    } else if (nidNo.length == 13) {
                        $("#errMsgNID").html('');
                        $('#customer_nid').css('border-color', '#e4eaec');

                        // // // Duplicate Check
                        var query = $(this).val();
                        var forWhich = $(this).attr("name");
                        var tableName = btoa('pos_customers');

                        var columnName = $(this).attr("name") + '&&is_delete';
                        var columnValue = $(this).val() + '&&0';
                        var url_text = "{{url('/ajaxCheckDuplicate')}}";
                        var fieldID = $(this).attr("id");
                        var updateID = null;

                        fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                            'errMsgNID', 'NID', updateID);

                        if ($('#' + fieldID).val() !== '') {
                            var numberOfEmptyFields = $('form').find('input[required],select[required]')
                                .filter(function() {
                                    return $(this).val() === "";
                                }).length;
                            if (numberOfEmptyFields == 0) {
                                $('#btnSubmit').removeClass("disabled");
                            }
                            $('#btnSubmit').removeAttr("disabled");
                            $(this).css('border-color', '#e4eaec');
                        }

                        // $.ajax({
                        //     type: "get",
                        //     url: "{{route('ajaxCheckDuplicate')}}",
                        //     data: {query: query, tableName: tableName, forWhich: forWhich},
                        //     dataType: "json",
                        //     success: function (data) {
                        //         if (data.exists) {
                        //             $('#errMsgNID').html('Please enter unique NID');
                        //             $('#customer_nid').css("border-color","red");
                        //         }
                        //         else {
                        //             var numberOfEmptyFields = $('form').find('input[required],select[required]')
                        //                                         .filter(function () {
                        //                                             return $(this).val() === "";
                        //                                         }).length;
                        //             if (numberOfEmptyFields == 0) {
                        //                 $('#btnSubmit').removeClass("disabled");
                        //             }
                        //             $('#btnSubmit').removeAttr("disabled");
                        //             $(this).css('border-color','#e4eaec');
                        //         }
                        //     },
                        // });
                    }
                } else {
                    $("#errMsgNID").html('');
                    $('#customer_nid').css("border-color", "#e4eaec");
                }
            });
        }
    }

    $(".identification").click(function() {
        $("#errMsg,#errMsgNID,#errMsgSC,#errMsgPP,#errMsgDL,#errMsgBR").html('');
        $('#customer_nid').val('');
        var selIdTxt = $(this).val();

        $('.identificationInput').each(function() {

            if (selIdTxt === 'nid') {
                $(this).attr("placeholder", "Enter NID No");
                $("#errMsg,#errMsgSC,#errMsgPP,#errMsgDL,#errMsgBR").prop('id', 'errMsgNID');
                $(".identificationInput").on("input", function(event) {
                    $(this).addClass('textNumber');
                    var nidNo = $(this).val();
                    if (nidNo.length > 0) {
                        if (nidNo.length != 13) {
                            if (nidNo.length != 17) {
                                $("#errMsgNID").html(
                                        "Invalid NID! NID must be of 13 or 17 Digits")
                                    .show();
                                $('#customer_nid').css("border-color", "red");
                                $('#btnSubmit').attr("disabled", "disabled");
                            } else if (nidNo.length == 17) {
                                $("#errMsgNID").html('');
                                $('#customer_nid').css('border-color', '#e4eaec');

                                // // // Duplicate Check
                                var query = $(this).val();
                                var forWhich = $(this).attr("name");
                                var tableName = btoa('pos_customers');

                                var columnName = $(this).attr("name") + '&&is_delete';
                                var columnValue = $(this).val() + '&&0';
                                var url_text = "{{url('/ajaxCheckDuplicate')}}";
                                var fieldID = $(this).attr("id");
                                var updateID = null;

                                fnCheckDuplicate(tableName, columnName, columnValue,
                                    url_text, fieldID,
                                    'errMsgNID', 'NID', updateID);

                                if ($('#' + fieldID).val() !== '') {
                                    var numberOfEmptyFields = $('form').find(
                                            'input[required],select[required]')
                                        .filter(function() {
                                            return $(this).val() === "";
                                        }).length;
                                    if (numberOfEmptyFields == 0) {
                                        $('#btnSubmit').removeClass("disabled");
                                    }
                                    $('#btnSubmit').removeAttr("disabled");
                                    $(this).css('border-color', '#e4eaec');
                                }


                                // $.ajax({
                                //     type: "get",
                                //     url: "{{route('ajaxCheckDuplicate')}}",
                                //     data: {query: query, tableName: tableName, forWhich: forWhich},
                                //     dataType: "json",
                                //     success: function (data) {
                                //         if (data.exists) {
                                //             $('#btnSubmit').attr("disabled","disabled");
                                //             $('#errMsgNID').html('Please enter unique NID');
                                //             $('#customer_nid').css("border-color","red");
                                //         }
                                //         else {
                                //             var numberOfEmptyFields = $('form').find('input[required],select[required]')
                                //                                         .filter(function () {
                                //                                             return $(this).val() === "";
                                //                                         }).length;
                                //             if (numberOfEmptyFields == 0) {
                                //                 $('#btnSubmit').removeClass("disabled");
                                //             }
                                //             $('#btnSubmit').removeAttr("disabled");
                                //             $(this).css('border-color','#e4eaec');
                                //         }
                                //     },
                                // });
                            }
                        } else if (nidNo.length == 13) {
                            $("#errMsgNID").html('');
                            $('#customer_nid').css('border-color', '#e4eaec');

                            // // // Duplicate Check
                            var query = $(this).val();
                            var forWhich = $(this).attr("name");
                            var tableName = btoa('pos_customers');

                            var columnName = $(this).attr("name") + '&&is_delete';
                            var columnValue = $(this).val() + '&&0';
                            var url_text = "{{url('/ajaxCheckDuplicate')}}";
                            var fieldID = $(this).attr("id");
                            var updateID = null;

                            fnCheckDuplicate(tableName, columnName, columnValue,
                                url_text, fieldID,
                                'errMsgNID', 'NID', updateID);

                            if ($('#' + fieldID).val() !== '') {
                                var numberOfEmptyFields = $('form').find(
                                        'input[required],select[required]')
                                    .filter(function() {
                                        return $(this).val() === "";
                                    }).length;
                                if (numberOfEmptyFields == 0) {
                                    $('#btnSubmit').removeClass("disabled");
                                }
                                $('#btnSubmit').removeAttr("disabled");
                                $(this).css('border-color', '#e4eaec');
                            }

                            // $.ajax({
                            //     type: "get",
                            //     url: "{{route('ajaxCheckDuplicate')}}",
                            //     data: {query: query, tableName: tableName, forWhich: forWhich},
                            //     dataType: "json",
                            //     success: function (data) {
                            //         if (data.exists) {
                            //             $('#errMsgNID').html('Please enter unique NID');
                            //             $('#customer_nid').css("border-color","red");
                            //         }
                            //         else {
                            //             var numberOfEmptyFields = $('form').find('input[required],select[required]')
                            //                                         .filter(function () {
                            //                                             return $(this).val() === "";
                            //                                         }).length;
                            //             if (numberOfEmptyFields == 0) {
                            //                 $('#btnSubmit').removeAttr("disabled");
                            //                 $('#btnSubmit').removeClass("disabled");
                            //             }

                            //             $(this).css('border-color','#e4eaec');
                            //         }
                            //     },
                            // });
                        }
                    } else {
                        $("#errMsgNID").html('');
                        $('#customer_nid').css("border-color", "#e4eaec");
                    }
                });
            } else if (selIdTxt === 'smartCard') {
                $(this).attr("placeholder", "Enter Smart Card No");
                $("#errMsg,#errMsgNID,#errMsgPP,#errMsgDL,#errMsgBR").prop('id', 'errMsgSC');
                $(".identificationInput").on("input", function(event) {
                    $(this).addClass('textNumber');
                    var cardNo = $(this).val();
                    if (cardNo.length > 0) {
                        if (cardNo.length != 10) {
                            $("#errMsgSC").html(
                                "Not a valid 10-digit Smart Card Number").show();
                            $(this).css('border-color', 'red');
                            $('#btnSubmit').attr("disabled", "disabled");
                        } else if (cardNo.length == 10) {
                            $("#errMsgSC").html('');

                            // // // Duplicate Check
                            var query = $(this).val();
                            var forWhich = $(this).attr("name");
                            var tableName = btoa('pos_customers');

                            var columnName = $(this).attr("name") + '&&is_delete';
                            var columnValue = $(this).val() + '&&0';
                            var url_text = "{{url('/ajaxCheckDuplicate')}}";
                            var fieldID = $(this).attr("id");
                            var updateID = null;

                            fnCheckDuplicate(tableName, columnName, columnValue,
                                url_text, fieldID,
                                'errMsgSC', 'smart card no.', updateID);

                            if ($('#' + fieldID).val() !== '') {
                                var numberOfEmptyFields = $('form').find(
                                        'input[required],select[required]')
                                    .filter(function() {
                                        return $(this).val() === "";
                                    }).length;
                                if (numberOfEmptyFields == 0) {
                                    $('#btnSubmit').removeClass("disabled");
                                }
                                $('#btnSubmit').removeAttr("disabled");
                                $(this).css('border-color', '#e4eaec');
                            }

                            // $.ajax({
                            //     type: "get",
                            //     url: "{{route('ajaxCheckDuplicate')}}",
                            //     data: {query: query, tableName: tableName, forWhich: forWhich},
                            //     dataType: "json",
                            //     success: function (data) {
                            //         if (data.exists) {
                            //             $('#btnSubmit').attr("disabled","disabled");
                            //             $('#errMsgSC').html('Please enter unique Smart Card ID');
                            //             $('#customer_nid').css("border-color","red");
                            //         }
                            //         else {
                            //             var numberOfEmptyFields = $('form').find('input[required],select[required]')
                            //                                     .filter(function () {
                            //                                         return $(this).val() === "";
                            //                                     }).length;
                            //             if (numberOfEmptyFields == 0) {
                            //                 $('#btnSubmit').removeClass("disabled");
                            //             }
                            //             $('#btnSubmit').removeAttr("disabled");
                            //             $('#customer_nid').css('border-color','#e4eaec');
                            //         }
                            //     },
                            // });
                        }
                    } else {
                        $("#errMsgSC").html('');
                        $('#customer_nid').css("border-color", "Red");
                    }

                });
            } else if (selIdTxt === 'passport') {
                $(this).attr("placeholder", "Enter passport No");
                $("#errMsg,#errMsgNID,#errMsgSC,#errMsgDL,#errMsgBR").prop('id', 'errMsgPP');
                $(".identificationInput").on("input", function(event) {
                    $(this).addClass('textNumber');
                    var passportNo = $(this).val();
                    if (passportNo.length > 0) {
                        if (passportNo.length != 9) {
                            $("#errMsgPP").html("Not a valid 9-digit Passport Number")
                                .show();
                            $(this).css('border-color', 'red');
                            $('#btnSubmit').attr("disabled", "disabled");
                        } else if (passportNo.length == 9) {
                            $("#errMsgPP").html('');
                            $('#customer_nid').css('border-color', '#e4eaec');

                            // // // Duplicate Check
                            var query = $(this).val();
                            var forWhich = $(this).attr("name");
                            var tableName = btoa('pos_customers');

                            var columnName = $(this).attr("name") + '&&is_delete';
                            var columnValue = $(this).val() + '&&0';
                            var url_text = "{{url('/ajaxCheckDuplicate')}}";
                            var fieldID = $(this).attr("id");
                            var updateID = null;

                            fnCheckDuplicate(tableName, columnName, columnValue,
                                url_text, fieldID,
                                'errMsgPP', 'passport no.', updateID);

                            if ($('#' + fieldID).val() !== '') {
                                var numberOfEmptyFields = $('form').find(
                                        'input[required],select[required]')
                                    .filter(function() {
                                        return $(this).val() === "";
                                    }).length;
                                if (numberOfEmptyFields == 0) {
                                    $('#btnSubmit').removeClass("disabled");
                                }
                                $('#btnSubmit').removeAttr("disabled");
                                $(this).css('border-color', '#e4eaec');
                            }

                            // $.ajax({
                            //     type: "get",
                            //     url: "{{route('ajaxCheckDuplicate')}}",
                            //     data: {query: query, tableName: tableName, forWhich: forWhich},
                            //     dataType: "json",
                            //     success: function (data) {
                            //         if (data.exists) {
                            //             $('#btnSubmit').attr("disabled","disabled");
                            //             $('#errMsgPP').html('Please enter unique Passport No');
                            //             $('#customer_nid').css("border-color","red");
                            //         }
                            //         else {
                            //             var numberOfEmptyFields = $('form').find('input[required],select[required]')
                            //                                         .filter(function () {
                            //                                             return $(this).val() === "";
                            //                                         }).length;
                            //             if (numberOfEmptyFields == 0) {
                            //                 $('#btnSubmit').removeClass("disabled");
                            //             }
                            //             $('#btnSubmit').removeAttr("disabled");
                            //             $(this).css('border-color','#e4eaec');
                            //         }
                            //     },
                            // });
                        }
                    } else {
                        $("#errMsgPP").html('');
                        $('#customer_nid').css("border-color", "Red");
                    }
                });
            } else if (selIdTxt === 'drivingLicense') {
                $(this).attr("placeholder", "Enter Driving License No");
                $("#errMsg,#errMsgNID,#errMsgSC,#errMsgPP,#errMsgBR").prop('id', 'errMsgDL');
                $(".identificationInput").on("input", function(event) {
                    $(this).removeClass('textNumber');
                    var licenceNo = $(this).val();
                    if (licenceNo.length > 0) {
                        if (licenceNo.length != 15) {
                            $("#errMsgDL").html(
                                    "Not a valid 15-digit Driving Licence Number")
                                .show();
                            $(this).css('border-color', 'red');
                            $('#btnSubmit').attr("disabled", "disabled");
                        } else if (licenceNo.length == 15) {
                            $("#errMsgDL").html('');
                            $('#customer_nid').css('border-color', '#e4eaec');

                            // // // Duplicate Check
                            var query = $(this).val();
                            var forWhich = $(this).attr("name");
                            var tableName = btoa('pos_customers');

                            var columnName = $(this).attr("name") + '&&is_delete';
                            var columnValue = $(this).val() + '&&0';
                            var url_text = "{{url('/ajaxCheckDuplicate')}}";
                            var fieldID = $(this).attr("id");
                            var updateID = null;

                            fnCheckDuplicate(tableName, columnName, columnValue,
                                url_text, fieldID,
                                'errMsgDL', 'driving license no.', updateID);

                            if ($('#' + fieldID).val() !== '') {
                                var numberOfEmptyFields = $('form').find(
                                        'input[required],select[required]')
                                    .filter(function() {
                                        return $(this).val() === "";
                                    }).length;
                                if (numberOfEmptyFields == 0) {
                                    $('#btnSubmit').removeClass("disabled");
                                }
                                $('#btnSubmit').removeAttr("disabled");
                                $(this).css('border-color', '#e4eaec');
                            }

                            // $.ajax({
                            //     type: "get",
                            //     url: "{{route('ajaxCheckDuplicate')}}",
                            //     data: {query: query, tableName: tableName, forWhich: forWhich},
                            //     dataType: "json",
                            //     success: function (data) {
                            //         if (data.exists) {
                            //             $('#btnSubmit').attr("disabled","disabled");
                            //             $('#errMsgDL').html('Please enter unique Driving Licence No');
                            //             $('#customer_nid').css("border-color","red");
                            //         }
                            //         else {
                            //             var numberOfEmptyFields = $('form').find('input[required],select[required]')
                            //                                         .filter(function () {
                            //                                             return $(this).val() === "";
                            //                                         }).length;
                            //             if (numberOfEmptyFields == 0) {
                            //                 $('#btnSubmit').removeClass("disabled");
                            //             }
                            //             $('#btnSubmit').removeAttr("disabled");
                            //             $(this).css('border-color','#e4eaec');
                            //         }
                            //     },
                            // });
                        }
                    } else {
                        $("#errMsgDL").html('');
                        $('#customer_nid').css("border-color", "Red");
                    }
                });
            } else if (selIdTxt === 'birthCertificate') {
                $(this).attr("placeholder", "Enter Birth Registration No");
                $("#errMsg,#errMsgNID,#errMsgSC,#errMsgPP,#errMsgDL").prop('id', 'errMsgBR');
                $(".identificationInput").on("input", function(event) {
                    $(this).addClass('textNumber');
                    var brNo = $(this).val();
                    if (brNo.length > 0) {
                        if (brNo.length != 17) {
                            $("#errMsgBR").html(
                                    "Not a valid 17-digit Birth Registration Number")
                                .show();
                            $(this).css('border-color', 'red');
                            $('#btnSubmit').attr("disabled", "disabled");
                        } else if (brNo.length == 17) {
                            $("#errMsgBR").html('');
                            $('#customer_nid').css('border-color', '#e4eaec');

                            // // // Duplicate Check
                            var query = $(this).val();
                            var forWhich = $(this).attr("name");
                            var tableName = btoa('pos_customers');

                            var columnName = $(this).attr("name") + '&&is_delete';
                            var columnValue = $(this).val() + '&&0';
                            var url_text = "{{url('/ajaxCheckDuplicate')}}";
                            var fieldID = $(this).attr("id");
                            var updateID = null;

                            fnCheckDuplicate(tableName, columnName, columnValue,
                                url_text, fieldID,
                                'errMsgBR', 'birth registration no.', updateID);

                            if ($('#' + fieldID).val() !== '') {
                                var numberOfEmptyFields = $('form').find(
                                        'input[required],select[required]')
                                    .filter(function() {
                                        return $(this).val() === "";
                                    }).length;
                                if (numberOfEmptyFields == 0) {
                                    $('#btnSubmit').removeClass("disabled");
                                }
                                $('#btnSubmit').removeAttr("disabled");
                                $(this).css('border-color', '#e4eaec');
                            }

                            // $.ajax({
                            //     type: "get",
                            //     url: "{{route('ajaxCheckDuplicate')}}",
                            //     data: {query: query, tableName: tableName, forWhich: forWhich},
                            //     dataType: "json",
                            //     success: function (data) {
                            //         if (data.exists) {
                            //             $('#btnSubmit').attr("disabled","disabled");
                            //             $('#errMsgBR').html('Please enter unique Birth Registration No');
                            //             $('#customer_nid').css("border-color","red");
                            //         }
                            //         else {
                            //             var numberOfEmptyFields = $('form').find('input[required],select[required]')
                            //                                         .filter(function () {
                            //                                             return $(this).val() === "";
                            //                                         }).length;
                            //             if (numberOfEmptyFields == 0) {
                            //                 $('#btnSubmit').removeClass("disabled");
                            //             }
                            //             $('#btnSubmit').removeAttr("disabled");
                            //             $(this).css('border-color','#e4eaec');
                            //         }
                            //     },
                            // });
                        }
                    } else {
                        $("#errMsgBR").html('');
                        $('#customer_nid').css("border-color", "Red");
                    }
                });
            }
        });
    });


    // Generate customer no. On Changing Brannch
    $('#branch_id').change(function() {
      var BranchID = $('#branch_id').val();
      if (BranchID != '') {
          $.ajax({
              method: "GET",
              url: "{{ url('/ajaxGCustomerNo') }}",
              dataType: "text",
              data: {
                  BranchID: BranchID
              },
              success: function(data) {
                  if (data) {

                      $('#customer_no').val(data);
                  }
              }
          });
      }

    });


    $('form').submit(function(event) {
        $(this).find(':submit').attr('disabled', 'disabled');
    });
});

function validate_fileupload(id) {
    var myFile = $('#' + id).prop('files');
    var filetype = myFile[0].type;
    var filesize = myFile[0].size / (1024 * 1024); // in mb

    var errorFlag = false;

    if (filesize > 1) {
        errorFlag = true;
    }

    if (filetype == 'image/jpeg' ||
        filetype == 'image/jpg' ||
        filetype == 'image/png' ||
        filetype == 'image/bmp' ||
        filetype == 'image/gif') {
        errorFlag = false;
    } else {
        errorFlag = true;
    }

    if (errorFlag === true) {
        $('#' + id).val('');
        swal({
            icon: 'error',
            title: 'Error',
            text: 'File size must be equal or less than 1 mb & file type is image. !!',
        });
    }
}
</script>
@endsection
