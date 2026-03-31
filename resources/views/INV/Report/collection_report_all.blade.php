@extends('Layouts.erp_master_full_width')
@section('content')

<?php 
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;

$startDate = Common::systemCurrentDate();
$endDate = Common::systemCurrentDate();

$productData = Common::ViewTableOrder('inv_products',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'product_name'],
    ['product_name', 'ASC']);

$groupData = Common::ViewTableOrder('inv_p_groups',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'group_name'],
    ['group_name', 'ASC']);
$categoryData = Common::ViewTableOrder('inv_p_categories',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'cat_name'],
    ['cat_name', 'ASC']);
$subCatData = Common::ViewTableOrder('inv_p_subcategories',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'sub_cat_name'],
    ['sub_cat_name', 'ASC']);
$brandData = Common::ViewTableOrder('inv_p_brands',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'brand_name'],
    ['brand_name', 'ASC']);
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
                <div class="col-lg-2">
                    <label class="input-title">Start Date</label>
                    <div class="input-group">
                        <input type="text" class="form-control datepicker-custom" id="start_date" name="StartDate"
                            placeholder="DD-MM-YYYY" value="{{ $startDate }}">
                    </div>
                </div>
                <div class="col-lg-2">
                    <label class="input-title">End Date</label>
                    <div class="input-group">
                        <input type="text" class="form-control datepicker-custom" id="end_date" name="EndDate"
                            placeholder="DD-MM-YYYY" value="{{ $endDate }}">
                    </div>
                </div>


                {!! HTML::forBranchFeildSearch_new('all','branch','branch_id', 'Branch', null)!!}
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
                    <span>All Collection Report</span><br>
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
                        onclick="fnDownloadExcel('ExportHeading,ExportDiv', 'Collection_Report_{{ (new Datetime())->format('d-m-Y') }}');">
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
                        <thead class="text-center">
                            <tr>
                                <th width="3%">SL</th>
                                <th>Collection Date</th>
                                <th>Customer Name</th>
                                <th>Bill No</th>
                                <!-- <th>Customer Code</th> -->
                                <th>Sales Amount</th>
                                <th>Processing Fee</th>
                                <th>Total Sales Amount</th>
                                <th>Collection Amount</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-align: right!important;"><b>Total</b></td>
                                <td class="text-right text-dark font-weight-bold" id="TotalSaleAmt"><b>0.00</b></td>
                                <td class="text-right text-dark font-weight-bold" id="service_charge"><b>0.00</b></td>
                                <td class="text-right text-dark font-weight-bold" id="total_payable_amount"><b>0.00</b></td>
                                <td class="text-right text-dark font-weight-bold" id="collection_amount"><b>0.00</b></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Page -->

<script>
function ajaxDataLoad(start_date = null, end_date = null, branch_id = null) {
    
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
            "url": "{{route('allcollectionDataTable')}}",
            "dataType": "json",
            "type": "post",
            "data": {
                _token: "{{ csrf_token() }}",
                startDate: start_date,
                endDate: end_date,
                branchId: branch_id
            }
        },
        columns: [{
                data: 'sl',
                className: 'text-center'
            },
            {
                data: 'collection_date',
            },
            {
                data: 'customer_name'
            },
            {
                data: 'sales_bill_no',
            },
            // {
            //     data: 'customer_code',

            // },
            {
                data: 'sales_amount',
                className: 'text-right'
            },
            {
                data: 'service_charge',
                className: 'text-right'
            },
            {
                data: 'total_payable_amount',
                className: 'text-right'
            },
            {
                data: 'collection_amount',
                className: 'text-right'
            },
        ],
        drawCallback: function(oResult) {
            if (oResult.json) {
                $('#totalRowDiv').html(oResult.json.totalRow);
                $('#TotalSaleAmt').html(oResult.json.ttl_sales_amount);
                $('#service_charge').html(oResult.json.ttl_service_charge);
                $('#total_payable_amount').html(oResult.json.total_payable_amount);
                $('#collection_amount').html(oResult.json.ttl_collection_amount);
            }
        },
    });
}

$(document).ready(function() {

    $('#searchButton').click(function() {
        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();
        var branch_id = $('#branch_id').val();
        $('#start_date_txt').html(start_date);
        $('#end_date_txt').html(end_date);

        ajaxDataLoad(start_date, end_date, branch_id);
    });
});
</script>
@endsection