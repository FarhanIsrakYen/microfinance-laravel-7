@extends('Layouts.erp_master_full_width')
@section('content')

<?php 
    use App\Services\CommonService as Common;
    use App\Services\HtmlService as HTML;
    use App\Services\HrService as HRS;

    $startDate = Common::systemCurrentDate();
    $endDate = Common::systemCurrentDate();

    $customerData = Common::ViewTableOrderIn('pos_customers',
        [['is_delete', 0], ['is_active', 1]],
        ['branch_id', HRS::getUserAccesableBranchIds()],
        ['id','customer_no', 'customer_name'],
        ['customer_name', 'ASC']);

    $employeeData = Common::ViewTableOrderIn('hr_employees',
        [['is_delete', 0], ['is_active', 1]],
        ['branch_id', HRS::getUserAccesableBranchIds()],
        ['id','employee_no', 'emp_name','emp_code'],
        ['emp_name', 'ASC']);

    $branchData = Common::ViewTableOrderIn('gnl_branchs',
        [['is_delete', 0], ['is_active', 1]],
        ['id', HRS::getUserAccesableBranchIds()],
        ['id', 'branch_name'],
        ['branch_name', 'ASC']);

    $zoneData = Common::ViewTableOrder('gnl_zones',
        [['is_delete', 0], ['is_active', 1]],
        ['id', 'zone_name'],
        ['zone_name', 'ASC']);

    $areaData = Common::ViewTableOrder('gnl_areas',
        [['is_delete', 0], ['is_active', 1]],
        ['id', 'area_name'],
        ['area_name', 'ASC']);

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
                {!! HTML::forBranchFeildSearch_new('all') !!}
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
                </div>
                <div class="col-lg-2">
                    <label class="input-title">Zone</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="zone_id" id="zone_id">
                            <option value="">Select Option</option>
                            @foreach($zoneData as $row)
                            <option value="{{ $row->id}}">{{ $row->zone_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-2">
                    <label class="input-title">Sales Type</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="sales_type" id="sales_type">
                            <option value="">All</option>
                            <option value="1">Cash Sales</option>
                            <option value="2">Installment Sales</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row align-items-center pb-10 d-print-none">

                <div class="col-lg-2">
                    <label class="input-title">Customer</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="customer_id" id="customer_id">
                            <option value="">Select Option</option>
                            @foreach ($customerData as $row)
                            <option value="{{ $row->customer_no }}">{{ $row->customer_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Sales By</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="employee_id" id="employee_id">
                            <option value="">Select Option</option>
                            @foreach ($employeeData as $row)
                            <option value="{{ $row->employee_no }}">{{sprintf("%04d", $row->emp_code)}} -
                                {{ $row->emp_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

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
                                                      '{{base64_encode("inv_p_subcategories")}}',
                                                      '{{base64_encode("prod_group_id")}}',
                                                      '{{base64_encode("id,sub_cat_name")}}',
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
                        <select class="form-control clsSelect2" name="sub_cat_id" id="sub_cat_id">
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
                        <select class="form-control clsSelect2" name="brand_id" id="brand_id">
                            <option value="">Select Option</option>
                            @foreach ($brandData as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->brand_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-12 pt-20 text-center">
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

            <div class="row text-dark ExportHeading">
                <div class="col-xl-12 col-lg-12 col-sm-12 col-md-12 col-12" style="text-align:center;">
                    <br>
                    <strong>{{ $groupInfo->group_name }}</strong><br>
                    <strong>{{ $branchInfo->branch_name }}</strong><br>
                    <span>Sales Details Report</span><br>
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
                        onclick="fnDownloadExcel('ExportHeading,ExportDiv', 'Sales_Details_Report_{{ (new Datetime())->format('d-m-Y') }}');">
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
                                <th width="15%">Customer Name</th>
                                <th width="7%">Sales Type</th>
                                <th width="10%">Bill No</th>
                                <th width="13%">Sales Date</th>
                                <th width="15%">Sales By</th>
                                <th width="20%">Product Name</th>
                                <th width="7%">Quantity</th>
                                <th width="15%">Total Sales Amount (Without P.F)</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7" style="text-align:right!important;"><b>TOTAL</b></td>
                                <td class="text-center text-dark font-weight-bold" id="total_quantity">0</td>
                                <td class="text-right text-dark font-weight-bold" id="total_amount">0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
function ajaxDataLoad(start_date = null, end_date = null, zone_id = null, area_id = null, branch_id = null, sales_type =
    null, customer_id = null, employee_id = null, group_id = null, cat_id = null, sub_cat_id = null, brand_id = null) {

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
            "url": "{{ url('inv/report/sales_details') }}",
            "dataType": "json",
            "type": "post",
            "data": {
                startDate: start_date,
                endDate: end_date,
                zoneId: zone_id,
                areaId: area_id,
                branchId: branch_id,
                salesType: sales_type,
                customerId: customer_id,
                employeeId: employee_id,
                groupId: group_id,
                catId: cat_id,
                subCatId: sub_cat_id,
                brandId: brand_id
            }
        },
        columns: [{
                data: 'sl',
                className: 'text-center'
            },
            {
                data: 'customer_name'
            },
            {
                data: 'sales_type'
            },
            {
                data: 'sales_bill_no',
                // width: '14%'
            },
            {
                data: 'sales_date',
                // width: '15%'
            },

            {
                data: 'emp_name'
            },
            {
                data: 'product_name'
            },
            {
                data: 'product_quantity',
                className: 'text-center'
            },
            {
                data: 'total_amount',
                className: 'text-right',
                // width: '15%'
            }
        ],
        drawCallback: function(oResult) {

            if (oResult.json) {
                $('#totalRowDiv').html(oResult.json.totalRow);
                $('#total_quantity').html(oResult.json.total_quantity);
                $('#total_amount').html(oResult.json.total_amount);
            }
        },
    });
}

$(document).ready(function() {

    // ajaxDataLoad();

    $('#searchButton').click(function() {

        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();
        var zone_id = $('#zone_id').val();
        var area_id = $('#area_id').val();
        var branch_id = $('#branch_id').val();
        var sales_type = $('#sales_type').val();
        var customer_id = $('#customer_id').val();
        var employee_id = $('#employee_id').val();
        var group_id = $('#group_id').val();
        var cat_id = $('#cat_id').val();
        var sub_cat_id = $('#sub_cat_id').val();
        var brand_id = $('#brand_id').val();

        ajaxDataLoad(start_date, end_date, zone_id, area_id, branch_id, sales_type, customer_id,
            employee_id,
            group_id, cat_id, sub_cat_id, brand_id);
    });
});

</script>
@endsection