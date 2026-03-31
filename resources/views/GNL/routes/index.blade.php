@extends('Layouts.erp_master')

@section('content')
<!-- Page -->
<div class="row">
    <div class="col-lg-12 table-responsive">
        <table class="table w-full table-hover table-bordered table-striped clsDataTable">
            <thead>
                <tr>
                    <th style="width: 3%;">SL#</th>
                    <th>Route</th>
                    <th>Module</th>
                    <th>Component</th>
                    <th>Operation</th>
                    <!-- <th style="width: 15%;">Action</th> -->
                </tr>
            </thead>
        </table>
    </div>
</div>
<!-- End Page -->
<script>
function ajaxDataLoad(){
    $('.clsDataTable').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        "ajax":{
                 "url": "{{url('gnl/ajaxRoutesIndex')}}",
                 "dataType": "json",
                 "type": "post",
                 "data":{ _token: "{{csrf_token()}}"}
               },
        "columns": [
            {data: 'sl', name: 'sl', orderable: false, targets: 1,className: 'text-center'},
            { "data": "route" },
            { "data": "moduleId" },
            { "data": "componentId" },
            { "data": "operationId" },
        ],
    });
}

$(document).ready( function () {
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
