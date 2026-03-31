@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;

$branchData = Common::ViewTableOrder('gnl_branchs',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'branch_name','branch_code'],
    ['branch_name', 'ASC']);
$employeeData = Common::ViewTableOrder('hr_employees',
            [['is_delete', 0], ['is_active', 1]],
            ['id','employee_no', 'emp_name','emp_code'],
            ['id', 'ASC']);
?>

<!-- Search Option Start -->

<div class="row align-items-center pb-10 d-print-none">
    <div class="col-lg-2">
        <label class="input-title">Branch</label>
        <div class="input-group">
            <select class="form-control clsSelect2" 
            name="branch_id" id="branch_id" 
            onchange="fnAjaxSelectBox('employee_name',
                                              this.value,
                                              '{{ base64_encode("hr_employees")}}',
                                              '{{base64_encode("branch_id")}}',
                                              '{{base64_encode("employee_no,emp_name")}}',
                                              '{{url("/ajaxSelectBox")}}');
                        fnAjaxSelectBox('employee_code',
                                                        this.value,
                                                        '{{ base64_encode("hr_employees")}}',
                                                        '{{base64_encode("branch_id")}}',
                                                        '{{base64_encode("employee_no,emp_code")}}',
                                                        '{{url("/ajaxSelectBox")}}');">

                <option value="">Select All</option>
                @foreach ($branchData as $row)
                <option value="{{ $row->id }}">{{ $row->branch_code }}-{{ $row->branch_name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-lg-2">
        <label class="input-title">Employee Name</label>
        <select class="form-control clsSelect2" name="employee_name" id="employee_name">
            <option value="">Select one</option>
            @foreach ($employeeData as $Row)
            <option value="{{ $Row->employee_no }}">{{ $Row->emp_name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-2">
        <label class="input-title">Employee Code</label>
        <select class="form-control clsSelect2" name="employee_code" id="employee_code">
            <option value="">Select one</option>
            @foreach ($employeeData as $Row)
            <option value="{{ $Row->employee_no }}">{{ $Row->emp_code }}</option>
            @endforeach
        </select>
    </div>

    @if(Common::getBranchId() == 1)
    <div class="col-lg-2 pt-20 text-center">
        <a href="javascript:void(0)" id="searchButton" name="searchButton" class="btn btn-primary btn-round">Search</a>
    </div>
    @endif
</div>
<!-- Search Option End -->

<div class="row">
    <div class="col-lg-12">
        <table class="table w-full table-hover table-bordered table-striped clsDataTable">
            <thead>
                <tr>
                    <th style="width:4%;">SL</th>
                    <th>Name</th>
                    <th>Emp. Code</th>
                    <th>Mobile</th>
                    <th>Email</th>
                    <th>Designation</th>
                    <th>Branch</th>
                    <!--<th>Company</th>-->
                    <th style="width:15%;" class="text-center">Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<!-- End Page -->
<script>
function ajaxDataLoad(BranchID = null, EmpName = null, Empcode = null) {

    $('.clsDataTable').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        stateSave: true,
        stateDuration: 1800,
        // ordering: false,
        // lengthMenu: [[10, 20, 30, 50], [10, 20, 30, 50]],
        "ajax": {
            "url": "{{route('employeeDatatable')}}",
            "dataType": "json",
            "type": "post",
            "data": {
                _token: "{{csrf_token()}}",
                BranchID: BranchID,
                EmpName: EmpName,
                Empcode: Empcode,
            }
        },
        columns: [{
                data: 'id',
                className: 'text-center',
                width: '5%'
            },
            {
                data: 'emp_name'
            },
            {
                data: 'emp_code'
            },
            {
                data: 'emp_phone'
            },
            {
                data: 'emp_email'
            },
            {
                data: 'emp_designation'
            },
            {
                data: 'branch_name'
            },
            {
                data: 'action',
                orderable: false,
                className: 'text-center d-print-none'
            },

        ],
        'fnRowCallback': function(nRow, aData, Index) {
            var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData
                .action.action_link);
            $('td:last', nRow).html(actionHTML);
        }

    });
}

$(document).ready(function() {

    ajaxDataLoad();

    $('#searchButton').click(function() {
        var BranchID = $('#branch_id').val();
        var EmpName = $('#employee_name').val();
        var Empcode = $('#employee_code').val();

        ajaxDataLoad(BranchID, EmpName, Empcode);
    });


});
// $(document).ready(function() {
// $("#branch_id").on('change', function(){
// var Branch = $('#branch_id').val();
//     $("#employee_name").change();
// });
// });
// Delete Data
function fnDelete(RowID) {
    /**
     * para 1 = link to delete without id
     * para 2 = ajax check link same for all
     * para 3 = id of deleting item
     * para 4 = matching column
     * para 5 = table 1
     * para 6 = table 2
     * para 7 = table 3
     */

    fnDeleteCheck(
        "{{url('pos/employee/delete/')}}",
        "{{url('/ajaxDeleteCheck')}}",
        RowID,
        "{{base64_encode('employee_id')}}",
        "{{base64_encode('is_delete,0')}}",
        "{{base64_encode('pos_sales_m')}}",
    );
}
</script>
@endsection