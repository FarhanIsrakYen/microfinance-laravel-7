@extends('Layouts.erp_master')

@section('content')

<div class="table-responsive">
    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
        <thead>
            <tr class="text-center">
                <th width="5%">SL</th>
                <th width="25%">Notice</th>
                <th width="50%">Branch</th>
                <th width="12%">Status</th>
                <th width="8%">Action</th>
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
            searching: false,
            "ajax":{
                     "url": "{{route('noticeDatatable')}}",
                     "dataType": "json",
                     "type": "post",
                     "data":{ _token: "{{csrf_token()}}"}
                   },
            "columns": [
                { data: 'sl', name: 'sl', orderable: false, targets: 1,className: 'text-center'},
                { "data": "name",orderable: false, },
                { "data": "branch", orderable: false },
                { "data": "status",className: 'text-center', orderable: false},
                { "data": "id", name: 'action', orderable: false },
            ],
            "columnDefs": [ {
              "targets": 4,
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass("text-center d-print-none");
                $(td).closest('tr').attr("cellData", cellData);
                $(td).html('<a href="./notice/edit/'+cellData+'" title="Edit" class="btnEdit"><i class="icon wb-edit mr-2 blue-grey-600"></i></a> <a href="javascript:void(0)" onclick="fnDelete('+cellData+');" title="Delete" class=""><i class="icon wb-trash mr-2 blue-grey-600"></i></a>');
                
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
            console.log(row);
            $.ajax({
                url: './notice/delete/'+rowID,
                type: 'POST',
                dataType: 'json',
                data: {noticeId : rowID},
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
                    $('.clsDataTable').DataTable().draw();
                }
                    
            })
            .fail(function() {
                alert("error");
            });
        });   
    }
 </script>

@endsection
