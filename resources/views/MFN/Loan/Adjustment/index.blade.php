@extends('Layouts.erp_master')
@section('content')

<!-- Page -->
<div class="table-responsive">
    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
        <thead>
            <tr>
             <th style="width: 3%;">SL</th>
             <th>Member Name</th>
             <th>Member Code</th>
             <th>Loan Code</th>
             <th width="8%">Date</th>
             <th width="8%">Adjustment Amount</th>
             <th>Adjustment Details</th>
             <th>Status</th>
             <th>Entry By</th>
             <th style="width: 80px;">Action</th>
         </tr>
     </thead>
    </table>
</div>

@include('MFN.Loan.Adjustment.view')

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
                "data": {}
            },
            "columns": [
                {data: 'sl', name: 'sl', orderable: false, targets: 0, className: 'text-center'},
                { "data": "memberName" },
                { "data": "memberCode" },
                { "data": "loanCode" },
                { "data": "date" },
                { "data": "adjustmentAmount", className: "text-right" },
                { "data": "adjustmentDetails" },
                { "data": "status" },
                { "data": "entryBy" },
                { "data": "id", name: 'action', orderable: false, "width": "10%" },
            ],
            "columnDefs": [{
                "targets": 9,
                "createdCell": function (td, cellData, rowData, row, col) {
                    $(td).addClass("text-center d-print-none");
                    $(td).closest('tr').attr("cellData", cellData);
                    $(td).html('<a href="javascript:void(0)" title="View" class="btnView" data-target="#rebateViewModal" onclick="fnView(\'' +
                        cellData + '\'' +
                        ')"><i class="icon wb-eye mr-2 blue-grey-600"></i></a><a href=' +
                        "{{ url()->current() }}" + '/edit/' + cellData +
                        ' title="Edit" class="btnEdit"><i class="icon wb-edit mr-2 blue-grey-600"></i></a> <a href="javascript:void(0)" onclick="fnDelete(\'' +
                        cellData + '\'' +
                        ');" title="Delete" class=""><i class="icon wb-trash mr-2 blue-grey-600"></i></a><a href="javascript:void(0)" onclick="fnApprove(\'' +
                        cellData + '\'' +
                        ');" title="Approve" class=""><i class="fa fa-check-square-o mr-2 blue-grey-600" aria-hidden="true"></i></a>'
                        );
                }
            }]

        });
    } 

    $(document).ready( function () {
        ajaxDataLoad();
    });

    function fnView(id) {

        $('#adjViewModal').modal('toggle');

        $.ajax({
            type: "POST",
            url: "{{ url()->current() }}" + '/view',
            data: { id : id},
            dataType: "json",
            success: function (response) {

                $('#memberName').html(response.memberName);
                $('#memberCode').html(response.memberCode);
                $('#loanCode').html(response.loanCode);
                $('#samityCode').html(response.samityCode);
                $('#date').html(response.date);
                $('#adjAmount').html(response.adjAmount);
                $('#adjDetails').html(response.adjDetails);
                $('#entryBy').html(response.entryBy);

            },
            error: function(){
                alert('error!');
            }
        });
    }
    
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

    function fnApprove(id) {

        swal({
                title: "Are you sure to approve this data?",
                text: "Once Approve, You can't change after!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((isConfirm) => {

                if (!isConfirm) {
                    return false;
                }

                $.ajax({
                        url: "{{ url()->current() }}" + '/approve',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            id: id
                        },
                    })
                    .done(function (response) {

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

                            ajaxDataLoad();
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
