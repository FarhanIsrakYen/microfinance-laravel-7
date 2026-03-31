@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\CommonService as Common;
?>

<!-- Search Option start -->
<?php
$PCategoryData = Common::ViewTableOrder('bill_p_categories',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'cat_name'],
    ['cat_name', 'ASC']);
?>

<div class="row align-items-center pb-10 d-print-none">
    <!-- <div class="row align-items-center d-flex justify-content-center pb-10 d-print-none"> -->
    <div class="col-lg-2">
        <label class="input-title">Category</label>
        <div class="input-group">
            <select class="form-control clsSelect2" id="prod_cat_id">
                <option value="">Select</option>
                @foreach ($PCategoryData as $Row)
                <option value="{{ $Row->id }}">{{ $Row->cat_name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-lg-2 pt-20 text-center">
        <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round" id="searchButton">Search</a>
    </div>
</div>
<!-- Search Option End -->


<div class="row">
    <div class="col-lg-12">
        <div class="">
            <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                <thead>
                    <tr>
                        <th style="width:3%;">SL</th>
                        <th style="width:15%;">Name</th>
                        <th style="width:6%;">Image</th>
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
function ajaxDataLoad(CategoryId = null) {
  // , ModelID = null, BrandID = null

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
            "url": "{{route('productBillDatatable')}}",
            "dataType": "json",
            "type": "post",
            "data": {
                CategoryId: CategoryId
            }
        },
        columns: [{
                data: 'id',
                orderable: false,
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
                data: 'Supplier'
            },
            {
                data: 'Price',
                orderable: false
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
        var CategoryId = $('#prod_cat_id').val();
        ajaxDataLoad(CategoryId);
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
        "{{url('bill/product/delete/')}}",
        "{{url('/ajaxDeleteCheck')}}",
        RowID,
        // "{{base64_encode('product_id')}}",
        // "",
        // "{{base64_encode('pos_purchases_d')}}",
        // "{{base64_encode('pos_purchases_r_d')}}",
        // "{{base64_encode('pos_ob_stock_d')}}"
    );
}
</script>
@endsection
