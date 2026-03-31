@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\CommonService as Common;

$PGroupData = Common::ViewTableOrder('pos_p_groups',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'group_name'],
    ['group_name', 'ASC']);
$PCategoryData = Common::ViewTableOrder('pos_p_categories',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'cat_name'],
    ['cat_name', 'ASC']);

$PSubCatData = Common::ViewTableOrder('pos_p_subcategories',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'sub_cat_name'],
    ['sub_cat_name', 'ASC']);

$PBrandData = Common::ViewTableOrder('pos_p_brands',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'brand_name'],
    ['brand_name', 'ASC']);

$PModelData = Common::ViewTableOrder('pos_p_models',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'model_name'],
    ['model_name', 'ASC']);

$SupplierData = Common::ViewTableOrder('pos_suppliers',
        [['is_delete', 0], ['is_active', 1]],
        ['id', 'sup_comp_name'],
        ['sup_comp_name', 'ASC']);

?>

<!-- Search Option start -->
<div class="row align-items-center pb-10 d-print-none">
    <!-- <div class="row align-items-center d-flex justify-content-center pb-10 d-print-none"> -->
    <div class="col-lg-2">
        <label class="input-title">Supplier Company</label>
        <div class="input-group">
            <select class="form-control clsSelect2" id="prod_supplier_id">
                <option value="">Select</option>
                @foreach ($SupplierData as $Row)
                <option value="{{ $Row->id }}">{{ $Row->sup_comp_name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-lg-2">
        <label class="input-title">Group</label>
        <div class="input-group">
            <select class="form-control clsSelect2" id="prod_group_id"
                onchange="fnAjaxSelectBox('prod_cat_id',this.value,
                  '{{ base64_encode('pos_p_categories')}}',
                  '{{base64_encode('prod_group_id')}}',
                  '{{base64_encode('id,cat_name')}}',
                  '{{url('/ajaxSelectBox')}}');
                  fnAjaxSelectBox('prod_sub_cat_id',this.value,
                    '{{ base64_encode('pos_p_subcategories')}}',
                    '{{base64_encode('prod_group_id')}}',
                    '{{base64_encode('id,sub_cat_name')}}',
                    '{{url('/ajaxSelectBox')}}');
                  fnAjaxSelectBox('prod_model_id',this.value,
                      '{{ base64_encode('pos_p_models')}}',
                      '{{base64_encode('prod_group_id')}}',
                      '{{base64_encode('id,model_name')}}',
                      '{{url('/ajaxSelectBox')}}');">
                <option value="">Select</option>
                @foreach ($PGroupData as $Row)
                <option value="{{ $Row->id }}">{{ $Row->group_name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-lg-2">
        <label class="input-title">Category</label>
        <div class="input-group">
            <select class="form-control clsSelect2" id="prod_cat_id"
            onchange="fnAjaxSelectBox('prod_sub_cat_id',this.value,
              '{{ base64_encode('pos_p_subcategories')}}',
              '{{base64_encode('prod_cat_id')}}',
              '{{base64_encode('id,sub_cat_name')}}',
              '{{url('/ajaxSelectBox')}}');

              fnAjaxSelectBox('prod_model_id',this.value,
                  '{{ base64_encode('pos_p_models')}}',
                  '{{base64_encode('prod_cat_id')}}',
                  '{{base64_encode('id,model_name')}}',
                  '{{url('/ajaxSelectBox')}}');">
                <option value="">Select</option>
                @foreach ($PCategoryData as $Row)
                <option value="{{ $Row->id }}">{{ $Row->cat_name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-lg-2">
        <label class="input-title">Sub Category</label>
        <div class="input-group">
            <select class="form-control clsSelect2" id="prod_sub_cat_id"
            onchange="fnAjaxSelectBox('prod_model_id',this.value,
                '{{ base64_encode('pos_p_models')}}',
                '{{base64_encode('prod_sub_cat_id')}}',
                '{{base64_encode('id,model_name')}}',
                '{{url('/ajaxSelectBox')}}');">
                <option value="">Select</option>
                @foreach ($PSubCatData as $Row)
                <option value="{{ $Row->id }}">{{ $Row->sub_cat_name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-lg-2">
        <label class="input-title">Brand</label>
        <div class="input-group">
            <select class="form-control clsSelect2" id="prod_brand_id">
                <option value="">Select one</option>
                @foreach ($PBrandData as $Row)
                <option value="{{ $Row->id }}">{{ $Row->brand_name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-lg-2">
        <label class="input-title">Model</label>
        <div class="input-group">
            <select class="form-control clsSelect2" id="prod_model_id">
                <option value="">Select</option>
                @foreach ($PModelData as $Row)
                <option value="{{ $Row->id }}">{{ $Row->model_name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    </div>
        <div class="row align-items-center pb-10 d-print-none">
        <div class="col-lg-12 pt-20 text-center">
            <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round" id="searchButton">Search</a>
        </div>
        </div>
<!-- Search Option End -->


<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                <thead>
                    <tr>
                        <th style="width:3%;">SL</th>
                        <th style="width:15%;">Name</th>
                        <th style="width:6%;">Image</th>
                        <th style="width:15%;">Barcode</th>
                        <th style="width:12%;">Supplier</th>
                        <th style="width:12%;">Price</th>
                        <th style="width:20%;">Details</th>
                        <!-- <th>Company</th> -->
                        <th style="width:10%;" class="text-center">Action</th>
                    </tr>
                </thead>

            </table>
        </div>

    </div>
</div>
<script>
function ajaxDataLoad(SupplierID = null, PGroupID = null, CategoryId = null, SubCatID = null, ModelID = null, BrandID = null) {

    $('.clsDataTable').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        order: [
            [1, "ASC"]
        ],
        stateSave: true,
        stateDuration: 1800,
        // aaSorting: [[0,'asc']],
        // iDisplayLength: 20,
        // ordering: false,
        // lengthMenu: [
        //     [20, 30, 50, 100],[20, 30, 50, 100]
        // ],
        "ajax": {
            "url": "{{route('productDatatable')}}",
            "dataType": "json",
            "type": "post",
            "data": {
                SupplierID:SupplierID,
                PGroupID: PGroupID,
                CategoryId: CategoryId,
                SubCatID: SubCatID,
                ModelID: ModelID,
                BrandID: BrandID
            }
        },
        columns: [{
                data: 'id',
                className: 'text-center'
            },
            {
                data: 'product_name'
            },
            {
                data: 'product_image',
                orderable: false
            },
            {
                data: 'prod_barcode'
            },
            {
                data: 'Supplier',
                orderable: false
            },
            {
                data: 'Price'
            },
            {
                data: 'Details',
                orderable: false
            },
            {
                data: 'action',
                orderable: false,
                className: 'text-center d-print-none'
            }

        ],
        'fnRowCallback': function(nRow, aData, Index) {
            var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData
                .action.action_link);
            $('td:last', nRow).html(actionHTML);
        }

    });
}

$(document).ready(function() {

    ajaxDataLoad();

    $('#searchButton').click(function() {
        var SupplierID = $('#prod_supplier_id').val();
        var PGroupID = $('#prod_group_id').val();
        var CategoryId = $('#prod_cat_id').val();
        var SubCatID = $('#prod_sub_cat_id').val();
        var ModelID = $('#prod_model_id').val();
        var BrandID = $('#prod_brand_id').val();
        ajaxDataLoad(SupplierID,PGroupID, CategoryId, SubCatID, ModelID, BrandID);
    });
});

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
        "{{url('pos/product/delete/')}}",
        "{{url('/ajaxDeleteCheck')}}",
        RowID,
        "{{base64_encode('product_id')}}",
        "",
        "{{base64_encode('pos_purchases_d')}}",
        "{{base64_encode('pos_purchases_r_d')}}",
        "{{base64_encode('pos_ob_stock_d')}}"
    );
}
</script>
@endsection
