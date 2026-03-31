@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\HtmlService as HTML;
?>

<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator" novalidate="true" autocomplete="off">
    @csrf
    <div class="row">
        <div class="col-lg-8 offset-lg-3">
            <!-- Html View Load  -->
            {!! HTML::forCompanyFeild($CustomerData->company_id) !!}
        </div>
    </div>
    <div class="row">
        <div class="col-lg-8 offset-lg-3">
            {!! HTML::forBranchFeild(true,'branch_id','branch_id',$CustomerData->branch_id) !!}
        </div>
    </div>

    <div class="row">
        <!--Form Left-->
        <div class="col-lg-6">

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Company</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" id="company_name"
                            name="company_name" placeholder="Enter Company Name" required
                            data-error="Please enter Company name." value="{{ $CustomerData->company_name }}">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Billing Name</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" id="billing_name"
                            name="billing_name" placeholder="Enter Billing Name" required
                            data-error="Please enter Billing Name." value="{{ $CustomerData->billing_name }}">
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
                            placeholder="Enter Coustomer ID" required
                            data-error="Please enter Customer Code."
                            onblur="fnCheckDuplicate(
                                '{{base64_encode('bill_customers')}}',
                                this.name+'&&is_delete',
                                this.value+'&&0',
                                '{{url('/ajaxCheckDuplicate')}}',
                                this.id,
                                'txtCodeError',
                                'customer Code',
                                '{{$CustomerData->id}}');">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Customer Name</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" id="customer_name"
                            name="customer_name" placeholder="Enter Customer Name"
                            value="{{$CustomerData->customer_name}}" required
                            data-error="Please enter Customer name.">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
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


            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">
                    NID/Smart Card/&nbsp Passport/Driving License/&nbsp Birth Certificate
                </label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <div class="radio-custom radio-primary">
                            <input type="radio" class="identification" id="n1" name="customer_id_type"
                            value="nid" {{ $CustomerData->customer_id_type == 'nid' ? 'checked' : ''}}>
                            <label for="n1">NID &nbsp &nbsp</label>
                        </div>
                        <div class="radio-custom radio-primary">
                            <input type="radio" class="identification" id="n2" name="customer_id_type"
                            value="smartCard" {{ $CustomerData->customer_id_type == 'smartCard' ? 'checked' : ''}}>
                            <label for="n2">Smart Card &nbsp &nbsp</label>
                        </div>
                        <div class="radio-custom radio-primary">
                            <input type="radio" class="identification" id="n3" name="customer_id_type"
                            value="passport" {{ $CustomerData->customer_id_type == 'passport' ? 'checked' : ''}}>
                            <label for="n3">Passport &nbsp &nbsp</label>
                        </div>
                        <div class="radio-custom radio-primary">
                            <input type="radio" class="identification" id="n4" name="customer_id_type"
                            value="drivingLicense" {{ $CustomerData->customer_id_type == 'drivingLicense' ? 'checked' : ''}}>
                            <label for="n4">Driving License &nbsp &nbsp</label>
                        </div>
                        <div class="radio-custom radio-primary">
                            <input type="radio" class="identification" id="n5" name="customer_id_type"
                            value="birthCertificate" {{ $CustomerData->customer_id_type == 'birthCertificate' ? 'checked' : ''}}>
                            <label for="n5">Birth Certificate &nbsp &nbsp</label>
                        </div>
                        <div class="input-group mt-4">
                            <input type="text" class="form-control round textNumber identificationInput" name="customer_nid"
                                id="customer_nid" placeholder="Enter NID No" value="{{$CustomerData->customer_nid}}">
                        </div>
                        <div class="help-block with-errors is-invalid" id="errMsg"></div>
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Mobile</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <input type="text" pattern="[01][0-9]{10}" class="form-control round textNumber"
                                name="customer_mobile" value="{{$CustomerData->customer_mobile}}"
                                id="customer_mobile" placeholder="Mobile Number (01*********)" required
                                data-error="Please enter mobile number (01*********)"
                                minlength="11" maxlength="11"
                                onblur="fnCheckDuplicate(
                                '{{base64_encode('pos_customers')}}',
                                this.name+'&&is_delete',
                                this.value+'&&0',
                                '{{url('/ajaxCheckDuplicate')}}',
                                this.id,
                                'errMsgPhone',
                                'mobile number',
                                '{{$CustomerData->id}}');">
                    </div>
                    <div class="help-block with-errors is-invalid" id="errMsgPhone"></div>
                </div>
            </div>



        </div>

        <!--Form Right-->
        <div class="col-lg-6">

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title">Email</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <input type="email" class="form-control round" id="customer_email"
                            name="customer_email" value="{{ $CustomerData->customer_email }}"
                            placeholder="Enter Email"
                            onblur="fnCheckDuplicate(
                                '{{base64_encode('bill_customers')}}',
                                this.name+'&&is_delete',
                                this.value+'&&0',
                                '{{url('/ajaxCheckDuplicate')}}',
                                this.id,
                                'errMsgEmail',
                                'Email ID',
                                '{{$CustomerData->id}}');">
                    </div>
                    <div class="help-block with-errors is-invalid" id="errMsgEmail"></div>
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
                            placeholder="DD-MM-YYYY">
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
              <label class="col-lg-4 input-title " for="cus_gender">Gender</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <div class="radio-custom radio-primary">
                            <input type="radio" id="g1" name="cus_gender" value="male"
                            {{ ($CustomerData->cus_gender == "male") ? 'checked' : '' }} >
                            <label for="g1">Male  &nbsp &nbsp  </label>
                        </div>
                        <div class="radio-custom radio-primary">
                            <input type="radio" id="g2" name="cus_gender" value="female"
                            {{ $CustomerData->cus_gender == "female" ? 'checked' : ''}} >
                            <label for="g2">Female &nbsp &nbsp </label>
                        </div>
                        <div class="radio-custom radio-primary">
                            <input type="radio" id="g3" name="cus_gender" value="others"
                            {{ $CustomerData->cus_gender == "others" ? 'checked' : ''}}>
                            <label for="g3">Others &nbsp &nbsp</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title">Present Address:<span
                        class="RequireText"></span></label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <textarea class="form-control round" id="present_addr"
                            name="present_addr" placeholder="Enter Present Address"
                            data-error="Please enter Present Address.">{{ $CustomerData->present_addr }}</textarea>
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title">Permanent Address:<span
                        class="RequireText"></span></label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <textarea class="form-control round" id="permanent_addr"
                            name="permanent_addr" placeholder="Enter Permanent Address"
                            data-error="Please enter Permanent Address.">{{ $CustomerData->permanent_addr }}</textarea>
                    </div>
                </div>
            </div>


            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title">Customer Picture</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group input-group-file" data-plugin="inputGroupFile">
                        <input type="text" class="form-control round">
                        <div class="input-group-append">
                            <span class="btn btn-success btn-file" style="height: 30px">
                                <i class="icon wb-upload" aria-hidden="true"></i>
                                <input type="file" id="customer_image" name="customer_image">

                                <!-- <input type="hidden" name="customer_image"
                                    value="{{$CustomerData->customer_image}}"> -->
                            </span>
                        </div>
                    </div>
                    <!-- @if(!empty($CustomerData->customer_image))
                        @if(file_exists($CustomerData->customer_image))
                        <img src="{{ asset('storage/uploads/customer/' . $CustomerData->id . '/'.$CustomerData->customer_image) }}" style="height: 32PX; width: 32PX;">
                        @endif
                    @endif -->
                </div>

                <div class="col-lg-1">
                    @if(!empty($CustomerData->customer_image))

                    @if(file_exists($CustomerData->customer_image))
                    <img src="{{ asset($CustomerData->customer_image) }}" style="width: 70px;">
                    @endif
                    @endif
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title">Description</label>
                <div class="col-lg-8 form-group">
                    <div class="input-group">
                        <textarea class="form-control" name="customer_desc" id="customer_desc"
                            rows="2" placeholder="Enter Description">{{$CustomerData->customer_desc}}</textarea>
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
                    <button type="submit" class="btn btn-primary btn-round" id="btnSubmit">Update</button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- frontend required field check -->
