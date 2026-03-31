@extends('Layouts.erp_master')
@section('content')
<?php 
use App\Services\HtmlService as HTML;
?>
<!--  Page -->

<div class="row">
    <div class="col-lg-9 offset-3 mb-2">
        <!-- Html View Load  -->
        <!-- {!! HTML::forCompanyFeild($PGroupData->company_id,'disabled') !!} -->
    </div>
</div>

<div class="row">
    <div class="col-lg-9 offset-lg-3">
        <div class="form-row align-items-center">
            <label class="col-lg-3 input-title RequiredStar">Group Name</label>
            <div class="col-lg-5">
                <div class="form-group">

                    <div class="input-group ">
                        <input type="text" class="form-control round" id="group_name" name="group_name" readonly
                            value="{{$PGroupData->group_name}}" placeholder="Enter Group Name" required="required">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-9">
                <div class="form-group d-flex justify-content-center">
                    <div class="example example-buttons">
                        <a href="javascript:void(0)" onclick="goBack();"
                            class="btn btn-default btn-round d-print-none">Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Page -->
@endsection