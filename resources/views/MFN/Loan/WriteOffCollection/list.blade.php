@extends('Layouts.erp_master')
@section('content')

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
                // { "data": "paymentType", className: 'text-center' },
                { "data": "id", name: 'action', orderable: false, "width": "10%" },
            ],
            "columnDefs": [{
                "targets": 9,
                "createdCell": function (td, cellData, rowData, row, col) {
                    $(td).addClass("text-center d-print-none");
                    $(td).closest('tr').attr("cellData", cellData);
                    $(td).html('<a href="./add/'+cellData+'" title="Add Collection" class=""><i class="fa fa-arrow-circle-right" aria-hidden="true"></i></a>');
                }
            }]

        });
    } 

    $(document).ready( function () {
        ajaxDataLoad();
    });

    function fnAdd(id) {
        
    }
</script>
<!-- End Page -->
@endsection
