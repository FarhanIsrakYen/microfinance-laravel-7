@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use App\Services\HtmlService as HTML;

$EmpList = Common::ViewTableOrderIn('hr_employees',
    [['is_delete', 0], ['is_active', 1]],
    ['branch_id', HRS::getUserAccesableBranchIds()],
    ['employee_no', 'emp_name'],
    ['emp_name', 'ASC']);

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
        <label class="input-title">Product</label>
        <select class="form-control clsSelect2" name="product_id" id="product_id">
            <option value="">Select All</option>
            @foreach ($productData as $row)
            <option value="{{ $row->id }}">{{ $row->product_name."(".$row->prod_barcode.")"}}</option>
            @endforeach
        </select>
    </div>
    
    <div class="col-lg-2">
      <label class="input-title">Collection By</label>
        <select class="form-control clsSelect2" name="employee_id" id="employee_id">
            <option value="">Select one</option>
            @foreach ($EmpList as $Row)
            <option value="{{ $Row->employee_no }}">{{ $Row->emp_name }}</option>
            @endforeach
        </select>
    </div>


    <div class="col-lg-2">
      <label class="input-title">Start Date</label>
        <div class="input-group ghdatepicker">
            <div class="input-group-prepend ">
                <span class="input-group-text ">
                    <i class="icon wb-calendar round" aria-hidden="true"></i>
                </span>
            </div>
            <input type="text" class="form-control round datepicker-custom" id="start_date" name="start_date"
                placeholder="DD-MM-YYYY">
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
            <input type="text" class="form-control round datepicker-custom" id="end_date" name="end_date"
                placeholder="DD-MM-YYYY">
        </div>
        <div class="help-block with-errors is-invalid"></div>
    </div>

    <div class="col-lg-2 pt-20 text-center">
        <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round"
            id="collectionSearch">Search</a>
    </div>
</div>
<!-- Search End -->

<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                <thead>
                    <tr>
                        <th width="3%">SL</th>
                        <th width="15%">Sales Bill No</th>
                        <th width="15%">Customer</th>
                        <th width="10%">Collection Date</th>
                        <th width="10%">Amount</th>
                        <th width="5%">Payment Type</th>
                        <th width="15%">Collection By</th>
                        <th width="10%">Branch</th>
                        <th width="5%">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- End Page -->

<script>
function ajaxDataLoad(SDate = null, EDate = null, BranchID = null, EmployeeID = null) {
    // console.log(EmployeeID)

    $('.clsDataTable').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        stateSave: true,
        stateDuration: 1800,
        order: [[3, "DESC"]],
        // ordering: false,
        // lengthMenu: [[10, 20, 30, 50], [10, 20, 30, 50]],
        "ajax": {
            "url": "{{route('CollectionList')}}",
            "dataType": "json",
            "type": "post",
            "data": {
                _token: "{{csrf_token()}}",
                SDate: SDate,
                EDate: EDate,
                BranchID: BranchID,
                EmployeeID: EmployeeID
            }
        },
        columns: [{
                data: 'id',
                className: 'text-center'
            },
            {
                data: 'sales_bill_no',
            },
            {
                data: 'customer_name',
                orderable: false
            },
            {
                data: 'collection_date',
            },
            {
                data: 'collection_amount',
                className: 'text-right'
            },
            {
                data: 'payment_system',
            },
            {
                data: 'collection_by',
                orderable: false
            },
            {
                data: 'branch_name',
                orderable: false
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                className: 'text-center'
            },

        ],
        'fnRowCallback': function(nRow, aData, Index) {
            var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData.action.action_link);
            $('td:last', nRow).html(actionHTML);
        }
        // drawCallback: function (oResult) {
        //     $('#TQuantity').html(oResult.json.totalQuantity);
        //     $('#TUnitPrice').html(oResult.json.totalUnitPrice);
        //     $('#TAmount').html(oResult.json.totalAmount);
        // },
    });
}

$(document).ready(function() {

    ajaxDataLoad();

    $('#collectionSearch').click(function() {

        var SDate = $('#start_date').val();
        var EDate = $('#end_date').val();
        var BranchID = $('#branch_id').val();
        var EmployeeID = $('#employee_id').val();


        ajaxDataLoad(SDate, EDate, BranchID, EmployeeID);
    });
    
    
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
        "{{url('pos/collection/delete/')}}",
        "{{url('/ajaxDeleteCheck')}}",
        RowID

    );
}

$('#branch_id').change(function() {

    // console.log('sss');


    fnAjaxSelectBox('employee_id',
        $('#branch_id').val(),
        '{{ base64_encode("hr_employees") }}',
        '{{base64_encode("branch_id")}}',
        '{{base64_encode("employee_no,emp_name")}}',
        '{{url("/ajaxSelectBox")}}'
    );

    // fnColletionNo();

});
</script>

@endsection
