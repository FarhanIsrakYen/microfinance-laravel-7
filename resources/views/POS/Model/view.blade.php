@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\HtmlService as HTML;
?>
<!--  Page -->

<div class="row">
    <div class="col-lg-9 offset-lg-3">
        <!-- Html View Load  -->
        <!-- {!! HTML::forCompanyFeild($PGroupData->company_id,'disabled') !!} -->
        <div class="form-row align-items-center">
            <label class="col-lg-3 input-title RequiredStar">Group</label>
            <div class="col-lg-5">
                <div class="form-group">

                    <div class="input-group ">
                        <select class="form-control clsSelect2" disabled>
                            <option value="{{$PGroupData->prod_group_id}}">
                                {{$PGroupData->pgroup->group_name}}</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
        </div>
        <div class="form-row align-items-center">
            <label class="col-lg-3 input-title RequiredStar">Category</label>
            <div class="col-lg-5">
                <div class="form-group">

                    <div class="input-group ">
                        <select class="form-control clsSelect2" disabled>
                            <option value="{{$PGroupData->prod_cat_id}}">{{$PGroupData->pcategory->cat_name}}</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
        </div>

        <div class="form-row align-items-center">
            <label class="col-lg-3 input-title RequiredStar">Sub Category</label>
            <div class="col-lg-5">
                <div class="form-group">

                    <div class="input-group ">
                        <select class="form-control clsSelect2" disabled>

                            <option value="{{$PGroupData->prod_sub_cat_id}}">
                                {{$PGroupData->psubcategory->sub_cat_name}}</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
        </div>

        <!-- <div class="form-row align-items-center">
            <label class="col-lg-3 input-title RequiredStar">Brand</label>
            <div class="col-lg-5">
                <div class="form-group">

                    <div class="input-group ">
                        <select class="form-control clsSelect2" disabled>

                            <option value="{{$PGroupData->brand_name}}">
                                {{$PGroupData->pbrand->brand_name}}</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
        </div> -->

        <div class="form-row align-items-center">
            <label class="col-lg-3 input-title RequiredStar">Model</label>
            <div class="col-lg-5">
                <div class="form-group">

                    <div class="input-group ">
                        <input type="text" value="{{$PGroupData->model_name}}" class="form-control round"
                            id="brand_name" name="brand_name" placeholder="Enter Brand Name" required="required"
                            readonly>
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