@extends('Layouts.erp_master_full_width')
@section('content')

<?php 
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;

$customerData = Common::ViewTableOrder('bill_customers', [['is_delete', 0], ['is_active', 1]], ['id', 'customer_name'], ['customer_name', 'ASC']);
$GroupData = Common::ViewTableOrder('pos_p_groups', [['is_delete', 0], ['is_active', 1]], ['id', 'group_name'], ['group_name', 'ASC']);
$CategoryData = Common::ViewTableOrder('pos_p_categories', [['is_delete', 0], ['is_active', 1]], ['id', 'cat_name'], ['cat_name', 'ASC']);
$SubCatData = Common::ViewTableOrder('pos_p_subcategories', [['is_delete', 0], ['is_active', 1]], ['id', 'sub_cat_name'], ['sub_cat_name', 'ASC']);
$BrandData = Common::ViewTableOrder('pos_p_brands', [['is_delete', 0], ['is_active', 1]], ['id', 'brand_name'], ['brand_name', 'ASC']);

$StartDate = Common::systemCurrentDate();
$EndDate = Common::systemCurrentDate();
// $BranchID = (isset($BranchID) && !empty($BranchID)) ? $BranchID : Common::getBranchId();

?>

<div class="panel">
    <div class="panel-body">

        <!-- Search Option Start -->
        <!-- <div class="row align-items-center d-flex justify-content-center pb-10"> -->
        <div class="row align-items-center pb-10 d-print-none">
            <!-- Html View Load For Branch Search -->
            <!-- {!! HTML::forBranchFeildSearch('all') !!} -->

            <div class="col-lg-2">
                <label class="input-title">Customer</label>
                <select class="form-control clsSelect2" name="customer_id" id="customer_id">
                    <option value="">Select one</option>
                    @foreach ($customerData as $Row)
                    <option value="{{ $Row->id }}">{{ $Row->customer_name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-lg-2">
              <label class="input-title">Start Date</label>
                <div class="input-group ghdatepicker">
                    <div class="input-group-prepend">
                        <span class="input-group-text ">
                            <i class="icon wb-calendar round" aria-hidden="true"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control datepicker-custom" id="start_date"
                        name="StartDate" placeholder="DD-MM-YYYY" value="{{$StartDate}}">
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
                    <input type="text" class="form-control datepicker-custom" id="end_date" name="EndDate"
                        placeholder="DD-MM-YYYY" value="{{$EndDate}}">
                </div>
            </div>
            
            
        </div>
        <!-- {{-- do not delete commentted code below --}} -->
        <div class="row align-items-center pb-10 d-print-none">
            <div class="col-lg-12 pt-20 text-center">
                <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round"
                    id="purchaseSearch">Search</a>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead>
                            <tr>
                                <th width="3%">SL</th>
                                <th width="15%">Agreement No</th>
                                <th width="12%">Agreement Date</th>
                                <th width="20%">Customer</th>
                                <th width="10%">Agreement End Date</th>
                                <th width="10%">Total Amount</th>
                                <th width="10%">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function ajaxDataLoad(SDate = null, EDate = null, BranchID = null, CustomerID = null, PGroupID = null, CategoryId =
    null, SubCatID = null, BrandID = null) {

    $('.clsDataTable').DataTable({
        "scrollY": false,
        destroy: true,
        processing: true,
        serverSide: true,
        order: [[ 1, "ASC" ]],
        stateSave: true,
        stateDuration: 1800,
        // ordering: false,
        // lengthMenu: [[10, 20, 30, 50], [10, 20, 30, 50]],
        "ajax": {
            "url": "{{route('SoftAgreementList')}}",
            "dataType": "json",
            "type": "post",
            "data": {
                _token: "{{csrf_token()}}",
                SDate: SDate,
                EDate: EDate,
                BranchID: BranchID,
                CustomerID: CustomerID,
                PGroupID: PGroupID,
                CategoryId: CategoryId,
                SubCatID: SubCatID,
                BrandID: BrandID
            }
        },
        columns: [{
                data: 'id', orderable: false, className: 'text-center'
            },
            {
                data: 'agreement_no',
            },
            {
                data: 'agreement_date',
            },
            {
                data: 'customer_name'
            },
            {
                data: 'agreement_end_date',
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
        var CustomerID = $('#customer_id').val();
        var PGroupID = $('#group_id').val();
        var CategoryId = $('#category_id').val();
        var SubCatID = $('#sub_cat_id').val();
        var BrandID = $('#brand_id').val();


        ajaxDataLoad(SDate, EDate, BranchID, CustomerID, PGroupID, CategoryId, SubCatID, BrandID);
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
        "{{url('bill/agreement_us/delete/')}}",
        "{{url('/ajaxDeleteCheck')}}",
        RowID
    );
}
</script>

@endsection
