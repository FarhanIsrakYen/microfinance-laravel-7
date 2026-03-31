@extends('Layouts.erp_master_full_width')
@section('content')

<?php 
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
use App\Services\HrService as HRS;

$startDate = Common::systemCurrentDate();
$endDate = Common::systemCurrentDate();

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
                        <input type="text" class="form-control datepicker-custom" id="start_date" name="StartDate" placeholder="DD-MM-YYYY" value="{{ $startDate }}">
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">End Date</label>
                    <div class="input-group">
                        <input type="text" class="form-control datepicker-custom" id="end_date" name="EndDate" placeholder="DD-MM-YYYY" value="{{ $endDate }}">
                    </div>
                </div>

                {!! HTML::forBranchFeildSearch_new('all')!!}

                <div class="col-lg-2">
                    <label class="input-title">Group</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="group_id" id="group_id" onchange="fnAjaxSelectBox('cat_id',
                                                        this.value,
                                                        '{{ base64_encode("inv_p_categories")}}',
                                                        '{{base64_encode("prod_group_id")}}',
                                                        '{{base64_encode("id,cat_name")}}',
                                                        '{{url("/ajaxSelectBox")}}');

                                                        fnAjaxSelectBox('sub_cat_id',
                                                                        this.value,
                                                                        '{{ base64_encode("inv_p_subcategories")}}',
                                                                        '{{base64_encode("prod_group_id")}}',
                                                                        '{{base64_encode("id,sub_cat_name")}}',
                                                                        '{{url("/ajaxSelectBox")}}');

                                                        fnAjaxSelectBox('model_id',this.value,
                                                                        '{{ base64_encode("inv_p_models")}}',
                                                                        '{{base64_encode("prod_group_id")}}',
                                                                        '{{base64_encode("id,model_name")}}',
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
                                                        fnAjaxSelectBox('model_id',
                                                                    this.value,
                                                                    '{{base64_encode("inv_p_models")}}',
                                                                    '{{base64_encode("prod_sub_cat_id")}}',
                                                                    '{{base64_encode("id,model_name")}}',
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
                        <select class="form-control clsSelect2" name="brand_id" id="brand_id" >
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
                <div class="col-lg-2 pt-20 text-center">
                    <a href="javascript:void(0)" name="searchButton" id="searchButton" class="btn btn-primary btn-round">Search</a>
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
                    <span>Total Sales Summary Report</span><br>
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
                        onclick="fnDownloadExcel('ExportHeading,ExportDiv', 'Sales_Summary_Report_{{ (new Datetime())->format('d-m-Y') }}');">
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
                                <th width="5%">SL</th>
                                <th>Branch Name</th>
                                <th>Total Quantity</th>
                                <th>Total Sales Amount (With PF.+Profit)</th>
                                <th>1st Installment</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" style="text-align:right!important;"><b>Total</b></td>
                                <td class="text-center"><b id="tQtn">0</b></td>
                                <td class="text-right" ><b id="tSAmt">0.00</b></td>
                                <td class="text-right" ><b id="fInstall">0.00</b></td>
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

        $('#searchButton').click(function() {

            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            var branch_id = $('#branch_id').val();
            var group_id = $('#group_id').val();
            var cat_id = $('#cat_id').val();
            var sub_cat_id = $('#sub_cat_id').val();
            var brand_id = $('#brand_id').val();
            var model_id = $('#model_id').val();

            $('#start_date_txt').html(start_date);
            $('#end_date_txt').html(end_date);

            ajaxDataLoad(start_date,end_date,branch_id, group_id,cat_id,sub_cat_id,
                brand_id,model_id);
        });
    });

    function ajaxDataLoad(start_date = null, end_date = null, branch_id = null, group_id = null,
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
                "url": "{{ route('INVTSalesSummaryDatatable') }}",
                "dataType": "json",
                "type": "post",
                "data": {
                    startDate: start_date,
                    endDate: end_date,
                    branchId: branch_id,
                    groupId: group_id,
                    catId: cat_id,
                    subCatId: sub_cat_id,
                    brandId: brand_id,
                    modelId: model_id
                }
            },
            columns: [{
                    data: 'sl',
                    className: 'text-center'
                },
                {
                    data: 'branch_name',
                },
                {
                    data: 'product_qtn',
                    className: 'text-center'
                },
                {
                    data: 'total_sales_amt',
                    className: 'text-right'
                },
                {
                  data: 'first_installment',
                  className: 'text-right'
                },
            ],
            drawCallback: function(oResult) {

                if (oResult.json) {
                    $('#totalRowDiv').html(oResult.json.totalRow);
                    $('#tQtn').html(oResult.json.total_quantity);
                    $('#tSAmt').html(oResult.json.total_sales_amount);
                    $('#fInstall').html(oResult.json.total_first_installment);

                }
            },
        });
    }

</script>
@endsection
