@extends('Layouts.erp_master_full_width')
@section('content')

<?php 
use App\Services\CommonService as Common;
?>

<?php
use App\Services\HrService as HRS;

$startDate = Common::systemCurrentDate();
$endDate = Common::systemCurrentDate();

$areaData = Common::ViewTableOrder('gnl_areas',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'area_name'],
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


$branchId = Common::getBranchId();

$branchInfo = Common::ViewTableFirst('gnl_branchs',
    [['is_delete', 0], ['is_active', 1],['id', $branchId]],
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
                        <input type="text" class="form-control datepicker-custom" id="start_date" name="StartDate" placeholder="DD-MM-YYYY" value="{{ $startDate }}">
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">End Date</label>
                    <div class="input-group">
                        <input type="text" class="form-control datepicker-custom" id="end_date" name="EndDate" placeholder="DD-MM-YYYY" value="{{ $endDate }}">
                    </div>
                </div>
                <div class="col-lg-2">
                    <label class="input-title">Area</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="area_id" id="area_id">

                            <option value="">Select Option</option>
                            @foreach($areaData as $row)
                            <option value="{{ $row->id}}">{{ $row->area_name}}</option>
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
            </div>

            <div class="row align-items-center pb-10 d-print-none">
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
                            @foreach ($brandData as $brand)
                            <option value="{{ $brand->id }}">{{ $brand->brand_name }}</option>
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
                <div class="col-lg-2 pt-20 text-center">
                    <a href="javascript:void(0)" name="searchButton" id="searchButton" class="btn btn-primary btn-round">Search</a>
                </div>
            </div>
            <div class= "row d-print-none text-right" data-html2canvas-ignore="true">
                <div class="col-lg-12">
                    <a href="javascript:void(0)" onClick="window.print();" style="background-color:transparent;border:none;" class="btnPrint mr-2">
                        <i class="fa fa-print fa-lg" style="font-size:20px;"></i>
                    </a>
                    <a href="javascript:void(0)" style="background-color:transparent;border:none;" onclick="fnDownloadPDF();">
                        <i class="fa fa-file-pdf-o fa-lg" style="font-size:20px;"></i>
                    </a>
                    <a href="javascript:void(0)" title="excel" style="background-color:transparent;border:none;" onclick="fnDownloadExcel();">
                        <i class="fa fa-file-excel-o fa-lg" style="font-size:20px; margin-left: 5px;"></i>
                    </a>

                </div>
            </div>

            <div class="row text-center d-none d-print-block">
                <div class="col-lg-12" style="color:#000;">
                    <strong>{{ $groupInfo->group_name }}</strong><br>
                    <strong>{{ $branchInfo->branch_name }}</strong><br>
                    <span>Sales Report</span><br>
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
                                <th width="3%" rowspan="2">#</th>
                                <th rowspan="2">Branch Name</th>
                                <th colspan="2">Number Of Customer</th>
                                <th colspan="2">Number Of Quantity</th>
                                <th colspan="2">Amount</th>
                                <th colspan="3">Total</th>
                            </tr>
                            <tr>
                                <th>Cash</th>
                                <th>Credit</th>
                                <th>Cash</th>
                                <th>Credit</th>
                                <th>Cash</th>
                                <th>Credit</th>
                                <th>Customer</th>
                                <th>Quantity</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="2" style="text-align:right!important;"><b>Total</b></td>
                                <td id="nCusCash"><b></b></td>
                                <td id="nCusCredit"><b></b></td>
                                <td class="text-center" id="nQtnCash"><b></b></td>
                                <td class="text-center" id="nQtnCredit"><b></b></td>
                                <td class="text-right" id="amtCash"><b>0.00</b></td>
                                <td class="text-right" id="amtCredit"><b>0.00</b></td>
                                <td id="tcustomer"><b>0.00</b></td>
                                <td class="text-center" id="tQtn"><b>0.00</b></td>
                                <td class="text-right" id="tAmt"><b>0.00</b></td>
                            </tr>
                        </tbody>
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
            var area_id = $('#area_id').val();
            var group_id = $('#group_id').val();
            var cat_id = $('#cat_id').val();
            var sub_cat_id = $('#sub_cat_id').val();
            var brand_id = $('#brand_id').val();
            var model_id = $('#model_id').val();

            $('#start_date_txt').html(start_date);
            $('#end_date_txt').html(end_date);

            ajaxDataLoad(start_date, end_date, area_id,  group_id, cat_id, sub_cat_id,
                brand_id, model_id);
        });
    });

    function ajaxDataLoad(start_date = null, end_date = null, area_id = null, group_id = null,
        cat_id = null, sub_cat_id = null, brand_id = null, model_id = null) {

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
                "url": "{{ route('AreaSalesDatatable') }}",
                "dataType": "json",
                "type": "post",
                "data": {
                    startDate: start_date,
                    endDate: end_date,
                    areaId: area_id,
                    supplierId: supplier_id,
                    groupId: group_id,
                    catId: cat_id,
                    subCatId: sub_cat_id,
                    brandId: brand_id,
                    modelId: model_id
                }
            },
            columns: [{
                    data: 'sl',
                    className: 'text-left'
                },
                {
                    data: 'branch_name'
                },
                {
                    data: 'nCCash'
                },
                {
                    data: 'nCCredit'
                },
                {
                    data: 'nQCash',
                    className: 'text-center'
                },
                {
                    data: 'nQCredit',
                    className: 'text-center'
                },
                {
                    data: 'amt_Cash',
                    className: 'text-right'
                },
                {
                    data: 'amt_Credit',
                    className: 'text-right'
                },
                {
                    data: 'tCustomer'
                },
                {
                    data: 'tQtn',
                    className: 'text-center'
                },
                {
                    data: 'tAmt',
                    className: 'text-right'
                },

            ],
            drawCallback: function(oResult) {

                if (oResult.json) {
                    $('#totalRowDiv').html(oResult.json.totalRow);
                    $('#nCusCash').html(oResult.json.ttl_ncus_cash);
                    $('#nCusCredit').html(oResult.json.ttl_ncus_credit);
                    $('#nQtnCash').html(oResult.json.ttl_nqtn_cash);
                    $('#nQtnCredit').html(oResult.json.ttl_nqtn_credit);
                    $('#amtCash').html(oResult.json.ttl_amt_cash);
                    $('#amtCredit').html(oResult.json.ttl_amt_credit);
                    $('#tcustomer').html(oResult.json.ttl_customer);
                    $('#tQtn').html(oResult.json.ttl_qtn);
                    $('#tAmt').html(oResult.json.ttl_amt);
                }
            },
        });
    }

    function fnDownloadPDF() {
        $('#export-table').tableExport({
            outputMode: 'file',
            tableName: 'sales report',
            type: 'pdf',
            fileName: 'sales report',
            trimWhitespace: true,
            RTL: false,
            displayTableName: false,
            ignoreColumn: [1, 4],
            jspdf: {
                orientation: 'p',
                unit: 'pt',
                format: 'a3',
            }
        });
    }

    function fnDownloadXLSX() {
        $('#export-table').tableExport({
            type: 'xlsx',
            fileName: 'sales report',
            displayTableName: false,
            ignoreColumn: [1, 4],
        });
    }
</script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/tableExport.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.plugin.autotable.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/xlsx.core.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/FileSaver.min.js') }}"></script>
@endsection
