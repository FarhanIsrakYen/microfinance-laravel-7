@extends('Layouts.erp_master')
@section('content')

<style>
    .page-header-actions{
        display: none;
    }
</style>
<!-- Page -->
<div class="row align-items-center">
    <div class="col-lg-12 text-right">
        <button type="button" class="btn btn-primary btn-round" data-toggle="modal"
            data-target="#interestProvisionModal">
            Generate
        </button>
    </div>
</div>
<hr>

<div class="table-responsive">
    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
        <thead>
            <tr class="text-center">
                <th style="width: 3%;">SL</th>
                <th>Date</th>
                <th>Provision Code</th>
                <th class="text-center">Amount (TK)</th>
                <th style="width: 80px;">Action</th>
            </tr>
        </thead>
    </table>
</div>

@include('MFN.Savings.InterestProvision.generate_provision')
@include('MFN.Savings.InterestProvision.view_modal')

<!-- End Page -->
<script>
    $(document).ready(function () {
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
                    "data": "provisionDate",
                    className: 'text-center'
                },
                {
                    "data": "provisionCode",
                    className: 'text-center'
                },
                {
                    "data": "amount",
                    className: 'text-right'
                },
                {
                    "data": "id",
                    name: 'action',
                    orderable: false,
                    "width": "10%"
                },
            ],
            "columnDefs": [{
                "targets": 4,
                "createdCell": function (td, cellData, rowData, row, col) {
                    $(td).addClass("text-center d-print-none");
                    $(td).closest('tr').attr("cellData", cellData);
                    $(td).html(
                        '<a href="javascript:void(0)" title="View" class="btnView" data-target="#rebateViewModal" onclick="fnView(\'' +
                        cellData + '\'' +
                        ')"><i class="icon wb-eye mr-2 blue-grey-600"></i></a><a href="javascript:void(0)" onclick="fnDelete(\'' +
                        cellData + '\'' +
                        ');" title="Delete" class=""><i class="icon wb-trash mr-2 blue-grey-600"></i></a>'
                    );
                }
            }]

        });
    }
    
    function fnView(id) {
        
        $('#provisionViewTable tbody').remove();
        $('#spinnerId').show('fast');
        $('#provisionViewModal').modal('toggle');

        $.ajax({
            type: "POST",
            url: "{{ url()->current() }}" + '/view',
            data: { id : id},
            dataType: "json",
            success: function (response) {
            
                $('#spinnerId').hide('fast');
                $('#provisionViewTable').append(response.provisionTableHtml);

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
