@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
?>

<div>
    <div class="row text-center pb-10 d-print-none">
        <div class="col-lg-12">
            <h2>POS AUTO VOUCHER SCRIPT</h2>
            <h6>BASED WITH DAY END DATA</h6>
        </div>
    </div>

    <!-- Search Option Start -->
    <div class="row align-items-center pb-10 d-print-none">
        <!-- Html View Load For Branch Search -->
        {!! HTML::forBranchFeildSearch('all') !!}

        <div class="col-lg-2">
            <label class="input-title">Start Date</label>
            <div class="input-group ghdatepicker">
                <div class="input-group-prepend ">
                    <span class="input-group-text ">
                        <i class="icon wb-calendar round" aria-hidden="true"></i>
                    </span>
                </div>
                <input type="text" class="form-control round datepicker-custom" id="StartDate" placeholder="DD-MM-YYYY">
            </div>
        </div>

        <div class="col-lg-2">
            <label class="input-title">End Date</label>
            <div class="input-group ghdatepicker">
                <div class="input-group-prepend ">
                    <span class="input-group-text ">
                        <i class="icon wb-calendar round" aria-hidden="true"></i>
                    </span>
                </div>
                <input type="text" class="form-control round datepicker-custom" id="EndDate" placeholder="DD-MM-YYYY">
            </div>
            <div class="help-block with-errors is-invalid"></div>
        </div>

        <div class="col-lg-4 pt-25 text-left">
            <button type="submit" id="submitButton" class="btn btn-danger btn-round"
                style="color:#000; font-weight:bold;">Execute Day End</button>
        </div>
    </div>

</div>
<!-- Search Option End -->

<div class="row" style="margin-top:2%;">
    <div class="col-lg-12">
        <table class="table w-full table-hover table-bordered table-striped clsDataTable">
            <thead>
                <tr>
                    <th style="width:3%;">SL</th>
                    <th>Branch</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<script>
    $(document).ready(function () {

        $('#submitButton').click(function (event) {

            // $('#submitButton').attr('disabled', 'disabled');
            event.preventDefault();
            var branch_id = $('#branch_id').val();
            var start_date = $('#StartDate').val();
            var end_date = $('#EndDate').val();

            if (start_date == '' && end_date == '') {
                swal({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please select Start & End Date.',
                });
                $('#submitButton').attr('disabled', false);
            } else {
                $.ajax({
                    method: "POST",
                    url: "{{url('pos/day_end/auto_voucher_script')}}",
                    dataType: "json",
                    data: {
                        branch_id: branch_id,
                        start_date: start_date,
                        end_date: end_date
                    },
                    success: function (result) {
                        if (result) {

                            $('#submitButton').attr('disabled', false);

                            if(result.alert_type == 'error'){
                                swal({
                                    icon: 'error',
                                    title: 'Error',
                                    text: result.message,
                                });
                            } else if(result.alert_type == 'warning'){
                                swal({
                                    icon: 'warning',
                                    title: 'Warning',
                                    text: result.message,
                                });
                            } else if(result.alert_type == 'success'){
                                $('.clsDataTable').DataTable({
                                    "ajax": result.data
                                });

                                swal({
                                    icon: 'success',
                                    title: 'Success',
                                    text: result.message,
                                });
                                // console.log(result);
                            }
                        }
                    }
                });
            }
        });
    });

</script>

@endsection
