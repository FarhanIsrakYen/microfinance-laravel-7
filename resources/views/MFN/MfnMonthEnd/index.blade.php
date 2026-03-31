@extends('Layouts.erp_master')
@section('content')
<style>
    tr td:last-child {
        text-align: center
    }
</style>

<?php  use App\Services\MfnService as MFN;
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
$BranchID = (isset($BranchID) && !empty($BranchID)) ? $BranchID : Common::getBranchId();

$dateData = Common::ViewTableOrder('mfn_month_end', [ ['branchId', $BranchID]], ['id', 'date'], ['date', 'ASC']);

if (!empty($dateData[0]->month_date)) {
    $date = new DateTime($dateData[0]->month_date);
}else{
    $sysDate = (isset($StartDate) && !empty($StartDate)) ? $StartDate : MFN::getBranchMisSoftwareStartDate($BranchID);

    $date = new DateTime($sysDate);
}
// dd($date);
// $date = $date.

// $date = $date->modify('-1 month');
// dd($date->modify('-1 day'));
// dd($date);
$date->modify('first day of this month');
$StartDate = $date->format('d-m-Y');


$date->modify('last day of this month');
$EndDate = (isset($EndDate) && !empty($EndDate)) ? $EndDate : $date->format('d-m-Y');
?>


<form action="{{ url('mfn/month_end/execute') }}" method="POST" data-toggle="validator" novalidate="true"
    id="monthEndFormId">
    @csrf
    <div class="row">
        <div class="col-lg-8 offset-lg-3">
            <!-- Html View Load  -->
            {!! HTML::forCompanyFeild('','',false) !!}
        </div>
    </div>

    <!-- Search Option Start -->
    <div class="row align-items-center pb-10">


        <div class="col-lg-2">
          <label class="input-title">Start Date</label>
            <div class="input-group ghdatepicker">
                <div class="input-group-prepend ">
                    <span class="input-group-text ">
                        <i class="icon wb-calendar round" aria-hidden="true"></i>
                    </span>
                </div>
                <input type="text" class="form-control round datepicker-custom" id="StartDate" name="StartDate"
                    placeholder="DD-MM-YYYY" value="{{ $StartDate }}">
            </div>
        </div>


        <div class="col-lg-2">
            <label class="input-title">End Date</label>
            <div class="input-group ghdatepicker">
                <div class="input-group-prepend ">
                    <span class="input-group-text ">
                        <i class="icon wb-calendar round" aria-hidden="true"></i>
                    </span>
                </div>
                <input type="text" class="form-control round datepicker-custom" id="EndDate" name="EndDate"
                    placeholder="DD-MM-YYYY" value="{{ $EndDate }}">
            </div>
            <div class="help-block with-errors is-invalid"></div>
        </div>

        <!-- Html View Load For Branch Search -->
        {!! HTML::forBranchFeildSearch($BranchID) !!}

        <div class="col-lg-2 pt-20 text-center">
            <a name="btnSearch" id="btnSearch" value="Search"
                class="btn btn-primary btn-round text-light">Search</a>
        </div>

        {{-- @foreach($GlobalRole as $key => $row) --}}
        {{-- @if($row['set_status'] == 15) --}}
        <div class="col-lg-2 text-left">
            <a name="btnMonthEnd" id="submitButton" value="Submit" onclick="fnCheckTtlDayEnd();"
                class="btn btn-danger btn-round" style="color:#000; font-weight:bold;">
                Execute Month End
            </a>
        </div>
        {{-- @endif --}}
        {{-- @endforeach --}}
    </div>
    <!-- Search Option End -->
</form>

<div class="row" style="margin-top:2%;">
    <div class="col-lg-12">
        <table class="table w-full table-hover table-bordered table-striped clsDataTable">
            <thead>
                <tr>
                    <th style="width:3%;">SL</th>
                    <th>Branch Name</th>
                    <th>Month Date</th>
                    <th>Status</th>
                    <th style="width:5%;">Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
         
  

<script>
$(document).ready(function() {
    $('.page-header-actions').hide();
    ajaxDataLoad();
    $('#btnSearch').click(function() {

        var SDate = $('#StartDate').val();
        var EDate = $('#EndDate').val();
        var branchID = $('#branch_id').val();
        // console.log(branchID);

        ajaxDataLoad(SDate, EDate, branchID);
    });
});

function ajaxDataLoad(SDate = null, EDate = null, branchID = null) {
    // console.log(branchID);
    $('.clsDataTable').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        // ordering: false,
        // lengthMenu: [[10, 20, 30, 50], [10, 20, 30, 50]],
        "ajax": {
            "url": "{{route('mfnmonthendDatatable')}}",
            "dataType": "json",
            "type": "post",
            "data": {
                _token: "{{csrf_token()}}",
                SDate: SDate,
                EDate: EDate,
                branchID: branchID,
            }
        },
        columns: [{
                data: 'id',
                
                name: 'id',
                orderable: false
            },
            {
                data: 'branch_name',
                name: 'branch_name',
                orderable: false
            },
            {
                data: 'month_date',
                name: 'month_date',
                orderable: false
            },
            {
                data: 'status',
                name: 'status',
                orderable: false
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                className: 'text-center'
            },
        ],
        'fnRowCallback': function(nRow, aData, Index) {
            $('.btnDelete', nRow).removeClass('btnDelete');
            var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData.action.action_link);
            $('td:last', nRow).html(actionHTML);
        },
        // 'fnRowCallback': function(nRow, aData, Index) {
        //     $('.btnDelete', nRow).removeClass('btnDelete');

        //     var actionHTML = '';

        //     if(aData.action !== 0){
        //         actionHTML = '<a href="javascript:void(0)" onclick="fnDelete('+aData.action+');" title="Delete" class=""><i class="icon wb-trash mr-2 blue-grey-600"></i></a>';
        //     }

        //     $('td:last', nRow).html(actionHTML);
        // }
    });
}

function fnCheckTtlDayEnd() {
    var branchId = $('#branch_id').val();
            // console.log(branchId);
            // $('#monthEndFormId')
            //             .append('<input type="hidden" name="btnMonthEnd" value="Submit">')
            //             .submit();
            // $('#monthEndFormId')
            //             .append('<input type="hidden" name="btnMonthEnd" value="Submit">')
            //             .submit();
    if (branchId != null) {

        $.ajax({
            type: "get",
            url: "{{ url('mfn/month_end/checkDayEndData') }}",
            data: {
                branchId: branchId
            },
            dataType: "json",
            success: function(data) {

                console.log(data);

                if (data.isDayEndCheck == false) {
                    swal({
                        icon: 'warning',
                        title: 'Error',
                        text: 'Please execute day end at first!',
                    });
                } else {


                    $('#monthEndFormId')
                        .append('<input type="hidden" name="btnMonthEnd" value="Submit">')
                        .submit();
                }
            },
        });
    }
}

$('form').submit(function(event) {
    $(this).find(':submit').attr('disabled', 'disabled');
});
function fnDelete(monthEndId) {
    if (monthEndId != null) {

        $.ajax({
            type: 'get',
            url: '{{ url("mfn/month_end/delete") }}',
            data: {
                monthEndId: monthEndId
            },
            dataType: 'json',
            success: function(data) {

                if (data.isDelete == true) {
                    toastr.success("Successfully Deleted!");
                    $('.clsDataTable').DataTable().ajax.reload();
                } else {
                    swal({
                        icon: 'warning',
                        title: 'Error',
                        text: 'Please delete day end at first!',
                    });
                }
            }
        });
    }
}





</script>

@endsection