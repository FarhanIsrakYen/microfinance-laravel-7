@extends('Layouts.erp_master')
@section('content')


<div class="table-responsive">
    <table class="table w-full table-hover table-bordered table-striped clsDataTable" id="Table">
        <thead>
            <tr>
                <th style="width: 3%;" class="text-center">SL</th>
                <th class="text-center">Samity Name </th>
                <th class="text-center">Samity Code </th>
                <th class="text-center">Field Officer</th>
                <th class="text-center">Samity Day</th>
                <th style="width: 10%;" class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>


<script>
    $(document).ready(function () {

        $('.page-header-actions').hide();
        ajaxDataLoad();

    });



    function ajaxDataLoad() {

        $('.clsDataTable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            order: [
                [0, "desc"]
            ],
            "ajax": {
                "url": "{{ url()->current() }}",
                "dataType": "json",
                "type": "post",
            },
            columns: [

                {
                    data: 'slNo',
                    className: 'text-center'
                },
                {
                    data: 'name'
                },
                {
                    data: 'samityCode'
                },
                {
                    data: 'fieldOfficerEmployee',
                    orderable: false,
                },
                {
                    data: 'autoProcessDay',
                    orderable: false,
                },
                {
                    data: 'id',
                    name: 'action',
                    orderable: false,
                    className: 'text-center'
                },

            ],
            "columnDefs": [{
                "targets": -1,
                "createdCell": function (td, cellData, rowData, row, col) {
                    $(td).addClass("text-center d-print-none");
                    $(td).closest('tr').attr("cellData", cellData);
                    console.log(rowData['isAutoprocessGiven']);
                    if (rowData['isAutoprocessGiven'] === true) {
                        $(td).html('<i class="icon fa  fa-check blue-grey-600"> </i><a href=' + "{{ url()->current() }}" + '/edit?samityId=' +cellData+ '&autoProcessDate=' + rowData['autoProcessDate'] +
                        ' title="View" class="btnView"> <i class="icon fa  wb-edit  blue-grey-600"></i></a>');                        
                    }
                    else{
                        $(td).html('<a href=' + "{{ url()->current() }}" + '/add?samityId=' + cellData + '&autoProcessDate=' + rowData['autoProcessDate'] +
                        ' title="View" class="btnView"><i class="icon fa-arrow-right blue-grey-600"></i></a>');
                    }                        
                }
            }]

        });

    }

</script>

@endsection
