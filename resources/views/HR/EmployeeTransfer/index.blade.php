@extends('Layouts.erp_master')
@section('content')

    <div class="table-responsive">
        <table class="table w-full table-hover table-bordered table-striped clsDataTable">
            <thead>
                <tr>
                    <th style="width: 3%;">SL</th>
                    <th class="text-center">Employee Code</th>
                    <th>Employee Name</th>
                    <th>Branch From</th>
                    <th>Branch To</th>
                    <th>Transfer Date</th>
                    <th>Status</th>
                    <th style="width: 15%; text-align: center;">Action</th>
                </tr>
            </thead>
        </table>
    </div>

    <script>
        $(document).ready(function() {
            ajaxDataLoad();
        });

        function ajaxDataLoad() {
            $('.clsDataTable').DataTable({
                destroy: true,
                processing: true,
                serverSide: true,
                "ajax": {
                    "url": "{{ url()->current() }}",
                    "dataType": "json",
                    "type": "post",
                    "data": {}
                },
                "columns": [{
                        data: 'sl',
                        name: 'sl',
                        orderable: false,
                        targets: 0,
                        className: 'text-center'
                    },
                    {
                        "data": "emp_code"
                    },
                    {
                        "data": "employeeName"
                    },
                    {
                        "data": "branchFrom"
                    },
                    {
                        "data": "branchTo"
                    },
                    {
                        "data": "transferDate"
                    },
                    {
                        "data": "status",
                        className: 'text-center'
                    },
                    {
                        "data": "action",
                        name: 'action',
                        orderable: false,
                        "width": "10%"
                    },
                ],
                // "columnDefs": [{
                //     "targets": 7,
                //     "createdCell": function(td, cellData, rowData, row, col) {
                //         $(td).addClass("text-center d-print-none");
                //         $(td).closest('tr').attr("cellData", cellData);
                //         $(td).html(
                //             '<a href="javascript:void(0)" title="View" class="btnView" data-target="#rebateViewModal" onclick="fnView(\'' +
                //             cellData + '\'' +
                //             ')"><i class="icon wb-eye mr-2 blue-grey-600"></i></a><a href=' +
                //             "{{ url()->current() }}" + '/edit/' + cellData +
                //             ' title="Edit" class="btnEdit"><i class="icon wb-edit mr-2 blue-grey-600"></i></a> <a href="javascript:void(0)" onclick="fnDelete(\'' +
                //             cellData + '\'' +
                //             ');" title="Delete" class=""><i class="icon wb-trash mr-2 blue-grey-600"></i></a>'
                //         );
                //     }
                // }],

                'fnRowCallback': function(nRow, aData, Index) {
                    var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name,
                        aData
                        .action.action_link);
                    $('td:last', nRow).html(actionHTML);
                }

            });
        }

        function fnApprove(rowID) {

            swal({
                    title: "Are you sure to approve data?",
                    text: "Once approve, this will be permanently approve!",
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
                                id: rowID
                            },
                        })
                        .done(function(response) {
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
                                $('.clsDataTable').DataTable().draw();
                            }
                        })
                        .fail(function() {
                            alert("error");
                        });
                });
        }

        function fnDelete(rowID) {
            /**
            * para 1 = link to delete without id
            * para 2 = ajax check link same for all
            * para 3 = id of deleting item
            * para 4 = matching column
            * para 5 = table 1
            * para 6 = table 2
            * para 7 = table 3
            */

            fnDeleteCheck(
                "{{ url()->current() . '/delete' }}",
                "{{ url('/ajaxDeleteCheck') }}",
                rowID,
            );
        }

    </script>

@endsection
