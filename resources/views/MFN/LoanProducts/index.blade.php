@extends('Layouts.erp_master')

@section('content')
<!-- Page -->
<style>
    tr td:last-child {
        text-align: center
    }
</style>
<div class="table-responsive">
    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
        <thead>
            <tr>
                <th style="width: 3%;">SL</th>
                <th>Name</th>
                <th>Short Name</th>
                <th>Product Code</th>
                <th>Product Type</th>
                <th>Product Category</th>
                <th>Funding Org.</th>                
                <th>Start Date</th>                
                <th style="width: 10%;" class="text-center">Action</th>
            </tr>
        </thead>
    </table>
</div>

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
            { "data": "productCategory" },
            { "data": "fundingOrg" },
            { "data": "startDate" },
            { "data": "id", name: 'action', orderable: false, "width": "10%" },
        ],
        'fnRowCallback': function(nRow, aData, Index) {
                var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData.action.action_link);
                $('td:last', nRow).html(actionHTML);
            },
        // "columnDefs": [ {
        //   "targets": 8,
        //   "createdCell": function (td, cellData, rowData, row, col) {
        //     $(td).addClass("text-center d-print-none");
        //     $(td).closest('tr').attr("cellData", cellData);
        //     $(td).html('<a href="./loanProducts/view/'+cellData+'" title="View" class="btnView"><i class="icon wb-eye mr-2 blue-grey-600"></i></a> <a href="./loanProducts/edit/'+cellData+'" title="Edit" class="btnEdit"><i class="icon wb-edit mr-2 blue-grey-600"></i></a> <a href="javascript:void(0)" onclick="fnDelete('+cellData+');" title="Delete" class=""><i class="icon wb-trash mr-2 blue-grey-600"></i></a>');
            
        //   }
        // } ]
    });
}

$(document).ready( function () {

    ajaxDataLoad();

});


function fnDelete(rowID) {

    swal({
        title: "Are you sure to delete data?",
        text: "Once Delete, this will be permanently delete!",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    })
    .then((isConfirm) => {
        if (!isConfirm) {
            return false;
        }
        var row = $('table tbody tr[cellData='+rowID+']');
        console.log(row);
        $.ajax({
            url: './loanProducts/delete',
            type: 'POST',
            dataType: 'json',
            data: {productId : rowID},
        })
        .done(function(response) {

            var row = $('table tbody tr[cellData='+rowID+']');
            
            if (response['alert-type']=='error') {
                swal({
                    icon: 'error',
                    title: 'Oops...',
                    text: response['message'],
                });
            }
            else{
                swal({
                    icon: 'success',
                    title: 'Success...',
                    text: response['message'],
                });
                row.remove();
            }
                
        })
        .fail(function() {
            alert("error");
        });
    });
    
    
    
}
 </script>
<!-- End Page -->
@endsection
