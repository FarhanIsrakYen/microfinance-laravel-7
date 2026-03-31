@extends('Layouts.erp_master')
@section('content')
<?php 
use App\Services\CommonService;

$branchID = CommonService::getBranchId();
?>
<!-- Search Options -->
<style>
    tr td:last-child {
        text-align: center
    }
</style>

<div class="row align-items-center pb-10 mb-4">

    @if (count($branchList) > 1)
    <div class="col-lg-2">
        <label class="input-title">Branch</label>
        <div class="input-group">
            <select class="form-control clsSelect2" name="branch_id" id="branch_id">
                <option value="">Select Option</option>
                @foreach ($branchList as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->branch_code .' - '. $branch->branch_name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    @else
    <input type="hidden" name="branch_id" id="branch_id" value="{{$branchID}}">
    @endif   

    <div class="col-lg-2">
        <label class="input-title">Name or Code</label>
        <div class="input-group">
            <input type="text" class="form-control" id="name_or_code" name="name_or_code"
                placeholder="Samity Name/Code" value="" autocomplete="off">
        </div>
    </div>

    <div class="col-lg-2">
        <label class="input-title">Start Date</label>
        <div class="input-group">
            <input type="text" class="form-control datepicker-custom" id="start_date" name="start_date"
                placeholder="DD-MM-YYYY" value="" autocomplete="off">
        </div>
    </div>

    <div class="col-lg-2">
        <label class="input-title">End Date</label>
        <div class="input-group">
            <input type="text" class="form-control datepicker-custom" id="end_date" name="end_date"
                placeholder="DD-MM-YYYY" value="" autocomplete="off">
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
                <th>Name</th>
                <th>Code</th>
                <th>Branch</th>
                <th>Working Area</th>
                <th>Field Officer</th>
                <th>Samity Type</th>
                <th>Samity Day</th>
                <th>Opening Date</th>
                <th>Max Active Member</th>
                <th style="width: 10%;" class="text-center">Action</th>
            </tr>
        </thead>
    </table>
</div>

<script>
    function ajaxDataLoad( branch_id = null, name_or_code = null, start_date = null, end_date = null ){
        $('.clsDataTable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            "ajax":{
                     "url": "{{url('mfn/samity')}}",
                     "dataType": "json",
                     "type": "post",
                     "data": {
                        branchId: branch_id,
                        samityNameOrCode: name_or_code,
                        startDate: start_date,
                        endDate: end_date,
                    }
                   },
            "columns": [
                {data: 'sl', name: 'sl', orderable: false, targets: 1, className: 'text-center'},
                { "data": "name" },
                { "data": "samityCode" },
                { "data": "branch" },
                { "data": "workingArea" },
                { "data": "fieldOfficer" },
                { "data": "samityType" },
                { "data": "samityDay" },
                { "data": "openingDate" },
                { "data": "maxActiveMember"},
                { "data": "id", name: 'action', orderable: false, "width": "10%" },
            ],
            "fnRowCallback": function(nRow, aData, Index) {
                var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData.action.action_link);
                $('td:last', nRow).html(actionHTML);
            },
            // "columnDefs": [ {
            //   "targets": 10,
            //   "createdCell": function (td, cellData, rowData, row, col) {
            //     $(td).addClass("text-center d-print-none");
            //     $(td).closest('tr').attr("cellData", cellData);
            //     $(td).html('<a href='+"{{ url()->current() }}"+'/view/'+cellData+' title="View" class="btnView"><i class="icon wb-eye mr-2 blue-grey-600"></i></a> <a href='+"{{ url()->current() }}"+'/edit/'+cellData+' title="Edit" class="btnEdit"><i class="icon wb-edit mr-2 blue-grey-600"></i></a> <a href="javascript:void(0)" onclick="fnDelete('+cellData+');" title="Delete" class=""><i class="icon wb-trash mr-2 blue-grey-600"></i></a>');                
            //   }
            // } ]
        });
    }

    $(document).ready( function () {

        $('#start_date').datepicker({
            dateFormat: 'dd-mm-yy',
            orientation: 'bottom',
            autoclose: true,
            todayHighlight: true,
            changeMonth: true,
            changeYear: true,
            yearRange: '1900:+10',
            onClose: function( selectedDate ) {
                $( "#end_date" ).datepicker( "option", "minDate", selectedDate );
            }
        });

        $("#end_date").datepicker({
            dateFormat: 'dd-mm-yy',
            orientation: 'bottom',
            autoclose: true,
            todayHighlight: true,
            changeMonth: true,
            changeYear: true,
            yearRange: '1900:+10',
            onClose: function( selectedDate ) {
                $( "#start_date" ).datepicker( "option", "maxDate", selectedDate );
            }
        });

        ajaxDataLoad();

        $('#searchButton').click(function() {

            var branch_id = $('#branch_id').val();
            var name_or_code = $('#name_or_code').val();
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();

            ajaxDataLoad( branch_id, name_or_code, start_date, end_date );
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
                url: "{{ url()->current() }}"+'/delete',
                type: 'POST',
                dataType: 'json',
                data: {samity_id : rowID},
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
