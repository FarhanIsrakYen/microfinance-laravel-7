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
                        <th>Ordered Date</th>
                        <th>Ordered No</th>
                        <th>Products</th>
                        <th>Delivery Date</th>
                        {{-- <th>Ordered From</th> --}}
                        <th>Supplier</th>
                        <th>Product Quantity</th>
                        <th>Status</th>
                        <th width="10%">Action</th>
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
        order: [[ 1, "ASC" ]],
        stateSave: true,
        stateDuration: 1800,
        // ordering: false,
        // lengthMenu: [[10, 20, 30, 50], [10, 20, 30, 50]],
        "ajax": {
            "url": "{{ url('inv/product_order') }}",
            "dataType": "json",
            "type": "post",
        },
        columns: [
            {
                data: 'id', orderable: false,
                className: 'text-center'
            },
            {
                data: 'order_date',
            },
            {
                data: 'order_no',
            },
            {
                data: 'product_name',
            },
            {
                data: 'delivery_date',
            },
            // {
            //     data: 'order_from',
            //     className: 'text-center'
            // },
            {
                data: 'order_to',
            },
            {
                data: 'total_quantity',
                className: 'text-center'
            },
            {
                data: 'status',
                orderable: false,
                className: 'text-center'
            },
            {
                data: 'action',
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
        "{{url('inv/product_order/delete/')}}",
        "{{url('/ajaxDeleteCheck')}}",
        RowID,
    );
}

</script>
@endsection
