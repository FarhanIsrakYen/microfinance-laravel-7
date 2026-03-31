@extends('Layouts.erp_master_full_width')
@section('content')

<?php 
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;

$startDate = Common::systemCurrentDate();
$endDate = Common::systemCurrentDate();

$saleMasterData = Common::ViewTableOrder('inv_use_m',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'sales_bill_no'],
    ['sales_bill_no', 'ASC']);

$branchId = Common::getBranchId();
$branchInfo = Common::ViewTableFirst('gnl_branchs',
    [['is_delete', 0], ['is_active', 1],
        ['id', $branchId]],
    ['id', 'branch_name']);
$groupInfo = Common::ViewTableFirst('gnl_groups',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'group_name']);

?>

<div class="w-full d-print-none">
    <div class="panel">
        <div class="panel-body">
            <div class="row align-items-center pb-10 d-print-none">

                {!! HTML::forBranchFeildSearch('all','branch','branch_id', 'Branch', null)!!}
                <div class="col-lg-2">
                    <label class="input-title">Sales By</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="employee_id" id="employee_id">
                            <option value="">Select All</option>

                        </select>
                    </div>
                </div>
                <div class="col-lg-2">
                    <label class="input-title">Customer Name</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="customer_id" id="customer_id">
                            <option value="">Select All</option>

                        </select>
                    </div>
                </div>
                <div class="col-lg-2">
                    <label class="input-title">Sales Bill No.</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="sales_bill_no" id="sales_bill_no">
                            <option value="">Select All</option>
                            @foreach ($saleMasterData as $Row)
                            <option value="{{ $Row->sales_bill_no }}">{{ $Row->sales_bill_no }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-2 pt-20 text-center">
                    <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round"
                        id="searchButton">Search</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="w-full">
    <div class="panel">
        <div class="panel-body">

            <div class="row text-dark ExportHeading">
                <div class="col-xl-12 col-lg-12 col-sm-12 col-md-12 col-12" style="text-align:center;">
                    <br>
                    <strong>{{ $groupInfo->group_name }}</strong><br>
                    <strong>{{ $branchInfo->branch_name }}</strong><br>
                    <span>Customer Due Report</span><br>
                    (<span id="start_date_txt">{{$startDate }}</span>
                    TO
                    <span id="end_date_txt">{{$endDate }}</span>)
                    <br><br>
                </div>
            </div>

            <div class="row d-print-none text-right">
                <div class="col-lg-12">
                    <a href="javascript:void(0)" onClick="window.print();"
                        style="background-color:transparent;border:none;" class="btnPrint mr-2">
                        <i class="fa fa-print fa-lg" style="font-size:20px;"></i>
                    </a>

                    <a href="javascript:void(0)" style="background-color:transparent;border:none;"
                        onclick="getDownloadPDF();">
                        <i class="fa fa-file-pdf-o fa-lg" style="font-size:20px; margin-left: 5px;"></i>
                    </a>

                    <a href="javascript:void(0)" title="Excel" style="background-color:transparent;border:none;"
                        onclick="fnDownloadExcel('ExportHeading,ExportDiv', 'Customer_Due_{{ (new Datetime())->format('d-m-Y') }}');">
                        <i class="fa fa-file-excel-o fa-lg" style="font-size:20px; margin-left: 5px;"></i>
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-6 col-lg-6 col-sm-6 col-md-6 col-6">
                    <!-- <span class="d-print-none"><b>Total Row:</b> <span id="totalRowDiv">0</span></span> -->
                </div>
                <div class="col-xl-6 col-lg-6 col-sm-6 col-md-6 col-6 text-right">
                    <span><b>Printed Date:</b> {{ (new Datetime())->format('d-m-Y') }} </span>
                </div>
            </div>

            <div class="row ExportDiv">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead>
                            <tr>
                                <th width="3%" rowspan="2">SL</th>
                                <th rowspan="2" width="15%">Customer Information</th>
                                <th colspan="4" width="35%">Sales Information</th>
                                <th colspan="5" width="47%">Transaction</th>
                            </tr>
                            <tr>
                                <!-- <th width="5%">Code</th>
                                <th width="10%">Name</th>
                                <th width="8%">Mobile</th>
                                <th width="10%">NID</th>
                                <th width="10%">Spouse</th> -->
                                <th>Bill No</th>
                                <th>Sales Date</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Sales Amount</th>
                                <th>Processing Fee</th>
                                <th>Gross Total</th>
                                <th>Paid Amount</th>
                                <th>Due Amount</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" style="text-align: right!important; font-weight:bold;">Total</td>
                                <td class="Tqnt text-right text-dark font-weight-bold">0.00</td>
                                <td class="Tsales text-right text-dark font-weight-bold">0.00</td>
                                <td class="Tprocfee text-right text-dark font-weight-bold">0.00</td>
                                <td class="Tgross text-right text-dark font-weight-bold">0.00</td>
                                <td class="TpaidAmt text-right text-dark font-weight-bold">0.00</td>
                                <td class="TdueAmt text-right text-dark font-weight-bold">0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Page -->

<style>
    @media print {
        @page { 
            size: landscape;
            margin: 5px!important;
        }

        .table > thead th {
            font-size: 70%!important;
            padding: 2px!important;
        }
        
        .table > tbody td {
            font-size: 70%!important;
            padding: 2px!important;
        }

        .table > tfoot td {
            font-size: 70%!important;
            padding: 2px!important;
        }

    }
</style>

<script>
function ajaxDataLoad(branch_id = null, employee_id = null, customer_id = null, sales_bill_no = null) {
    $('.clsDataTable').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        // lengthMenu: [[10, 20, 30, 50, -1], [10, 20, 30, 50, "All"]],
        paging: false,
        ordering: false,
        info: false,
        searching: false,
        "ajax": {
            "url": "{{ url('inv/report/customer_due') }}",
            "dataType": "json",
            "type": "post",
            "data": {
                _token: "{{ csrf_token() }}",
                branchID: branch_id,
                employeeID: employee_id,
                CustomerID: customer_id,
                SaleBillNo: sales_bill_no,
            }
        },
        columns: [{
                data: 'sl',
                className: 'text-center'
            },
            {
                data: 'customer_info'
            },
            // {
            //     data: 'customer_name'
            // },
            // {
            //     data: 'mobile'
            // },
            // {
            //     data: 'cus_nid'
            // },
            // {
            //     data: 'spouse_name',
            // },
            {
                data: 'bill_no',
            },
            {
                data: 'sales_date',
                // width: '20%'
            },
            {
                data: 'product',
            },
            {
                data: 'quantity',
                className: 'text-center',
                // width: '5%'
            },
            {
                data: 'sales_amount',
                className: 'text-right'
            },
            {
                data: 'processing_fee',
                className: 'text-right'
            },
            {
                data: 'gross_total',
                className: 'text-right'
            },
            {
                data: 'paid_amount',
                className: 'text-right'
            },
            {
                data: 'due_amount',
                className: 'text-right'
            },
        ],
        drawCallback: function(oResult) {
            if (oResult.json) {
                $('#totalRowDiv').html(oResult.json.totalRow);
                $('.Tqnt').html(oResult.json.ttl_qnt);
                $('.Tsales').html(oResult.json.ttl_sales_amount);
                $('.Tprocfee').html(oResult.json.ttl_service_charge);
                $('.Tpayable_amt').html(oResult.json.total_payable_amount);
                $('.Tgross').html(oResult.json.ttl_gross_amount);
                $('.TpaidAmt').html(oResult.json.ttl_paid_amount);
                $('.TdueAmt').html(oResult.json.ttl_due_amount);
            }
        },
    });
}
$(document).ready(function() {
    $('#searchButton').click(function() {
        var branch_id = $('#branch_id').val();
        var employee_id = $('#employee_id').val();
        var customer_id = $('#customer_id').val();
        var sales_bill_no = $('#sales_bill_no').val();

        // console.log(branch_id);
        ajaxDataLoad(branch_id, employee_id, customer_id, sales_bill_no);
    });
});



$("#branch_id").on('change', function() {
    fnAjaxSelectBox('employee_id',
        this.value,
        '{{ base64_encode("hr_employees")}}',
        '{{base64_encode("branch_id")}}',
        '{{base64_encode("employee_no,emp_name")}}',
        '{{url("/ajaxSelectBox")}}');
    fnAjaxSelectBox('customer_id',
        this.value,
        '{{ base64_encode("pos_customers")}}',
        '{{base64_encode("branch_id")}}',
        '{{base64_encode("customer_no,customer_name")}}',
        '{{url("/ajaxSelectBox")}}');
});
</script>
@endsection