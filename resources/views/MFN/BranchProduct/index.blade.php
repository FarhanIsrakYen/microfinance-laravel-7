@extends('Layouts.erp_master')

@section('content')
<style>
    tr td:last-child {
        text-align: center
    }
</style>

<div class="table-responsive">
    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
        <thead>
            <tr>
                <th style="width: 5%;">SL</th>
                <th>Branch</th>
                <th>Branch Code</th>
                <th>Loan Product</th>
                <th>Savings Product</th>
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
                     "url": "{{route('prodAssignDatatable')}}",
                     "dataType": "json",
                     "type": "post",
                     "data":{ _token: "{{csrf_token()}}"}
                   },
            "columns": [
                { data: 'sl', name: 'sl', orderable: false, targets: 1,className: 'text-center'},
                { "data": "branchName" },
                { "data": "branchCode" },
                { "data": "loanProdctNames" },
                { "data": "savingProdctNames" },
                { "data": "branchId", name: 'action', orderable: false },
            ],
            'fnRowCallback': function(nRow, aData, Index) {
                var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData.action.action_link);
                $('td:last', nRow).html(actionHTML);
            },
            // "columnDefs": [ {
            //   "targets": 5,
            //   "createdCell": function (td, cellData, rowData, row, col) {
            //     $(td).addClass("text-center d-print-none");
            //     $(td).closest('tr').attr("cellData", cellData);
            //     $(td).html('<a href="./branchProduct/view/'+cellData+'" title="View" class="btnView"><i class="icon wb-eye mr-2 blue-grey-600"></i></a> <a href="./branchProduct/edit/'+cellData+'" title="Edit" class="btnEdit"><i class="icon wb-edit mr-2 blue-grey-600"></i></a>');
                
            //   }
            // } ]
        });
    }

    $(document).ready( function () {
        ajaxDataLoad();
    });

 </script>

@endsection
