@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\HtmlService as HTML;
?>

<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator"
  novalidate="true" autocomplete="off">
    @csrf
    <div class="row">
        <div class="col-lg-8 offset-3 mb-2">
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
        <div class="col-lg-6">
            <?php
            $CustomerID = (empty(Session::get('CustomerID'))) ? null : Session::get('CustomerID');
            // echo $CustomerID;
            ?>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar" for="selCustID">Customer Name</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="customer_id" id="customer_id"
                            required data-error="Please select Customer" style="width: 100%">

                            <option value="">Select Customer</option>



                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Gurantor Name</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round" id="gr_name" name="gr_name"
                            placeholder="Enter Gurantor Name" required
                            data-error="Please enter Gurantor name.">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Father's Name</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round" id="gr_father_name"
                            name="gr_father_name" placeholder="Enter Father's Name">
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Mother's Name</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round" id="gr_mother_name"
                            name="gr_mother_name" placeholder="Enter Mother's Name">
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
                            name="gr_dob" placeholder="DD-MM-YYYY" autocomplete="off">
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Marital Status</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <div class="radio-custom radio-primary">
                            <input type="radio" name="gr_marital_status" value="Single"
                                class="MarritalStatus" checked="">
                            <label for="g1">Single &nbsp &nbsp </label>
                        </div>
                        <div class="radio-custom radio-primary">
                            <input type="radio" name="gr_marital_status" value="Married"
                                class="MarritalStatus">
                            <label for="g2">Married &nbsp &nbsp </label>
                        </div>
                        <div class="radio-custom radio-primary">
                            <input type="radio" name="gr_marital_status" value="Divorced"
                                class="MarritalStatus">
                            <label for="g3">Divorced &nbsp &nbsp</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center" id="spouse" style="display: none;">
                <label class="col-lg-4 input-title">Spouse Name</label>
                <div class="col-lg-7">
                    <div class="input-group ">
                        <input type="text" class="form-control round" id="txtGrSpouse"
                            name="gr_spouse_name" placeholder="Enter Spouse Name">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Email</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="email" class="form-control round" id="gr_email" name="gr_email"
                            placeholder="Enter Email"
                            onblur="fnCheckDuplicate(
                                '{{base64_encode('pos_guarantors')}}',
                                this.name+'&&is_delete',
                                this.value+'&&0',
                                '{{url('/ajaxCheckDuplicate')}}',
                                this.id,
                                'txtCodeError',
                                'Email');" >
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Mobile</label>
                <div class="col-lg-7">
                    <div class="input-group ">
                        <input type="text" class="form-control round textNumber" id="gr_mobile"
                            name="gr_mobile" placeholder="Mobile Number (01*********)"
                            pattern="[01][0-9]{10}" required
                            data-error="Please enter mobile number (01*********)"
                            minlength="11" maxlength="11"
                            onblur="fnCheckDuplicate(
                                '{{base64_encode('pos_guarantors')}}',
                                this.name+'&&is_delete',
                                this.value+'&&0',
                                '{{url('/ajaxCheckDuplicate')}}',
                                this.id,
                                'txtCodeError2',
                                'mobile number');">
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
                            <input type="radio" class="identification" id="n1"
                            name="gr_id_type" value="nid" checked>
                            <label for="n1">NID &nbsp &nbsp</label>
                        </div>
                        <div class="radio-custom radio-primary">
                            <input type="radio" class="identification" id="n2"
                            name="gr_id_type" value="smartCard">
                            <label for="n2">Smart Card &nbsp &nbsp</label>
                        </div>
                        <div class="radio-custom radio-primary">
                            <input type="radio" class="identification" id="n3"
                            name="gr_id_type" value="passport">
                            <label for="n3">Passport &nbsp &nbsp</label>
                        </div>
                        <div class="radio-custom radio-primary">
                            <input type="radio" class="identification" id="n4"
                            name="gr_id_type" value="drivingLicense">
                            <label for="n4">Driving License &nbsp &nbsp</label>
                        </div>
                        <div class="radio-custom radio-primary">
                            <input type="radio" class="identification" id="n5"
                            name="gr_id_type" value="birthCertificate">
                            <label for="n5">Birth Certificate &nbsp &nbsp</label>
                        </div>
                        <div class="input-group mt-4">
                            <input type="text" class="form-control round identificationInput"
                            name="gr_nid" id="gr_nid" placeholder="Enter NID No" required data-error="Fill Up this Form">
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
                            name="gr_yearly_income" placeholder="Enter Yearly Income">
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Relation</label>
                <div class="col-lg-7">
                    <div class="input-group ">
                        <input type="text" class="form-control round" id="gr_relation_with"
                            name="gr_relation_with" placeholder="Enter Relation">
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Description</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <textarea class="form-control round" id="gr_desc" name="gr_desc" rows="1"
                            placeholder="Enter Description"></textarea>
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
                                      );">
                          <option value="" selected="selected">Select Division</option>
                          @foreach ($DivisionData as $Row)
                          <option value="{{$Row->id}}">{{$Row->division_name}}</option>
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
                           name="gr_pre_upazila_id"
                          id="gr_pre_upazila_id"  onchange="fnAjaxSelectBox(
                                          'gr_pre_union_id',
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
                           name="gr_pre_union_id"
                          id="gr_pre_union_id" onchange="fnAjaxSelectBox(
                                          'gr_pre_village_id',
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
                           name="gr_pre_village_id"
                          id="gr_pre_village_id">
                          <option value="" selected="selected">Select Village</option>
                      </select>
                    </div>
                </div>

                <div class="col-lg-6 form-group">
                    <div class="input-group">
                      <textarea class="form-control" id="gr_pre_remarks" name="gr_pre_remarks"
                          rows="2" placeholder="Enter Remarks"></textarea>
                    </div>
                </div>
            </div>
            <!-- Present Address End -->

            <!-- Permanent Address Start -->
            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title">Same As Present Address</label>
                <div class="col-lg-7 form-group">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="input-group checkbox-custom checkbox-primary">
                                    <input type="checkbox" id="checkbox_addr">
                                    <label></label>
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
                          <option value="{{$Row->id}}">{{$Row->division_name}}</option>
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
                          rows="2" placeholder="Enter Remarks"></textarea>
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
                    <button type="submit" class="btn btn-primary btn-round" id="btnSubmit">Save</button>
                    <!-- <a href="#"><button type="button" class="btn btn-warning btn-round">Next</button></a> -->
                </div>
            </div>
        </div>
    </div>
