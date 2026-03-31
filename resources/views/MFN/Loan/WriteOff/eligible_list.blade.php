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
        <!-- Search Option Start -->
        <div class="row align-items-center d-flex pb-10">

            <label class="input-title">Branch</label>
            <div class="col-lg-2">
                <select class="form-control" name="branchId" id="branchId">
                </select>
            </div>

            <label class="input-title">Samity</label>
            <div class="col-lg-2">
                <select class="form-control" name="samityId" id="samityId">
                </select>
            </div>

            <div class="col-lg-2">
                <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round"
                    id="searchBtn" onclick="ajaxDataLoad();">Search</a>
            </div>
        </div>
        <!-- Search Option End -->

        <div class="row">
            <div class="table-responsive">
                <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                    <thead>
                        <tr>
                            <th style="width: 2%;">SL</th>
                            <th>Loan Code</th>
                            <th>Member Name</th>
                            <th>Father/Spouse Name</th>
                            <th>Disburse Date</th>
                            <th>Last Repay Date</th>
                            <th class="text-center">Total Payable Amount</th>
                            <th class="text-center">Paid Amount</th>
                            <th class="text-center">Total Due Amount</th>
                            <th class="text-center">Total Pricipal Due Amount</th>
                            <th class="text-center">Total Service Charge Due Amount</th>
                            <th class="text-center">Last Transaction Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

    </div>
</div>

<link rel="stylesheet" href="{{asset('assets/css/selectize.bootstrap3.min.css')}}">
<script src="{{asset('assets/js/selectize.min.js')}}"></script>

<style>
    .selectize-control div.active {
        background-color: lightblue;
    }
    .selectize-control .lebel {
        color: #804739;
        font-weight: bold;
    }
</style>

<!-- End Page -->
<script>

    $(document).ready( function () {
        //ajaxDataLoad();
        selectizeBranch(<?= json_encode($branchList) ?>);
        @if(Auth::user()->branch_id != 1)
            $('#branchId').data('selectize').setValue(<?= Auth::user()->branch_id ?>);
        @endif
    });

    function ajaxDataLoad(){

        var branchId = $('#branchId').val();
        var samityId = $('#samityId').val();

        $('.clsDataTable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            "ajax":{
                "url": "{{ url()->current() }}",
                "dataType": "json",
                "type": "post",
                "data": { branchId: branchId, samityId: samityId }
            },
            "columns": [
                {data: 'sl', name: 'sl', orderable: false, targets: 0, className: 'text-center'},
                { "data": "loanCode" },
                { "data": "memberName" },
                { "data": "fOrSName" },
                { "data": "disburseDate", className: 'text-center', orderable: false, "width": "8%" },
                { "data": "lastRepayDate", className: 'text-center', orderable: false, "width": "8%" },
                { "data": "ttlPayableAmt", className: 'text-right', orderable: false },
                { "data": "paidAmt", className: 'text-right', orderable: false },
                { "data": "ttlDueAmt", className: 'text-right', orderable: false },
                { "data": "ttlPrincipalDueAmt", className: 'text-right', orderable: false },
                { "data": "ttlServiceChargeDueAmt", className: 'text-right', orderable: false },
                { "data": "lastTransDate", className: 'text-center', orderable: false },
                { "data": "id", name: 'action', orderable: false, "width": "3%" },
            ],
            'fnRowCallback': function(nRow, aData, Index) {
                var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData.action.action_link);
                $('td:last', nRow).html(actionHTML);
            },
            // "columnDefs": [{
            //     "targets": 12,
            //     "createdCell": function (td, cellData, rowData, row, col) {
            //         $(td).addClass("text-center d-print-none");
            //         $(td).closest('tr').attr("cellData", cellData);
            //         $(td).html('<a href="./add/'+cellData+'" title="Add"><i class="fa fa-arrow-circle-right" aria-hidden="true"></i></a>'
            //             );
            //     }
            // }]
        });
    } 

    function selectizeBranch(options) {

        $('#branchId').selectize({
            valueField: 'id',
            labelField: 'branch',
            searchField: ['branch_code', 'branch_name'],
            sortField: [{
                field: "branch_code",
                direction: "asc"
            }],
            // sortDirection: 'asc',
            placeholder: 'Select Branch',
            highlight: true,
            allowEmptyOption: true,
            maxItems: 1,
            //only using options-value in jsfiddle - real world it's using the load-function
            options: options,
            create: false,
            render: {
                option: function (branch, escape) {

                    return '<div>' +
                        '<span class="lebel">' + branch.branch_code + ' - ' + branch.branch_name + '</span>' +
                        '</div>';

                }
            }
        });
    }

    $('#branchId').change(function(e) {

        var element = jQuery('#samityId');

        if(element[0].selectize){
            element[0].selectize.destroy();
        }

        var branchId = $(this).val();

        if(branchId == ''){
            return false;
        }

        $.ajax({
            type: "POST",
            url: "./getData",
            data: {context : 'samity', branchId : branchId},
            dataType: "json",
            success: function (response) {

                selectizeSamity(response['samitys']);
            },
            error: function(){
                alert('error!');
            }
        });
    });

    function selectizeSamity(options) {

        $('#samityId').selectize({
            valueField: 'id',
            labelField: 'samity',
            searchField: ['samityCode', 'name'],
            sortField: [{
                field: "samityCode",
                direction: "asc"
            }],
            // sortDirection: 'asc',
            placeholder: 'Select Samity',
            highlight: true,
            allowEmptyOption: true,
            maxItems: 1,
            //only using options-value in jsfiddle - real world it's using the load-function
            options: options,
            create: false,
            render: {
                option: function (samity, escape) {

                    return '<div>' +
                        '<span class="lebel">'+ samity.samityCode + ' - ' + samity.name + '</span>' +
                        '</div>';

                }
            }
        });
    }

</script>
@endsection
