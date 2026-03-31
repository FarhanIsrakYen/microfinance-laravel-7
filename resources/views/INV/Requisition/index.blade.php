@extends('Layouts.erp_master')
@section('content')

<!-- Page -->
<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                <thead>
                    <tr>
                        <th>SL</th>
                        <th>Date</th>
                        <th>Requisition No</th>
                        {{-- <th>Requisition To</th> --}}
                        <th>Requisition From</th>
                        <th>Products</th>
                        <th>Total Quantity</th>
                        {{-- <th>Supplier</th> --}}
                        {{-- <th>Details</th> --}}
                        <th>Status</th>
                        <th width="8%">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
<!-- End Page -->
<script type="text/javascript">

$(document).ready(function() {

    ajaxDataLoad();
});

function ajaxDataLoad() {

    var table = $('.clsDataTable').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        order: [[ 1, "DESC" ]],
        // stateSave: true,
        // stateDuration: 1800,
        // ordering: false,
        // lengthMenu: [[10, 20, 30, 50], [10, 20, 30, 50]],
        "ajax": {
            "url": "{{ url('inv/requisition') }}",
            "dataType": "json",
            "type": "post",
        },
        columns: [
            {
                data: 'id', orderable: false,
                className: 'text-center'
            },
            {
                data: 'requisition_date',
            },
            {
                data: 'requisition_no',
            },
            // {
            //     data: 'branch_to',
            //     className: 'text-center'
            // },
            {
                data: 'branch_from',
                orderable: false
            },
            {
                data: 'product_name',
            },
            {
                data: 'total_quantity',
                className: 'text-center'
            },
            // {
            //     data: 'supplier_name',
            //     className: 'text-center'
            // },
            // {
            //     data: 'details'
            // },
            {
                data: 'status',
                className: 'text-center'
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
        "{{url('inv/requisition/delete/')}}",
        "{{url('/ajaxDeleteCheck')}}",
        RowID,
        "{{base64_encode('requisition_no')}}",
        "{{base64_encode('is_delete,0')}}",
        "{{base64_encode('inv_issues_m')}}"
    );
}


</script>
@endsection