</form>

<script type="text/javascript">
// -----If Married Radio Button is clicked,
//        show Spouse Name Div  -------//
$(document).ready(function() {

    fnAjaxSelectBox(
                        'customer_id',
                        $('#branch_id').val(),
                        '{{base64_encode('pos_customers')}}',
                        '{{base64_encode('branch_id')}}',
                        '{{base64_encode('customer_no,customer_name')}}',
                        '{{url('/ajaxSelectBox')}}'

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

    if ($('.identification').is(':checked')){
      var idTxt = $('.identification:checked').val();

      // Natinal ID Validation
      if(idTxt === 'nid'){
          $(this).attr("placeholder", "Enter NID No");
          $("#errMsg,#errMsgSC,#errMsgPP,#errMsgDL,#errMsgBR").prop('id', 'errMsgNID');
          $(".identificationInput").on("input", function(event){
              $(this).addClass('textNumber');
              var nidNo = $(this).val();
              if (nidNo.length > 0) {
                  if (nidNo.length != 13) {
                      if (nidNo.length != 17) {
                          $("#errMsgNID").html("Invalid NID! NID must be of 13 or 17 Digits").show();
                          $('#gr_nid').css("border-color","red");
                          $('#btnSubmit').attr("disabled","disabled");
                      }else if ( nidNo.length == 17 ){
                          $("#errMsgNID").html('');
                          $('#gr_nid').css('border-color','#e4eaec');

                        // // // Duplicate Check
                        var query = $(this).val();
                        var forWhich = $(this).attr("name");
                        var tableName = btoa('pos_guarantors');

                        var columnName = $(this).attr("name")+'&&is_delete';
                        var columnValue = $(this).val()+'&&0';
                        var url_text = "{{url('/ajaxCheckDuplicate')}}";
                        var fieldID = $(this).attr("id");
                        var updateID = null;

                        fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                        'errMsgNID', 'NID', updateID);

                        if($('#'+ fieldID).val() !== ''){
                            var numberOfEmptyFields = $('form').find('input[required],select[required]')
                                                    .filter(function () {
                                                        return $(this).val() === "";
                                                    }).length;
                            if (numberOfEmptyFields == 0) {
                                $('#btnSubmit').removeClass("disabled");
                            }
                            $('#btnSubmit').removeAttr("disabled");
                            $(this).css('border-color','#e4eaec');
                        }



                        //   $.ajax({
                        //       type: "get",
                        //       url: "{{route('ajaxCheckDuplicate')}}",
                        //       data: {query: query, tableName: tableName, forWhich: forWhich},
                        //       dataType: "json",
                        //       success: function (data) {
                        //           if (data.exists) {
                        //               $('#btnSubmit').attr("disabled","disabled");
                        //               $('#errMsgNID').html('Please enter unique NID');
                        //               $('#gr_nid').css("border-color","red");
                        //           }
                        //           else {
                        //               var numberOfEmptyFields = $('form').find('input[required],select[required]')
                        //                                       .filter(function () {
                        //                                           return $(this).val() === "";
                        //                                       }).length;
                        //               if (numberOfEmptyFields == 0) {
                        //                   $('#btnSubmit').removeClass("disabled");
                        //               }
                        //               $('#btnSubmit').removeAttr("disabled");
                        //               $(this).css('border-color','#e4eaec');
                        //           }
                        //       },
                        //   });
                      }
                  } else if ( nidNo.length == 13 ){
                      $("#errMsgNID").html('');
                      $('#gr_nid').css('border-color','#e4eaec');

                      // // // Duplicate Check
                      var query = $(this).val();
                        var forWhich = $(this).attr("name");
                        var tableName = btoa('pos_guarantors');

                        var columnName = $(this).attr("name")+'&&is_delete';
                        var columnValue = $(this).val()+'&&0';
                        var url_text = "{{url('/ajaxCheckDuplicate')}}";
                        var fieldID = $(this).attr("id");
                        var updateID = null;

                        fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                        'errMsgNID', 'NID', updateID);

                        if($('#'+ fieldID).val() !== ''){
                            var numberOfEmptyFields = $('form').find('input[required],select[required]')
                                                    .filter(function () {
                                                        return $(this).val() === "";
                                                    }).length;
                            if (numberOfEmptyFields == 0) {
                                $('#btnSubmit').removeClass("disabled");
                            }
                            $('#btnSubmit').removeAttr("disabled");
                            $(this).css('border-color','#e4eaec');
                        }

                    //   $.ajax({
                    //       type: "get",
                    //       url: "{{route('ajaxCheckDuplicate')}}",
                    //       data: {query: query, tableName: tableName, forWhich: forWhich},
                    //       dataType: "json",
                    //       success: function (data) {
                    //           if (data.exists) {
                    //               $('#errMsgNID').html('Please enter unique NID');
                    //               $('#gr_nid').css("border-color","red");
                    //           }
                    //           else {
                    //               var numberOfEmptyFields = $('form').find('input[required],select[required]')
                    //                                           .filter(function () {
                    //                                               return $(this).val() === "";
                    //                                           }).length;
                    //               if (numberOfEmptyFields == 0) {
                    //                   $('#btnSubmit').removeClass("disabled");
                    //               }
                    //               $('#btnSubmit').removeAttr("disabled");
                    //               $(this).css('border-color','#e4eaec');
                    //           }
                    //       },
                    //   });
                  }
              }
          });
      }
  }

  $(".identification").click(function() {
      $("#errMsg,#errMsgNID,#errMsgSC,#errMsgPP,#errMsgDL,#errMsgBR").html('');
      $('#gr_nid').val('');
      var selIdTxt = $(this).val();

      $( '.identificationInput' ).each(function() {

          if(selIdTxt === 'nid'){
              $(this).attr("placeholder", "Enter NID No");
              $("#errMsg,#errMsgSC,#errMsgPP,#errMsgDL,#errMsgBR").prop('id', 'errMsgNID');
              $(".identificationInput").on("input", function(event){
                  $(this).addClass('textNumber');
                  var nidNo = $(this).val();
                  if (nidNo.length > 0) {
                      if (nidNo.length != 13) {
                          if (nidNo.length != 17) {
                              $("#errMsgNID").html("Invalid NID! NID must be of 13 or 17 Digits").show();
                              $('#gr_nid').css("border-color","red");
                              $('#btnSubmit').attr("disabled","disabled");
                          }else if ( nidNo.length == 17 ){
                              $("#errMsgNID").html('');
                              $('#gr_nid').css('border-color','#e4eaec');

                            // // // Duplicate Check
                            var query = $(this).val();
                            var forWhich = $(this).attr("name");
                            var tableName = btoa('pos_guarantors');

                            var columnName = $(this).attr("name")+'&&is_delete';
                            var columnValue = $(this).val()+'&&0';
                            var url_text = "{{url('/ajaxCheckDuplicate')}}";
                            var fieldID = $(this).attr("id");
                            var updateID = null;

                            fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                            'errMsgNID', 'NID', updateID);

                            if($('#'+ fieldID).val() !== ''){
                                var numberOfEmptyFields = $('form').find('input[required],select[required]')
                                                        .filter(function () {
                                                            return $(this).val() === "";
                                                        }).length;
                                if (numberOfEmptyFields == 0) {
                                    $('#btnSubmit').removeClass("disabled");
                                }
                                $('#btnSubmit').removeAttr("disabled");
                                $(this).css('border-color','#e4eaec');
                            }


                            //   $.ajax({
                            //       type: "get",
                            //       url: "{{route('ajaxCheckDuplicate')}}",
                            //       data: {query: query, tableName: tableName, forWhich: forWhich},
                            //       dataType: "json",
                            //       success: function (data) {
                            //           if (data.exists) {
                            //               $('#btnSubmit').attr("disabled","disabled");
                            //               $('#errMsgNID').html('Please enter unique NID');
                            //               $('#gr_nid').css("border-color","red");
                            //           }
                            //           else {
                            //               var numberOfEmptyFields = $('form').find('input[required],select[required]')
                            //                                           .filter(function () {
                            //                                               return $(this).val() === "";
                            //                                           }).length;
                            //               if (numberOfEmptyFields == 0) {
                            //                   $('#btnSubmit').removeClass("disabled");
                            //               }
                            //               $('#btnSubmit').removeAttr("disabled");
                            //               $(this).css('border-color','#e4eaec');
                            //           }
                            //       },
                            //   });
                          }
                      } else if ( nidNo.length == 13 ){
                          $("#errMsgNID").html('');
                          $('#gr_nid').css('border-color','#e4eaec');

                          // // // Duplicate Check
                            var query = $(this).val();
                            var forWhich = $(this).attr("name");
                            var tableName = btoa('pos_guarantors');

                            var columnName = $(this).attr("name")+'&&is_delete';
                            var columnValue = $(this).val()+'&&0';
                            var url_text = "{{url('/ajaxCheckDuplicate')}}";
                            var fieldID = $(this).attr("id");
                            var updateID = null;

                            fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                            'errMsgNID', 'NID', updateID);

                            if($('#'+ fieldID).val() !== ''){
                                var numberOfEmptyFields = $('form').find('input[required],select[required]')
                                                        .filter(function () {
                                                            return $(this).val() === "";
                                                        }).length;
                                if (numberOfEmptyFields == 0) {
                                    $('#btnSubmit').removeClass("disabled");
                                }
                                $('#btnSubmit').removeAttr("disabled");
                                $(this).css('border-color','#e4eaec');
                            }

                        //   $.ajax({
                        //       type: "get",
                        //       url: "{{route('ajaxCheckDuplicate')}}",
                        //       data: {query: query, tableName: tableName, forWhich: forWhich},
                        //       dataType: "json",
                        //       success: function (data) {
                        //           if (data.exists) {
                        //               $('#errMsgNID').html('Please enter unique NID');
                        //               $('#gr_nid').css("border-color","red");
                        //           }
                        //           else {
                        //               var numberOfEmptyFields = $('form').find('input[required],select[required]')
                        //                                           .filter(function () {
                        //                                               return $(this).val() === "";
                        //                                           }).length;
                        //               if (numberOfEmptyFields == 0) {
                        //                   $('#btnSubmit').removeAttr("disabled");
                        //                   $('#btnSubmit').removeClass("disabled");
                        //               }

                        //               $(this).css('border-color','#e4eaec');
                        //           }
                        //       },
                        //   });
                      }
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
                          $("#errMsgSC").html("Not a valid 10-digit Smart Card Number").show();
                          $(this).css('border-color','red');
                          $('#btnSubmit').attr("disabled","disabled");
                      }
                      else if ( cardNo.length == 10 ){
                          $("#errMsgSC").html('');

                          // // // Duplicate Check
                        var query = $(this).val();
                        var forWhich = $(this).attr("name");
                        var tableName = btoa('pos_guarantors');

                        var columnName = $(this).attr("name")+'&&is_delete';
                        var columnValue = $(this).val()+'&&0';
                        var url_text = "{{url('/ajaxCheckDuplicate')}}";
                        var fieldID = $(this).attr("id");
                        var updateID = null;

                        fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                        'errMsgSC', 'smart card no.', updateID);

                        if($('#'+ fieldID).val() !== ''){
                            var numberOfEmptyFields = $('form').find('input[required],select[required]')
                                                    .filter(function () {
                                                        return $(this).val() === "";
                                                    }).length;
                            if (numberOfEmptyFields == 0) {
                                $('#btnSubmit').removeClass("disabled");
                            }
                            $('#btnSubmit').removeAttr("disabled");
                            $(this).css('border-color','#e4eaec');
                        }

                        //   $.ajax({
                        //       type: "get",
                        //       url: "{{route('ajaxCheckDuplicate')}}",
                        //       data: {query: query, tableName: tableName, forWhich: forWhich},
                        //       dataType: "json",
                        //       success: function (data) {
                        //           if (data.exists) {
                        //               $('#btnSubmit').attr("disabled","disabled");
                        //               $('#errMsgSC').html('Please enter unique Smart Card ID');
                        //               $('#gr_nid').css("border-color","red");
                        //           }
                        //           else {
                        //               var numberOfEmptyFields = $('form').find('input[required],select[required]')
                        //                                       .filter(function () {
                        //                                           return $(this).val() === "";
                        //                                       }).length;
                        //               if (numberOfEmptyFields == 0) {
                        //                   $('#btnSubmit').removeClass("disabled");
                        //               }
                        //               $('#btnSubmit').removeAttr("disabled");
                        //               $('#gr_nid').css('border-color','#e4eaec');
                        //           }
                        //       },
                        //   });
                      }
                  }
              });
          }
          else if(selIdTxt === 'passport'){
              $(this).attr("placeholder", "Enter passport No");
              $("#errMsg,#errMsgNID,#errMsgSC,#errMsgDL,#errMsgBR").prop('id', 'errMsgPP');
              $(".identificationInput").on("input", function(event){
                  $(this).addClass('textNumber');
                  var passportNo = $(this).val();
                  if(passportNo.length > 0) {
                      if (passportNo.length != 9) {
                          $("#errMsgPP").html("Not a valid 9-digit Passport Number").show();
                          $(this).css('border-color','red');
                          $('#btnSubmit').attr("disabled","disabled");
                      }
                      else if ( passportNo.length == 9 ){
                          $("#errMsgPP").html('');
                          $('#gr_nid').css('border-color','#e4eaec');

                        // // // Duplicate Check
                        var query = $(this).val();
                        var forWhich = $(this).attr("name");
                        var tableName = btoa('pos_guarantors');

                        var columnName = $(this).attr("name")+'&&is_delete';
                        var columnValue = $(this).val()+'&&0';
                        var url_text = "{{url('/ajaxCheckDuplicate')}}";
                        var fieldID = $(this).attr("id");
                        var updateID = null;

                        fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                        'errMsgPP', 'passport no.', updateID);

                        if($('#'+ fieldID).val() !== ''){
                            var numberOfEmptyFields = $('form').find('input[required],select[required]')
                                                    .filter(function () {
                                                        return $(this).val() === "";
                                                    }).length;
                            if (numberOfEmptyFields == 0) {
                                $('#btnSubmit').removeClass("disabled");
                            }
                            $('#btnSubmit').removeAttr("disabled");
                            $(this).css('border-color','#e4eaec');
                        }

                        //   $.ajax({
                        //       type: "get",
                        //       url: "{{route('ajaxCheckDuplicate')}}",
                        //       data: {query: query, tableName: tableName, forWhich: forWhich},
                        //       dataType: "json",
                        //       success: function (data) {
                        //           if (data.exists) {
                        //               $('#btnSubmit').attr("disabled","disabled");
                        //               $('#errMsgPP').html('Please enter unique Passport No');
                        //               $('#gr_nid').css("border-color","red");
                        //           }
                        //           else {
                        //               var numberOfEmptyFields = $('form').find('input[required],select[required]')
                        //                                           .filter(function () {
                        //                                               return $(this).val() === "";
                        //                                           }).length;
                        //               if (numberOfEmptyFields == 0) {
                        //                   $('#btnSubmit').removeClass("disabled");
                        //               }
                        //               $('#btnSubmit').removeAttr("disabled");
                        //               $(this).css('border-color','#e4eaec');
                        //           }
                        //       },
                        //   });
                      }
                  }
              });
          }
          else if(selIdTxt === 'drivingLicense'){
              $(this).attr("placeholder", "Enter Driving License No");
              $("#errMsg,#errMsgNID,#errMsgSC,#errMsgPP,#errMsgBR").prop('id', 'errMsgDL');
              $(".identificationInput").on("input", function(event){
                  $(this).removeClass('textNumber');
                  var licenceNo = $(this).val();
                  if(licenceNo.length > 0) {
                      if (licenceNo.length != 15) {
                          $("#errMsgDL").html("Not a valid 15-digit Driving Licence Number").show();
                          $(this).css('border-color','red');
                          $('#btnSubmit').attr("disabled","disabled");
                      }
                      else if ( licenceNo.length == 15 ){
                          $("#errMsgDL").html('');
                          $('#gr_nid').css('border-color','#e4eaec');

                          // // // Duplicate Check
                        var query = $(this).val();
                        var forWhich = $(this).attr("name");
                        var tableName = btoa('pos_guarantors');

                        var columnName = $(this).attr("name")+'&&is_delete';
                        var columnValue = $(this).val()+'&&0';
                        var url_text = "{{url('/ajaxCheckDuplicate')}}";
                        var fieldID = $(this).attr("id");
                        var updateID = null;

                        fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                        'errMsgDL', 'driving license no.', updateID);

                        if($('#'+ fieldID).val() !== ''){
                            var numberOfEmptyFields = $('form').find('input[required],select[required]')
                                                    .filter(function () {
                                                        return $(this).val() === "";
                                                    }).length;
                            if (numberOfEmptyFields == 0) {
                                $('#btnSubmit').removeClass("disabled");
                            }
                            $('#btnSubmit').removeAttr("disabled");
                            $(this).css('border-color','#e4eaec');
                        }

                        //   $.ajax({
                        //       type: "get",
                        //       url: "{{route('ajaxCheckDuplicate')}}",
                        //       data: {query: query, tableName: tableName, forWhich: forWhich},
                        //       dataType: "json",
                        //       success: function (data) {
                        //           if (data.exists) {
                        //               $('#btnSubmit').attr("disabled","disabled");
                        //               $('#errMsgDL').html('Please enter unique Driving Licence No');
                        //               $('#gr_nid').css("border-color","red");
                        //           }
                        //           else {
                        //               var numberOfEmptyFields = $('form').find('input[required],select[required]')
                        //                                           .filter(function () {
                        //                                               return $(this).val() === "";
                        //                                           }).length;
                        //               if (numberOfEmptyFields == 0) {
                        //                   $('#btnSubmit').removeClass("disabled");
                        //               }
                        //               $('#btnSubmit').removeAttr("disabled");
                        //               $(this).css('border-color','#e4eaec');
                        //           }
                        //       },
                        //   });
                      }
                  }
              });
          }
          else if(selIdTxt === 'birthCertificate'){
              $(this).attr("placeholder", "Enter Birth Registration No");
              $("#errMsg,#errMsgNID,#errMsgSC,#errMsgPP,#errMsgDL").prop('id', 'errMsgBR');
              $(".identificationInput").on("input", function(event){
                  $(this).addClass('textNumber');
                  var brNo = $(this).val();
                  if(brNo.length > 0) {
                      if (brNo.length != 17) {
                          $("#errMsgBR").html("Not a valid 17-digit Birth Registration Number").show();
                          $(this).css('border-color','red');
                          $('#btnSubmit').attr("disabled","disabled");
                      }
                      else if ( brNo.length == 17 ){
                          $("#errMsgBR").html('');
                          $('#gr_nid').css('border-color','#e4eaec');

                            // // // Duplicate Check
                        var query = $(this).val();
                        var forWhich = $(this).attr("name");
                        var tableName = btoa('pos_guarantors');

                        var columnName = $(this).attr("name")+'&&is_delete';
                        var columnValue = $(this).val()+'&&0';
                        var url_text = "{{url('/ajaxCheckDuplicate')}}";
                        var fieldID = $(this).attr("id");
                        var updateID = null;

                        fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                        'errMsgBR', 'birth registration no.', updateID);

                        if($('#'+ fieldID).val() !== ''){
                            var numberOfEmptyFields = $('form').find('input[required],select[required]')
                                                    .filter(function () {
                                                        return $(this).val() === "";
                                                    }).length;
                            if (numberOfEmptyFields == 0) {
                                $('#btnSubmit').removeClass("disabled");
                            }
                            $('#btnSubmit').removeAttr("disabled");
                            $(this).css('border-color','#e4eaec');
                        }

                        //   $.ajax({
                        //       type: "get",
                        //       url: "{{route('ajaxCheckDuplicate')}}",
                        //       data: {query: query, tableName: tableName, forWhich: forWhich},
                        //       dataType: "json",
                        //       success: function (data) {
                        //           if (data.exists) {
                        //               $('#btnSubmit').attr("disabled","disabled");
                        //               $('#errMsgBR').html('Please enter unique Birth Registration No');
                        //               $('#gr_nid').css("border-color","red");
                        //           }
                        //           else {
                        //               var numberOfEmptyFields = $('form').find('input[required],select[required]')
                        //                                           .filter(function () {
                        //                                               return $(this).val() === "";
                        //                                           }).length;
                        //               if (numberOfEmptyFields == 0) {
                        //                   $('#btnSubmit').removeClass("disabled");
                        //               }
                        //               $('#btnSubmit').removeAttr("disabled");
                        //               $(this).css('border-color','#e4eaec');
                        //           }
                        //       },
                        //   });
                      }
                  }
              });
          }
      });
  });

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

    $("#checkbox_addr input[type=checkbox]").click(function() {
        alert("clicked");
        if ($(this).attr("checked") == "checked"){
          $(this + " input").attr("checked") = "checked";
          $('#gr_par_division_id').val($('#gr_pre_division_id').val());
        } else {
          $(this + " input").attr("checked") = "";
        }
    });

    $("#checkbox_addr").click(function() {
        if ($('#checkbox_addr').is(':checked')){
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
        }
    });


    $('form').submit(function (event) {
        $(this).find(':submit').attr('disabled', 'disabled');
    });
});


    // function GetSelectedText(id) {

    //     if ($('#' + id).is(':checked')) {
    //         $('#gr_par_division_id').val($('#gr_pre_division_id option:selected').val());


    //         $('#gr_par_division_id').trigger('change');

    //         $('#gr_par_district_id').val($('#gr_pre_district_id').val());
    //         $('#gr_par_upazila_id').val($('#gr_pre_upazila_id').val());
    //         $('#gr_par_union_id').val($('#pre_union_id').val());
    //         $('#par_village_id').val($('#pre_village_id').val());
    //         $('#gr_par_remarks').val($('#gr_pre_remarks').val());

    //         fnAjaxSelectBox(
    //             'gr_par_district_id',
    //             $('#gr_pre_division_id').val(),
    //             '{{base64_encode('gnl_districts')}}',
    //             '{{base64_encode('division_id')}}',
    //             '{{base64_encode('id,district_name')}}',
    //             '{{url('/ajaxSelectBox')}}',
    //             $('#gr_pre_district_id').val()
    //         );

    //         fnAjaxSelectBox(
    //             'gr_par_upazila_id',
    //             $('#gr_pre_district_id').val(),
    //             '{{base64_encode('gnl_upazilas')}}',
    //             '{{base64_encode('district_id')}}',
    //             '{{base64_encode('id,upazila_name')}}',
    //             '{{url('/ajaxSelectBox')}}',
    //             $('#gr_pre_upazila_id').val()
    //         );
    //         fnAjaxSelectBox(
    //             'gr_par_union_id',
    //             $('#gr_pre_upazila_id').val(),
    //             '{{base64_encode('gnl_unions')}}',
    //             '{{base64_encode('upazila_id')}}',
    //             '{{base64_encode('id,union_name')}}',
    //             '{{url('/ajaxSelectBox')}}',
    //             $('#gr_pre_union_id').val()
    //         );
    //         fnAjaxSelectBox(
    //             'gr_par_village_id',
    //             $('#gr_pre_union_id').val(),
    //             '{{base64_encode('gnl_villages')}}',
    //             '{{base64_encode('union_id')}}',
    //             '{{base64_encode('id,village_name')}}',
    //             '{{url('/ajaxSelectBox')}}',
    //             $('#gr_pre_village_id').val()
    //         );
    //         // $('#gr_par_division_id').val($('#pre_division_id').val());
    //         // $('#gr_par_district_id').val($('#gr_pre_district_id').val());
    //         //  $('#gr_par_upazila_id').val($('#gr_pre_upazila_id').val());
    //         //  $('#gr_par_union_id').val($('#pre_union_id').val());
    //         // $('#par_village_id').val($('#pre_village_id').val());
    //         // $('#gr_par_remarks').val($('#gr_pre_remarks').val());
    //     } else  {
    //             $('#gr_par_division_id').val('');
    //             $('#gr_par_district_id').val('');
    //             $('#gr_par_upazila_id').val('');
    //             $('#gr_par_union_id').val('');
    //             $('#gr_par_village_id').val('');
    //             // $('#par_remarks').val('');

    //             // $('#gr_par_division_id').trigger('change');
    //             // $('#gr_par_district_id').trigger('change');
    //             // $('#gr_par_upazila_id').trigger('change');
    //             // $('#gr_par_union_id').trigger('change');
    //             // $('#gr_par_village_id').trigger('change');
    //         }
    // }

</script>
@endsection
