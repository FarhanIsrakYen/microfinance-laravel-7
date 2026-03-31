@extends('Layouts.erp_master_full_width')

@section('content')
<style>
    tr td:last-child {
        text-align: center
    }
</style>
<!-- Page -->
<div class="panel">
    <div class="panel-body">
        <div class="row align-items-center pb-10 mb-4">

            @if (count($branchList) >1)
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
            <input type="hidden" name="filBranch" id="filBranch" value="{{$branchList[0]->id}}">
            @endif 
        
            @if ($samities)
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
            @endif
        
            <div class="col-lg-2">
                <label class="input-title">Product</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" name="fillProduct" id="fillProduct">
                        <option value="">All</option>
                        @foreach ($loanProducts as $loanProduct)
                            <option value="{{ $loanProduct->id }}">{{ $loanProduct->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        
        
            <div class="col-lg-2">
                <label class="input-title">Loan Code</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="loanCode" name="loanCode" placeholder="loan Code"
                        value="" autocomplete="off">
                </div>
            </div>
        
            <div class="col-lg-2">
                <label class="input-title">Start Date</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="start_date" name="start_date"
                        placeholder="DD-MM-YYYY" value="" autocomplete="off">
                </div>
            </div>
        
            <div class="col-lg-2">
                <label class="input-title">End Date</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="end_date" name="end_date"
                        placeholder="DD-MM-YYYY" value="" autocomplete="off">
                </div>
            </div>

            <div class="col-lg-2">
                <br>
                <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round float-right" id="searchButton">Search</a>
            </div>
        
        </div>
        {{-- <div class="row align-items-center mb-4" style="float: right;">
            <div class="col-lg-2 text-center">
                <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round" id="searchButton">Search</a>
            </div>
        </div> --}}


        <div class="table-responsive">
            <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                <thead>
                    <tr>
                        <th style="width: 3%;">SL</th>
                        <th>Loan Code</th>
                        <th>Member Code</th>
                        <th>Member Name</th>
                        <th>Loan Amount</th>
                        <th>Total Repay Amount</th>
                        <th>Int. Rate</th>
                        <th>Disburse Date</th>
                        <th>First Repay Date</th>
                        <th>NOI</th>
                        <th>Auth. Status</th>
                        <th>Loan Status</th>
                        <th>Entry By</th>
                        <th style="width: 15%;">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
<!-- End Page -->
<script>
    function ajaxDataLoad(branch_id=null, samity_id=null, product=null, loanCode=null, start_date=null, end_date=null){
        $('.clsDataTable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            "ajax":{
                     "url": "{{ url()->current() }}",
                     "dataType": "json",
                     "type": "post",
                     "data": {
                        branch_id : branch_id,
                        samity_id : samity_id,
                        product : product,
                        loanCode : loanCode,
                        start_date : start_date,
                        end_date : end_date
                    }
                   },
            "columns": [
                {data: 'sl', name: 'sl', orderable: false, targets: 1, className: 'text-center'},
                { "data": "loanCode" },
                { "data": "memberCode" },
                { "data": "memberName" },
                { "data": "loanAmount" },
                { "data": "repayAmount" },
                { "data": "LoanIntMethods" },
                { "data": "disbursementDate" },
                { "data": "firstRepayDate" },
                { "data": "numberOfInstallment" },
                { "data": "isAuthorized" },
                { "data": "loanStatus" },
                { "data": "empName" },
                { "data": "id", name: 'action', orderable: false, "width": "10%" },
            ],
            'fnRowCallback': function(nRow, aData, Index) {
                var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData.action.action_link);
                $('td:last', nRow).html(actionHTML);
            },
            "columnDefs": [
                {
                    "targets": -4,
                    "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass("text-center");
                        if (rowData['isAuthorized'] == 1) {
                        $(td).html('<span style="font-size: 16px; color: Dodgerblue;"><i class="fas fa-check"></i></span>');
                        }else{
                        $(td).html('<span style="font-size: 16px; color: red;"><i class="far fa-times-circle"></i></span>');
                        }   
                    }
                
                },
            //     {
            //     "targets": -1,
            //     "createdCell": function (td, cellData, rowData, row, col) {
            //         $(td).addClass("text-center d-print-none");
            //         $(td).closest('tr').attr("cellData", cellData);
            //         if (rowData['isAuthorized'] == 1) {
            //             $(td).html('<a href=' + "{{ url()->current() }}" + '/view/' + cellData +
            //             ' title="View" class="btnView"><i class="icon wb-eye mr-2 blue-grey-600"></i></a> <a href="javascript:void(0);" title="Edit" class="btnEdit"><i class="icon wb-edit mr-2 blue-grey-600"></i></a> <a href="javascript:void(0);" title="Delete" class=""><i class="icon wb-trash mr-2 blue-grey-600"></i></a>'
            //             );
            //         }else{
            //             $(td).html('<a href=' + "{{ url()->current() }}" + '/view/' + cellData +
            //             ' title="View" class="btnView"><i class="icon wb-eye mr-2 blue-grey-600"></i></a> <a href=' +
            //             "{{ url()->current() }}" + '/edit/' + cellData +
            //             ' title="Edit" class="btnEdit"><i class="icon wb-edit mr-2 blue-grey-600"></i></a> <a href="javascript:void(0)" onclick="fnDelete(' +
            //             '\'' + cellData + '\'' +
            //             ');" title="Delete" class=""><i class="icon wb-trash mr-2 blue-grey-600"></i></a>'
            //             );
            //         }
            //     }
            // },
            {
                "targets": 10,
                "createdCell": function (td, cellData, rowData, row, col) {
                    if (cellData == 1) {
                        $(td).html('Authorized');                        
                    }
                    else{
                        $(td).html('Unauthorized');                        
                    }
                }
            }
            ]

        });
    }

    $(document).ready( function () {
        ajaxDataLoad();
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
                var row = $('table tbody tr[cellData=' + '\'' + rowID + '\'' + ']');
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

    $('#searchButton').click(function () {

        var branch_id = $('#filBranch').val();
        var samity_id = $('#filSamity').val();
        var fillProduct = $('#fillProduct').val();
        var loanCode = $('#loanCode').val();
        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();

        ajaxDataLoad(branch_id, samity_id, fillProduct, loanCode, start_date, end_date);
    });

    $('#start_date, #end_date').click(function(){
        $(this).val('');
    });
    $('#start_date, #end_date').on('input',function(){
        $(this).val('');
    });


    

</script>
@endsection
