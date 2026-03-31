@extends('Layouts.erp_master')
@section('content')
<!-- Page -->
<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                <thead>
                    <tr>
                        <th style="width:4%;">SL</th>
                        <th>Name</th>
                        <th>Supplier Type</th>
                        <th>Supplier Company</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th style="width:15%;" class="text-center">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- End Page -->
<script>
function ajaxDataLoad() {
    $('.clsDataTable').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        stateSave: true,
        stateDuration: 1800,
        // ordering: false,
        // lengthMenu: [[10, 20, 30, 50], [10, 20, 30, 50]],
        "ajax": {
            "url": "{{route('INVsupplierDatatable')}}",
            "dataType": "json",
            "type": "post",
            "data": {
                _token: "{{csrf_token()}}"
            }
        },
        columns: [

            {
                data: 'id',
                name: 'id',
                className: 'text-center'
            },
            {
                data: 'sup_name'
            },
            {
                data: 'supplier_type',
                name: 'supplier_type'
            },
            {
                data: 'sup_comp_name',
                name: 'sup_comp_name'
            },
            {
                data: 'sup_email',
                name: 'sup_email'
            },
            {
                data: 'sup_phone',
                name: 'sup_phone'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                className: 'text-center d-print-none'
            },

        ],
        'fnRowCallback': function(nRow, aData, Index) {
            var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData.action.action_link);
            $('td:last', nRow).html(actionHTML);
        }
    });
}

$(document).ready(function() {
    ajaxDataLoad();
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
        "{{url('inv/supplier/delete/')}}",
        "{{url('/ajaxDeleteCheck')}}",
        RowID,
        "{{base64_encode('supplier_id')}}",
        "{{base64_encode('is_delete,0')}}",
        "{{base64_encode('inv_products')}}",
        "{{base64_encode('inv_purchases_m')}}"
    );
}
</script>
@endsection
