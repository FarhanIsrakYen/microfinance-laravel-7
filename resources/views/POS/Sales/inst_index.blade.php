@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
use App\Services\HrService as HRS;

// $SupplierData = Common::ViewTableOrder('pos_suppliers', [['is_delete', 0], ['is_active', 1]], ['id', 'sup_name'], ['sup_name', 'ASC']);
//   $GroupData = Common::ViewTableOrder('pos_p_groups', [['is_delete', 0], ['is_active', 1]], ['id', 'group_name'], ['group_name', 'ASC']);
//   $CategoryData = Common::ViewTableOrder('pos_p_categories', [['is_delete', 0], ['is_active', 1]], ['id', 'cat_name'], ['cat_name', 'ASC']);
//   $SubCatData = Common::ViewTableOrder('pos_p_subcategories', [['is_delete', 0], ['is_active', 1]], ['id', 'sub_cat_name'], ['sub_cat_name', 'ASC']);
//   $BrandData = Common::ViewTableOrder('pos_p_brands', [['is_delete', 0], ['is_active', 1]], ['id', 'brand_name'], ['brand_name', 'ASC']);

$CustomerData = DB::table('pos_customers')
    ->where([['is_delete', 0], ['is_active', 1]])
    ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
    ->select('customer_name', 'customer_no')
    ->orderBy('customer_no', 'ASC')
    ->get();

$EmployeeData = DB::table('hr_employees')
    ->where([['is_delete', 0], ['is_active', 1]])
    ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
    ->select('emp_name', 'employee_no', 'emp_code')
    ->orderBy('emp_code', 'ASC')
    ->get();

$InstallmentTypeData = DB::table('gnl_installment_type')
    ->where([['is_active', 1]])
    ->select('id', 'name')
    ->orderBy('id', 'ASC')
    ->get();

$InstallmentMonthData = DB::table('pos_inst_packages')
    ->where([['is_delete', 0], ['is_active', 1]])
    ->select('prod_inst_month', 'id')
    ->orderBy('prod_inst_month', 'ASC')
    ->get();

$StartDate = Common::systemCurrentDate();
$EndDate = Common::systemCurrentDate();
$productData = Common::ViewTableOrder('pos_products',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'product_name', 'prod_barcode'],
    ['product_name', 'ASC']);



?>

<!-- Page -->

