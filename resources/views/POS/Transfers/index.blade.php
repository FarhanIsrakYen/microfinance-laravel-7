@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;

$StartDate =  Common::systemCurrentDate();
$EndDate = Common::systemCurrentDate();
$GroupData = Common::ViewTableOrder('pos_p_groups', [['is_delete', 0], ['is_active', 1]], ['id', 'group_name'], ['group_name', 'ASC']);
$CategoryData = Common::ViewTableOrder('pos_p_categories', [['is_delete', 0], ['is_active', 1]], ['id', 'cat_name'], ['cat_name', 'ASC']);
$SubCatData = Common::ViewTableOrder('pos_p_subcategories', [['is_delete', 0], ['is_active', 1]], ['id', 'sub_cat_name'], ['sub_cat_name', 'ASC']);
$BrandData = Common::ViewTableOrder('pos_p_brands', [['is_delete', 0], ['is_active', 1]], ['id', 'brand_name'], ['brand_name', 'ASC']);
$productData = Common::ViewTableOrder('pos_products',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'product_name', 'prod_barcode'],
    ['product_name', 'ASC']);
$branchdata = Common::ViewTableOrder('gnl_branchs',
    [['is_delete', 0], ['is_active', 1],['is_approve', 1]],
    ['id', 'branch_name', 'branch_code'],
    ['branch_code', 'ASC']);



?>

