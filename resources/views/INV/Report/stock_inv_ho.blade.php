@extends('Layouts.erp_master_full_width')
@section('content')

<?php
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
use App\Services\HrService as HRS;

$startDate = Common::systemCurrentDate();
$endDate = Common::systemCurrentDate();

$supplierData = Common::ViewTableOrderIn('inv_suppliers',
                [['is_delete', 0], ['is_active', 1]],
                ['branch_id', HRS::getUserAccesableBranchIds()],
                ['id', 'sup_name'],
                ['id', 'ASC']);

$zoneData = Common::ViewTableOrder('gnl_zones',
              [['is_delete', 0], ['is_active', 1]],
              ['id', 'zone_name'],
              ['id', 'ASC']);

$areaData = Common::ViewTableOrder('gnl_areas',
            [['is_delete', 0], ['is_active', 1]],
            ['id', 'area_name'],
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

                {{-- <div class="col-lg-2">
                    <label class="input-title">Zone</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="zone_id" id="zone_id">
                            <option value="">Select Option</option>
                            @foreach ($zoneData as $row)
                            <option value="{{ $row->id }}">{{ $row->zone_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-2">
                    <label class="input-title">Area</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="area_id" id="area_id">
                            <option value="">Select Option</option>
                            @foreach ($areaData as $row)
                            <option value="{{ $row->id }}">{{ $row->area_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div> --}}

                {{-- {!! HTML::forBranchFeildSearch_new() !!} --}}


                <div class="col-lg-2">
                    <label class="input-title">Group</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="group_id" id="group_id" onchange="fnAjaxSelectBox('cat_id',
                                                        this.value,
                                                        '{{ base64_encode("inv_p_categories")}}',
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
                                                        '{{base64_encode("inv_p_subcategories")}}',
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
                <div class="col-lg-2">
                    <label class="input-title">Sub Category</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="sub_cat_id" id="sub_cat_id" onchange="fnAjaxSelectBox('brand_id',
                                                          this.value,
                                                          '{{base64_encode("inv_p_brands")}}',
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
                                                          '{{base64_encode("inv_p_models")}}',
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
                            <option value="{{ $product->id }}">{{ $product->product_name."(".$product->prod_barcode.")" }}</option>
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
                        <select class="form-control clsSelect2" name="stock" id="stock">
                            <option value="0">With Zero</option>
                            <option value="1">Without Zero</option>
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Start Date</label>
                    <div class="input-group">
                        <input type="text" class="form-control datepicker-custom" id="start_date" name="end_date" placeholder="DD-MM-YYYY" value="{{ $startDate }}">
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">End Date</label>
                    <div class="input-group">
                        <input type="text" class="form-control datepicker-custom" id="end_date" name="end_date" placeholder="DD-MM-YYYY" value="{{ $endDate }}">
                    </div>
                </div>

                <div class="col-lg-2 pt-20 text-center">
                    <a href="javascript:void(0)" name="searchButton" id="searchButton" class="btn btn-primary btn-round">Search</a>
                </div>
            </div>

            {{-- <div class="text-center">

            </div> --}}
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
                    <span>Stock Report- Inventory</span><br>
                    (<span id="start_date_txt">{{ (new Datetime($startDate))->format('d-m-Y') }}</span>
                    TO
                    <span id="end_date_txt">{{ (new Datetime($endDate))->format('d-m-Y') }}</span>)
                    <br><br>
                </div>
            </div>

            <div class="row d-print-none text-right">
                <div class="col-lg-12">
                    <a href="javascript:void(0)" onClick="window.print();" style="background-color:transparent;border:none;" class="btnPrint mr-2">
                        <i class="fa fa-print fa-lg" style="font-size:20px;"></i>
                    </a>
                    <a href="javascript:void(0)" style="background-color:transparent;border:none;"
                    onclick="getDownloadPDF();">
                        <i class="fa fa-file-pdf-o fa-lg" style="font-size:20px; margin-left: 5px;"></i>
                    </a>

                    <a href="javascript:void(0)" title="Excel" style="background-color:transparent;border:none;"
                    onclick="fnDownloadExcel('ExportHeading,ExportDiv', 'Stock_Report_HO_Inv_{{ (new Datetime())->format('d-m-Y') }}');">
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
                        <thead class="text-center">
                            <tr>
                                <th rowspan="3" width="3%">SL</th>
                                <th rowspan="3" width="12%">Product Name</th>
                                <th rowspan="2" colspan="2">Opening Stock</th>
                                <th colspan="6" >Add Current Prieod</th>
                                <th colspan="4" >Less Current Prieod</th>
                                <th rowspan="2" colspan="2">Closing Stock</th>
                            </tr>
                            <tr>
                                <th colspan="2">Purchases</th>
                                <th colspan="2">Issue Return</th>
                                <th colspan="2">Adjustment</th>
                                <th colspan="2">Purchase Return</th>
                                <th colspan="2">Issue</th>
                                <!-- {{-- <th colspan="2">Adjustment</th> --}} -->
                            </tr>
                            <tr>
                                <th>Qty</th>
                                <th>Amount</th>

                                <th>Qty</th>
                                <th>Amount</th>

                                <th>Qty</th>
                                <th>Amount</th>

                                <th>Qty</th>
                                <th>Amount</th>

                                <th>Qty</th>
                                <th>Amount</th>

                                <th>Qty</th>
                                <th>Amount</th>

                                <th>Qty</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="text-right"><b>TOTAL:</b></td>
                                <td class="text-center"><b id="oSQtn">0</b></td>
                                <td class="text-right"><b id="oSAmt">0.00</b></td>

                                <td class="text-center"><b id="pQtn">0</b></td>
                                <td class="text-right"><b id="pAmt">0.00</b></td>

                                <td class="text-center"><b id="isRQtn">0</b></td>
                                <td class="text-right"><b id="isRAmt">0.00</b></td>

                                <td class="text-center"><b id="adjQtn">0</b></td>
                                <td class="text-right"><b id="adjAmt">0.00</b></td>

                                <td class="text-center"><b id="pRQtn">0</b></td>
                                <td class="text-right"><b id="pRAmt">0.00</b></td>

                                <td class="text-center"><b id="isQtn">0</b></td>
                                <td class="text-right"><b id="isAmt">0.00</b></td>

                                <td class="text-center"><b id="cStockQtn">0</b></td>
                                <td class="text-right"><b id="cStockAmt">0.00</b></td>
                            </tr>
                        </tfoot>
                    </table>
                     @include('../elements.signature.signatureSet')
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* customprint */
    @media print {
        @page {
            size: landscape;
            margin: 10px!important;
        }

        .table > thead th {
            font-size: 80%!important;
            padding: 2x!important;
        }

        .table > tbody td {
            font-size: 80%!important;
            padding: 2x!important;
        }

        .table > tfoot td {
            font-size: 80%!important;
            padding: 2x!important;
        }

    }
</style>

<script>
    $(document).ready(function() {

        $('#searchButton').click(function() {

            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            var zone_id = $('#zone_id').val();
            var area_id = $('#area_id').val();
            var supplier_id = $('#supplier_id').val();
            var group_id = $('#group_id').val();
            var cat_id = $('#cat_id').val();
            var sub_cat_id = $('#sub_cat_id').val();
            var brand_id = $('#brand_id').val();
            var model_id = $('#model_id').val();
            var product_id = $('#product_id').val();
            var stock = $('#stock').val();

            $('#start_date_txt').html(start_date);
            $('#end_date_txt').html(end_date);

            ajaxDataLoad(start_date, end_date, zone_id, area_id, supplier_id, group_id, cat_id, sub_cat_id, brand_id, model_id, product_id, stock);
        });
    });

    function ajaxDataLoad(start_date = null, end_date = null, zone_id = null, area_id = null, supplier_id = null, group_id = null, cat_id = null, sub_cat_id = null, brand_id = null, model_id = null, product_id = null, stock = null) {

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
                "url": "{{ url('inv/report/stock_inv_ho') }}",
                "dataType": "json",
                "type": "post",
                "data": {
                    _token: "{{ csrf_token() }}",
                    startDate: start_date,
                    endDate: end_date,
                    zoneId: zone_id,
                    areaId: area_id,
                    supplierId: supplier_id,
                    groupId: group_id,
                    catId: cat_id,
                    subCatId: sub_cat_id,
                    brandId: brand_id,
                    modelId: model_id,
                    productId: product_id,
                    stock: stock
                }
            },
            columns: [{
                    data: 'id'
                },
                {
                    data: 'product_name'
                },
                {
                    data: 'openning_stock_qtn',
                    className: 'text-center'
                },
                {
                    data: 'openning_stock_amt',
                    className: 'text-right'
                },
                {
                    data: 'purchase_qtn',
                    className: 'text-center'
                },
                {
                    data: 'purchase_amt',
                    className: 'text-right'
                },
                {
                    data: 'issue_return_qtn',
                    className: 'text-center'
                },
                {
                    data: 'issue_return_amt',
                    className: 'text-right'
                },
                {
                    data: 'adj_qtn',
                    className: 'text-center'
                },
                {
                    data: 'adj_amt',
                    className: 'text-right'
                },
                {
                    data: 'purchase_return_qtn',
                    className: 'text-center'
                },
                {
                    data: 'purchase_return_amt',
                    className: 'text-right'
                },
                {
                    data: 'issue_qtn',
                    className: 'text-center'
                },
                {
                    data: 'issue_amt',
                    className: 'text-right'
                },
                {
                    data: 'stock_qtn',
                    className: 'text-center'
                },
                {
                    data: 'stock_amt',
                    className: 'text-right'
                }
            ],
            drawCallback: function(oResult) {

                if (oResult.json) {
                    $('#totalRowDiv').html(oResult.json.totalRow);
                    $('#oSQtn').html(oResult.json.os_ttl_qtn);
                    $('#oSAmt').html(oResult.json.os_ttl_amt);

                    $('#pQtn').html(oResult.json.pur_ttl_qtn);
                    $('#pAmt').html(oResult.json.pur_ttl_amt);

                    $('#isRQtn').html(oResult.json.isr_ttl_qtn);
                    $('#isRAmt').html(oResult.json.isr_ttl_amt);

                    $('#adjQtn').html(oResult.json.adj_ttl_qtn);
                    $('#adjAmt').html(oResult.json.adj_ttl_amt);

                    $('#pRQtn').html(oResult.json.pr_ttl_qtn);
                    $('#pRAmt').html(oResult.json.pr_ttl_amt);

                    $('#isQtn').html(oResult.json.is_ttl_qtn);
                    $('#isAmt').html(oResult.json.is_ttl_amt);

                    $('#cStockQtn').html(oResult.json.stock_ttl_qtn);
                    $('#cStockAmt').html(oResult.json.stock_ttl_amt);
                }
            }
        });
    }
</script>
@endsection
