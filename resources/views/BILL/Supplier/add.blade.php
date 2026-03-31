@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\HtmlService as HTML;
?>

<form enctype="multipart/form-data" method="post" data-toggle="validator" novalidate="true">
    @csrf
    <div class="row">
        <div class="col-lg-8 offset-lg-3">
            <!-- Html View Load  -->
            {!! HTML::forCompanyFeild() !!}
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 offset-lg-3">

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Supplier Name</label>
                <div class="col-lg-5">
                    <div class="form-group">
                        <div class="input-group">
                            <input type="text" class="form-control round"
                                placeholder="Enter Supplier Name" name="sup_name" id="sup_name" required
                                data-error="Please enter supplier name.">
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Supplier Type</label>
                <div class="col-lg-5 form-group">

                    <div class="input-group">
                        <select class="form-control clsSelect2" name="supplier_type" id="supplier_type"
                            required data-error="Select Supplier Type.">
                            <option value="">Select Type</option>
                            <option value="1">Purchase</option>
                            <option value="2">Commision</option>

                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div id="comissionIDinput" style="display:none;">
                <div class="form-row align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">Commision</label>
                    <div class="col-lg-5">
                        <div class="form-group">
                            <div class="input-group ">
                                <input type="number" class="form-control round"
                                    placeholder="Enter Comission Percentage." name="comission_percent"
                                    id="comission_percent">
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Supplier's Company</label>
                <div class="col-lg-5">
                    <div class="form-group">
                        <div class="input-group ">
                            <input type="text" class="form-control round"
                                placeholder="Enter Company Name" name="sup_comp_name" id="sup_comp_name"
                                required data-error="Please enter supplier's company name.">
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Email</label>
                <div class="col-lg-5">
                    <div class="form-group">
                        <div>
                            <input type="email" class="form-control round" id="sup_email"
                                name="sup_email" placeholder="Enter Email" required
                                data-error="Please enter email.">
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>
            <!-- {{-- <div class="form-row align-items-center">
              <label class="col-lg-3 input-title" >Email for Notify</label>
                <div class="col-lg-5">
                    <div class="form-group">
                        <div>
                            <input type="email" class="form-control round" id="sup_email_notify" name="sup_email_notify" placeholder="Enter Email for Notify" data-error="Please enter correct email.">
                        </div>
                    </div>
                </div>
            </div> --}} -->
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Mobile</label>
                <div class="col-lg-5">
                    <div class="form-group">
                        <input type="text" pattern="[01][0-9]{10}" class="form-control round textNumber" name="sup_phone"
                                id="emp_phone" placeholder="Mobile Number (01*********)" required
                                data-error="Please enter mobile number (01*********)" minlength="11" maxlength="11">
                        <div class="help-block with-errors is-invalid" id="errMsgPhone"></div>
                    </div>
                </div>
            </div>
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title">Address</label>
                <div class="col-lg-5">
                    <div class="form-group">
                        <div class="input-group ">
                            <textarea class="form-control round" id="sup_addr" name="sup_addr" rows="2"
                                placeholder="Enter Address"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title">Website</label>
                <div class="col-lg-5">
                    <div class="form-group">
                        <div class="input-group ">
                            <input type="text" class="form-control round" name="sup_web_add"
                                id="sup_web_add" placeholder="https://example.com">
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title">Description</label>
                <div class="col-lg-5">
                    <div class="form-group">
                        <div class="input-group ">
                            <textarea class="form-control round fix-size" id="sup_desc" name="sup_desc"
                                rows="2" placeholder="Enter Description"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Reference No</label>
                <div class="col-lg-5">
                    <div class="form-group">
                        <div class="input-group ">
                            <input type="text" class="form-control round" id="sup_ref_no"
                                name="sup_ref_no" required placeholder="Enter Reference No">
                        </div>
                        <!-- {{-- <div class="help-block with-errors is-invalid"></div> --}} -->
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Attention 1</label>
                <div class="col-lg-5">
                    <div class="form-group">
                        <div class="input-group ">
                            <textarea class="form-control round" rows="2" name="sup_attentionA"
                                placeholder="Enter Attentions." required data-error="Enter attentions 1"></textarea>
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
              <label class="col-lg-3 input-title" >Attention 2</label>
                <div class="col-lg-5">
                    <div class="form-group">
                        <div class="input-group ">
                            {{-- <input type="text" class="form-control round" id="sup_attentionB" name="sup_attentionB" placeholder="Enter Attention 2"> --}}

                            <textarea class="form-control round" rows="2" id="sup_attentionB" name="sup_attentionB" placeholder="Enter Attention 2"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
              <label class="col-lg-3 input-title" >Attention 3</label>
                <div class="col-lg-5">
                    <div class="form-group">
                        <div class="input-group ">
                            {{-- <input type="text" class="form-control round" id="sup_attentionC" name="sup_attentionC" placeholder="Enter Attention 3"> --}}

                            <textarea class="form-control round" rows="2" id="sup_attentionC" name="sup_attentionC" placeholder="Enter Attention 3"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <div class="col-lg-2"></div>
                <div class="col-lg-5">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                            <button type="submit" class="btn btn-primary btn-round" id="btnSubmit">Save</button>
                            <!-- <a href="#"><button type="button" class="btn btn-warning btn-round">Next</button></a> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<!-- End Page -->
<script>
$(document).ready(function() {
    $('#supplier_type').change(function() {
        // console.log('test 2');

        if ($(this).val() == '2') {
            $("#comissionIDinput").show();
        } else {
            $("#comissionIDinput").hide();
        }


    });

    $('form').submit(function (event) {
        $(this).find(':submit').attr('disabled', 'disabled');
    });
});
</script>
@endsection