<!-- Search Option start -->
<div class="row align-items-center pb-10 d-print-none">
    <!-- Html View Load For Branch Search -->

    @if(Common::getBranchId() == 1)
    <!-- Only Head office can see both transfer in and out ,branch will see transfer out -->
    {!! HTML::forBranchFeildSearch('all','branch_from','branch_from','Branch From') !!}
    @endif

    <!-- {!! HTML::forBranchFeildSearch('all','branch_to','branch_to','Branch To') !!} -->

    <div class="col-lg-2">
        <label class="input-title">branch to</label>
        <select class="form-control clsSelect2" name="branch_to" id="branch_to">
            <option value="">Select All</option>
            @foreach ($branchdata as $row)
            <option value="{{ $row->id }}">{{ sprintf("%04d", $row->branch_code)  . " - " . $row->branch_name }}</option>
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

    <!-- <div class="col-lg-2">
      <label class="input-title">Transfer Type</label>
        <select class="form-control clsSelect2" name="t_type" id="t_type">
            <option value="">Select All</option>
            <option value="1">Transfer Out</option>
            <option value="2">Transfer In</option>
        </select>
    </div> -->
    <div class="col-lg-2">
      <label class="input-title">Group</label>
        <select class="form-control clsSelect2" name="group_id" id="group_id"
        onchange="fnAjaxSelectBox('category_id',this.value,
          '{{ base64_encode('pos_p_categories')}}',
          '{{base64_encode('prod_group_id')}}',
          '{{base64_encode('id,cat_name')}}',
          '{{url('/ajaxSelectBox')}}');
          fnAjaxSelectBox('sub_cat_id',this.value,
            '{{ base64_encode('pos_p_subcategories')}}',
            '{{base64_encode('prod_group_id')}}',
            '{{base64_encode('id,sub_cat_name')}}',
            '{{url('/ajaxSelectBox')}}');">
            <option value="">Select one</option>
            @foreach ($GroupData as $Row)
            <option value="{{ $Row->id }}">{{ $Row->group_name }}</option>
            @endforeach
        </select>
    </div>


    <div class="col-lg-2">
      <label class="input-title">Category</label>
        <select class="form-control clsSelect2" name="category_id" id="category_id"
        onchange="fnAjaxSelectBox('sub_cat_id',this.value,
          '{{ base64_encode('pos_p_subcategories')}}',
          '{{base64_encode('prod_cat_id')}}',
          '{{base64_encode('id,sub_cat_name')}}',
          '{{url('/ajaxSelectBox')}}');">
            <option value="">Select one</option>
            @foreach ($CategoryData as $Row)
            <option value="{{ $Row->id }}">{{ $Row->cat_name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-2">
      <label class="input-title">Sub Category</label>
        <select class="form-control clsSelect2" name="sub_cat_id" id="sub_cat_id">
            <option value="">Select one</option>
            @foreach ($SubCatData as $Row)
            <option value="{{ $Row->id }}">{{ $Row->sub_cat_name }}</option>
            @endforeach
        </select>
    </div>




</div>

<div class="row align-items-center pb-10 d-print-none">
        <div class="col-lg-2">
          <label class="input-title">Brand</label>
            <select class="form-control clsSelect2" name="brand_id" id="brand_id">
                <option value="">Select one</option>
                @foreach ($BrandData as $Row)
                <option value="{{ $Row->id }}">{{ $Row->brand_name }}</option>
                @endforeach
            </select>
        </div>
        
        <div class="col-lg-2">
          <label class="input-title">Start Date</label>
            <div class="input-group ghdatepicker">
                <div class="input-group-prepend">
                    <span class="input-group-text">
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
                <div class="input-group-prepend">
                    <span class="input-group-text">
                        <i class="icon wb-calendar round" aria-hidden="true"></i>
                    </span>
                </div>
                <input type="text" class="form-control round datepicker-custom" id="end_date" name="end_date"
                    placeholder="DD-MM-YYYY">
            </div>
        </div>

    <div class="col-lg-2 pt-20 text-center">
        <a href="javascript:void(0)" id="btnSearch" class="btn btn-primary btn-round">Search</a>
    </div>
</div>
<!-- Search Option End -->

<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                <thead>
                    <tr>
                        <th width="3%">SL</th>
                        <th>Date</th>
                        <th>Bill No</th>
                        <th>Transfer From</th>
                        <th>Transfer To</th>
                        <th>Product</th>
                        <th>Total Quantity</th>
                        <th class="text-center">Total Amount</th>
                        <th width="10%">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>


<script>
function ajaxDataLoad(sDate = null, eDate = null, branchFrom = null, branchTo = null, Type = null,
  PGroupID = null, CategoryId = null, SubCatID = null, BrandID = null) {

    $('.clsDataTable').DataTable({
        destroy: true,
        // retrieve: true,
        processing: true,
        serverSide: true,
        order: [
            [1, "DESC"]
        ],
        stateSave: true,
        stateDuration: 1800,
        // ordering: false,
        // lengthMenu: [[10, 20, 30, 50], [10, 20, 30, 50]],
        "ajax": {
            "url": "{{route('transferDatatable')}}",
            "dataType": "json",
            "type": "post",
            "data": {
                _token: "{{csrf_token()}}",
                sDate: sDate,
                eDate: eDate,
                branchFrom: branchFrom,
                branchTo: branchTo,
                Type: Type,
                PGroupID: PGroupID,
                CategoryId: CategoryId,
                SubCatID: SubCatID,
                BrandID: BrandID
            }
        },
        columns: [
            {
                data: 'id',
                className: 'text-center'
            },
            {
                data: 'transfer_date',
            },
            {
                data: 'bill_no'
            },
            {
                data: 'branch_from',
                orderable: false,
            },
            {
                data: 'branch_to',
                orderable: false,
            },
            {
                data: 'product_name',
                orderable: false,
            },
            {
                data: 'total_quantity',
                className: 'text-center'
            },
            {
                data: 'total_amount',
                className: 'text-right'
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

    });
}

$(document).ready(function() {
    ajaxDataLoad();

    $('#btnSearch').click(function() {

        var sDate = $('#start_date').val();
        var eDate = $('#end_date').val();
        var branchFrom = $('#branch_from').val();
        var branchTo = $('#branch_to').val();
        var Type = $('#t_type').val();
        var PGroupID = $('#group_id').val();
        var CategoryId = $('#category_id').val();
        var SubCatID = $('#sub_cat_id').val();
        var BrandID = $('#brand_id').val();
        ajaxDataLoad(sDate, eDate, branchFrom, branchTo, Type, PGroupID, CategoryId, SubCatID, BrandID);
    });
    
    
});
</script>
<script>
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
        "{{url('pos/transfer/delete/')}}",
        "{{url('/ajaxDeleteCheck')}}",
        RowID

    );
}
</script>
@endsection
