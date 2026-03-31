@extends('Layouts.erp_master_full_width')
@section('content')
<style>
    tr td:last-child {
        text-align: center
    }
</style>
<?php 
use App\Services\CommonService;

$branchID = CommonService::getBranchId();
?>
<div class="panel">
    <div class="panel-body">
<!-- Search Options -->
<div class="row align-items-center pb-10 mb-4">

    @if (count($branchList) > 1)
    <div class="col-lg-2">
        <label class="input-title">Branch</label>
        <div class="input-group">
            <select class="form-control clsSelect2" name="filBranch" id="filBranch">
                <option value="">All</option>
                @foreach ($branchList as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->branch_code . ' - ' . $branch->branch_name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    @else
    <input type="hidden" name="filBranch" id="filBranch" value="{{$branchID}}">
    @endif 

    <div class="col-lg-2">
        <label class="input-title">Samity</label>
        <div class="input-group">
            <select class="form-control clsSelect2" name="filSamity" id="filSamity">
                <option value="">All</option>
                @foreach ($samities as $samity)
                    <option value="{{ $samity->id }}">{{ $samity->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-lg-2">
        <label class="input-title">Primary Product</label>
        <div class="input-group">
            <select class="form-control clsSelect2" name="filPrimaryProduct" id="filPrimaryProduct">
                <option value="">All</option>
                @foreach ($loanProducts as $loanProduct)
                    <option value="{{ $loanProduct->id }}">{{ $loanProduct->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-lg-2">
        <label class="input-title">Savings Product</label>
        <div class="input-group">
            <select class="form-control clsSelect2" name="filSavProduct" id="filSavProduct">
                <option value="">All</option>
                @foreach ($savProducts as $savProduct)
                    <option value="{{ $savProduct->id }}">{{ $savProduct->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-lg-2">
        <label class="input-title">Savings Code</label>
        <div class="input-group">
            <input type="text" class="form-control" id="savingsCode" name="savingsCode" placeholder="Savings Code"
                value="" autocomplete="off">
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

    
</div>
<div class="row align-items-center mb-4" style="float: right;">
    <div class="col-lg-2 text-center">
        <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round" id="searchButton">Search</a>
    </div>
</div>

<div class="table-responsive">
    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
        <thead>
            <tr>
                <th style="width: 3%;">SL</th>
                <th class="text-center">Savings Account</th>
                <th>Savings Product</th>
                <th>Member Code</th>
                <th>Member Name</th>
                <th>Samity Code</th>
                <th>Samity Name</th>
                <th>Branch</th>
                <th>Auto Process Amount</th>
                <th>Opening Date</th>
                <th>Status</th>
                <th>Closing Date</th>
                <th>Entry By</th>
                <th style="width: 15%; text-align: center;">Action</th>
            </tr>
        </thead>
    </table>
    </div>
  </div>
 </div>
<script>
    function ajaxDataLoad(branch_id = null, samity_id = null, filPrimaryProduct= null, filSavProduct = null,savingsCode = null, start_date = null, end_date = null) {
        $('.clsDataTable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            "ajax": {
                "url": "{{ url()->current() }}",
                "dataType": "json",
                "type": "post",
                "data": {
                    filBranch: branch_id,
                    filSamity: samity_id,
                    filPrimaryProduct: filPrimaryProduct,
                    filSavProduct: filSavProduct,
                    savingsCode: savingsCode,
                    startDate: start_date,
                    endDate: end_date,
                }
            },
            "columns": [{
                    data: 'sl',
                    name: 'sl',
                    orderable: false,
                    targets: 1,
                    className: 'text-center'
                },
                {
                    "data": "accountCode"
                },
                {
                    "data": "product"
                },
                {
                    "data": "memberCode"
                },
                {
                    "data": "member"
                },
                {
                    "data": "samityCode"
                },
                {
                    "data": "samityName"
                },
                {
                    "data": "branchName"
                },
                {
                    "data": "autoProcessAmount", "className" : "text-right"
                },
                {
                    "data": "openingDate"
                },                
                {
                    "data": "status"
                },
                {
                    "data": "closingDate"
                },
                {
                    "data": "empName"
                },
                {
                    "data": "id",
                    name: 'action',
                    orderable: false,
                    "width": "80px"
                },
            ],
            'fnRowCallback': function(nRow, aData, Index) {
                var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData.action.action_link);
                $('td:last', nRow).html(actionHTML);
            },
            // "columnDefs": [{
            //     "targets": 13,
            //     "createdCell": function (td, cellData, rowData, row, col) {
            //         $(td).addClass("text-center d-print-none");
            //         $(td).closest('tr').attr("cellData", cellData);
            //         $(td).html('<a href=' + "{{ url()->current() }}" + '/view/' + cellData +
            //             ' title="View" class="btnView"><i class="icon wb-eye mr-2 blue-grey-600"></i></a> <a href=' +
            //             "{{ url()->current() }}" + '/edit/' + cellData +
            //             ' title="Edit" class="btnEdit"><i class="icon wb-edit mr-2 blue-grey-600"></i></a> <a href="javascript:void(0)" onclick="fnDelete(' +
            //             '\'' + cellData + '\'' +
            //             ');" title="Delete" class=""><i class="icon wb-trash mr-2 blue-grey-600"></i></a>'
            //             );
            //     }
            // }]
        });
    }

    $(document).ready(function () {

        $('#start_date').datepicker({
            dateFormat: 'dd-mm-yy',
            orientation: 'bottom',
            autoclose: true,
            todayHighlight: true,
            changeMonth: true,
            changeYear: true,
            yearRange: '1900:+10',
            onClose: function (selectedDate) {
                $("#end_date").datepicker("option", "minDate", selectedDate);
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
            onClose: function (selectedDate) {
                $("#start_date").datepicker("option", "maxDate", selectedDate);
            }
        });

        ajaxDataLoad();

        $('#searchButton').click(function () {

            var branch_id = $('#filBranch').val();
            var samity_id = $('#filSamity').val();
            var filPrimaryProduct = $('#filPrimaryProduct').val();
            var filSavProduct = $('#filSavProduct').val();
            var savingsCode = $('#savingsCode').val();
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();

            ajaxDataLoad(branch_id, samity_id, filPrimaryProduct, filSavProduct, savingsCode, start_date, end_date);
        });

        $("#filBranch").change(function (e) {
            e.preventDefault();

            $('#filSamity option:gt(0)').remove();

            if($(this).val() == ''){
                return false;
            }

            $.ajax({
                type: "POST",
                url: "./../getSamities",
                data: {branchId : $("#filBranch").val()},
                dataType: "json",
                success: function (samities) {
                    $.each(samities, function (index, samity) {
                        $('#filSamity').append("<option value="+samity.id+">"+samity.name+"</option>");
                    });
                },
                error: function(){
                    alert('error!');
                }
            });
        });

        $("#start_date, #end_date").on('click', function () {
            this.value = '';
        });
    }); /* end ready */


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

@endsection
