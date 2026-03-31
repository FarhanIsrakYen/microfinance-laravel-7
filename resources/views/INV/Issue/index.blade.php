@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
?>
<?php
$StartDate =  Common::systemCurrentDate();
$EndDate = Common::systemCurrentDate();
$GroupData = Common::ViewTableOrder('inv_p_groups', [['is_delete', 0], ['is_active', 1]], ['id', 'group_name'], ['group_name', 'ASC']);
$CategoryData = Common::ViewTableOrder('inv_p_categories', [['is_delete', 0], ['is_active', 1]], ['id', 'cat_name'], ['cat_name', 'ASC']);
$SubCatData = Common::ViewTableOrder('inv_p_subcategories', [['is_delete', 0], ['is_active', 1]], ['id', 'sub_cat_name'], ['sub_cat_name', 'ASC']);
$BrandData = Common::ViewTableOrder('inv_p_brands', [['is_delete', 0], ['is_active', 1]], ['id', 'brand_name'], ['brand_name', 'ASC']);

?>
<!-- Search Option start -->
<div class="row align-items-center pb-10 d-print-none">
    <!-- Html View Load For Branch Search -->
    {!! HTML::forBranchFeildSearch('all') !!}

    <div class="col-lg-2">
        <label class="input-title">Issue From/To</label>
        <select class="form-control clsSelect2" name="t_type" id="t_type">
            <option value="">Select All</option>
            <option value="1">Issue From</option>
            <option value="2">Issue To</option>
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
            <input type="text" class="form-control round datepicker-custom" id="end_date" name="end_date"
                placeholder="DD-MM-YYYY" value="{{$EndDate}}">
        </div>
        <div class="help-block with-errors is-invalid"></div>
    </div>



    <div class="col-lg-2 pt-20 text-center">
        <a href="javascript:void(0)" id="btnSearch" class="btn btn-primary btn-round">Search</a>
    </div>
</div>

<!-- <div class="row align-items-center d-flex justify-content-center pb-10">
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
</div> -->

<!-- <div class="row align-items-center d-flex justify-content-center">

</div> -->
<!-- Search Option End -->

<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table w-full table-hover table-bordered table-striped clsDataTable" id="tableID">
                <thead>
                    <tr>
                        <th width="3%">SL</th>
                        <th class="text-center">Date</th>
                        <th>Bill No</th>
                        <th class="text-right">Products</th>
                        <th class="text-center">Total Quantity</th>
                        <th>Branch From </th>
                        <th>Branch To </th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    //console.log('test');
    ajaxDataLoad();

    $('#btnSearch').click(function() {

        var sDate = $('#start_date').val();
        var eDate = $('#end_date').val();
        var branchID = $('#branch_id').val();
        var pGroupID = $('#group_id').val();
        var categoryId = $('#category_id').val();
        var subCatID = $('#sub_cat_id').val();
        var brandID = $('#brand_id').val();
        var Type = $('#t_type').val();

        ajaxDataLoad(sDate, eDate, branchID, Type, pGroupID, categoryId, subCatID, brandID);
    });
});



function ajaxDataLoad(sDate = null, eDate = null, branchID = null, Type = null,
    pGroupID = null, categoryId = null, subCatID = null, brandID = null) {

    $('.clsDataTable').DataTable({
        destroy: true,
        // retrieve: true,
        processing: true,
        serverSide: true,
        order: [
            [0, "desc"]
        ],
        stateSave: true,
        stateDuration: 1800,
        // ordering: false,
        // lengthMenu: [[10, 20, 30, 50], [10, 20, 30, 50]],
        "ajax": {
            "url": "{{route('INVissueDatatable')}}",
            "dataType": "json",
            "type": "post",
            "data": {
                _token: "{{csrf_token()}}",
                sDate: sDate,
                eDate: eDate,
                branchID: branchID,
                Type: Type,
                pGroupID: pGroupID,
                categoryId: categoryId,
                subCatID: subCatID,
                brandID: brandID,
            }
        },
        columns: [

            {
                data: 'id',
                className: 'text-center'
            },
            {
                data: 'bill_no'
            },
            {
                data: 'issue_date',
            },
            {
                data: 'product_name',
                orderable: false
            },
            {
                data: 'total_quantity',
                className: 'text-center'
            },
            {
                data: 'branch_from',
                orderable: false
            },
            {
                data: 'branch_to',
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

    });

}

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
        "{{url('inv/issue/delete/')}}",
        "{{url('/ajaxDeleteCheck')}}",
        RowID

    );
}
</script>
@endsection
