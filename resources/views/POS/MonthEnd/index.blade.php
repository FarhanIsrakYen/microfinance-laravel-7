@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;



?>

<div id="monthEndFormId">
    <div class="row">
        <div class="col-lg-8 offset-lg-3">
            <!-- Html View Load  -->
            {!! HTML::forCompanyFeild('','',false) !!}
        </div>
    </div>

    <!-- Search Option Start -->
    <div class="row align-items-center pb-10">
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

        <div class="col-lg-4 pt-25 text-center">
            <a href="javascript:void(0);" id="btnSearch" class="btn btn-primary btn-round">Search</a>

            @foreach($GlobalRole as $key => $row)
            @if($row['set_status'] == 15)

            <button type="submit" id="submitButton" class="btn btn-danger btn-round"
                style="color:#000; font-weight:bold;"> Execute Month End
            </button>

            @endif
            @endforeach
        </div>

        <div class="col-lg-1 text-right">
            

        </div>
    </div>
    <!-- Search Option End -->
</div>

<div style="margin-top:2%;">
    <div class="table-responsive">
        <table class="table w-full table-hover table-bordered table-striped clsDataTable" id="tblMonthEnd">
            <thead>
                <tr>
                    <th>SL</th>
                    <th>Branch Name</th>
                    <th>End Month</th>
                    <th>Total Days</th>
                    <th>Total Customer</th>
                    <th>Total Product</th>
                    <th>Total Sales Amount</th>
                    <th>Total Collection</th>
                    <th>Total Due</th>
                    {{-- <th>Status</th> --}}
                    <th width="5%">Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<script>
    $(document).ready(function () {
        var branchID = $('#branch_id option:selected').val();

        $('#branch_id').change(function () {
            branchID = $('#branch_id').val();
        });

        $(".btnDelete").removeClass("btnDelete");
        monthEndDatatableLoad();

        $('#btnSearch').click(function () {
            var SDate = $('#StartDate').val();
            var EDate = $('#EndDate').val();

            monthEndDatatableLoad(SDate, EDate, branchID);
        });

        
        
    });


    function monthEndDatatableLoad(sDate = null, eDate = null, branchID = null) {

        $('.clsDataTable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            order: [
                [2, "DESC"]
            ],
            stateSave: true,
            stateDuration: 1800,
            "ajax": {
                "url": "{{ url('pos/month_end') }}",
                "dataType": "json",
                "type": "post",
                "data": {
                    _token: "{{csrf_token()}}",
                    sDate: sDate,
                    eDate: eDate,
                    branchID: branchID,
                }
            },
            columns: [{
                    data: 'id',
                    className: 'text-center'
                },
                {
                    data: 'branch_id',
                    orderable: false,
                },
                {
                    data: 'month_date',
                    orderable: true,
                    className: 'text-center'
                },
                {
                    data: 'total_working_day',
                    className: 'text-center',
                    orderable: false,
                },
                {
                    data: 'total_current_month_customer',
                    className: 'text-center',
                    orderable: false,
                },
                {
                    data: 'total_product_quantity',
                    className: 'text-center',
                    orderable: false,
                },
                {
                    data: 'total_current_month_sales_amount',
                    className: 'text-right',
                    orderable: false,
                },
                {
                    data: 'total_current_month_collection',
                    className: 'text-right',
                    orderable: false,
                },
                {
                    data: 'total_current_month_due',
                    className: 'text-right',
                    orderable: false,
                },
                // {
                //     data: 'is_active',
                //     className: 'text-center',
                //     orderable: false,
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
                    aData
                    .action.action_link);
                $('td:last', nRow).html(actionHTML);
                $('.btnDelete', nRow).removeClass('btnDelete');

            }
        });
    }

    function fnDelete(monthEndId) {
        if (monthEndId != null) {

            $.ajax({
                type: 'get',
                url: '{{ url("pos/month_end/delete") }}',
                data: {
                    monthEndId: monthEndId
                },
                dataType: 'json',
                success: function (data) {

                    if (data.isDelete == true) {
                        toastr.success("Successfully remove data");
                        $('.clsDataTable').DataTable().ajax.reload();
                    } else {
                        swal({
                            icon: 'warning',
                            title: 'Error',
                            text: 'Please delete day end at first!',
                        });
                    }
                }
            });
        }
    }


    // Execute Month end
    $(document).ready(function () {

        $('#submitButton').click(function (event) {

            $('#submitButton').attr('disabled', 'disabled');
            event.preventDefault();

            var company_id = $('#company_id').val();
            var branchID = $('#branch_id').val();

            // Check execute all day of this month
            $.ajax({
                type: "get",
                url: "{{ url('pos/month_end/checkDayEndData') }}",
                data: {
                    branchID: branchID
                },
                dataType: "json",
                success: function (dataM) {
                    
                    if (dataM.isDayEndCheck == false) {
                        toastr.error("Please execute day end at first!");
                    } else if (dataM.isValidBranch == false){
                        toastr.error("Invalid Branch !!! Please try again.");
                    } else {
                        $.ajax({
                            method: "POST",
                            url: "{{url('pos/month_end/execute')}}",
                            dataType: "json",
                            data: {
                                company_id: company_id,
                                branchID: branchID
                            },
                            success: function (data) {
                                if (data) {
                                    if (data.alert_type == 'success') {
                                        $('.clsDataTable').DataTable().ajax
                                            .reload();
                                        toastr.success(data.message);

                                    } else if (data.alert_type == 'error') {
                                        toastr.error(data.message);
                                    }
                                }
                            }
                        });
                    }

                    $('#submitButton').attr('disabled', false);
                },
            });
        });

    });

</script>
@endsection
