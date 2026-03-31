@extends('Layouts.erp_master_full_width')
@section('content')

<?php
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;

$startDate = Common::systemCurrentDate();
$endDate = Common::systemCurrentDate();

$productData = Common::ViewTableOrder('inv_products',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'product_name','product_code'],
    ['product_name', 'ASC']);

$branchData = Common::ViewTableOrder('gnl_branchs',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'branch_name'],
    ['branch_name', 'ASC']);


$branchId = Common::getbranchId();

$branchInfo = Common::ViewTableFirst('gnl_branchs',
    [['is_delete', 0], ['is_active', 1],
        ['id', $branchId]],
    ['id', 'branch_name']);

$groupInfo = Common::ViewTableFirst('gnl_groups',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'group_name']);

?>
<!-- Page -->
<div class="w-full d-print-none">
    <div class="panel">
        <div class="panel-body">
            <div class="row align-items-center pb-10 d-print-none">

                {!! HTML::forBranchFeildSearch('all')!!}

                <!-- <div class="col-lg-2">
                    <label class="input-title">Product</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="product_id" id="product_id">
                            <option value="">Select All</option>
                            @foreach ($productData as $row)
                            <option value="{{ $row->id }}">{{ $row->product_code ? $row->product_name."(".$row->product_code.")" : $row->product_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div> -->
            <!-- </div>

            <div class="row align-items-center pb-10 d-print-none"> -->

                <div class="col-lg-2">
                    <label class="input-title">Start Date</label>
                    <div class="input-group">
                        <input type="text" class="form-control datepicker-custom" id="start_date" name="StartDate"
                            placeholder="DD-MM-YYYY" value="{{ $startDate}}">
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">End Date</label>
                    <div class="input-group">
                        <input type="text" class="form-control datepicker-custom" id="end_date" name="EndDate"
                            placeholder="DD-MM-YYYY" value="{{ $endDate }}">
                    </div>
                </div>
                <div class="col-lg-2 pt-20 text-center">
                    <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round"
                        id="useSearch">Search</a>
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
                    <span>Uses Report</span><br>
                    (<span id="start_date_txt">{{ (new Datetime($startDate))->format('d-m-Y') }}</span>
                    To
                    <span id="end_date_txt">{{ (new Datetime($endDate))->format('d-m-Y') }}</span>)
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
                        onclick="fnDownloadExcel('ExportHeading,ExportDiv', 'Uses_Report_{{ (new Datetime())->format('d-m-Y') }}');">
                        <i class="fa fa-file-excel-o fa-lg" style="font-size:20px; margin-left: 5px;"></i>
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-6 col-lg-6 col-sm-6 col-md-6 col-6">
                    <!-- <span class="d-print-none"><b>Total Row:</b> <span id="totalRowDiv">0</span></span> -->
                </div>
                <div class="col-xl-6 col-lg-6 col-sm-6 col-md-6 col-6 text-right">
                    <span><b>Printed Date:</b> {{ date("d-m-Y") }} </span>
                </div>
            </div>

            <div class="row ExportDiv">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead>
                            <tr>
                                <th width="5%">SL</th>
                                <th width="10%">Uses Date</th>
                                <th width="15%">Uses Bill No</th>
                                <!-- <th>Barcode</th> -->
                                <th width="15%">Branch</th>
                                <th>Product Name</th>
                                <th width="8%">Quantity</th>
                                <!-- <th>Unit Price</th>
                                <th>Total Amount</th> -->
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" style="text-align:right!important;"><strong>Total</strong></td>
                                <td class="text-center"><strong id="TQuantity"></strong></td>
                               <!--  <td class="text-right"><strong id="TUnitPrice"></strong></td>
                                <td class="text-right"><strong id="TAmount">0.00</strong></td> -->
                            </tr>
                        </tfoot>
                    </table>
                    @include('../elements.signature.signatureSet')
                </div>
            </div>
        </div>
    </div>
</div>

<!-- End Page -->
<script>
function ajaxDataLoad(startDate = null, endDate = null, branchId = null, productId = null, groupId = null, catId = null,
    subCatId = null, modelID = null) {

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
            "url": "{{route('INVuseDataTableReport')}}",
            "dataType": "json",
            "type": "post",
            "data": {
                _token: "{{csrf_token()}}",
                startDate: startDate,
                endDate: endDate,
                branchId: branchId,
                productId: productId,
                groupId: groupId,
                catId: catId,
                subCatId: subCatId,
                modelID: modelID

            }
        },
        columns: [{
                data: 'id',
                className: 'text-center'
            },
            {
                data: 'uses_date'
            },
            {
                data: 'uses_bill_no'
            },
            // {
            //     data: 'product_code'
            // },
            {
                data: 'branch'
            },
            {
                data: 'product_name'
            },
            {
                data: 'product_quantity',
                className: 'text-center'
            },
            // {
            //     data: 'unit_cost_price',
            //     className: 'text-right'
            // },
            // {
            //     data: 'total_cost_price',
            //     className: 'text-right'
            // },

        ],
        drawCallback: function(oResult) {
            $('#totalRowDiv').html(oResult.json.totalRow);
            $('#TQuantity').html(oResult.json.totalQuantity);
            // $('#TUnitPrice').html(oResult.json.totalUnitPrice);
            // $('#TAmount').html(oResult.json.totalAmount);
        },
    });
}

$(document).ready(function() {

    ajaxDataLoad();

    $('#useSearch').click(function() {

        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();
        var branchId = $('#branch_id').val();
        var productId = $('#product_id').val();
        var groupId = $('#prod_group_id').val();
        var catId = $('#prod_cat_id').val();
        var subCatId = $('#prod_sub_cat_id').val();
        var modelID = $('#prod_model_id').val();


        ajaxDataLoad(startDate, endDate, branchId, productId, groupId, catId, subCatId, modelID);
    });
});

</script>
@endsection