<script>

    $(document).ready(function() {
        if ($('.identification').is(':checked')){
            var idTxt = $('.identification:checked').val();

            // Natinal ID Validation
            if(idTxt === 'nid'){
                $(this).attr("placeholder", "Enter NID No");
                $("#errMsg,#errMsgSC,#errMsgPP,#errMsgDL,#errMsgBR").prop('id', 'errMsgNID');
                $(".identificationInput").on("blur", function(event){
                    // $('#n2,#n3,#n4,#n5').prop('disabled', true);
                    var nidNo = $(this).val();
                    if (nidNo.length > 0) {
                        if (nidNo.length != 13) {
                            if (nidNo.length != 17) {
                                $("#errMsgNID").html("Invalid NID! NID must be of 13 or 17 Digits")
                                .show();
                                $('#btnSubmit').attr('disabled', 'disabled');
                                console.log(nidNo.length);
                                // $(this).focus();
                            }else if ( nidNo.length == 17 ){
                                $('#btnSubmit').removeAttr("disabled");
                                $("#errMsgNID").html('');
                            }
                        } else if ( nidNo.length == 13 ){
                            $('#btnSubmit').removeAttr("disabled");
                            $("#errMsgNID").html('');
                        }
                    }else if ( nidNo.length == 0 ){
                            $('#btnSubmit').removeAttr("disabled");
                            $("#errMsgNID").html('');
                        }
                });
            }
            else if(idTxt === 'smartCard'){
                $(this).attr("placeholder", "Enter Smart Card No");
                $("#errMsg,#errMsgNID,#errMsgPP,#errMsgDL,#errMsgBR").prop('id', 'errMsgSC');
                $(".identificationInput").on("blur", function(event){
                    var cardNo = $(this).val();
                    if (cardNo.length > 0) {
                        if(cardNo.length != 10) {
                            $("#errMsgSC").html("Not a valid 10-digit Smart Card Number").show();
                            $('#btnSubmit').attr('disabled', 'disabled');
                            // $(this).focus();
                            console.log(cardNo.length);
                        }else if ( cardNo.length == 10 ){
                                console.log(cardNo.length);
                                $('#btnSubmit').removeAttr("disabled");
                                $("#errMsgSC").html('');
                            }
                    } else if ( cardNo.length == 0 ){
                                console.log(cardNo.length);
                                $('#btnSubmit').removeAttr("disabled");
                                $("#errMsgSC").html('');
                            }
                });
            }
            else if(idTxt === 'passport'){
                $(this).attr("placeholder", "Enter passport No");
                $("#errMsg,#errMsgNID,#errMsgSC,#errMsgDL,#errMsgBR").prop('id', 'errMsgPP');
                $(".identificationInput").on("blur", function(event){
                    var passportNo = $(this).val();
                    if(passportNo.length != 0) {
                        if (passportNo.length != 9) {
                            $("#btnSubmit").attr("disabled", true);
                            $("#errMsgPP").html("Not a valid 9-digit Passport Number").show();
                            // $(this).focus();
                        } else if ( passportNo.length == 9 ){
                                $('#btnSubmit').removeAttr("disabled");
                                $("#errMsgPP").html('');

                            }
                    } else if ( passportNo.length == 0 ){
                                $('#btnSubmit').removeAttr("disabled");
                                $("#errMsgPP").html('');
                            }
                });
            }
            else if(idTxt === 'drivingLicense'){
                $(this).attr("placeholder", "Enter Driving License No");
                $("#errMsg,#errMsgNID,#errMsgSC,#errMsgPP,#errMsgBR").prop('id', 'errMsgDL');
                $(".identificationInput").on("blur", function(event){
                    var licenceNo = $(this).val();
                    if(licenceNo.length != 0) {
                        if (licenceNo.length != 15) {
                            $("#btnSubmit").attr("disabled", true);
                            $("#errMsgDL").html("Not a valid 15-digit Driving Licence Number").show();
                            // $(this).focus();
                        } else if ( licenceNo.length == 15 ){
                                $('#btnSubmit').removeAttr("disabled");
                                $("#errMsgDL").html('');

                            }
                    } else if ( licenceNo.length == 0 ){
                                $('#btnSubmit').removeAttr("disabled");
                                $("#errMsgDL").html('');
                            }
                });
            }
            else if(idTxt === 'birthCertificate'){
                $(this).attr("placeholder", "Enter Birth Registration No");
                $("#errMsg,#errMsgNID,#errMsgSC,#errMsgPP,#errMsgDL").prop('id', 'errMsgBR');
                $(".identificationInput").on("blur", function(event){
                    var brNo = $(this).val();
                    if(brNo.length != 0) {
                        if (brNo.length != 17) {
                            $("#btnSubmit").attr("disabled", true);
                            $("#errMsgBR").html("Not a valid 17-digit Birth Registration Number").show();
                            // $(this).focus();
                        } else if ( brNo.length == 17 ){
                                $('#btnSubmit').removeAttr("disabled");
                                $("#errMsgBR").html('');
                            }
                    } else if ( brNo.length == 0 ){
                                $('#btnSubmit').removeAttr("disabled");
                                $("#errMsgBR").html('');
                            }
                });
            }
        }

        $(".identification").click(function() {
            $("#errMsg,#errMsgNID,#errMsgSC,#errMsgPP,#errMsgDL,#errMsgBR").html('');
            $('#customer_nid').val('');
            var selIdTxt = $(this).val();
            console.log(selIdTxt);

            $( '.identificationInput' ).each(function() {

                if(selIdTxt === 'nid'){
                    $(this).attr("placeholder", "Enter NID No");
                    $("#errMsg,#errMsgSC,#errMsgPP,#errMsgDL,#errMsgBR").prop('id', 'errMsgNID');
                    $(".identificationInput").on("blur", function(event){
                        // $('#n2,#n3,#n4,#n5').prop('disabled', true);
                        var nidNo = $(this).val();
                        if (nidNo.length != 0) {
                            if (nidNo.length != 13) {
                                if (nidNo.length != 17) {
                                    $("#errMsgNID").html("Invalid NID! NID must be of 13 or 17 Digits")
                                    .show();
                                    $('#btnSubmit').attr('disabled', 'disabled');
                                    console.log(nidNo.length);
                                    // $(this).focus();
                                }else if ( nidNo.length == 17 ){
                                    $('#btnSubmit').removeAttr("disabled");
                                    $("#errMsgNID").html('');

                                }
                            } else if ( nidNo.length == 13 ){
                                $('#btnSubmit').removeAttr("disabled");
                                $("#errMsgNID").html('');
                            }
                        }else if ( nidNo.length == 0 ){
                                $('#btnSubmit').removeAttr("disabled");
                                $("#errMsgNID").html('');
                            }
                    });
                }
                else if(selIdTxt === 'smartCard'){
                    $(this).attr("placeholder", "Enter Smart Card No");
                    $("#errMsg,#errMsgNID,#errMsgPP,#errMsgDL,#errMsgBR").prop('id', 'errMsgSC');
                    $(".identificationInput").on("blur", function(event){
                        var cardNo = $(this).val();
                        if (cardNo.length > 0) {
                            if(cardNo.length != 10) {
                                $("#errMsgSC").html("Not a valid 10-digit Smart Card Number").show();
                                $('#btnSubmit').attr('disabled', 'disabled');
                                // $(this).focus();
                                console.log(cardNo.length);
                            }else if ( cardNo.length == 10 ){
                                    console.log(cardNo.length);
                                    $('#btnSubmit').removeAttr("disabled");
                                    $("#errMsgSC").html('');
                                }
                        } else if ( cardNo.length == 0 ){
                                    console.log(cardNo.length);
                                    $('#btnSubmit').removeAttr("disabled");
                                    $("#errMsgSC").html('');
                                }
                    });
                }
                else if(selIdTxt === 'passport'){
                    $(this).attr("placeholder", "Enter passport No");
                    $("#errMsg,#errMsgNID,#errMsgSC,#errMsgDL,#errMsgBR").prop('id', 'errMsgPP');
                    $(".identificationInput").on("blur", function(event){
                        var passportNo = $(this).val();
                        if(passportNo.length != 0) {
                            if (passportNo.length != 9) {
                                $("#btnSubmit").attr("disabled", true);
                                $("#errMsgPP").html("Not a valid 9-digit Passport Number").show();
                                // $(this).focus();
                            } else if ( passportNo.length == 9 ){
                                    $('#btnSubmit').removeAttr("disabled");
                                    $("#errMsgPP").html('');

                                }
                        } else if ( passportNo.length == 0 ){
                                    $('#btnSubmit').removeAttr("disabled");
                                    $("#errMsgPP").html('');
                                }
                    });

                }
                else if(selIdTxt === 'drivingLicense'){
                    $(this).attr("placeholder", "Enter Driving License No");
                    $("#errMsg,#errMsgNID,#errMsgSC,#errMsgPP,#errMsgBR").prop('id', 'errMsgDL');
                    $(".identificationInput").on("blur", function(event){
                        var licenceNo = $(this).val();
                        if(licenceNo.length != 0) {
                            if (licenceNo.length != 15) {
                                $("#btnSubmit").attr("disabled", true);
                                $("#errMsgDL").html("Not a valid 15-digit Driving Licence Number").show();
                                // $(this).focus();
                            } else if ( licenceNo.length == 15 ){
                                    $('#btnSubmit').removeAttr("disabled");
                                    $("#errMsgDL").html('');

                                }
                        } else if ( licenceNo.length == 0 ){
                                    $('#btnSubmit').removeAttr("disabled");
                                    $("#errMsgDL").html('');
                                }
                    });
                }
                else if(selIdTxt === 'birthCertificate'){
                    $(this).attr("placeholder", "Enter Birth Registration No");
                    $("#errMsg,#errMsgNID,#errMsgSC,#errMsgPP,#errMsgDL").prop('id', 'errMsgBR');
                    $(".identificationInput").on("blur", function(event){
                        var brNo = $(this).val();
                        if(brNo.length != 0) {
                            if (brNo.length != 17) {
                                $("#btnSubmit").attr("disabled", true);
                                $("#errMsgBR").html("Not a valid 17-digit Birth Registration Number").show();
                                // $(this).focus();
                            } else if ( brNo.length == 17 ){
                                    $('#btnSubmit').removeAttr("disabled");
                                    $("#errMsgBR").html('');
                                }
                        } else if ( brNo.length == 0 ){
                                    $('#btnSubmit').removeAttr("disabled");
                                    $("#errMsgBR").html('');
                                }
                    });
                }

            });
        });

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


    });



</script>
@endsection
