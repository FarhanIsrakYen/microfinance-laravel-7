@extends('Layouts.erp_master')

@section('content')
<style>
    tr td:last-child {
        text-align: center
    }
</style>
<!-- Search Options -->
<div class="row align-items-center pb-10 mb-4">

    <div class="col-lg-2">
        <label class="input-title">Branch</label>
        <div class="input-group">
            <select class="form-control clsSelect2" name="branch_id" id="branch_id">
                <option value="">Select Option</option>
                @foreach ($branchList as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->branch_name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-lg-2 pt-20 text-center">
        <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round"
            id="searchButton">Search</a>
    </div>
</div>
<div class="table-responsive">
    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
        <thead>
            <tr>
                <th style="width: 3%;">SL</th>
                <th>Samity</th>
                <th>Samity Code</th>
                <th>Branch</th>
                <th>Opening Date</th>
                <th>Closing Date</th>
                <th style="width: 15%;" class="text-center">Action</th>
            </tr>
        </thead>
    </table>
</div>


<script>
    function ajaxDataLoad(branch_id = null){
        $('.clsDataTable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            "ajax":{
                     "url": "{{url('mfn/samityclosing')}}",
                     "dataType": "json",
                     "type": "post",
                     "data": {
                        branchId: branch_id
                    }
                   },
            "columns": [
                {data: 'sl', name: 'sl', orderable: false, targets: 1,className: 'text-center'},
                { "data": "name" },
                { "data": "samityCode" },
                { "data": "branch" },
                { "data": "openingDate" },
                { "data": "closingDate" },
                { "data": "id", name: 'action', orderable: false, "width": "15%" },
            ],
            'fnRowCallback': function(nRow, aData, Index) {
                var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData.action.action_link);
                $('td:last', nRow).html(actionHTML);
            },
            // "columnDefs": [ {
            //   "targets": 6,
            //   "createdCell": function (td, cellData, rowData, row, col) {
            //     $(td).addClass("text-center d-print-none");
            //     $(td).closest('tr').attr("cellData", cellData);
            //     $(td).html('<a href="javascript:void(0)" onclick="fnDelete('+cellData+');" title="Delete" class=""><i class="icon wb-trash mr-2 blue-grey-600"></i></a>');
                
            //   }
            // } ]
        });
    }

    $(document).ready( function () {
        ajaxDataLoad();
        $('#searchButton').click(function() {

            var branch_id = $('#branch_id').val();
            ajaxDataLoad( branch_id );
        });
    });


    // function fnDelete(RowID) {
    //     *
    //      * para1 = link to delete without id
    //      * para 2 = ajax check link same for all
    //      * para 3 = id of deleting item
    //      * para 4 = matching column
    //      * para 5 = table 1
    //      * para 6 = table 2
    //      * para 7 = table 3
         

    //     fnDeleteCheck(
    //         "{{url('gnl/branch/delete/')}}",
    //         "{{url('/ajaxDeleteCheck')}}",
    //         RowID,
    //         "{{base64_encode('branch_id')}}",
    //         "",
    //         "{{base64_encode('gnl_map_area_branch')}}"
    //     );
    // }


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
            var row = $('table tbody tr[cellData='+rowID+']');
            console.log(row);
            $.ajax({
                url: "{{ url()->current() }}"+'/delete',
                type: 'POST',
                dataType: 'json',
                data: {closing_id : rowID},
            })
            .done(function(response) {

                var row = $('table tbody tr[cellData='+rowID+']');
                
                if (response['alert-type']=='error') {
                    swal({
                        icon: 'error',
                        title: 'Oops...Delete Unsuccessful',
                        text: response['message'],
                    });
                }
                else{
                    swal({
                        icon: 'success',
                        title: 'Success...',
                        text: response['message'],
                    });
                    row.remove();
                    $('.clsDataTable').DataTable().draw();
                }
                    
            })
            .fail(function() {
                alert("error");
            });
        });   
    }
 </script>
<!-- End Page -->
@endsection
