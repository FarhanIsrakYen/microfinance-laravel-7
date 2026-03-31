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
             <th colspan="3" class="text-center">Write Off Amount</th>
             <th rowspan="2" class="text-center">Collection Amount</th>
             <th rowspan="2">Entry By</th>
             <th rowspan="2" style="width: 80px;">Action</th>
         </tr>
         <tr>
             <th>P</th>
             <th>I</th>
             <th>P+I</th>
             {{-- <th>Write Off</th> --}}
         </tr>
     </thead>
    </table>
</div>

@include('MFN.Loan.WriteOffCollection.view')

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
                { "data": "loanAmount", className: 'text-right' },
                { "data": "ineterestAmount", className: 'text-right' },
                { "data": "pi", className: 'text-right' },
                { "data": "collectionAmount", className: 'text-right' },
                { "data": "entryBy", className: 'text-center' },
                { "data": "id", name: 'action', orderable: false, "width": "10%" },
            ],
            'fnRowCallback': function(nRow, aData, Index) {
                var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData.action.action_link);
                $('td:last', nRow).html(actionHTML);
            },
            // "columnDefs": [{
            //     "targets": 10,
            //     "createdCell": function (td, cellData, rowData, row, col) {
            //         $(td).addClass("text-center d-print-none");
            //         $(td).closest('tr').attr("cellData", cellData);
            //         $(td).html('<a href="javascript:void(0)" title="View" class="btnView" data-target="#loanViewModal" onclick="fnView(\'' +
            //             cellData + '\'' +
            //             ')"><i class="icon wb-eye mr-2 blue-grey-600"></i></a> <a href=' +
            //             "{{ url()->current() }}" + '/edit/' + cellData +
            //             ' title="Edit" class="btnEdit"><i class="icon wb-edit mr-2 blue-grey-600"></i></a> <a href="javascript:void(0)" onclick="fnDelete(\'' +
            //             cellData + '\'' +
            //             ');" title="Delete" class=""><i class="icon wb-trash mr-2 blue-grey-600"></i></a>'
            //             );
            //     }
            // }]

        });
    } 

    $(document).ready( function () {
        ajaxDataLoad();
        $(document).on('click', '.btnView', function(e) {
            e.preventDefault();
            let ref= $(this).attr('href');
            ref= ref.split('/');
            let id= ref[ref.length -1];
            fnView(id);
        });
    });

    function fnView(id) {

        $('#writeOffCollnViewModal').modal('toggle');

        $.ajax({
            type: "POST",
            url: "{{ url()->current() }}" + '/view',
            data: { id : id},
            dataType: "json",
            success: function (response) {

                console.log(response)

                $('#memberName').html(response.memberName);
                $('#memberCode').html(response.memberCode);
                $('#loanCode').html(response.loanCode);
                $('#date').html(response.date);
                $('#principalAmount').html(response.principalAmount);
                $('#interestAmount').html(response.interestAmount);
                $('#amount').html(response.amount);
                $('#principalWithInterest').html(response.principalWithInterest);
                $('#entryBy').html(response.entryBy);
                $('#paymentType').html(response.paymentType);

                // $('#loanViewModal').modal('toggle');

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
