@extends('Layouts.erp_master_full_width')
@section('content')

<?php
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
use App\Services\HrService as HRS;

$startDate = Common::systemCurrentDate();
$endDate = Common::systemCurrentDate();

$supplierData = Common::ViewTableOrder('inv_suppliers',
    [['is_delete', 0], ['is_active', 1], ['branch_id', 1]],
    ['id', 'sup_name'],
    ['id', 'ASC']);

$groupData = Common::ViewTableOrder('inv_p_groups',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'group_name'],
    ['id', 'ASC']);

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

$modelData = Common::ViewTableOrder('inv_p_models',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'model_name'],
    ['id', 'ASC']);

$productData = Common::ViewTableOrder('inv_products',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'product_name','prod_barcode'],
    ['id', 'ASC']);

$branchId = 1;

// $branchInfo = Common::ViewTableFirst('gnl_branchs',
//     [['is_delete', 0], ['is_active', 1],
//         ['id', $branchId]],
//     ['id', 'branch_name']);

$groupInfo = Common::ViewTableFirst('gnl_groups',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'group_name']);

?>

<div class="w-full d-print-none">
    <div class="panel">
        <div class="panel-body">
            <div class="row align-items-center pb-10 d-print-none">


                {{-- {!! HTML::forBranchFeildSearch_new() !!} --}}


                <div class="col-lg-2">
                    <label class="input-title">Group</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="group_id" id="group_id" onchange="fnAjaxSelectBox('cat_id',
                                            this.value,
                                            '{{ base64_encode("inv_p_categories")}}',
                                            '{{base64_encode("prod_group_id")}}',
                                            '{{base64_encode("id,cat_name")}}',
                                            '{{url("/ajaxSelectBox")}}');
                                            fnAjaxSelectBox('product_id',
                                                        this.value,
                                                        '{{base64_encode("inv_products")}}',
                                                        '{{base64_encode("prod_group_id")}}',
                                                        '{{base64_encode("id,product_name")}}',
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
                                                '{{base64_encode("inv_p_subcategories")}}',
                                                '{{base64_encode("prod_cat_id")}}',
                                                '{{base64_encode("id,sub_cat_name")}}',
                                                '{{url("/ajaxSelectBox")}}');
                                                fnAjaxSelectBox('product_id',
                                                        this.value,
                                                        '{{base64_encode("inv_products")}}',
                                                        '{{base64_encode("prod_cat_id")}}',
                                                        '{{base64_encode("id,product_name")}}',
                                                        '{{url("/ajaxSelectBox")}}');">
                            <option value="">Select Option</option>
                            @foreach ($categoryData as $row)
                            <option value="{{ $row->id }}">{{ $row->cat_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Sub Category</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="sub_cat_id" id="sub_cat_id" onchange="fnAjaxSelectBox('model_id',
                                                        this.value,
                                                        '{{base64_encode("inv_p_models")}}',
                                                        '{{base64_encode("prod_sub_cat_id")}}',
                                                        '{{base64_encode("id,model_name")}}',
                                                        '{{url("/ajaxSelectBox")}}');
                                                        fnAjaxSelectBox('product_id',
                                                        this.value,
                                                        '{{base64_encode("inv_products")}}',
                                                        '{{base64_encode("prod_sub_cat_id")}}',
                                                        '{{base64_encode("id,product_name")}}',
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
                        <select class="form-control clsSelect2" name="brand_id" id="brand_id" onchange="fnAjaxSelectBox('product_id',
                                                        this.value,
                                                        '{{base64_encode("inv_products")}}',
                                                        '{{base64_encode("prod_brand_id")}}',
                                                        '{{base64_encode("id,product_name")}}',
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
                                                        '{{base64_encode("inv_products")}}',
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
                          <option value="{{ $product->id }}">{{ $product->product_name."(". $product->prod_barcode.")" }}</option>
                          @endforeach
                      </select>
                  </div>
              </div>

            </div>

            <div class="row align-items-center pb-10 d-print-none">
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
                    <label class="input-title">Stock</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="stockSearch" id="stockSearch">
                            <option value="0">With Zero</option>
                            <option value="1">Without Zero</option>
                        </select>
                    </div>
                </div>

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

                <div class="col-lg-2 pt-20 text-center">
                    <a href="javascript:void(0)" name="searchButton" id="searchButton"
                        class="btn btn-primary btn-round">Search</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="w-full">
    <div class="panel">
        <div class="panel-body">

            <div class="row d-print-block text-dark ExportHeading">
                <div class="col-xl-12 col-lg-12 col-sm-12 col-md-12 col-12" style="text-align:center;">
                    <br>
                    <strong>{{ $groupInfo->group_name }}</strong><br>
                    <strong>Head Office</strong><br>
                    <span>Stock Report (Head Office)</span><br>
                    (<span id="start_date_txt">{{ (new Datetime($startDate))->format('d-m-Y') }}</span>
                    TO
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
                    onclick="fnDownloadExcel('ExportHeading,ExportDiv', 'Stock_Report_HO_{{ (new Datetime())->format('d-m-Y') }}');">
                        <i class="fa fa-file-excel-o fa-lg" style="font-size:20px; margin-left: 5px;"></i>
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-6 col-lg-6 col-sm-6 col-md-6 col-6">
                    <span class="d-print-none"><b>Total Row:</b> <span id="totalRowDiv">0</span></span>
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
                                <th width="3%">SL</th>
                                <th>Product Name</th>
                                <th>Opening Balance</th>
                                <th>Purchase</th>
                                <th>Purchase Return</th>
                                <th>Issue</th>
                                <th>Issue Return</th>
                                <th>Adj</th>
                                <th>Stock</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" style="text-align:right!important;">TOTAL</td>

                                <td class="text-center text-dark font-weight-bold" id="o_stock">0</td>

                                <td class="text-center text-dark font-weight-bold" id="purchase">0</td>

                                <td class="text-center text-dark font-weight-bold" id="purchase_r">0</td>

                                <td class="text-center text-dark font-weight-bold" id="issue">0</td>

                                <td class="text-center text-dark font-weight-bold" id="issue_r">0</td>

                                <td class="text-center text-dark font-weight-bold" id="adj">0</td>

                                <td class="text-center text-dark font-weight-bold" id="stock">0</td>
                            </tr>
                        </tfoot>
                    </table>
                   @include('../elements.signature.signatureSet')
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {

    $('#searchButton').click(function() {

        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();
        // var branch_id = $('#branch_id').val();
        var supplier_id = $('#supplier_id').val();
        var group_id = $('#group_id').val();
        var cat_id = $('#cat_id').val();
        var sub_cat_id = $('#sub_cat_id').val();
        var brand_id = $('#brand_id').val();
        var model_id = $('#model_id').val();
        var product_id = $('#product_id').val();
        var stockSearch = $('#stockSearch').val();

        $('#start_date_txt').html(start_date);
        $('#end_date_txt').html(end_date);

        ajaxDataLoad(start_date, end_date, supplier_id, group_id, cat_id, sub_cat_id,
            brand_id, model_id, product_id, stockSearch);
    });
});

function ajaxDataLoad(start_date = null, end_date = null, supplier_id = null, group_id = null,
    cat_id = null, sub_cat_id = null, brand_id = null, model_id = null, product_id = null, stockSearch = null) {

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
            "url": "{{ url('inv/report/stock_ho') }}",
            "dataType": "json",
            "type": "post",
            "data": {
                startDate: start_date,
                endDate: end_date,
                supplierId: supplier_id,
                groupId: group_id,
                catId: cat_id,
                subCatId: sub_cat_id,
                brandId: brand_id,
                modelId: model_id,
                productId: product_id,
                stockSearch: stockSearch
            }
        },
        columns: [{
                data: 'id'
            },
            {
                data: 'product_name'
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
                data: 'Issue',
                className: 'text-center'
            },
            {
                data: 'IssueReturn',
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
                $('#issue').html(oResult.json.ttlIssue);
                $('#issue_r').html(oResult.json.ttlIssueR);
                $('#adj').html(oResult.json.ttlAdj);
                $('#stock').html(oResult.json.ttlStock);
            }
        },
    });
}

// function getDownloadPDF() {
//     $('.clsDataTablecopy').tableExport({
//         type: 'pdf',
//         fileName: 'ho_stock_report',
//         jspdf: {
//             orientation: 'l',
//             format: 'a4',
//             margins: {
//                 left: 10,
//                 right: 10,
//                 top: 20,
//                 bottom: 20
//             },
//             autotable: {
//                 styles: {
//                     overflow: 'linebreak'
//                 },
//                 tableWidth: 'auto'
//             }
//         }
//     });
// }
</script>
<!-- {{-- for save file which is download --}}
<script type="text/javascript" src="{{ asset('assets/js/pdf/tableExport.min.js') }}"></script>

{{-- for export datatable into pdf --}}
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.plugin.autotable.js') }}"></script> -->

@endsection
