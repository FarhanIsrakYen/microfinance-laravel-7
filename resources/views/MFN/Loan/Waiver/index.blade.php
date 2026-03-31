@extends('Layouts.erp_master')
@section('content')
<style>
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
             <th colspan="4" class="text-center">Weiver Amount</th>
             <th rowspan="2" style="width: 80px;">Action</th>
         </tr>
         <tr>
             <th>P</th>
             <th>I</th>
             <th>P+I</th>
             <th>Weiver</th>
         </tr>
     </thead>
    </table>
</div>

@include('MFN.Loan.Waiver.view')

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
                { "data": "amount", className: 'text-right' },
                { "data": "waiver", className: 'text-right' },
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
            //         $(td).html('<a href="javascript:void(0)" title="View" class="btnView" data-target="#waiverViewModal" onclick="fnView(\'' +
            //             cellData + '\'' +
            //             ')"><i class="icon wb-eye mr-2 blue-grey-600"></i></a><a href="javascript:void(0)" onclick="fnDelete(\'' +
            //             cellData + '\'' +
            //             ');" title="Delete" class=""><i class="icon wb-trash mr-2 blue-grey-600"></i></a>'
            //             );
            //     }
            // }]

        });
    }

    $(document).ready( function () {
        ajaxDataLoad();
        //prevent redirect on view and show modal
        $(document).on('click', '.btnView', function(e) {
            e.preventDefault();
            let ref= $(this).attr('href');
            ref= ref.split('/');
            let id= ref[ref.length -1];
            fnView(id);
        });
    });

    function fnView(id) {

        $('#waiverViewModal').modal('toggle');

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
                $('#waiverDate').html(response.waiverDate);
                $('#waiverAmount').html(response.waiverAmount);
                $('#entryBy').html(response.entryBy);
                $('#notes').html(response.notes);

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
</script>
<!-- End Page -->
@endsection
