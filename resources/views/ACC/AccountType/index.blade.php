@extends('Layouts.erp_master')
@section('content')

<div class="row">
    <div class="col-lg-12">
        <table class="table w-full table-hover table-bordered table-striped clsDataTable">
            <thead>
                <tr>
                    <th style="width:3%;">SL</th>
                    <th>Name</th>
                    <th>Parent</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            </tbody>


        </table>
    </div>
</div>
<!-- End Page -->
<script>
function ajaxDataLoad() {
    $('.clsDataTable').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        // ordering: false,
        // lengthMenu: [[10, 20, 30, 50], [10, 20, 30, 50]],
        "ajax": {
            "url": "{{route('accountTypeDatatable')}}",
            "dataType": "json",
            "type": "post",
            "data": {
                _token: "{{csrf_token()}}"
            }
        },
        columns: [{
                data: 'id',
                name: 'id',
                className: 'text-center'
            },
            {
                data: 'name',
                name: 'name'
            },
            {
                data: 'parent_id',
                name: 'parent_id',
                orderable: false
            },
            {
                data: 'description',
                name: 'description'
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
        "{{url('acc/acc_type/delete/')}}",
        "{{url('/ajaxDeleteCheck')}}",
        RowID,

    );
}
</script>
<!-- End Page -->


@endsection
