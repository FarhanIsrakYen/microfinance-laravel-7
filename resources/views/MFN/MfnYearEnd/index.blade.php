@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\HtmlService as HTML;


?>


<form action="{{ url('mfn/year_end/execute') }}" method="POST" data-toggle="validator" novalidate="true"
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
            <a name="btnSearch" id="btnMonthEndSearch" value="Search"
                class="btn btn-primary btn-round text-light">Search</a>
        </div>

        {{-- @foreach($GlobalRole as $key => $row)
        @if($row['set_status'] == 15) --}}
        <div class="col-lg-2 text-left">
            <a name="btnMonthEnd" id="submitButton" value="Submit" onclick="fnCheckTtlDayEnd();"
                class="btn btn-danger btn-round" style="color:#000; font-weight:bold;">
                Execute Year End
            </a>
        </div>
        {{-- @endif
        @endforeach --}}
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
                    <th style="width:10%;">Action</th>
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
    console.log(branchID);
    $('.clsDataTable').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        // ordering: false,
        // lengthMenu: [[10, 20, 30, 50], [10, 20, 30, 50]],
        "ajax": {
            "url": "{{ url()->current() }}",
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
                data: 'date',
                name: 'date',
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
            // dont delete this comment
            // $('td:nth-child(10) .btnDelete', nRow).removeClass('btnDelete'); // dont delete this comment
            
            $('.btnDelete', nRow).removeClass('btnDelete');

            var actionHTML = '';

            if(aData.action !== 0){
                actionHTML = '<a href="javascript:void(0)" onclick="fnDelete('+aData.action+');" title="Delete" class=""><i class="icon wb-trash mr-2 blue-grey-600"></i></a>';
            }

            $('td:last', nRow).html(actionHTML);
        }
    });
}

function fnCheckTtlDayEnd() {
    var branchId  = $('#branch_id').val();
    if (branchId != null) {

        $.ajax({
            type: "get",
            url: "{{ url('mfn/year_end/checkMonthEndData') }}",
            data: {
                branchId: branchId
            },
            dataType: "json",
            success: function(data) {

                if (data.isDayEndCheck == false) {
                    swal({
                        icon: 'warning',
                        title: 'Error',
                        text: 'Please execute Month end at first!',
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
function fnDelete(yearEndId) {
    if (yearEndId != null) {

        $.ajax({
            type: 'get',
            url: '{{ url("mfn/year_end/delete") }}',
            data: {
                yearEndId: yearEndId
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
                        text: 'Please delete Month  end  / Day end at first!',
                    });
                }
            }
        });
    }
}




</script>

@endsection