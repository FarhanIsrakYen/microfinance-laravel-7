@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\HtmlService as HTML;
?>

<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator" novalidate="true">
    @csrf
    <div class="row">
        <div class="col-lg-9 offset-lg-3">

          <!-- Html View Load  -->
         {!! HTML::forCompanyFeild() !!}

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar" for="area_name">Area Name</label>

                <div class="col-lg-5">
                    <div class="form-group">
                        <div class="">
                            <input type="text" class="form-control round" id="area_name" name="area_name" placeholder="Enter Area Name" required="true" required data-error="Please select Area Name.">
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar" for="area_code">Area Code</label>
                <div class="col-lg-5">
                    <div class="form-group">
                        <div class="input-group ">
                            <input type="text" class="form-control round" id="area_code" name="area_code" placeholder="Enter Area Code" required="true" required data-error="Please select Area code.">
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>


            <div class="form-row align-items-center">
                <label class="col-lg-12 input-title">
                    Branchs
                </label>

                <div class="col-lg-12" id="BranchDiv">

                </div>
            </div>

            <div class="form-row align-items-center">
                <div class="col-lg-9">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                            <button type="submit" class="btn btn-primary btn-round" id="submitButtonforArea">Save</button>
                            <!--<button type="button" class="btn btn-warning btn-round">Next</button>-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    function ajaxLoadBranch(){
        var CompanyID = $('#company_id').val();

        $.ajax({
            method: "GET",
            url: "{{url('gnl/area/ajaxAreaList')}}",
            dataType: "text",
            data: {
                CompanyID: CompanyID
            },
            success: function (data) {
                if (data) {
                    // console.log(data);
                    $('#BranchDiv').html(data);
                }
            }
        });
    }

    $(document).ready(function () {
      ajaxLoadBranch();

        $('#company_id').change(function () {

            if ($(this).val() != '') {

                ajaxLoadBranch();
            }
        });
        $('form').submit(function (event) {
            // event.preventDefault();
            $(this).find(':submit').attr('disabled', 'disabled');
            // $(this).submit();
        });
    });
</script>
@endsection
