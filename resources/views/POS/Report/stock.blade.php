@extends('Layouts.erp_master_full_width')
@section('content')

<!-- Page -->
<?php
use App\Services\HrService as HRS;

$startDate = Common::systemCurrentDate();
$endDate = Common::systemCurrentDate();

$supplierData = Common::ViewTableOrderIn('pos_suppliers',
    [['is_delete', 0], ['is_active', 1]],
    ['branch_id', HRS::getUserAccesableBranchIds()],
    ['id', 'sup_name'],
    ['id', 'ASC']);

$groupData = Common::ViewTableOrder('pos_p_groups',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'group_name'],
    ['id', 'ASC']);

$categoryData = Common::ViewTableOrder('pos_p_categories',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'cat_name'],
    ['cat_name', 'ASC']);

$subCatData = Common::ViewTableOrder('pos_p_subcategories',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'sub_cat_name'],
    ['sub_cat_name', 'ASC']);

$brandData = Common::ViewTableOrder('pos_p_brands',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'brand_name'],
    ['brand_name', 'ASC']);

$modelData = Common::ViewTableOrder('pos_p_models',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'model_name'],
    ['id', 'ASC']);

$productData = Common::ViewTableOrder('pos_products',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'product_name'],
    ['id', 'ASC']);

$branchId = Common::getBranchId();

$branchInfo = Common::ViewTableFirst('gnl_branchs',
    [['is_delete', 0], ['is_active', 1],
        ['id', $branchId]],
    ['id', 'branch_name']);

$groupInfo = Common::ViewTableFirst('gnl_groups',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'group_name']);

?>

