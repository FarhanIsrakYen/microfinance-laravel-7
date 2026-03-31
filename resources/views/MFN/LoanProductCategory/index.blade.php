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
                <th>Name</th>
                <th>Short Name</th>
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
                     "url": "{{route('loanProdCatDatatable')}}",
                     "dataType": "json",
                     "type": "post",
                     "data":{ _token: "{{csrf_token()}}"}
                   },
            "columns": [
                { data: 'sl', name: 'sl', orderable: false, targets: 1,className: 'text-center'},
                { "data": "name" },
                { "data": "shortName" },
                { "data": "id", name: 'action', orderable: false },
            ],
            'fnRowCallback': function(nRow, aData, Index) {
                var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData.action.action_link);
                $('td:last', nRow).html(actionHTML);
            },
            // "columnDefs": [ {
            //   "targets": 3,
            //   "createdCell": function (td, cellData, rowData, row, col) {
            //     $(td).addClass("text-center d-print-none");
            //     $(td).closest('tr').attr("cellData", cellData);
            //     $(td).html('<a href="./loanProductCategory/view/'+cellData+'" title="View" class="btnView"><i class="icon wb-eye mr-2 blue-grey-600"></i></a> <a href="./loanProductCategory/edit/'+cellData+'" title="Edit" class="btnEdit"><i class="icon wb-edit mr-2 blue-grey-600"></i></a> <a href="javascript:void(0)" onclick="fnDelete('+cellData+');" title="Delete" class=""><i class="icon wb-trash mr-2 blue-grey-600"></i></a>');
                
            //   }
            // } ]
        });
    }

    $(document).ready( function () {
        ajaxDataLoad();
    });


    function fnDelete(RowID) {
        /**
         * para1 = link to delete without id
         * para 2 = ajax check link same for all
         * para 3 = id of deleting item
         * para 4 = matching column
         * para 5 = table 1
         * para 6 = table 2
         * para 7 = table 3
         */

        // fnDeleteCheck(
        //     "{{url('mfn/loanProductCategory/delete/')}}",
        //     "{{url('/ajaxDeleteCheck')}}",
        //     RowID,
        // );

        console.log(RowID);

        fnDeleteCheck(
            "{{url('mfn/loanProductCategory/delete/')}}",
            "{{url('/ajaxDeleteCheck')}}",
            RowID,
            "{{base64_encode('productCategoryId')}}",
            "",
            "{{base64_encode('mfn_loan_products')}}"
        );
    }
 </script>

@endsection
