@extends('Layouts.erp_master_full_width')
@section('content')

<?php
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;

$SupplierData = Common::ViewTableOrder('inv_suppliers', [['is_delete', 0], ['is_active', 1]], ['id', 'sup_name'], ['sup_name', 'ASC']);
$GroupData = Common::ViewTableOrder('inv_p_groups', [['is_delete', 0], ['is_active', 1]], ['id', 'group_name'], ['group_name', 'ASC']);
$CategoryData = Common::ViewTableOrder('inv_p_categories', [['is_delete', 0], ['is_active', 1]], ['id', 'cat_name'], ['cat_name', 'ASC']);
$SubCatData = Common::ViewTableOrder('inv_p_subcategories', [['is_delete', 0], ['is_active', 1]], ['id', 'sub_cat_name'], ['sub_cat_name', 'ASC']);
$BrandData = Common::ViewTableOrder('inv_p_brands', [['is_delete', 0], ['is_active', 1]], ['id', 'brand_name'], ['brand_name', 'ASC']);

$StartDate = Common::systemCurrentDate();
$EndDate = Common::systemCurrentDate();
// $BranchID = (isset($BranchID) && !empty($BranchID)) ? $BranchID : Common::getBranchId();

?>

<div class="panel">
    <div class="panel-body">
            <!-- Search Option Start -->
            <div class="row align-items-center pb-10 d-print-none">
                <!-- Html View Load For Branch Search -->
                {!! HTML::forBranchFeildSearch('all') !!}

                {{-- {!! HTML::forBranchFeildSearch() !!} --}}



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
                  <label class="input-title">Start Date</label>
                    <div class="input-group ghdatepicker">
                        <div class="input-group-prepend">
                            <span class="input-group-text ">
                                <i class="icon wb-calendar round" aria-hidden="true"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control datepicker-custom" id="start_date" name="StartDate"
                            placeholder="DD-MM-YYYY" value="{{$StartDate}}">
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
                <div class="col-lg-2 pt-20 text-center">
                    <a href="javascript:void(0)" id="btnPurReturnSearch" name="searchButton"
                        class="btn btn-primary btn-round">Search</a>
                </div>
            </div>
            <!-- {{-- do not delete commentted code below --}}
            <div class="row align-items-center d-flex justify-content-center pb-10">
                <div class="col-lg-2">
                    <label class="input-title">Group</label>
                    <select class="form-control clsSelect2" name="group_id" id="group_id">
                        <option value="">Select one</option>
                        @foreach ($GroupData as $Row)
                        <option value="{{ $Row->id }}">{{ $Row->group_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="input-title">Category</label>
                    <select class="form-control clsSelect2" name="category_id" id="category_id">
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

                <div class="col-lg-2">
                    <label class="input-title">Brand</label>
                    <select class="form-control clsSelect2" name="brand_id" id="brand_id">
                        <option value="">Select one</option>
                        @foreach ($BrandData as $Row)
                        <option value="{{ $Row->id }}">{{ $Row->brand_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row align-items-center d-flex justify-content-center">
            </div> -->

        <div class="row">
            <div class="col-lg-12">
                <div class="">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable"
                        id="purchase_return_datatable">
                        <thead>
                            <tr class="text-center">
                                <th width="5%">SL</th>
                                <th>Return Date</th>
                                <th>Bill No</th>
                                <th>Supplier</th>
                                <th>Products</th>
                                <th>Total Quantity</th>
                                <!-- <th>Total Amount</th> -->
                                <th>Branch</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
<!-- End Page -->

<script type="text/javascript">
$(document).ready(function() {

    //load data into the search fields
    ajaxDataLoad();

    $('#btnPurReturnSearch').click(function() {

        var SDate = $('#start_date').val();
        var EDate = $('#end_date').val();
        var BranchID = $('#branch_id').val();
        var SupplierID = $('#supplier_id').val();

        ajaxDataLoad(SDate, EDate, BranchID, SupplierID);
    });
});


function ajaxDataLoad(SDate = null, EDate = null, BranchID = null, SupplierID = null) {

    $('.clsDataTable').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        stateSave: true,
        stateDuration: 1800,
        "ajax": {
            "url": "{{ route('INVpurReturnIndexDtable') }}",
            "dataType": "json",
            "type": "post",
            "data": {
                _token: "{{csrf_token()}}",
                SDate: SDate,
                EDate: EDate,
                BranchID: BranchID,
                SupplierID: SupplierID,
            }
        },
        columns: [{
                data: 'id'
            },
            {
                data: 'return_date'
            },
            {
                data: 'bill_no'
            },
            {
                data: 'sup_name'
            },
            {
                data: 'product_name',
            },
            {
                data: 'total_quantity',
                className: 'text-center'
            },
            {
                data: 'branch_name'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false
            },
        ],
        'fnRowCallback': function(nRow, aData, Index) {
            var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData
                .action.action_link);
            $('td:last', nRow).html(actionHTML);
        }
    });
}

function fnDelete(RowID) {
    /**
     * para 1 = link to delete without id
     * para 2 = ajax check link same for all
     * para 3 = id of deleting item
     * para 4 = matching column
     * para 5 = table 1
     * para 6 = table 2
     * para 7 = table 3
     */

    fnDeleteCheck(
        "{{url('inv/purchase_return/delete/')}}",
        "{{url('/ajaxDeleteCheck')}}",
        RowID,
        "",
        "",
        ""
    );
}
</script>


@endsection
