@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
use App\Services\BillService as BILLS;
?>

<form method="post" class="form-horizontal" data-toggle="validator" novalidate="true" autocomplete="off" id="bill_form">
    @csrf

    <div class="row">
        <div class="col-lg-8 offset-lg-3">
            <!-- Html View Load  -->
            {!! HTML::forCompanyFeild() !!}
        </div>
    </div>
    <div class="row">
        <div class="col-lg-8 offset-lg-3">
            {!! HTML::forBranchFeild(false,'branch_id','branch_id',null,'','Branch Froms') !!}
        </div>
    </div>



    <div class="row">
        <div class="col-lg-3">
            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Month</label>
                <div class="col-lg-8">
                    <div class="input-group">
                        <input type="text" class="form-control" id="month_year" name="month_year" 
                        placeholder="MM-YYYY">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-2 text-center">
            <a href="javascript:void(0)" class="btn btn-primary btn-round"id="prepareBill">Prepare Bill</a>
        </div>
    </div>

    <table class="table w-full table-hover table-bordered table-striped" id="table1">
        <thead>
            <tr>
                <th style="width:5%;">SL</th>
                <th>Customer Name</th>
                <!-- <th>Customer Code</th> -->
                <th>Agreement No</th>
                <th>Agreement Date</th>
                <th>Service Start Date</th>
                <th>Service Fee</th>
                <th>Action</th>
            </tr>
        </thead>
    </table>

    <table class="table w-full table-hover table-bordered table-striped" id="table2" style="display:none;">
        <thead>
            <tr>
                <th style="width:5%;">SL</th>
                <th>Customer Name</th>
                <!-- <th>Customer Code</th> -->
                <th>Agreement No</th>
                <th>Agreement Date</th>
                <th>Service Start Date</th>
                <th>Service Fee</th>
                 <th>Action</th>
            </tr>
        </thead>
        <tbody id="table2Body"></tbody>     
    </table>

    <div class="row">
        <div class="col-lg-9 offset-lg-2">
            <div class="form-group d-flex justify-content-center">
                <div class="example example-buttons">
                  <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                    <button type="submit" class="btn btn-primary btn-round"
                        id="validateButton2">Generate Bill</button>
                </div>
            </div>
        </div>
    </div>
</form>

<style type="text/css">
    .ui-datepicker-calendar {
        display: none;
    }
</style>
<script>

$(document).ready(function() {
    $('.page-header-actions').hide();

    $('#month_year').datepicker({
        dateFormat: 'mm-yy',
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        todayButton: false,
        onClose: function(dateText, inst) {
            var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
            var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
            $(this).val($.datepicker.formatDate('mm-yy', new Date(year, month, 1)));
        }
    });

    $("#prepareBill").click(function(){
        var monthYearsel = $('#month_year').val();

        if (monthYearsel == '') {
            swal({
                icon: 'error',
                title: 'Error',
                text: 'Please select Month!',
            });
        }

        else {
            var month = monthYearsel.split("-")[0];
            var year = monthYearsel.split("-")[1];

            var getDate = new Date('01-' + monthYearsel);
            var dayOfMonth = new Date(getDate.getFullYear(), getDate.getMonth() + 1, 0).getDate();

            var startDate = year + '-' + month + '-' + '01';
            var endDate = year + '-' + month + '-' + dayOfMonth;

            $.ajax({
                method: "GET",
                url: "{{url('/ajaxAutoProcessBill')}}",
                dataType: "text",
                data: {
                    startDate: startDate,
                    endDate: endDate,
                    monthYearsel : monthYearsel
                },
                success: function(data) {
                    if (data) {
                        $('#table1').hide();
                        $('#table2Body')
                            .empty()
                            .append(data);
                        $('#table2').show();
                    }
                    else {
                        swal({
                            icon: 'error',
                            title: 'Error',
                            text: 'No Agreement For this Month!',
                        });
                    }
                }
            });
        }


        // ajaxDataLoad(startDate,endDate);
    });

    $('#validateButton2').on('click', function(event) {
        event.preventDefault();
        if ($("#table2 tbody").is(":empty")) {
            swal({
                icon: 'error',
                title: 'Error',
                text: 'Cant Generate Bill!',
            });
        }
        else {
            $('#bill_form').submit();
        }
    });
    
});


</script>
@endsection
