@extends('Layouts.erp_master')

@section('content')

<div class="table-responsive">
    <table class="table table-hover table-bordered table-striped clsDataTable">
        <thead>
            <tr>
                <th style="width: 5%;">SL</th>
                <th>Name</th>
                <th style="width: 15%;" class="text-center">Action</th>
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
                     "url": "{{route('empDesgDatatable')}}",
                     "dataType": "json",
                     "type": "post",
                     "data":{ _token: "{{csrf_token()}}"}
                   },
            "columns": [
                { data: 'sl', name: 'sl', orderable: false, targets: 1,className: 'text-center'},
                { "data": "name" },
                { "data": "id", name: 'action', orderable: false },
            ],
            "columnDefs": [ {
              "targets": 2,
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass("text-center d-print-none");
                $(td).closest('tr').attr("cellData", cellData);
                $(td).html('<a href="./emp_designation/edit/'+cellData+'" title="Edit" class="btnEdit"><i class="icon wb-edit mr-2 blue-grey-600"></i></a> <a href="javascript:void(0)" onclick="fnDelete('+cellData+');" title="Delete" class=""><i class="icon wb-trash mr-2 blue-grey-600"></i></a>');
                
              }
            } ]
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
            $.ajax({
                url: './emp_designation/delete',
                type: 'POST',
                dataType: 'json',
                data: { desigId : rowID },
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

@endsection
