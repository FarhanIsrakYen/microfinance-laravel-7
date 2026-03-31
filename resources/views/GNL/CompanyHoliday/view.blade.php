@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\HtmlService as HTML;
?>

<div class="row">
        <div class="col-lg-9 offset-lg-3">

            <!-- Html View Load  -->
            {!! HTML::forCompanyFeild($CompHolidayData->company_id,'disabled') !!}


            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Holiday Title</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" placeholder="Enter comp Holiday Title"
                            name="ch_title" id="ch_title" value="{{$CompHolidayData->ch_title}}" readonly>
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title">Day</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        @foreach($days as $key => $value)
                        @if($key==$CompHolidayData->ch_day)
                        <input type="text" class="form-control round" name="ch_day" id="ch_day" value="{{ $value }}"
                            readonly>
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Effective Date</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group ghdatepicker">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="icon wb-calendar round" aria-hidden="true"></i>
                            </span>
                        </div>
                        <?php
                        if(!empty($CompHolidayData->ch_eff_date)){
                            $CompHolidayData->ch_eff_date = new DateTime($CompHolidayData->ch_eff_date);
                        }
                        ?>
                        <input type="text" class="form-control round datepicker" id="ch_eff_date" name="ch_eff_date"
                            placeholder="DD-MM-YYYY" autocomplete="off"
                            value="{{ $CompHolidayData->ch_eff_date->format('d-m-Y') }}" disabled>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title">Description</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <textarea class="form-control round" id="textgvtHolidayDesc" name="ch_description" rows="2"
                            placeholder="No Description" readonly>{{$CompHolidayData->ch_description}}</textarea>
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
    
@endsection