<div class="w-full">
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

                {!! HTML::forBranchFeildSearch_new() !!}

                <div class="col-lg-2">
                    <label class="input-title">Supplier</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="supplier_id" id="supplier_id">
                            <option value="">Select Option</option>
                            @foreach ($supplierData as $row)
                            <option value="{{ $row->id }}">{{ $row->sup_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Group</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="group_id" id="group_id" onchange="fnAjaxSelectBox('cat_id',
                                                        this.value,
                                                        '{{ base64_encode("pos_p_categories")}}',
                                                        '{{base64_encode("prod_group_id")}}',
                                                        '{{base64_encode("id,cat_name")}}',
                                                        '{{url("/ajaxSelectBox")}}');">

                            <option value="">Select Option</option>
                            @foreach($groupData as $row)
                            <option value="{{ $row->id}}">{{ $row->group_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Category</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="cat_id" id="cat_id" onchange="fnAjaxSelectBox('sub_cat_id',
                                                        this.value,
                                                        '{{base64_encode("pos_p_subcategories")}}',
                                                        '{{base64_encode("prod_cat_id")}}',
                                                        '{{base64_encode("id,sub_cat_name")}}',
                                                        '{{url("/ajaxSelectBox")}}');">
                            <option value="">Select Option</option>
                            @foreach ($categoryData as $row)
                            <option value="{{ $row->id }}">{{ $row->cat_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row align-items-center pb-10 d-print-none">
                <div class="col-lg-2">
                    <label class="input-title">Sub Category</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="sub_cat_id" id="sub_cat_id" onchange="fnAjaxSelectBox('brand_id',
                                                        this.value,
                                                        '{{base64_encode("pos_p_brands")}}',
                                                        '{{base64_encode("prod_sub_cat_id")}}',
                                                        '{{base64_encode("id,brand_name")}}',
                                                        '{{url("/ajaxSelectBox")}}');">
                            <option value="">Select Option</option>
                            @foreach ($subCatData as $row)
                            <option value="{{ $row->id }}">{{ $row->sub_cat_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Brand</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="brand_id" id="brand_id" onchange="fnAjaxSelectBox('model_id',
                                                        this.value,
                                                        '{{base64_encode("pos_p_models")}}',
                                                        '{{base64_encode("prod_brand_id")}}',
                                                        '{{base64_encode("id,model_name")}}',
                                                        '{{url("/ajaxSelectBox")}}');">
                            <option value="">Select Option</option>
                            @foreach ($brandData as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->brand_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Model</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="model_id" id="model_id" onchange="fnAjaxSelectBox('product_id',
                                                        this.value,
                                                        '{{base64_encode("pos_products")}}',
                                                        '{{base64_encode("prod_model_id")}}',
                                                        '{{base64_encode("id,product_name")}}',
                                                        '{{url("/ajaxSelectBox")}}');">
                            <option value="">Select Option</option>
                            @foreach ($modelData as $model)
                            <option value="{{ $model->id }}">{{ $model->model_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Product</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="product_id" id="product_id">
                            <option value="">Select Option</option>
                            @foreach ($productData as $product)
                            <option value="{{ $product->id }}">{{ $product->product_name }}</option>
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
                    <a href="javascript:void(0)" title="excel" style="background-color:transparent;border:none;"
                        onclick="fnDownloadExcel();">
                        <i class="fa fa-file-excel-o fa-lg" style="font-size:20px; margin-left: 5px;"></i>
                    </a>
                    <a href="javascript:void(0)" title="csv" style="background-color:transparent;border:none;"
                        onclick="fnDownloadCSV();">
                        <i class="fa fa-file-excel-o fa-lg" style="font-size:20px; margin-left: 5px;"></i>
                    </a>
                    <a href="javascript:void(0)" title="xlsx" style="background-color:transparent;border:none;"
                        onclick="fnDownloadXLSX();">
                        <i class="fa fa-file-excel-o fa-lg" style="font-size:20px; margin-left: 5px;"></i>
                    </a>
                </div>
            </div>

            <div class="row text-center d-none d-print-block">
                <div class="col-lg-12" style="color:#000;">
                    <strong>{{ $groupInfo->group_name }}</strong><br>
                    <strong>{{ $branchInfo->branch_name }}</strong><br>
                    <span>Stock Report</span><br>
                    (<span id="start_date_txt">{{ date('d-M-Y', strtotime($startDate)) }}</span>
                    to
                    <span id="end_date_txt">{{ date('d-M-Y', strtotime($endDate)) }}</span>)
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
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable" id="export-table" data-tableexport-display="always">
                        <thead>
                            <tr>
                                <th width="3%">SL#</th>
                                <th>Supplier</th>
                                <th>Product Name</th>
                                <th>Barcode</th>
                                <th>Details</th>
                                <th>O.Stock</th>
                                <th>Purchase</th>
                                <th>Purchase Return</th>
                                <th>Transfer In</th>
                                <th>Transfer Out</th>
                                <th>Sales</th>
                                <th>Sales Return</th>
                                <th>Adj</th>
                                <th>Stock</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <td colspan="5" style=""><b>TOTAL</b></td>

                                <td id="o_stock" style="font-color:#000; font-weight:bold;">
                                    0</td>
                                <td id="purchase" style="font-color:#000; font-weight:bold;">
                                    0</td>

                                <td id="purchase_r" style="font-color:#000; font-weight:bold;">0</td>

                                <td id="transfer_in" style="font-color:#000; font-weight:bold;">0</td>

                                <td id="transfer_out" style="font-color:#000; font-weight:bold;">0</td>

                                <td id="sales" style="font-color:#000; font-weight:bold;">0</td>

                                <td id="sales_r" style="font-color:#000; font-weight:bold;">0</td>

                                <td id="adj" style="font-color:#000; font-weight:bold;">0</td>
                                <td id="stock" style="font-color:#000; font-weight:bold;">0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {

    // ajaxDataLoad();

    $('#searchButton').click(function() {

        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();
        var branch_id = $('#branch_id').val();
        var supplier_id = $('#supplier_id').val();
        var group_id = $('#group_id').val();
        var cat_id = $('#cat_id').val();
        var sub_cat_id = $('#sub_cat_id').val();
        var brand_id = $('#brand_id').val();
        var model_id = $('#model_id').val();
        var product_id = $('#product_id').val();

        $('#start_date_txt').html(start_date);
        $('#end_date_txt').html(end_date);

        ajaxDataLoad(start_date, end_date, branch_id, supplier_id, group_id, cat_id, sub_cat_id,
            brand_id, model_id, product_id);
    });
});

function ajaxDataLoad(start_date = null, end_date = null, branch_id = null, supplier_id = null, group_id = null,
    cat_id = null, sub_cat_id = null, brand_id = null, model_id = null, product_id = null) {

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
            "url": "{{ url('pos/report/stock') }}",
            "dataType": "json",
            "type": "post",
            "data": {
                _token: "{{ csrf_token() }}",
                startDate: start_date,
                endDate: end_date,
                branchId: branch_id,
                supplierId: supplier_id,
                groupId: group_id,
                catId: cat_id,
                subCatId: sub_cat_id,
                brandId: brand_id,
                modelId: model_id,
                productId: product_id
            }
        },
        columns: [{
                data: 'id'
            },
            {
                data: 'sup_name',
                className: 'text-center'
            },
            {
                data: 'product_name',
                className: 'text-center'
            },
            {
                data: 'prod_barcode',
                className: 'text-center'
            },
            {
                data: 'details'
            },
            {
                data: 'openning_stock',
                className: 'text-center'
            },
            {
                data: 'purchase',
                className: 'text-center'
            },
            {
                data: 'purchase_return',
                className: 'text-center'
            },
            {
                data: 'transfer_in',
                className: 'text-center'
            },
            {
                data: 'transfer_out',
                className: 'text-center'
            },
            {
                data: 'sales',
                className: 'text-center'
            },
            {
                data: 'sales_return',
                className: 'text-center'
            },
            {
                data: 'adj',
                className: 'text-center'
            },
            {
                data: 'stock',
                className: 'text-center'
            },
        ],
        drawCallback: function(oResult) {

            if (oResult.json) {
                $('#totalRowDiv').html(oResult.json.totalRow);
                $('#o_stock').html(oResult.json.ttlOStock);
                $('#purchase').html(oResult.json.ttlPurchase);
                $('#purchase_r').html(oResult.json.ttlPurchaseR);
                $('#transfer_in').html(oResult.json.ttlTransferIn);
                $('#transfer_out').html(oResult.json.ttlTransferOut);
                $('#sales').html(oResult.json.ttlSales);
                $('#sales_r').html(oResult.json.ttlSalesR);
                $('#adj').html(oResult.json.ttlAdj);
                $('#stock').html(oResult.json.ttlStock);
            }
        },
    });
}

$('#branch_id').change(function() {

    fnAjaxSelectBox('supplier_id',
        this.value,
        '{{base64_encode("pos_suppliers")}}',
        '{{base64_encode("branch_id")}}',
        '{{base64_encode("id,sup_name")}}',
        '{{url("/ajaxSelectBox")}}');
});

function fnDownloadPDF() {
    $('#export-table').tableExport({
        outputMode: 'file',
        tableName: 'stock report',
        type: 'pdf',
        fileName: 'stock-report',
        trimWhitespace: true,               
        RTL: false, 
        jspdf: {
            orientation: 'p',
            unit:'pt',
            format: 'bestfit',
            autotable: {
                styles: {
                    cellPadding: 2,
                    rowHeight: 12,
                    fontSize: 8,
                    fillColor: 255,
                    textColor: 50,
                    fontStyle: 'normal',
                    overflow: 'ellipsize',
                    halign: 'inherit',
                    valign: 'middle',
                },
                tableWidth: 'auto',
            }
        }
    });
}

function fnDownloadExcel() {
    $('#export-table').tableExport({
        type: 'excel',
        fileName: 'stock-report',
    });
}

function fnDownloadCSV() {
    $('#export-table').tableExport({
        type: 'csv',
        fileName: 'stock-report',
    });
}

function fnDownloadXLSX() {
    $('#export-table').tableExport({
        type: 'xlsx',
        fileName: 'stock-report',
    });
}

</script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/tableExport.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.plugin.autotable.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/xlsx.core.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/FileSaver.min.js') }}"></script>
@endsection