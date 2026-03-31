@extends('Layouts.erp_master_full_width')
@section('content')

<?php
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;

$SupplierData = Common::ViewTableOrder('pos_suppliers', [['is_delete', 0], ['is_active', 1]], ['id', 'sup_name'], ['sup_name', 'ASC']);
$GroupData = Common::ViewTableOrder('pos_p_groups', [['is_delete', 0], ['is_active', 1]], ['id', 'group_name'], ['group_name', 'ASC']);
$CategoryData = Common::ViewTableOrder('pos_p_categories', [['is_delete', 0], ['is_active', 1]], ['id', 'cat_name'], ['cat_name', 'ASC']);
$SubCatData = Common::ViewTableOrder('pos_p_subcategories', [['is_delete', 0], ['is_active', 1]], ['id', 'sub_cat_name'], ['sub_cat_name', 'ASC']);
$BrandData = Common::ViewTableOrder('pos_p_brands', [['is_delete', 0], ['is_active', 1]], ['id', 'brand_name'], ['brand_name', 'ASC']);

$StartDate = Common::systemCurrentDate();
$EndDate = Common::systemCurrentDate();
// $BranchID = (isset($BranchID) && !empty($BranchID)) ? $BranchID : Common::getBranchId();
$productData = Common::ViewTableOrder('pos_products',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'product_name','prod_barcode'],
    ['product_name', 'ASC']);



?>

<div class="panel">
    <div class="panel-body">

        <!-- Search Option Start -->
        <div class="row align-items-center pb-10 d-print-none">
            <!-- Html View Load For Branch Search -->
            {!! HTML::forBranchFeildSearch('all') !!}

            {{-- {!! HTML::forBranchFeildSearch() !!} --}}

            <div class="col-lg-2">
                <label class="input-title">Product</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" name="product_id" id="product_id">
                        <option value="">Select All</option>
                        @foreach ($productData as $row)
                        <option value="{{ $row->id }}">{{ $row->product_name."(".$row->prod_barcode.")"}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-lg-2">
                <label class="input-title">Supplier</label>
                <select class="form-control clsSelect2" name="supplier_id" id="supplier_id">
                    <option value="">Select one</option>
                    @foreach ($SupplierData as $Row)
                    <option value="{{ $Row->id }}">{{ $Row->sup_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2">
              <label class="input-title">Group</label>
                <select class="form-control clsSelect2" name="group_id" id="group_id"
                onchange="fnAjaxSelectBox('category_id',
                                                this.value,
                                                '{{ base64_encode("pos_p_categories")}}',
                                                '{{base64_encode("prod_group_id")}}',
                                                '{{base64_encode("id,cat_name")}}',
                                                '{{url("/ajaxSelectBox")}}');
                                                fnAjaxSelectBox('sub_cat_id',
                                                this.value,
                                                '{{ base64_encode("pos_p_subcategories")}}',
                                                '{{base64_encode("prod_group_id")}}',
                                                '{{base64_encode("id,sub_cat_name")}}',
                                                '{{url("/ajaxSelectBox")}}');">
                    <option value="">Select one</option>
                    @foreach ($GroupData as $Row)
                    <option value="{{ $Row->id }}">{{ $Row->group_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2">
                <label class="input-title">Category</label>
                <select class="form-control clsSelect2" name="category_id" id="category_id"
                onchange="fnAjaxSelectBox('sub_cat_id',
                                                this.value,
                                                '{{ base64_encode("pos_p_subcategories")}}',
                                                '{{base64_encode("prod_cat_id")}}',
                                                '{{base64_encode("id,sub_cat_name")}}',
                                                '{{url("/ajaxSelectBox")}}');">

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
                    <input type="text" class="form-control datepicker-custom" id="start_date"
                        name="StartDate" placeholder="DD-MM-YYYY">
                </div>
            </div>

            <div class="col-lg-2">
                <label class="input-title">End Date</label>
                <div class="input-group ghdatepicker">
                    <div class="input-group-prepend ">
                        <span class="input-group-text">
                            <i class="icon wb-calendar round" aria-hidden="true"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control datepicker-custom" id="end_date" name="EndDate"
                        placeholder="DD-MM-YYYY">
                </div>
            </div>

            <div class="col-lg-2 pt-20 text-center">
                <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round"
                    id="purchaseSearch">Search</a>
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
                                <th width="8%">Date</th>
                                <th width="10%">Bill No</th>
								<th width="10%">Order No</th>
                                <th width="12%">Supplier</th>
                                <th width="20%">Product</th>
                                <th width="7%">Total Quantity</th>
                                <th width="8%">Payable Amount</th>
                                <th width="7%">Paid</th>
                                <th width="7%">Due</th>
                                <th width="8%">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function ajaxDataLoad(SDate = null, EDate = null, BranchID = null, ProductID= null, SupplierID = null, PGroupID = null,
  CategoryId =  null, SubCatID = null, BrandID = null) {

    $('.clsDataTable').DataTable({
        "scrollY": false,
        destroy: true,
        processing: true,
        serverSide: true,
        order: [[ 1, "DESC" ]],
        stateSave: true,
        stateDuration: 1800,
        // ordering: false,
        // lengthMenu: [[10, 20, 30, 50], [10, 20, 30, 50]],
        "ajax": {
            "url": "{{route('PurchaseList')}}",
            "dataType": "json",
            "type": "post",
            "data": {
                _token: "{{csrf_token()}}",
                SDate: SDate,
                EDate: EDate,
                BranchID: BranchID,
                ProductID:ProductID,
                SupplierID: SupplierID,
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
                data: 'purchase_date',
            },
            {
                data: 'bill_no',
            },
			{
                data: 'order_no'
            },
            {
                data: 'supplier_name',
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
                data: 'total_payable_amount',
                className: 'text-right'
            },
            {
                data: 'paid_amount',
                className: 'text-right'
            },
            {
                data: 'due_amount',
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
        // drawCallback: function (oResult) {
        //     $('#TQuantity').html(oResult.json.totalQuantity);
        //     $('#TUnitPrice').html(oResult.json.totalUnitPrice);
        //     $('#TAmount').html(oResult.json.totalAmount);
        // },
    });
}

$(document).ready(function() {

    ajaxDataLoad();
    $('#purchaseSearch').click(function() {

        var SDate = $('#start_date').val();
        var EDate = $('#end_date').val();
        var BranchID = $('#branch_id').val();
        var ProductID = $('#product_id').val();
        var SupplierID = $('#supplier_id').val();
        var PGroupID = $('#group_id').val();
        var CategoryId = $('#category_id').val();
        var SubCatID = $('#sub_cat_id').val();
        var BrandID = $('#brand_id').val();


        ajaxDataLoad(SDate, EDate, BranchID, ProductID, SupplierID, PGroupID, CategoryId, SubCatID, BrandID);
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
        "{{url('pos/purchase/delete/')}}",
        "{{url('/ajaxDeleteCheck')}}",
        RowID
    );
}
</script>

@endsection
