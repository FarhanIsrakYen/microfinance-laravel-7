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
                            <option value="{{$ProjectTypeData->group_id}}" selected="selected">
                                {{$ProjectTypeData->group->group_name}}</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            {!! HTML::forCompanyFeild($ProjectTypeData->company_id,'disabled') !!}

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar" for="groupName">Project</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" disabled>
                            <option value="{{$ProjectTypeData->project_id}}" selected="selected">
                                {{$ProjectTypeData->project->project_name}}</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar" for="groupName">Project Type</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" id="textProjectType" readonly
                            name="project_type_name" value="{{$ProjectTypeData->project_type_name}}"
                            placeholder="Enter Project Type" required
                            data-error="Please enter project type .">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Project Type Code</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" name="project_type_code" id="checkDuplicateCode"
                            value="{{$ProjectTypeData->project_type_code}}" class="form-control round"
                            placeholder="Enter Project Type Code" required
                            data-error="Please enter project type code." readonly>
                    </div>
                    <!-- <div class="help-block with-errors is-invalid"></div> -->
                    <div class="help-block is-invalid" id="txtCodeError"></div>
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