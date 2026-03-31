@extends('Layouts.erp_master_full_width')
@section('content')
<?php
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;

$StartDate =  Common::systemCurrentDate();
$EndDate = Common::systemCurrentDate();
$productData = Common::ViewTableOrder('pos_products',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'product_name', 'prod_barcode'],
    ['product_name', 'ASC']);


?>
<div class="panel">
    <div class="panel-body">
<!-- Search Option start -->
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
        <a href="javascript:void(0)" id="btnSearch" class="btn btn-primary btn-round">Search</a>
    </div>
</div>
<!-- Search Option End -->

        <!-- Page -->
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead>
                            <tr>
                                <th width="3%">SL</th>
                                <th width="12%">Date</th>
                                <th width="12%">Requisition No</th>
                                <th width="15%">Requisition From</th>
                                <th with="25%" >Products</th>
                                <th width="15%">Total Quantity</th>
                                <th width="10%">Status</th>
                                <th width="8%">Action</th>
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
    ajaxDataLoad();

    $('#btnSearch').click(function() {

        var sDate = $('#start_date').val();
        var eDate = $('#end_date').val();
        var branchID = $('#branch_id').val();
        ajaxDataLoad(sDate, eDate, branchID);
    });

    
    
});

function ajaxDataLoad(sDate = null, eDate = null, branchID = null) {

    var table = $('.clsDataTable').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        order: [[ 1, "DESC" ]],
        stateSave: true,
        stateDuration: 1800,
        // ordering: false,
        // lengthMenu: [[10, 20, 30, 50], [10, 20, 30, 50]],
        "ajax": {
            "url": "{{ url('pos/requisition') }}",
            "dataType": "json",
            "type": "post",
            "data": {
                _token: "{{csrf_token()}}",
                sDate: sDate,
                eDate: eDate,
                branchID: branchID
            }
        },
        columns: [
            {
                data: 'id',
                // orderable: false,
                className: 'text-center'
            },
            {
                data: 'requisition_date',
            },
            {
                data: 'requisition_no',
            },
            {
                data: 'branch_from',
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
                data: 'status',
                className: 'text-center',
                orderable: false,
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
        //     $('#tQtn').html(oResult.json.final_ttl_qtn);
        // },
    });
}

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
        "{{url('pos/requisition/delete/')}}",
        "{{url('/ajaxDeleteCheck')}}",
        RowID,
        "{{base64_encode('requisition_no')}}",
        "{{base64_encode('is_delete,0')}}",
        "{{base64_encode('pos_issues_m')}}"
    );
}


</script>
@endsection
