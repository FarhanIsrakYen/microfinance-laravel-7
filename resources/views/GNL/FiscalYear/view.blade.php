@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\HtmlService as HTML;
?>
    <form enctype="multipart/form-data" method="post" class="form-horizontal" >
        @csrf
        <div class="row">
            <div class="col-lg-9 offset-lg-3">
                {!! HTML::forCompanyFeild($FiscalYear->company_id,'disabled') !!}
                <div class="form-row align-items-center">
                    <label class="col-lg-3 input-title RequiredStar" for="fy_name">name</label>
                    <div class="col-lg-5 form-group">
                        <div class="input-group">
                            <input type="text" class="form-control round" value="{{$FiscalYear->fy_name}}" name="fy_name" id="fy_name" required data-error="Please enter Fiscal year name." readonly>
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
                <div class="form-row align-items-center">
                    <label class="col-lg-3 input-title RequiredStar" for="fy_start_date">Start Date</label>
                    <div class="col-lg-5 form-group">
                        <div class="input-group">
                            <div class="input-group-prepend ">
                                <span class="input-group-text ">
                                    <i class="icon wb-calendar round" aria-hidden="true"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control round datepicker" id="fy_start_date" name="fy_start_date"  value="{{date('d-m-Y', strtotime($FiscalYear->fy_start_date))}}" disabled>
                        </div>
                    </div>
                </div>
                <div class="form-row align-items-center">
                    <div class="col-lg-2"></div>
                    <div class="col-lg-5">
                        <div class="form-group d-flex justify-content-center">
                            <div class="example example-buttons">
                                <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
