@extends('Layouts.erp_master')
@section('content')
<?php 
use App\Services\HtmlService as HTML;
?>

<!-- Page -->
    <form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator" 
    novalidate="true" autocomplete="off">
        @csrf
        <div class="row">
            <div class="col-lg-9 offset-lg-3">

                <!-- Html View Load  -->
                {!! HTML::forCompanyFeild() !!}

                <div class="form-row align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">Holiday Title</label>
                    <div class="col-lg-5 form-group">
                        <div class="input-group">
                            <input type="text" class="form-control round" name="ch_title" id="ch_title"
                            placeholder="Enter comp Holiday Title"  
                            required data-error="Please enter comp Holiday Title.">
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>

                <div class="form-row align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">Day</label>
                    <div class="col-lg-5 form-group">
                        <div class="row">
                            @foreach($days as $day)
                            <div class="col-lg-4">
                                <div class="input-group checkbox-custom checkbox-primary">
                                        <input type="checkbox" name="ch_day[]" id="ch_day_{{$day}}" value="{{ $day }}">
                                        <label for="ch_day_{{$day}}">{{ $day}}</label>
                                </div>
                            </div>
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
                            <input type="text" class="form-control round datepickerNotRange" onchange="fnCheckDayEnd();"
                            id="ch_eff_date" name="ch_eff_date" placeholder="DD-MM-YYYY"
                            required data-error="Please Select Date" >
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>

                <div class="form-row align-items-center">
                    <label class="col-lg-3 input-title" >Description</label>
                    <div class="col-lg-5 form-group">
                        <div class="input-group">
                            <textarea class="form-control round" id="ch_description" name="ch_description" 
                                rows="2" placeholder="Enter Description"></textarea>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-lg-9">
                        <div class="form-group d-flex justify-content-center">
                            <div class="example example-buttons">
                                <a href="javascript:void(0)" class="btn btn-default btn-round" onclick="goBack()">Back</a>
                                <button type="submit" class="btn btn-primary btn-round">Save</button>
                                <!-- <a href="#"><button type="button" class="btn btn-warning btn-round">Next</button></a> -->
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
<!-- End Page -->

<script type="text/javascript">
    $('form').submit(function (event) {
        $(this).find(':submit').attr('disabled', 'disabled');
    });

     //check Day End value on database in real time

     function fnCheckDayEnd() {
        var startDateFrom = $('#ch_eff_date').val();
        // console.log(startDateFrom);

        if (startDateFrom != '') {
            $.ajax({
                type: "get",
                url: "{{url('gnl/compholiday/CheckDayEnd')}}",
                data: {
                   startDateFrom: startDateFrom,
                },
                dataType: "json",
                success: function (data) {

                    if (data.Table == "DayEnd") {
                        swal({
                            title: "Day end exist, please select another day !!",
                            icon: "error",
                        });

                        $('#ch_eff_date').val('');
                    }else if(data.Table == "emptydata"){
                        swal({
                            title: "Date Empty, please select a date !!",
                            icon: "error",
                        });

                        $('#ch_eff_date').val('');
                    }
                }
            });

        }
    }
</script>

@endsection
