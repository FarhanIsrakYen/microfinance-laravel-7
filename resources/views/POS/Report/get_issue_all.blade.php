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

$issueData = Common::ViewTableOrder('pos_issues_d',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'issue_bill_no'],
    ['issue_bill_no', 'ASC']);

$branchData = Common::ViewTableOrder('gnl_branchs',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'branch_name'],
    ['branch_name', 'ASC']);

$groupData = Common::ViewTableOrder('pos_p_groups',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'group_name'],
    ['group_name', 'ASC']);

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

$branchId = Common::getBranchId();

$branchInfo = Common::ViewTableFirst('gnl_branchs',
    [['is_delete', 0], ['is_active', 1],
        ['id', $branchId]],
    ['id', 'branch_name']);

$groupInfo = Common::ViewTableFirst('gnl_groups',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'group_name']);

?>

<!-- <div class="w-full"> -->

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
                    <label class="input-title">Branch To</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="branch_id" id="branch_id">
                            <option value="">Select All</option>
                            @foreach ($branchData as $row)
                            <option value="{{ $row->id }}">{{ $row->branch_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Product Name</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="product_id" id="product_id">
                            <option value="">Select All</option>
                            @foreach ($productData as $row)
                            <option value="{{ $row->id }}">{{ $row->product_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-2">
                    <label class="input-title">Issue Bill No</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="issue_bill_no" id="issue_bill_no">
                            <option value="">Select All</option>
                            @foreach ($issueData as $row)
                            <option value="{{ $row->issue_bill_no }}">{{ $row->issue_bill_no}}</option>
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

                            <option value="">Select All</option>
                            @foreach($groupData as $row)
                            <option value="{{ $row->id}}">{{ $row->group_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row align-items-center pb-10 d-print-none">
                <div class="col-lg-2">
                    <label class="input-title">Category</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="cat_id" id="cat_id" onchange="fnAjaxSelectBox('sub_cat_id',
                                                        this.value,
                                                        '{{base64_encode("pos_p_subcategories")}}',
                                                        '{{base64_encode("prod_cat_id")}}',
                                                        '{{base64_encode("id,sub_cat_name")}}',
                                                        '{{url("/ajaxSelectBox")}}');">
                            <option value="">Select All</option>
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
                            <option value="">Select All</option>
                            @foreach ($subCatData as $row)
                            <option value="{{ $row->id }}">{{ $row->sub_cat_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Brand</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="brand_id" id="brand_id">
                            <option value="">Select All</option>
                            @foreach ($brandData as $row)
                            <option value="{{ $row->id }}">{{ $row->brand_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2 pt-20 text-center">
                    <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round"
                        id="searchButton">Search</a>
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
                    <span>Issue Report</span><br>
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
                                <th class="text-center">Issue Date</th>
                                <th>Issue No</th>
                                <th>Issue To(Branch)</th>
                                <th>Product Name</th>
                                <th class="text-right">Sale Price</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-right">Total Amount</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-right"><b>TOTAL</b></td>
                                <td class="text-right" id="sale_price" style="font-color:#000; font-weight:bold;">0.00
                                </td>

                                <td class="text-right" id="product_quantity" style="font-color:#000; font-weight:bold;">
                                    0.00</td>

                                <td class="text-right" id="total_cost_amount"
                                    style="font-color:#000; font-weight:bold;">0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

<!-- </div> -->

<script>
function ajaxDataLoad(start_date = null, end_date = null, branch_id = null, product_id = null,
    issue_bill_no = null, group_id = null, cat_id = null, sub_cat_id = null, brand_id = null) {

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
            "url": "{{ route('issueDataTable') }}",
            "dataType": "json",
            "type": "post",
            "data": {
                _token: "{{ csrf_token() }}",
                startDate: start_date,
                endDate: end_date,
                branchId: branch_id,
                productId: product_id,
                issue_bill_no: issue_bill_no,
                groupId: group_id,
                catId: cat_id,
                subCatId: sub_cat_id,
                brandId: brand_id
            }
        },
        columns: [{
                data: 'id'
            },
            {
                data: 'issue_date',
                className: 'text-center'
            },
            {
                data: 'issue_bill_no'
            },
            {
                data: 'branch_name'
            },
            {
                data: 'product_name'
            },
            {
                data: 'sale_price',
                className: 'text-right'
            },
            {
                data: 'product_quantity',
                className: 'text-center'
            },
            {
                data: 'total_cost_amount',
                className: 'text-right'
            },
        ],
        drawCallback: function(oResult) {

            if (oResult.json) {
                $('#totalRowDiv').html(oResult.json.totalRow);
                $('#sale_price').html(oResult.json.sale_price);
                $('#product_quantity').html(oResult.json.product_quantity);
                $('#total_cost_amount').html(oResult.json.total_cost_amount);
            }
        },
    });
}

$(document).ready(function() {

    // ajaxDataLoad();

    $('#searchButton').click(function() {

        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();
        var branch_id = $('#branch_id').val();
        var product_id = $('#product_id').val();
        var issue_bill_no = $('#issue_bill_no').val();
        var group_id = $('#group_id').val();
        var cat_id = $('#cat_id').val();
        var sub_cat_id = $('#sub_cat_id').val();
        var brand_id = $('#brand_id').val();
        $('#start_date_txt').html(start_date);
        $('#end_date_txt').html(end_date);
        // console.log(branch_id);
        ajaxDataLoad(start_date, end_date, branch_id, product_id, issue_bill_no,
            group_id, cat_id, sub_cat_id, brand_id);

    });
});

function fnDownloadPDF() {
    $('.clsDataTable').tableExport({
        type: 'pdf',
        fileName: 'Issue report',
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
        fileName: 'Issue report',
    });
}
</script>
@endsection
