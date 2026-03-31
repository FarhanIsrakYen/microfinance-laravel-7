@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;

$StartDate = (isset($StartDate) && !empty($StartDate)) ? $StartDate : Common::systemCurrentDate();
$EndDate = (isset($EndDate) && !empty($EndDate)) ? $EndDate : Common::systemCurrentDate();
$BranchID = (isset($BranchID) && !empty($BranchID)) ? $BranchID : Common::getBranchId();
?>

<div>

    <div class="row">
        <div class="col-lg-8 offset-lg-3">
            <!-- Html View Load  -->
            {!! HTML::forCompanyFeild('','',false) !!}
        </div>
    </div>

    <!-- Search Option Start -->
    <div class="row align-items-center pb-10 d-print-none">

        <!-- Html View Load For Branch Search -->
        {!! HTML::forBranchFeildSearch($BranchID) !!}

        <div class="col-lg-2">
            <label class="input-title">Start Date</label>
            <div class="input-group ghdatepicker">
                <div class="input-group-prepend ">
                    <span class="input-group-text ">
                        <i class="icon wb-calendar round" aria-hidden="true"></i>
                    </span>
                </div>
                <input type="text" class="form-control round datepicker-custom" id="StartDate" name="StartDate"
                    placeholder="DD-MM-YYYY">
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
                <input type="text" class="form-control round datepicker-custom" id="EndDate" name="EndDate"
                    placeholder="DD-MM-YYYY">
            </div>
            <div class="help-block with-errors is-invalid"></div>
        </div>


        <div class="col-lg-2 pt-20 text-center">
            <a href="javascript:void(0);" id="btnSearch" class="btn btn-primary btn-round">Search</a>
        </div>

        <div class="col-lg-1 text-right">
            @foreach($GlobalRole as $key => $row)
            @if($row['set_status'] == 15)
            <button type="submit" name="btnDayEnd" id="submitButton" value="Submit" class="btn btn-danger btn-round"
                style="color:#000; font-weight:bold;"> Execute Day End
            </button>
            @endif
            @endforeach

        </div>
    </div>
    <!-- Search Option End -->
</div>

<div class="row" style="margin-top:2%;">
    <div class="col-lg-12">
        <table class="table w-full table-hover table-bordered table-striped clsDataTable">
            <thead>
                <tr>
                    <th width="3%">SL</th>
                    <th>Branch Name</th>
                    <th>Branch Date</th>
                    {{-- <th>Status</th> --}}
                    <th style="width:5%;">Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>



<script>
    $(document).ready(function () {
        ajaxDataLoad();
        $('#btnSearch').click(function () {

            var SDate = $('#StartDate').val();
            var EDate = $('#EndDate').val();
            var branchID = $('#branch_id').val();
            // console.log(branchID);

            ajaxDataLoad(SDate, EDate, branchID);
        });
    });

    function ajaxDataLoad(SDate = null, EDate = null, branchID = null) {
        // console.log(branchID);
        $('.clsDataTable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            order: [
                [2, "DESC"]
            ],
            stateSave: true,
            stateDuration: 300,
            // ordering: false,
            // lengthMenu: [[10, 20, 30, 50], [10, 20, 30, 50]],
            "ajax": {
                "url": "{{route('accdayendDatatable')}}",
                "dataType": "json",
                "type": "post",
                "data": {
                    _token: "{{csrf_token()}}",
                    SDate: SDate,
                    EDate: EDate,
                    branchID: branchID,
                }
            },
            columns: [{
                    data: 'id',
                    name: 'id',
                    // orderable: false
                },
                {
                    data: 'branch_name',
                    name: 'branch_name',
                    orderable: false
                },
                {
                    data: 'branch_date',
                    name: 'branch_date',
                    // orderable: false
                },
                // {
                //     data: 'status',
                //     name: 'status',
                //     orderable: false
                // },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    className: 'text-center'
                },
            ],
            'fnRowCallback': function (nRow, aData, Index) {
                // dont delete this comment
                // $('td:nth-child(10) .btnDelete', nRow).removeClass('btnDelete'); // dont delete this comment


                var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name,
                    aData.action.action_link);
                $('td:last', nRow).html(actionHTML);
                // console.log(actionHTML);
                $('.btnDelete', nRow).removeClass('btnDelete');
            }
        });
    }

    function fnDelete(RowID) {

        $.ajax({
            method: "GET",
            url: "{{url('acc/day_end/ajaxdDeleteAccDayEnd')}}",
            dataType: "text",
            data: {
                RowID: RowID,
            },
            success: function (data) {
                if (data) {

                    if (data == 'child') {
                        swal({
                            icon: 'error',
                            title: 'Error',
                            text: 'Please ! Delete update day end data first.',
                        });
                    } else if (data == 'db_error') {

                        swal({
                            icon: 'error',
                            title: 'Error',
                            text: 'Please ! Check Date and try again.',
                        });

                    } else if (data == 'month_end') {
                        swal({
                            icon: 'error',
                            title: 'Error',
                            text: 'Please ! Delete month end data for selected.',
                        });
                    } else {
                        var data_temp = JSON.parse(data);

                        if (data_temp.status === 'success') {
                            $('.clsDataTable').DataTable().ajax.reload();
                            $('#systemDate').html(data_temp.new_date);
                            toastr.success(data_temp.message);
                        }
                    }
                }
            }
        });

    }

    // Execute day end
    $(document).ready(function () {

        $('#submitButton').click(function (event) {

            $('#submitButton').attr('disabled', 'disabled');
            event.preventDefault();

            var company_id = $('#company_id').val();
            var branch_id = $('#branch_id').val();

            $.ajax({
                method: "POST",
                url: "{{url('acc/day_end/end')}}",
                dataType: "json",
                data: {
                    company_id: company_id,
                    branch_id: branch_id
                },
                success: function (data) {
                    if (data) {
                        $('#submitButton').attr('disabled', false);
                        if (data.alert_type == 'success') {

                            $('.clsDataTable').DataTable().ajax.reload();
                            $('#systemDate').html(data.system_date);

                            toastr.success(data.message);

                        } else if (data.alert_type == 'warning') {
                            $('.clsDataTable').DataTable().ajax.reload();
                            $('#systemDate').html(data.system_date);

                            toastr.warning(data.message);

                        } else if (data.alert_type == 'error') {
                            toastr.error(data.message);
                        }
                    }
                }
            });
        });
    });

</script>

@endsection