<!-- Search Option Start -->
<div class="row align-items-center pb-10 d-print-none">
    <!-- Html View Load For Branch Search -->
    {!! HTML::forBranchFeildSearch('all') !!}

    <div class="col-lg-2">
      <label class="input-title">Customer</label>
        <select class="form-control clsSelect2" name="customer_id" id="customer_id">
            <option value="">Select one</option>
            @foreach ($CustomerData as $Row)
            <option value="{{ $Row->customer_no }}">{{ $Row->customer_name." (".$Row->customer_no.")" }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-2">
        <label class="input-title">Product</label>
        <select class="form-control clsSelect2" name="product_id" id="product_id">
            <option value="">Select All</option>
            @foreach ($productData as $row)
            <option value="{{ $row->id }}">{{ $row->product_name."(".$row->prod_barcode.")"}}</option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-2">
      <label class="input-title">Installment Type</label>
        <select class="form-control clsSelect2" name="installment_type_id" id="installment_type_id">
            <option value="">Select one</option>
            @foreach ($InstallmentTypeData as $Row)
            <option value="{{ $Row->id }}">{{ $Row->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-2">
      <label class="input-title">Installment Month</label>
        <select class="form-control clsSelect2" name="installment_month_id" id="installment_month_id">
            <option value="">Select one</option>
            @foreach ($InstallmentMonthData as $Row)
            <option value="{{ $Row->id }}">{{ $Row->prod_inst_month }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-2">
      <label class="input-title">Sales By</label>
        <select class="form-control clsSelect2" name="employee_id" id="employee_id">
            <option value="">Select one</option>
            @foreach ($EmployeeData as $Row)
            <option value="{{ $Row->employee_no }}">{{ $Row->emp_name." (".$Row->emp_code.")" }}</option>
            @endforeach
        </select>
    </div>

    {{-- <div class="col-lg-2">&nbsp</div> --}}

    <div class="col-lg-2">
      <label class="input-title">Start Date</label>
        <div class="input-group ghdatepicker">
            <div class="input-group-prepend ">
                <span class="input-group-text ">
                    <i class="icon wb-calendar round" aria-hidden="true"></i>
                </span>
            </div>
            <input type="text" class="form-control round datepicker-custom" id="start_date"
                name="startDate" placeholder="DD-MM-YYYY">
        </div>
    </div>

    <div class="col-lg-2">
        <label class="input-title">End Date</label>
        <div class="input-group ghdatepicker">
            <div class="input-group-prepend ">
                <span class="input-group-text ">
                    <i class="icon wb-calendar round" aria-hidden="true"></i>
                </span>
            </div>
            <input type="text" class="form-control round datepicker-custom" id="end_date"
                name="endDate" placeholder="DD-MM-YYYY">
        </div>
    </div>

    <div class="col-lg-12 pt-20 text-center">
        <a href="javascript:void(0)" name="searchButton"
            class="btn btn-primary btn-round" id="salesSearch">Search</a>
    </div>
</div>

<!-- {{-- do not delete commentted code below --}}
        {{-- <div class="row align-items-center d-flex justify-content-center pb-10">

            <label class="input-title">Group</label>
            <div class="col-lg-2">
                <select class="form-control clsSelect2" name="group_id" id="group_id">
                    <option value="">Select one</option>
                    @foreach ($GroupData as $Row)
                    <option value="{{ $Row->id }}">{{ $Row->group_name }}</option>
                    @endforeach
                </select>
            </div>

            <label class="input-title">Category</label>
            <div class="col-lg-2">
                <select class="form-control clsSelect2" name="category_id" id="category_id">
                    <option value="">Select one</option>
                    @foreach ($CategoryData as $Row)
                    <option value="{{ $Row->id }}">{{ $Row->cat_name }}</option>
                    @endforeach
                </select>
            </div>

            <label class="input-title">Sub Category</label>
            <div class="col-lg-2">
                <select class="form-control clsSelect2" name="sub_cat_id" id="sub_cat_id">
                    <option value="">Select one</option>
                    @foreach ($SubCatData as $Row)
                    <option value="{{ $Row->id }}">{{ $Row->sub_cat_name }}</option>
                    @endforeach
                </select>
            </div>

            <label class="input-title">Brand</label>
            <div class="col-lg-2">
                <select class="form-control clsSelect2" name="brand_id" id="brand_id">
                    <option value="">Select one</option>
                    @foreach ($BrandData as $Row)
                    <option value="{{ $Row->id }}">{{ $Row->brand_name }}</option>
                    @endforeach
                </select>
            </div>

        </div> --}} -->

<!-- Search Option End -->

<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                <thead>
                    <tr class="text-center">
                        <th width="3%">SL</th>
                        <th>Date</th>
                        <th>Bill No</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Total Amount</th>
                        <th>Paid</th>
                        <th>Due</th>
                        <th>Installment</th>
                        <th>Branch</th>
                        <th width="7%">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
<!-- End Page -->

<script>

function ajaxDataLoad(sDate = null, eDate = null, branchID = null,
            salesType = null, pGroupID = null, categoryId = null, subCatID = null, brandID = null,
            customerID = null, installmentTypeID = null, installmentMonthID = null, employeeID = null){

    $('.clsDataTable').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        order: [[ 1, "DESC" ]],
        stateSave: true,
        stateDuration: 300,
        // ordering: false,
        // lengthMenu: [[10, 20, 30, 50], [10, 20, 30, 50]],
        "ajax":{
            "url": "{{route('InstallmentSalesList')}}",
            "dataType": "json",
            "type": "post",
            "data":{ _token: "{{csrf_token()}}",
                    sDate: sDate,
                    eDate: eDate,
                    branchID: branchID,
                    salesType: salesType,
                    pGroupID: pGroupID,
                    categoryId: categoryId,
                    subCatID: subCatID,
                    brandID: brandID,
                    customerID: customerID,
                    installmentTypeID: installmentTypeID,
                    installmentMonthID: installmentMonthID,
                    employeeID: employeeID,
                }
            },
        columns: [
            { data: 'id'},
            // { data: 'sales_type'},
            { data: 'sales_date'},
            { data: 'sales_bill_no'},
            { data: 'customer_name', orderable: false,},
            { data: 'product_name', orderable: false,},
            { data: 'total_quantity', className: 'text-center'},
            { data: 'total_amount', className: 'text-right'},
            { data: 'paid_amount', className: 'text-right'},
            { data: 'due_amount', className: 'text-right'},
            { data: 'installment_amount', className: 'text-right'},
            { data: 'branch_name', orderable: false,},
            { data: 'action', name: 'action', orderable: false, className: 'text-center'},

        ],
        'fnRowCallback': function(nRow, aData, Index) {
            var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData.action.action_link);

            var hiturl = "<?=url('pos/sales_installment/invoice')?>/"+aData.sales_bill_no;
            actionHTML += '<a href="'+hiturl+'" title="Invoice" class="btnView"><i class="fa fa-file-powerpoint-o"></i></a>';


            $('td:last', nRow).html(actionHTML);
        }
        // drawCallback: function (oResult) {
        //     $('#TQuantity').html(oResult.json.totalQuantity);
        //     $('#TUnitPrice').html(oResult.json.totalUnitPrice);
        //     $('#TAmount').html(oResult.json.totalAmount);
        // },
    });
}

$(document).ready( function () {

    ajaxDataLoad();

    $('#salesSearch').click(function(){

        var sDate = $('#start_date').val();
        var eDate = $('#end_date').val();
        var branchID = $('#branch_id').val();
        var pGroupID = $('#group_id').val();
        var categoryId = $('#category_id').val();
        var subCatID = $('#sub_cat_id').val();
        var brandID = $('#brand_id').val();
        var customerID = $('#customer_id').val();
        var installmentTypeID = $('#installment_type_id').val();
        var installmentMonthID = $('#installment_month_id').val();
        var employeeID = $('#employee_id').val();

        // var salesType = $('#sales_type').val();
        var salesType = 2;

        ajaxDataLoad(sDate, eDate, branchID, salesType, pGroupID, categoryId, subCatID, brandID,
            customerID, installmentTypeID, installmentMonthID, employeeID);
    });

    // Link to Cash Sale Entry
    var html = '<a target="_blank" href="{{url('pos/sales_cash/add')}}" class="btn btn-sm btn-primary btn-outline btn-round mr-2">';
        html += '<i class="icon wb-link" aria-hidden="true"></i>';
        html += '<span class="hidden-sm-down">&nbsp;Cash Entry</span>';
        html += '</a>';
    $('.page-header-actions').prepend(html);

    
    
});

// Delete Data
function fnDelete(RowID) {
    /**
     * para1 = link to delete without id
     * para 2 = ajax check link same for all
     * para 3 = id of deleting item
     * para 4 = matching column
     * para 5 = table 1
     * para 6 = table 2
     * para 7 = table 3
     */

    fnDeleteCheck(
        "{{url('pos/sales_installment/delete/')}}",
        "{{url('/ajaxDeleteCheck')}}",
        RowID

    );
}

</script>

@endsection
