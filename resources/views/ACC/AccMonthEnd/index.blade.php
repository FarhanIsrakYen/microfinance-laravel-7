@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;

$BranchID = (isset($BranchID) && !empty($BranchID)) ? $BranchID : Common::getBranchId();

$dateData = Common::ViewTableOrder('acc_month_end', [['is_delete', 0], ['branch_id', $BranchID], ['is_active', 1]], ['id', 'month_date'], ['month_date', 'ASC']);

if (!empty($dateData[0]->month_date)) {
    $date = new DateTime($dateData[0]->month_date);
}else{
    $sysDate = (isset($StartDate) && !empty($StartDate)) ? $StartDate : Common::getBranchSoftwareStartDate($BranchID, 'acc');

    $date = new DateTime($sysDate);
}

$date->modify('first day of this month');
$StartDate = $date->format('d-m-Y');


$date->modify('last day of this month');
$EndDate = (isset($EndDate) && !empty($EndDate)) ? $EndDate : $date->format('d-m-Y');
?>


<div id="monthEndFormId">

    <div class="row">
        <div class="col-lg-8 offset-lg-3">
            <!-- Html View Load  -->
            {!! HTML::forCompanyFeild('','',false) !!}
        </div>
    </div>

    <!-- Search Option Start -->
    <div class="row align-items-center pb-10">
      <!-- Html View Load For Branch Search -->
      {!! HTML::forBranchFeildSearch($BranchID) !!}
        <div class="col-lg-2">
          <label class="input-title">Start Date</label>
            <div class="input-group ghdatepicker">
                <div class="input-group-prepend ">
                    <span class="input-group-text ">
                        <i class="icon wb-calendar round" aria-hidden="true"></i>
                    </span>
                </div>
                <input type="text" class="form-control round datepicker-custom" id="StartDate" name="StartDate"
                    placeholder="DD-MM-YYYY">
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
                    placeholder="DD-MM-YYYY">
            </div>
            <div class="help-block with-errors is-invalid"></div>
        </div>



        <div class="col-lg-2 pt-20 text-center">
            <a name="btnSearch" id="btnSearch" value="Search"
                class="btn btn-primary btn-round text-light">Search</a>
        </div>

        @foreach($GlobalRole as $key => $row)
        @if($row['set_status'] == 15)
        <div class="col-lg-2 text-left">
            <a name="btnMonthEnd" id="submitButton"
                class="btn btn-danger btn-round" style="color:#000; font-weight:bold;">
                Execute Month End
            </a>
        </div>
        @endif
        @endforeach
    </div>
    <!-- Search Option End -->
</div>

<div class="row" style="margin-top:2%;">
    <div class="col-lg-12">
        <table class="table w-full table-hover table-bordered table-striped clsDataTable">
            <thead>
                <tr>
                    <th width="3%">SL</th>
                    <th>Branch Name</th>
                    <th>Month Date</th>
                    {{-- <th>Status</th> --}}
                    <th style="width:5%;">Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>



<script>
$(document).ready(function() {
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
            "url": "{{route('accmonthendDatatable')}}",
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
            // {
            //     data: 'status',
            //     name: 'status',
            //     orderable: false
            // },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                className: 'text-center'
            },
        ],
        'fnRowCallback': function(nRow, aData, Index) {
            // dont delete this comment
            // $('td:nth-child(10) .btnDelete', nRow).removeClass('btnDelete'); // dont delete this comment


            var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData.action.action_link);
            $('td:last', nRow).html(actionHTML);
            $('.btnDelete', nRow).removeClass('btnDelete');
        }
    });
}

function fnDelete(monthEndId) {
    if (monthEndId != null) {

        $.ajax({
            type: 'get',
            url: '{{ url("acc/month_end/delete") }}',
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


// Execute Month end
$(document).ready(function() {

$('#submitButton').click(function(event) {

    $('#submitButton').attr('disabled', 'disabled');
    event.preventDefault();

    var company_id = $('#company_id').val();
    var branch_id = $('#branch_id').val();

    // Check execute all day of this month
    $.ajax({
        type: "get",
        url: "{{ url('acc/month_end/checkDayEndData') }}",
        data: {
            branchId: branch_id
        },
        dataType: "json",
        success: function(dataM) {

            if (dataM.isDayEndCheck == false) {
                toastr.error("Please execute day end at first!");
            } else {
                $.ajax({
                    method: "POST",
                    url: "{{url('acc/month_end/execute')}}",
                    dataType: "json",
                    data: {
                        company_id: company_id,
                        branch_id: branch_id
                    },
                    success: function(data) {
                        if (data) {
                            if (data.alert_type == 'success') {
                                $('.clsDataTable').DataTable().ajax
                            .reload();
                                toastr.success(data.message);

                            } else if (data.alert_type == 'error') {
                                toastr.error(data.message);
                            }
                        }
                    }
                });
            }

            $('#submitButton').attr('disabled', false);
        },
    });


});
});

</script>

@endsection
