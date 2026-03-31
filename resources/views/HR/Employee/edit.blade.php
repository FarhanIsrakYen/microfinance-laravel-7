@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
?>
<?php 
    $designationData = Common::ViewTableOrder('hr_designations',
                            [['is_delete', 0]],
                            ['id', 'name'],
                            ['name', 'ASC']);

    $departmentData = Common::ViewTableOrder('hr_departments',
                            [['is_delete', 0]],
                            ['id', 'dept_name'],
                            ['dept_name', 'ASC']);
?>

<!-- Page -->
    <form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator" novalidate="true" autocomplete="off">
        @csrf
        <div class="row">
            <div class="col-lg-8 offset-lg-3">
                <!-- Html View Load  -->
                {!! HTML::forCompanyFeild($EmployeeData->company_id) !!}
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8 offset-lg-3">
                {!! HTML::forBranchFeild(true,'branch_id','branch_id',$EmployeeData->branch_id) !!}
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">

                <div class="form-row form-group align-items-center">
                    <label class="col-lg-4 input-title RequiredStar">Employee Code</label>
                    <div class="col-lg-7">
                        <div class="input-group">
                            <input type="text" class="form-control round" id="emp_code" name="emp_code"
                                value="{{sprintf('%04d',$EmployeeData->emp_code)}}"
                                placeholder="Enter Employee Code" required readonly
                                data-error="Please enter Employee Code.">
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>

                <div class="form-row form-group align-items-center">
                    <label class="col-lg-4 input-title RequiredStar">Employee Name</label>
                    <div class="col-lg-7">
                        <div class="input-group">
                            <input type="text" class="form-control round" id="emp_name" name="emp_name"
                                placeholder="Enter Employee Name" required
                                value="{{ $EmployeeData->emp_name}}"
                                data-error="Please enter Employee name.">
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>

                <div class="form-row form-group align-items-center">
                    <label class="col-lg-4 input-title">Father's Name</label>
                    <div class="col-lg-7">
                        <div class="input-group">
                            <input type="text" class="form-control round" id="emp_father_name"
                                name="emp_father_name" value="{{$EmployeeData->emp_father_name}}"
                                placeholder="Enter Father's Name">
                        </div>
                    </div>
                </div>

                <div class="form-row form-group align-items-center">
                    <label class="col-lg-4 input-title">Mother's Name</label>
                    <div class="col-lg-7">
                        <div class="input-group">
                            <input type="text" class="form-control round" 
                                id="emp_mother_name" name="emp_mother_name"
                                value="{{ $EmployeeData->emp_mother_name }}"
                                placeholder="Enter Mother's Name">
                        </div>
                    </div>
                </div>

                <div class="form-row form-group align-items-center">
                    <label class="col-lg-4 input-title">Date of Birth</label>
                    <div class="col-lg-7">
                        <div class="input-group">
                            <div class="input-group-prepend ">
                                <span class="input-group-text ">
                                    <i class="icon wb-calendar round" aria-hidden="true"></i>
                                </span>
                            </div>

                            <input type="text" class="form-control round datepicker" id="emp_dob"
                                name="emp_dob" value="<?= (!empty($EmployeeData->emp_dob)) ? date('d-m-Y', strtotime($EmployeeData->emp_dob)) : '' ?>"
                                autocomplete="off" placeholder="DD-MM-YYYY">

                        </div>
                    </div>
                </div>

                <div class="form-row form-group align-items-center">
                    <label class="col-lg-4 input-title">Email</label>
                    <div class="col-lg-7">
                        <div class="input-group">
                            <input type="email" class="form-control round" id="emp_email" name="emp_email"
                                value="{{$EmployeeData->emp_email}}" placeholder="Enter Email">
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>

                <div class="form-row form-group align-items-center">
                    <label class="col-lg-4 input-title RequiredStar">Mobile</label>
                    <div class="col-lg-7">
                        <input type="text" pattern="[01][0-9]{10}" class="form-control round textNumber" name="emp_phone"
                        value="{{$EmployeeData->emp_phone}}"
                                id="emp_phone" placeholder="Mobile Number (01*********)" required
                                data-error="Please enter mobile number (01*********)" 
                                minlength="11" maxlength="11"
                                onblur="fnCheckDuplicate(
                                '{{base64_encode('hr_employees')}}', 
                                this.name+'&&is_delete', 
                                this.value+'&&0',
                                '{{url('/ajaxCheckDuplicate')}}',
                                this.id,
                                'errMsgPhone', 
                                'mobile number',
                                '{{$EmployeeData->id}}');">
                        <div class="help-block with-errors is-invalid" id="errMsgPhone"></div>
                    </div>
                </div>

                <div class="form-row form-group align-items-center">
                    <label class="col-lg-4 input-title">
                        NID/Smart Card/&nbsp Passport/Driving License/&nbsp Birth Certificate
                    </label>
                    <div class="col-lg-7">
                        <div class="input-group">
                            <div class="radio-custom radio-primary">
                                <input type="radio" class="identification" id="n1" name="emp_id_type" 
                                value="nid" {{ $EmployeeData->emp_id_type == 'nid' ? 'checked' : ''}}>
                                <label for="n1">NID &nbsp &nbsp</label>
                            </div>
                            <div class="radio-custom radio-primary">
                                <input type="radio" class="identification" id="n2" name="emp_id_type" 
                                value="smartCard" {{ $EmployeeData->emp_id_type == 'smartCard' ? 'checked' : ''}}>
                                <label for="n2">Smart Card &nbsp &nbsp</label>
                            </div>
                            <div class="radio-custom radio-primary">
                                <input type="radio" class="identification" id="n3" name="emp_id_type" 
                                value="passport" {{ $EmployeeData->emp_id_type == 'passport' ? 'checked' : ''}}>
                                <label for="n3">Passport &nbsp &nbsp</label>
                            </div>
                            <div class="radio-custom radio-primary">
                                <input type="radio" class="identification" id="n4" name="emp_id_type" 
                                value="drivingLicense" {{ $EmployeeData->emp_id_type == 'drivingLicense' ? 'checked' : ''}}>
                                <label for="n4">Driving License &nbsp &nbsp</label>
                            </div>
                            <div class="radio-custom radio-primary">
                                <input type="radio" class="identification" id="n5" name="emp_id_type" 
                                value="birthCertificate" {{ $EmployeeData->emp_id_type == 'birthCertificate' ? 'checked' : ''}}>
                                <label for="n5">Birth Certificate &nbsp &nbsp</label>
                            </div>
                            <div class="input-group mt-4">
                                <input type="text" class="form-control round textNumber identificationInput" name="emp_national_id"
                                    id="emp_national_id" placeholder="Enter NID No" value="{{$EmployeeData->emp_national_id}}">
                            </div>
                            <div class="help-block with-errors is-invalid" id="errMsg"></div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-lg-6">

                <div class="form-row form-group align-items-center">
                    <label class="col-lg-4 input-title" for="emp_gender">Gender</label>
                    <div class="col-lg-7">
                        <div class="input-group">
                            <div class="radio-custom radio-primary">
                                <input type="radio" id="g1" name="emp_gender" value="male"
                                    {{ $EmployeeData->emp_gender == 'male' ? 'checked' : ''}}>
                                <label for="g1">Male &nbsp &nbsp </label>
                            </div>
                            <div class="radio-custom radio-primary">
                                <input type="radio" id="g2" name="emp_gender" value="female"
                                    {{ $EmployeeData->emp_gender == 'female' ? 'checked' : ''}}>
                                <label for="g2">Female &nbsp &nbsp </label>
                            </div>
                            <div class="radio-custom radio-primary">
                                <input type="radio" id="g3" name="emp_gender" value="others"
                                    {{ $EmployeeData->emp_gender == 'others' ? 'checked' : ''}}>
                                <label for="g3">Others &nbsp &nbsp</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-row form-group align-items-center">
                    <label class="col-lg-4 input-title">Designation</label>
                    <div class="col-lg-7">
                        <div class="input-group">
                            <select class="form-control clsSelect2" required data-error="Please select Designation"
                            name="designation_id" id="designation_id">
                                <option value="">Select Designation</option>
                                @foreach ($designationData as $Row)
                                <option value="{{$Row->id}}" {{ $Row->id == $EmployeeData->designation_id ? 'selected' : '' }}>{{$Row->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row form-group align-items-center">
                    <label class="col-lg-4 input-title RequiredStar">Department</label>
                    <div class="col-lg-7">
                        <div class="input-group">
                            <select class="form-control clsSelect2" required 
                            data-error="Please select department"
                            name="department_id" id="department_id">
                                <option value="">Select Department</option>
                                @foreach ($departmentData as $Row)
                                <option value="{{$Row->id}}" {{ $Row->id == $EmployeeData->department_id ? 'selected' : '' }}>{{$Row->dept_name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row form-group align-items-center">
                    <label class="col-lg-4 input-title">Present Address</label>
                    <div class="col-lg-7">
                        <div class="input-group">
                            <textarea class="form-control round" id="emp_present_addr" name="emp_present_addr" 
                                rows="2" placeholder="Enter Address">{{$EmployeeData->emp_present_addr}}</textarea>
                        </div>
                    </div>
                </div>
                <div class="form-row form-group align-items-center">
                    <label class="col-lg-4 input-title">Permanent Address</label>
                    <div class="col-lg-7">
                        <div class="input-group">
                            <textarea class="form-control round" id="emp_parmanent_addr"
                                name="emp_parmanent_addr" rows="2"
                                placeholder="Enter Permanent Address">{{$EmployeeData->emp_parmanent_addr}}</textarea>
                        </div>
                    </div>
                </div>
                <div class="form-row form-group align-items-center">
                    <label class="col-lg-4 input-title">Description</label>
                    <div class="col-lg-7">
                        <div class="input-group">
                            <textarea class="form-control round" id="emp_description" name="emp_description"
                                rows="2" placeholder="Enter Description">{{$EmployeeData->emp_description}}</textarea>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="row align-items-center">
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
<!-- End Page -->

<script>
    var empId = "{{ $EmployeeData->id }}";

    $('form').submit(function (event) {
        $(this).find(':submit').attr('disabled', 'disabled');
    });

    if ($('.identification').is(':checked')){
            var idTxt = $('.identification:checked').val();

            // Natinal ID Validation
            if(idTxt === 'nid'){
                $(this).attr("placeholder", "Enter NID No");
                $("#errMsg,#errMsgSC,#errMsgPP,#errMsgDL,#errMsgBR").prop('id', 'errMsgNID');
                $(".identificationInput").on("input", function(event){
                    var nidNo = $(this).val();
                    if (nidNo.length > 0) {
                        if (nidNo.length != 13) {
                            if (nidNo.length != 17) {
                                $("#errMsgNID").html("Invalid NID! NID must be of 13 or 17 Digits").show();
                                $('#btnSubmit').attr('disabled', 'disabled');
                                $('#emp_national_id').css("border-color","red");
                            }else if ( nidNo.length == 17 ){
                                $('#btnSubmit').removeAttr("disabled");
                                $('#btnSubmit').removeClass("disabled");
                                $("#errMsgNID").html('');
                                $('#emp_national_id').css('border-color','#e4eaec');
                            }
                        } else if ( nidNo.length == 13 ){
                            $('#btnSubmit').removeAttr("disabled");
                            $('#btnSubmit').removeClass("disabled");
                            $("#errMsgNID").html('');
                            $('#emp_national_id').css('border-color','#e4eaec');
                        }
                    }
                    else {
                        $("#errMsgNID").html('');
                        $('#emp_national_id').css("border-color","#e4eaec");
                        $('#btnSubmit').removeAttr('disabled');
                    }

                    // // // Duplicate Check
                    var query = $(this).val();
                    var forWhich = $(this).attr("name");
                    var tableName = btoa('hr_employees');

                    var columnName = $(this).attr("name")+'&&is_delete';
                    var columnValue = $(this).val()+'&&0';
                    var url_text = "{{url('/ajaxCheckDuplicate')}}";
                    var fieldID = $(this).attr("id");
                    var updateID = '{{ $EmployeeData->id }}';

                    fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                    'errMsgNID', 'NID.', updateID);

                    if($('#'+ fieldID).val() !== ''){
                        $('#btnSubmit').removeAttr("disabled");
                        $('#btnSubmit').removeClass("disabled");
                        $('#emp_national_id').css('border-color','#e4eaec');
                    }

                    // $.ajax({
                    //     type: "get",
                    //     url: "{{route('ajaxCheckDuplicate')}}",
                    //     data: {query: query, tableName: tableName, forWhich: forWhich},
                    //     dataType: "json",
                    //     success: function (data) {
                    //         if (data.exists) {
                    //             if (data.rowID != empId) {
                    //                 $('#btnSubmit').attr('disabled', 'disabled');
                    //                 $('#errMsgNID').html('Please enter unique NID');
                    //                 $('#emp_national_id').css("border-color","red");
                    //             }
                    //             else {
                    //                 $('#btnSubmit').removeAttr("disabled");
                    //                 $('#btnSubmit').removeClass("disabled");
                    //                 $('#emp_national_id').css('border-color','#e4eaec');
                    //             }
                    //         }
                    //     },
                    // });
                });
            }
            else if(idTxt === 'smartCard'){
                $(this).attr("placeholder", "Enter Smart Card No");
                $("#errMsg,#errMsgNID,#errMsgPP,#errMsgDL,#errMsgBR").prop('id', 'errMsgSC');
                $(".identificationInput").on("input", function(event){
                    var cardNo = $(this).val();
                    if (cardNo.length > 0) {
                        if(cardNo.length != 10) {
                            $("#errMsgSC").html("Not a valid 10-digit Smart Card Number").show();
                            $('#btnSubmit').attr('disabled', 'disabled');
                            $('#emp_national_id').css("border-color","red");
                        }else if ( cardNo.length == 10 ){
                                $('#btnSubmit').removeAttr("disabled");
                                $('#btnSubmit').removeClass("disabled");
                                $("#errMsgSC").html('');
                                $('#emp_national_id').css('border-color','#e4eaec');
                            }
                    }
                    else {
                        $("#errMsgSC").html('');
                        $('#emp_national_id').css("border-color","#e4eaec");
                        $('#btnSubmit').removeAttr('disabled');
                    }

                    // // // Duplicate Check
                    var query = $(this).val();
                    var forWhich = $(this).attr("name");
                    var tableName = btoa('hr_employees');

                    var columnName = $(this).attr("name")+'&&is_delete';
                    var columnValue = $(this).val()+'&&0';
                    var url_text = "{{url('/ajaxCheckDuplicate')}}";
                    var fieldID = $(this).attr("id");
                    var updateID = '{{ $EmployeeData->id }}';

                    fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                    'errMsgSC', 'smart card no.', updateID);

                    if($('#'+ fieldID).val() !== ''){
                        $('#btnSubmit').removeAttr("disabled");
                        $('#btnSubmit').removeClass("disabled");
                        $('#emp_national_id').css('border-color','#e4eaec');
                    }

                    // $.ajax({
                    //     type: "get",
                    //     url: "{{route('ajaxCheckDuplicate')}}",
                    //     data: {query: query, tableName: tableName, forWhich: forWhich},
                    //     dataType: "json",
                    //     success: function (data) {
                    //         if (data.exists) {
                    //             if (data.rowID != empId) {
                    //                 $('#btnSubmit').attr('disabled', 'disabled');
                    //                 $('#errMsgSC').html('Please enter unique Smart Card No');
                    //                 $('#emp_national_id').css("border-color","red");
                    //             }
                    //             else {
                    //                 $('#btnSubmit').removeAttr("disabled");
                    //                 $('#btnSubmit').removeClass("disabled");
                    //                 $('#emp_national_id').css('border-color','#e4eaec');
                    //             }
                    //         }
                    //     },
                    // });
                });
            }
            else if(idTxt === 'passport'){
                $(this).attr("placeholder", "Enter passport No");
                $("#errMsg,#errMsgNID,#errMsgSC,#errMsgDL,#errMsgBR").prop('id', 'errMsgPP');
                $(".identificationInput").on("input", function(event){
                    var passportNo = $(this).val();
                    if(passportNo.length > 0) {
                        if (passportNo.length != 9) {
                            $("#btnSubmit").attr("disabled", true);
                            $("#errMsgPP").html("Not a valid 9-digit Passport Number").show();
                            $('#emp_national_id').css('border-color','red');
                        } 
                        else if ( passportNo.length == 9 ){
                            $('#btnSubmit').removeAttr("disabled");
                            $('#btnSubmit').removeClass("disabled");
                            $("#errMsgPP").html('');
                            $('#emp_national_id').css('border-color','#e4eaec');
                        }   
                    }
                    else {
                        $("#errMsgPP").html('');
                        $('#emp_national_id').css("border-color","#e4eaec");
                        $('#btnSubmit').removeAttr('disabled');
                    }

                    // // // Duplicate Check
                    var query = $(this).val();
                    var forWhich = $(this).attr("name");
                    var tableName = btoa('hr_employees');

                    var columnName = $(this).attr("name")+'&&is_delete';
                    var columnValue = $(this).val()+'&&0';
                    var url_text = "{{url('/ajaxCheckDuplicate')}}";
                    var fieldID = $(this).attr("id");
                    var updateID = '{{ $EmployeeData->id }}';

                    fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                    'errMsgPP', 'passport no.', updateID);

                    if($('#'+ fieldID).val() !== ''){
                        $('#btnSubmit').removeAttr("disabled");
                        $('#btnSubmit').removeClass("disabled");
                        $('#emp_national_id').css('border-color','#e4eaec');
                    }

                    // $.ajax({
                    //     type: "get",
                    //     url: "{{route('ajaxCheckDuplicate')}}",
                    //     data: {query: query, tableName: tableName, forWhich: forWhich},
                    //     dataType: "json",
                    //     success: function (data) {
                    //         if (data.exists) {
                    //             if (data.rowID != empId) {
                    //                 $('#btnSubmit').attr('disabled', 'disabled');
                    //                 $('#errMsgPP').html('Please enter unique Passport No');
                    //                 $('#emp_national_id').css('border-color','red');
                    //             }
                    //             else {
                    //                 $('#btnSubmit').removeAttr("disabled");
                    //                 $('#btnSubmit').removeClass("disabled");
                    //                 $('#emp_national_id').css('border-color','#e4eaec');
                    //             }
                    //         }
                    //     },
                    // });
                });

            }
            else if(idTxt === 'drivingLicense'){
                $(this).attr("placeholder", "Enter Driving License No");
                $("#errMsg,#errMsgNID,#errMsgSC,#errMsgPP,#errMsgBR").prop('id', 'errMsgDL');
                $(".identificationInput").on("input", function(event){
                    var licenceNo = $(this).val();
                    if(licenceNo.length > 0) {
                        if (licenceNo.length != 15) {
                            $("#btnSubmit").attr("disabled", true);
                            $("#errMsgDL").html("Not a valid 15-digit Driving Licence Number").show();
                            $('#emp_national_id').css('border-color','red');
                        } 
                        else if ( licenceNo.length == 15 ){
                            $('#btnSubmit').removeAttr("disabled");
                            $('#btnSubmit').removeClass("disabled");
                            $("#errMsgDL").html('');
                            $('#emp_national_id').css('border-color','#e4eaec');
                        }   
                    } 
                    else {
                        $("#errMsgDL").html('');
                        $('#emp_national_id').css("border-color","#e4eaec");
                        $('#btnSubmit').removeAttr('disabled');
                    }
                    

                    // // // Duplicate Check
                    var query = $(this).val();
                    var forWhich = $(this).attr("name");
                    var tableName = btoa('hr_employees');

                    var columnName = $(this).attr("name")+'&&is_delete';
                    var columnValue = $(this).val()+'&&0';
                    var url_text = "{{url('/ajaxCheckDuplicate')}}";
                    var fieldID = $(this).attr("id");
                    var updateID = '{{ $EmployeeData->id }}';

                    fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                    'errMsgDL', 'driving license no.', updateID);

                    if($('#'+ fieldID).val() !== ''){
                        $('#btnSubmit').removeAttr("disabled");
                        $('#btnSubmit').removeClass("disabled");
                        $('#emp_national_id').css('border-color','#e4eaec');
                    }

                    // $.ajax({
                    //     type: "get",
                    //     url: "{{route('ajaxCheckDuplicate')}}",
                    //     data: {query: query, tableName: tableName, forWhich: forWhich},
                    //     dataType: "json",
                    //     success: function (data) {
                    //         if (data.exists) {
                    //             if (data.rowID != empId) {
                    //                 $('#btnSubmit').attr('disabled', 'disabled');
                    //                 $('#errMsgDL').html('Please enter unique Birth Registration No');
                    //                 $('#emp_national_id').css('border-color','red');
                    //             }
                    //             else {
                    //                 $('#btnSubmit').removeAttr("disabled");
                    //                 $('#btnSubmit').removeClass("disabled");
                    //                 $('#emp_national_id').css('border-color','#e4eaec');
                    //             }
                    //         }
                    //     },
                    // });
                });
            }
            else if(idTxt === 'birthCertificate'){
                $(this).attr("placeholder", "Enter Birth Registration No");
                $("#errMsg,#errMsgNID,#errMsgSC,#errMsgPP,#errMsgDL").prop('id', 'errMsgBR');
                $(".identificationInput").on("input", function(event){
                    var brNo = $(this).val();
                    if(brNo.length > 0) {
                        if (brNo.length != 17) {
                            $("#btnSubmit").attr("disabled", true);
                            $("#errMsgBR").html("Not a valid 17-digit Birth Registration Number").show();
                            $('#emp_national_id').css('border-color','red');
                        } 
                        else if ( brNo.length == 17 ){
                            $('#btnSubmit').removeAttr("disabled");
                            $('#btnSubmit').removeClass("disabled");
                            $("#errMsgBR").html('');
                            $('#emp_national_id').css('border-color','#e4eaec');
                        }   
                    }
                    else {
                        $("#errMsgBR").html('');
                        $('#emp_national_id').css("border-color","#e4eaec");
                        $('#btnSubmit').removeAttr('disabled');
                    }

                    // // // Duplicate Check
                    var query = $(this).val();
                    var forWhich = $(this).attr("name");
                    var tableName = btoa('hr_employees');

                    var columnName = $(this).attr("name")+'&&is_delete';
                    var columnValue = $(this).val()+'&&0';
                    var url_text = "{{url('/ajaxCheckDuplicate')}}";
                    var fieldID = $(this).attr("id");
                    var updateID = '{{ $EmployeeData->id }}';

                    fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                    'errMsgBR', 'birth registration no.', updateID);

                    if($('#'+ fieldID).val() !== ''){
                        $('#btnSubmit').removeAttr("disabled");
                        $('#btnSubmit').removeClass("disabled");
                        $('#emp_national_id').css('border-color','#e4eaec');
                    }

                    // $.ajax({
                    //     type: "get",
                    //     url: "{{route('ajaxCheckDuplicate')}}",
                    //     data: {query: query, tableName: tableName, forWhich: forWhich},
                    //     dataType: "json",
                    //     success: function (data) {
                    //         if (data.exists) {
                    //             if (data.rowID != empId) {
                    //                 $('#btnSubmit').attr('disabled', 'disabled');
                    //                 $('#errMsgBR').html('Please enter unique Birth Registration No');
                    //                 $('#emp_national_id').css('border-color','red');
                    //             }
                    //             else {
                    //                 $('#btnSubmit').removeAttr("disabled");
                    //                 $('#btnSubmit').removeClass("disabled");
                    //                 $('#emp_national_id').css('border-color','#e4eaec');
                    //             }
                    //         }
                    //     },
                    // });
                });
            }
    }

    $(".identification").click(function() {
        $("#errMsg,#errMsgNID,#errMsgSC,#errMsgPP,#errMsgDL,#errMsgBR").html('');
        $('#emp_national_id').val('');
        var selIdTxt = $(this).val();

        if ("{{ $EmployeeData->emp_id_type }}" == selIdTxt) {
            $('.identificationInput').val("{{ $EmployeeData->emp_national_id }}");
        }

        $( '.identificationInput' ).each(function() {

            if(selIdTxt === 'nid'){
                $(this).attr("placeholder", "Enter NID No");
                $("#errMsg,#errMsgSC,#errMsgPP,#errMsgDL,#errMsgBR").prop('id', 'errMsgNID');
                $(".identificationInput").on("input", function(event){
                    var nidNo = $(this).val();
                    if (nidNo.length != 0) {
                        if (nidNo.length != 13) {
                            if (nidNo.length != 17) {
                                $("#errMsgNID").html("Invalid NID! NID must be of 13 or 17 Digits").show();
                                $('#btnSubmit').attr('disabled', 'disabled');
                                $('#emp_national_id').css('border-color','red');
                            }else if ( nidNo.length == 17 ){
                                $('#btnSubmit').removeAttr("disabled");
                                $('#btnSubmit').removeClass("disabled");
                                $("#errMsgNID").html('');

                            }
                        } else if ( nidNo.length == 13 ){
                            $('#btnSubmit').removeAttr("disabled");
                            $('#btnSubmit').removeClass("disabled");
                            $("#errMsgNID").html('');
                            $('#emp_national_id').css('border-color','#e4eaec');
                        }
                    }
                    else {
                        $("#errMsgNID").html('');
                        $('#emp_national_id').css("border-color","#e4eaec");
                        $('#btnSubmit').removeAttr('disabled');
                    }

                    // // // Duplicate Check
                    var query = $(this).val();
                    var forWhich = $(this).attr("name");
                    var tableName = btoa('hr_employees');

                    var columnName = $(this).attr("name")+'&&is_delete';
                    var columnValue = $(this).val()+'&&0';
                    var url_text = "{{url('/ajaxCheckDuplicate')}}";
                    var fieldID = $(this).attr("id");
                    var updateID = '{{ $EmployeeData->id }}';

                    fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                    'errMsgBR', 'birth registration no.', updateID);

                    if($('#'+ fieldID).val() !== ''){
                        $('#btnSubmit').removeAttr("disabled");
                        $('#btnSubmit').removeClass("disabled");
                        $('#emp_national_id').css('border-color','#e4eaec');
                    }

                    // $.ajax({
                    //     type: "get",
                    //     url: "{{route('ajaxCheckDuplicate')}}",
                    //     data: {query: query, tableName: tableName, forWhich: forWhich},
                    //     dataType: "json",
                    //     success: function (data) {
                    //         if (data.exists) {
                    //             if (data.rowID != empId) {
                    //                 $('#btnSubmit').attr('disabled', 'disabled');
                    //                 $('#errMsgBR').html('Please enter unique Birth Registration No');
                    //                 $('#emp_national_id').css('border-color','red');
                    //             }
                    //             else {
                    //                 $('#btnSubmit').removeAttr("disabled");
                    //                 $('#btnSubmit').removeClass("disabled");
                    //                 $('#emp_national_id').css('border-color','#e4eaec');
                    //             }
                    //         }
                    //     },
                    // });
                });
            }
            else if(selIdTxt === 'smartCard'){
                $(this).attr("placeholder", "Enter Smart Card No");
                $("#errMsg,#errMsgNID,#errMsgPP,#errMsgDL,#errMsgBR").prop('id', 'errMsgSC');
                $(".identificationInput").on("input", function(event){
                    var cardNo = $(this).val();
                    if (cardNo.length > 0) {
                        if(cardNo.length != 10) {
                            $("#errMsgSC").html("Not a valid 10-digit Smart Card Number").show();
                            $('#btnSubmit').attr('disabled', 'disabled');
                            $('#emp_national_id').css("border-color","red");
                        }else if ( cardNo.length == 10 ){
                                $('#btnSubmit').removeAttr("disabled");
                                $('#btnSubmit').removeClass("disabled");
                                $("#errMsgSC").html('');
                                $('#emp_national_id').css('border-color','#e4eaec');
                            }
                    }
                    else {
                        $("#errMsgSC").html('');
                        $('#emp_national_id').css("border-color","#e4eaec");
                        $('#btnSubmit').removeAttr('disabled');
                    }

                    // // // Duplicate Check
                    var query = $(this).val();
                    var forWhich = $(this).attr("name");
                    var tableName = btoa('hr_employees');

                    var columnName = $(this).attr("name")+'&&is_delete';
                    var columnValue = $(this).val()+'&&0';
                    var url_text = "{{url('/ajaxCheckDuplicate')}}";
                    var fieldID = $(this).attr("id");
                    var updateID = '{{ $EmployeeData->id }}';

                    fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                    'errMsgSC', 'smart card no.', updateID);

                    if($('#'+ fieldID).val() !== ''){
                        $('#btnSubmit').removeAttr("disabled");
                        $('#btnSubmit').removeClass("disabled");
                        $('#emp_national_id').css('border-color','#e4eaec');
                    }

                    // $.ajax({
                    //     type: "get",
                    //     url: "{{route('ajaxCheckDuplicate')}}",
                    //     data: {query: query, tableName: tableName, forWhich: forWhich},
                    //     dataType: "json",
                    //     success: function (data) {
                    //         if (data.exists) {
                    //             if (data.rowID != empId) {
                    //                 $('#btnSubmit').attr('disabled', 'disabled');
                    //                 $('#errMsgSC').html('Please enter unique Smart Card ID');
                    //                 $('#emp_national_id').css("border-color","red");
                    //             }
                    //             else {
                    //                 $('#btnSubmit').removeAttr("disabled");
                    //                 $('#btnSubmit').removeClass("disabled");
                    //                 $('#emp_national_id').css('border-color','#e4eaec');
                    //             }
                    //         }
                    //     },
                    // });
                });
            }
            else if(selIdTxt === 'passport'){
                $(this).attr("placeholder", "Enter passport No");
                $("#errMsg,#errMsgNID,#errMsgSC,#errMsgDL,#errMsgBR").prop('id', 'errMsgPP');
                $(".identificationInput").on("input", function(event){
                    var passportNo = $(this).val();
                    if(passportNo.length > 0) {
                        if (passportNo.length != 9) {
                            $("#btnSubmit").attr("disabled", true);
                            $("#errMsgPP").html("Not a valid 9-digit Passport Number").show();
                            $('#emp_national_id').css('border-color','red');
                        } 
                        else if ( passportNo.length == 9 ){
                            $('#btnSubmit').removeAttr("disabled");
                            $('#btnSubmit').removeClass("disabled");
                            $("#errMsgPP").html('');
                            $('#emp_national_id').css('border-color','#e4eaec');
                        }   
                    }
                    else {
                        $("#errMsgPP").html('');
                        $('#emp_national_id').css("border-color","#e4eaec");
                        $('#btnSubmit').removeAttr('disabled');
                    }

                    // // // Duplicate Check
                    var query = $(this).val();
                    var forWhich = $(this).attr("name");
                    var tableName = btoa('hr_employees');

                    var columnName = $(this).attr("name")+'&&is_delete';
                    var columnValue = $(this).val()+'&&0';
                    var url_text = "{{url('/ajaxCheckDuplicate')}}";
                    var fieldID = $(this).attr("id");
                    var updateID = '{{ $EmployeeData->id }}';

                    fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                    'errMsgPP', 'passport no.', updateID);

                    if($('#'+ fieldID).val() !== ''){
                        $('#btnSubmit').removeAttr("disabled");
                        $('#btnSubmit').removeClass("disabled");
                        $('#emp_national_id').css('border-color','#e4eaec');
                    }
                    
                    // $.ajax({
                    //     type: "get",
                    //     url: "{{route('ajaxCheckDuplicate')}}",
                    //     data: {query: query, tableName: tableName, forWhich: forWhich},
                    //     dataType: "json",
                    //     success: function (data) {
                    //         if (data.exists) {
                    //             if (data.rowID != empId) {
                    //                 $('#btnSubmit').attr('disabled', 'disabled');
                    //                 $('#errMsgPP').html('Please enter unique Passport No');
                    //                 $('#emp_national_id').css("border-color","red");
                    //             }
                    //             else {
                    //                 $('#btnSubmit').removeAttr("disabled");
                    //                 $('#btnSubmit').removeClass("disabled");
                    //                 $('#emp_national_id').css('border-color','#e4eaec');
                    //             }
                    //         }
                    //     },
                    // });
                });

            }
            else if(selIdTxt === 'drivingLicense'){
                $(this).attr("placeholder", "Enter Driving License No");
                $("#errMsg,#errMsgNID,#errMsgSC,#errMsgPP,#errMsgBR").prop('id', 'errMsgDL');
                $(".identificationInput").on("input", function(event){
                    var licenceNo = $(this).val();
                    if(licenceNo.length > 0) {
                        if (licenceNo.length != 15) {
                            $("#btnSubmit").attr("disabled", true);
                            $("#errMsgDL").html("Not a valid 15-digit Driving Licence Number").show();
                            $('#emp_national_id').css('border-color','red');
                        } 
                        else if ( licenceNo.length == 15 ){
                            $('#btnSubmit').removeAttr("disabled");
                            $('#btnSubmit').removeClass("disabled");
                            $("#errMsgDL").html('');
                            $('#emp_national_id').css('border-color','#e4eaec');
                        }   
                    } 
                    else {
                        $("#errMsgDL").html('');
                        $('#emp_national_id').css("border-color","#e4eaec");
                        $('#btnSubmit').removeAttr('disabled');
                    }

                    // // // Duplicate Check
                    var query = $(this).val();
                    var forWhich = $(this).attr("name");
                    var tableName = btoa('hr_employees');

                    var columnName = $(this).attr("name")+'&&is_delete';
                    var columnValue = $(this).val()+'&&0';
                    var url_text = "{{url('/ajaxCheckDuplicate')}}";
                    var fieldID = $(this).attr("id");
                    var updateID = '{{ $EmployeeData->id }}';

                    fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                    'errMsgDL', 'driving license no.', updateID);

                    if($('#'+ fieldID).val() !== ''){
                        $('#btnSubmit').removeAttr("disabled");
                        $('#btnSubmit').removeClass("disabled");
                        $('#emp_national_id').css('border-color','#e4eaec');
                    }

                    // $.ajax({
                    //     type: "get",
                    //     url: "{{route('ajaxCheckDuplicate')}}",
                    //     data: {query: query, tableName: tableName, forWhich: forWhich},
                    //     dataType: "json",
                    //     success: function (data) {
                    //         if (data.exists) {
                    //             if (data.rowID != empId) {
                    //                 $('#btnSubmit').attr('disabled', 'disabled');
                    //                 $('#errMsgDL').html('Please enter unique Driving Licence No');
                    //                 $('#emp_national_id').css("border-color","red");
                    //             }
                    //             else {
                    //                 $('#btnSubmit').removeAttr("disabled");
                    //                 $('#btnSubmit').removeClass("disabled");
                    //                 $('#emp_national_id').css('border-color','#e4eaec');
                    //             }
                    //         }
                    //     },
                    // });
                });
            }
            else if(selIdTxt === 'birthCertificate'){
                $(this).attr("placeholder", "Enter Birth Registration No");
                $("#errMsg,#errMsgNID,#errMsgSC,#errMsgPP,#errMsgDL").prop('id', 'errMsgBR');
                $(".identificationInput").on("input", function(event){
                    var brNo = $(this).val();
                    if(brNo.length > 0) {
                        if (brNo.length != 17) {
                            $("#btnSubmit").attr("disabled", true);
                            $("#errMsgBR").html("Not a valid 17-digit Birth Registration Number").show();
                            $('#emp_national_id').css('border-color','red');
                        } 
                        else if ( brNo.length == 17 ){
                            $('#btnSubmit').removeAttr("disabled");
                            $('#btnSubmit').removeClass("disabled");
                            $("#errMsgBR").html('');
                            $('#emp_national_id').css('border-color','#e4eaec');
                        }   
                    }
                    else {
                        $("#errMsgBR").html('');
                        $('#emp_national_id').css("border-color","#e4eaec");
                        $('#btnSubmit').removeAttr('disabled');
                    }

                    // // // Duplicate Check
                    var query = $(this).val();
                    var forWhich = $(this).attr("name");
                    var tableName = btoa('hr_employees');

                    var columnName = $(this).attr("name")+'&&is_delete';
                    var columnValue = $(this).val()+'&&0';
                    var url_text = "{{url('/ajaxCheckDuplicate')}}";
                    var fieldID = $(this).attr("id");
                    var updateID = '{{ $EmployeeData->id }}';

                    fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                    'errMsgBR', 'birth registration no.', updateID);

                    if($('#'+ fieldID).val() !== ''){
                        $('#btnSubmit').removeAttr("disabled");
                        $('#btnSubmit').removeClass("disabled");
                        $('#emp_national_id').css('border-color','#e4eaec');
                    }

                    // $.ajax({
                    //     type: "get",
                    //     url: "{{route('ajaxCheckDuplicate')}}",
                    //     data: {query: query, tableName: tableName, forWhich: forWhich},
                    //     dataType: "json",
                    //     success: function (data) {
                    //         if (data.exists) {
                    //             if (data.rowID != empId) {
                    //                 $('#btnSubmit').attr('disabled', 'disabled');
                    //                 $('#errMsgBR').html('Please enter unique Birth Registration No');
                    //                 $('#emp_national_id').css("border-color","red");
                    //             }
                    //             else {
                    //                 $('#btnSubmit').removeAttr("disabled");
                    //                 $('#btnSubmit').removeClass("disabled");
                    //                 $('#emp_national_id').css('border-color','#e4eaec');
                    //             }
                    //         }
                    //     },
                    // });
                });
            }
            
        });
    });
</script>

@endsection
