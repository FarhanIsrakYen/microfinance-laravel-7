@extends('Layouts.erp_master')

@section('content')
<!-- Page -->

<style>
.table>tbody td p {
    margin-bottom: 0px;
}
</style>
<!-- Page -->
<div class="row">
    <div class="col-lg-12 table-responsive">
        <table class="table w-full table-hover table-bordered table-striped clsDataTable">
            <thead>
                <tr>
                    <th style="width: 3%;">SL</th>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Contact Info</th>
                    <th>Opening Date</th>
                    <th>Company</th>
                    <th>Approve</th>
                    <th style="width: 15%;">Action</th>
                </tr>
            </thead>
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
        order: [
            [1, "ASC"]
        ],
        // stateSave: true,
        // stateDuration: 1800,
        // ordering: false,
        // lengthMenu: [[10, 20, 30, 50], [10, 20, 30, 50]],
        "ajax": {
            "url": "{{route('branchDatatable')}}",
            "dataType": "json",
            "type": "post",
            "data": {
                _token: "{{csrf_token()}}"
            }
        },
        columns: [
            {
                data: 'id',
                className: 'text-center'
            },
            {
                data: 'branch_name',
                name: 'branch_name'
            },
            {
                data: 'branch_code',
                name: 'branch_code',
                className: 'text-center'
            },
            {
                data: 'Contact Info',
                name: 'Contact Info',
            },
            {
                data: 'opening Date',
                name: 'opening Date'
            },
            {
                data: 'comp_name',
                name: 'comp_name',
                orderable: false,
            },
            {
                data: 'approved',
                name: 'approved',
                className: 'text-center',
                orderable: false,
            },
            {
                data: 'action',
                orderable: false,
                className: 'text-center d-print-none'
            },
        ],
        'fnRowCallback': function(nRow, aData, Index) {
            var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData.action.action_link);
            $('td:last', nRow).html(actionHTML);
            // $('td:nth-child(8)', nRow).html(actionHTML);
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
        "{{url('gnl/branch/delete/')}}",
        "{{url('/ajaxDeleteCheck')}}",
        RowID,
        "{{base64_encode('branch_id')}}",
        "",
        "{{base64_encode('gnl_map_area_branch')}}"
    );
}
</script>
<!-- End Page -->
@endsection
