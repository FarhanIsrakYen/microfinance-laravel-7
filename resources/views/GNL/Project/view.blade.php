@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\HtmlService as HTML;
?>

<!-- Page -->
    <div class="row">
        <div class="col-lg-9 offset-lg-3">

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar" for="groupName">GROUP</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group ">
                        <select class="form-control clsSelect2" disabled>
                            <option value="{{$ProjectData->group_id}}" selected="selected">
                                {{$ProjectData->group->group_name}}</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            {!! HTML::forCompanyFeild($ProjectData->company_id,'disabled') !!}

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar" for="groupName">PROJECT NAME</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" placeholder="Enter village Name"
                            name="project_name" readonly value="{{$ProjectData->project_name}}"
                            id="project_name" required data-error="Please enter Project name.">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Project Code</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" name="project_code" id="checkDuplicateCode"
                            value="{{$ProjectData->project_code}}" class="form-control round"
                            placeholder="Enter Project Code" required
                            data-error="Please enter project code."
                            readonly>
                    </div>
                    <!-- <div class="help-block with-errors is-invalid"></div> -->
                    <div class="help-block is-invalid" id="txtCodeError"></div>
                    @error('comp_code')
                    <div class="help-block with-errors is-invalid">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-lg-9">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();"
                                class="btn btn-default btn-round">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- End Page -->
@endsection