@extends('Layouts.erp_master')
@section('content')

<!-- Page -->
<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                <thead>
                    <tr>
                        <th>SL#</th>
                        <th>Ordered No</th>
                        <th>Ordered Date</th>
                        <th>Delivery Date</th>
                        {{-- <th>Ordered From</th> --}}
                        <th>Supplier</th>
                        <th>Product Quantity</th>
                        <th>Details</th>
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
                data: 'order_no',
                className: 'text-center'
            },
            {
                data: 'order_date',
                className: 'text-center'
            },
            {
                data: 'delivery_date',
                className: 'text-center'
            },
            // {
            //     data: 'order_from',
            //     className: 'text-center'
            // },
            {
                data: 'sup_name',
                className: 'text-center'
            },
            {
                data: 'total_quantity',
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
        // drawCallback: function (oResult) {
        //     $('#tQtn').html(oResult.json.final_ttl_qtn);
        // },
    });
}

</script>
@endsection