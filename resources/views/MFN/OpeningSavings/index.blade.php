@extends('Layouts.erp_master')

@section('content')
<?php $branchId = Auth::user()->branch_id ?>

@if($branchId == 1)
<div class="row mb-4">
    <div class="col-lg-2">
        <label class="input-title">Branch</label>
        <div class="input-group">
            <select class="form-control clsSelect2" name="branch_id" id="branch_id">
                <option value="">Select Option</option>
                @foreach ($branchList as $row)
                <option value="{{ $row->id }}">{{ $row->branch_code . " - ".$row->branch_name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-lg-2 pt-20">
        <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round"
            id="searchButton">Search</a>
    </div>
</div>
@endif
<div class="table-responsive">
    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
        <thead>
            <tr>
                <th width="5%">SL</th>
                <th width="30%">Loan Product</th>
                <th width="30%">Savings Product</th>
                <th>Gender</th>
                <th width="20%" class="text-center">Action</th>
            </tr>
        </thead>
    </table>
</div>

<script>
    function ajaxDataLoad(branchId){
        $('.clsDataTable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            "ajax":{
                    "url": "{{route('openingSavingsDatatable')}}",
                    "dataType": "json",
                    "type": "post",
                    "data":{
                            _token: "{{csrf_token()}}",
                            branchId: branchId
                        }
                   },
            "columns": [
                { data: 'sl', name: 'sl', orderable: false, targets: 1,className: 'text-center'},
                { "data": "loanProduct" },
                { "data": "savingsProduct" },
                { "data": "gender" },
                { "data": "id", name: 'action', orderable: false },
            ],
            "columnDefs": [ {
              "targets": 4,
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass("text-center d-print-none");
                $(td).closest('tr').attr("cellData", cellData);
                $(td).html('<a href="./openingSavingsInfo/view/'+cellData+'" title="View" class="btnView"><i class="icon wb-eye mr-2 blue-grey-600"></i></a> <a href="./openingSavingsInfo/edit/'+cellData+'" title="Edit" class="btnEdit"><i class="icon wb-edit mr-2 blue-grey-600"></i></a> <a href="javascript:void(0)" onclick="fnDelete('+cellData+');" title="Delete" class=""><i class="icon wb-trash mr-2 blue-grey-600"></i></a>');
                
              }
            } ]
        });
    }

    $(document).ready( function () {
        var branchId = '<?php echo $branchId ?>';
        if (branchId != 1) {
            ajaxDataLoad(branchId);
        }

        $('#searchButton').click(function() {
            var branchId = $('#branch_id').val();
            if (branchId == '') {
                swal({
                    icon: 'warning',
                    title: 'Warning',
                    text: 'Please select A Branch',
                });
                return false;
            }
            ajaxDataLoad(branchId);
        });
    });

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
                url: './openingSavingsInfo/delete/'+ rowID,
                type: 'POST',
                dataType: 'json',
                data: {wareaId : rowID},
            })
            .done(function(response) {

                var row = $('table tbody tr[cellData='+rowID+']');
                
                if (response['alert-type']=='error') {
                    swal({
                        icon: 'error',
                        title: 'Oops...',
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

@endsection
