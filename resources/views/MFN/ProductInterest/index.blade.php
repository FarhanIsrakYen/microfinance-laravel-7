@extends('Layouts.erp_master')

@section('content')
<!-- Page -->

<div class="table-responsive">
    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
        <thead>
            <tr>
                <th style="width: 3%;">SL</th>
                <th>Name</th>
                <th>Short Name</th>
                <th>Product Code</th>
                <th>Product Type</th>                
                <th style="width: 10%;" class="text-center">Action</th>
            </tr>
        </thead>
    </table>
</div>
<!-- End Page -->
<script>
function ajaxDataLoad(){
    $('.clsDataTable').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        "ajax":{
                 "url": "{{ url()->current() }}",
                 "dataType": "json",
                 "type": "post",
               },
        "columns": [
            {data: 'sl', name: 'sl', orderable: false, targets: 1,className: 'text-center'},
            { "data": "name" },
            { "data": "shortName" },
            { "data": "productCode" },
            { "data": "productType" },
            { "data": "id", name: 'action', orderable: false, "width": "10%" },
        ],
        "columnDefs": [ {
          "targets": 5,
          "createdCell": function (td, cellData, rowData, row, col) {
            $(td).addClass("text-center d-print-none");
            $(td).closest('tr').attr("cellData", cellData);
            $(td).html('<a href="./productInterest/view/'+cellData+'" title="View" class="btnView"><i class="icon wb-eye mr-2 blue-grey-600"></i></a> <a href="./productInterest/edit/'+cellData+'" title="Edit" class="btnEdit"><i class="icon wb-edit mr-2 blue-grey-600"></i></a>');
            
          }
        } ]
    });
}

$(document).ready( function () {

    ajaxDataLoad();

});


 </script>
<!-- End Page -->
@endsection
