@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;

    $flag = '' ;
    if ( Common::getBranchId() == 1 ||
        count(App\Services\HrService::getUserAccesableBranchIds()) > 1) {
            $flag = true;
    }
    else
        $flag = false;
?>

<!-- Search Option Start -->
<div class="row align-items-center d-flex justify-content-center">
    <!-- Html View Load For Branch Search -->
    {!! HTML::forBranchFeildSearch('all') !!}

    @if(Common::getBranchId() == 1)
    <div class="col-lg-2 text-center pt-20">
        <a href="javascript:void(0)" id="searchButton" name="searchButton" class="btn btn-primary btn-round">Search</a>
    </div>
    @endif
</div>
<!-- Search Option End -->
<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table w-full table-hover table-bordered table-striped clsDataTable" id="cust_due_sales_datatable">
                <thead>
                    <tr>
                        <th style="width:5%;">SL</th>
                        <th>Opening Date</th>
                        <th>Total Customer</th>
                        <th>Total Sale Amount</th>
                        <th>Total Collection Amount</th>
                        <th>Total Due Amount</th>
                        <th>Branch</th>
                        <th style="width:12%;">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- End Page -->

<script>
function ajaxDataLoad(BranchID = null) {

    $('.clsDataTable').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        stateSave: true,
        stateDuration: 1800,
        order: [[1, "DESC"]],
        "ajax": {
            "url": "{{ url('pos/customer_ob') }}",
            "dataType": "json",
            "type": "post",
            "data": {
                _token: "{{csrf_token()}}",
                BranchID: BranchID,
            }
        },
        render: false,
        columns: [{
                data: 'id',
                className: 'text-center'
            },
            {
                data: 'opening_date'
            },
            {
                data: 'total_customer'
            },
            {
                data: 'total_sales_amount',
                "className": "text-right",
            },
            {
                data: 'total_collection',
                "className": "text-right",
            },
            {
                data: 'total_due_amount',
                "className": "text-right",
            },
            {
                data: 'branch_name',orderable: false
            },
            {
                data: 'action',
                name: 'action',
                "className": "text-center",
                orderable: false
            },
        ],
        'fnRowCallback': function(nRow, aData, Index) {
            var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData.action.action_link);
            $('td:last', nRow).html(actionHTML);
        },
        drawCallback: function(oResult) {
            if (oResult.json) {

                var TotalRow = oResult.json.recordsTotal;
                var current_branch_id = oResult.json.current_branch_id;
                var access_branch = oResult.json.access_branch;

                if(current_branch_id === 1 || access_branch.length > 1 || TotalRow === 0){
                    $('.page-header-actions').show();
                }
                else{
                    $('.page-header-actions').hide();
                }

            }
        },
    });
}

$(document).ready(function() {
    ajaxDataLoad();

    $('#searchButton').click(function() {
        var BranchID = $('#branch_id').val();
        ajaxDataLoad(BranchID);
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
        "{{url('pos/customer_ob/delete/')}}",
        "{{url('/ajaxDeleteCheck')}}",
        RowID
    );
}

</script>


@endsection
