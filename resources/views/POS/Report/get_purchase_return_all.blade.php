@extends('Layouts.erp_master_full_width')
@section('content')

<script type="text/javascript" src="{{ asset('assets/js/pdf/tableExport.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.plugin.autotable.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/xlsx.core.min.js') }}"></script>
<!-- Page -->
<?php

$startDate = Common::systemCurrentDate();
$endDate = Common::systemCurrentDate();

$productData = Common::ViewTableOrder('pos_products',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'product_name'],
    ['product_name', 'ASC']);

$brandData = Common::ViewTableOrder('pos_p_brands',
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

<div class="w-full" style="min-height: calc(100% - 44px)">

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

                <div class="col-lg-2">
                    <label class="input-title">Product name</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="product_id" id="product_id">
                            <option value="">Select All</option>
                            @foreach ($productData as $Row)
                            <option value="{{ $Row->id }}">{{ $Row->product_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-2 pt-20 text-center">
                    <a href="javascript:void(0)" name="searchButton" id="searchButton"
                        class="btn btn-primary btn-round">Search</a>
                </div>
            </div>




            <div class="row d-print-none text-right">
                <div class="col-lg-12">
                    <a href="javascript:void(0)" onClick="window.print();"
                        style="background-color:transparent;border:none;" class="btnPrint mr-2">
                        <i class="fa fa-print fa-lg" style="font-size:20px;"></i>
                    </a>
                    <a href="javascript:void(0)" style="background-color:transparent;border:none;"
                        onclick="fnDownloadPDF();">
                        <i class="fa fa-file-pdf-o fa-lg" style="font-size:20px;"></i>
                    </a>
                    <a href="javascript:void(0)" style="background-color:transparent;border:none;"
                        onclick="fnDownloadExcel();">
                        <i class="fa fa-file-excel-o fa-lg" style="font-size:20px; margin-left: 5px;"></i>
                        {{-- <i class="fa fa-file-pdf-o fa-lg" style="font-size:20px;"></i> --}}
                    </a>
                </div>
            </div>

            <div class="row text-center d-none d-print-block">
                <div class="col-lg-12" style="color:#000;">
                    <strong>{{ $groupInfo->group_name }}</strong><br>
                    <strong>{{ $branchInfo->branch_name }}</strong><br>
                    <span>Purchase Return Report</span><br>
                    (<span id="start_date_txt">{{$startDate }}</span>
                    to
                    <span id="end_date_txt">{{$endDate }}</span>)
                </div>
            </div>


            <div class="row">
                <div class="col-xl-6 col-lg-6 col-sm-6 col-md-6 col-6">
                    <span class="d-print-none"><b>Total Row:</b> <span id="totalRowDiv">0</span></span>
                </div>
                <div class="col-xl-6 col-lg-6 col-sm-6 col-md-6 col-6 text-right">
                    <span><b>Printed Date:</b> {{ date("d-m-Y") }} </span>
                </div>
            </div>

            <div class="row">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead>
                            <tr>
                                <th width="3%">#</th>
                                <th class="text-center">Return Date</th>
                                <th>Product Name</th>
                                <th>Product Quantity</th>
                                <th>Unit Price</th>
                                <th class="text-center">Total Amount</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-right"><b>TOTAL</b></td>
                                <td class="text-center" id="product_quantity"
                                    style="font-color:#000; font-weight:bold;">
                                    0</td>
                                <td class="text-right" id="unit_cost_price" style="font-color:#000; font-weight:bold;">
                                    0</td>

                                <td class="text-right" id="total_cost_price" style="font-color:#000; font-weight:bold;">
                                    0.00</td>

                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function ajaxDataLoad(start_date = null, end_date = null, product_id = null) {

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
            "url": "{{ route('PurReturnDataTable') }}",
            "dataType": "json",
            "type": "post",
            "data": {
                _token: "{{ csrf_token() }}",
                startDate: start_date,
                endDate: end_date,
                productId: product_id
            }
        },
        columns: [{
                data: 'id'
            },
            {
                data: 'return_date'
            },
            {
                data: 'product_name'
            },
            {
                data: 'product_quantity'
            },
            {
                data: 'unit_cost_price'
            },
            {
                data: 'total_cost_price',
                className: 'text-center'
            },
        ],
        drawCallback: function(oResult) {

            if (oResult.json) {
                $('#totalRowDiv').html(oResult.json.totalRow);
                $('#product_quantity').html(oResult.json.product_quantity);
                $('#unit_cost_price').html(oResult.json.unit_cost_price);
                $('#total_cost_price').html(oResult.json.total_cost_price);
            }
        },
    });
}

$(document).ready(function() {

    // ajaxDataLoad();

    $('#searchButton').click(function() {

        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();
        var product_id = $('#product_id').val();
        $('#start_date_txt').html(start_date);
        $('#end_date_txt').html(end_date);
        ajaxDataLoad(start_date, end_date, product_id);
    });
});

function fnDownloadPDF() {
    $('.clsDataTable').tableExport({
        type: 'pdf',
        fileName: 'purchase return report',
        jspdf: {
            orientation: 'l',
            format: 'a4',
            margins: {
                left: 10,
                right: 10,
                top: 20,
                bottom: 20
            },
            autotable: {
                styles: {
                    overflow: 'linebreak'
                },
                tableWidth: 'auto'
            }
        }
    });
}

function fnDownloadExcel() {
    $('.clsDataTable').tableExport({
        type: 'excel',
        fileName: 'purchase return report',
    });
}
</script>
@endsection