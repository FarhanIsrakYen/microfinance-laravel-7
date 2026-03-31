@extends('Layouts.erp_master')
@section('content')

<style>
    .page-header-actions{
        display: none;
    }

    tr td:last-child {
        text-align: center
    }
</style>

<!-- Page -->
<div class="table-responsive">
    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
        <thead>
            <tr>
             <th rowspan="2" style="width: 3%;">SL</th>
             <th rowspan="2">Member Name</th>
             <th rowspan="2">Member Code</th>
             <th rowspan="2">Loan Code</th>
             <th rowspan="2">Date</th>
             <th colspan="4" class="text-center">Write Off Amount</th>
             {{-- <th rowspan="2">Entry By</th> --}}
             <th rowspan="2" style="width: 80px;">Action</th>
         </tr>
         <tr>
             <th>P</th>
             <th>I</th>
             <th>P+I</th>
             <th>Write Off</th>
             {{-- <th>Rebate</th> --}}
         </tr>
     </thead>
    </table>
</div>

<!-- End Page -->
<script>
    function ajaxDataLoad( ){
        $('.clsDataTable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            "ajax":{
                "url": "{{ url()->current() }}",
                "dataType": "json",
                "type": "post",
                "data": {}
            },
            "columns": [
                {data: 'sl', name: 'sl', orderable: false, targets: 0, className: 'text-center'},
                { "data": "memberName" },
                { "data": "memberCode" },
                { "data": "loanCode" },
                { "data": "date" },
                { "data": "principalAmount", className: 'text-right' },
                { "data": "interestAmount", className: 'text-right' },
                { "data": "pi", className: 'text-right' },
                { "data": "writeOffAmount", className: 'text-right' },
                // { "data": "rebateAmount", className: 'text-right' },
                // { "data": "paymentType", className: 'text-center' },
                { "data": "id", name: 'action', orderable: false, "width": "10%" },
            ],
            'fnRowCallback': function(nRow, aData, Index) {
                var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData.action.action_link);
                $('td:last', nRow).html(actionHTML);
            },
            // "columnDefs": [{
            //     "targets": 9,
            //     "createdCell": function (td, cellData, rowData, row, col) {
            //         $(td).addClass("text-center d-print-none");
            //         $(td).closest('tr').attr("cellData", cellData);
            //         $(td).html('<a href="javascript:void(0)" onclick="fnDelete(\'' +
            //             cellData + '\'' +
            //             ');" title="Delete" class=""><i class="icon wb-trash mr-2 blue-grey-600"></i></a>'
            //             );
            //     }
            // }]

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
                var row = $('table tbody tr[cellData=' + '\'' + rowID + '\'' + ']');
                console.log(row);
                $.ajax({
                        url: "{{ url()->current() }}" + '/delete',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            id: rowID
                        },
                    })
                    .done(function (response) {

                        var row = $('table tbody tr[cellData=' + '\'' + rowID + '\'' + ']');
                        if (response['alert-type'] == 'error') {
                            swal({
                                icon: 'error',
                                title: 'Oops...',
                                text: response['message'],
                            });
                        } else {
                            swal({
                                icon: 'success',
                                title: 'Success...',
                                text: response['message'],
                            });
                            row.remove();
                            $('.clsDataTable').DataTable().draw();
                        }

                    })
                    .fail(function () {
                        alert("error");
                    });
            });
    }
</script>
<!-- End Page -->
@endsection
