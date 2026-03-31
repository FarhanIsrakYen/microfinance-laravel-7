@extends('Layouts.erp_master')
@section('content')

<?php          
use App\Services\MfnService as MFN;
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
                        $BranchID = (isset($BranchID) && !empty($BranchID)) ? $BranchID : Common::getBranchId();
                        $StartDate = (isset($StartDate) && !empty($StartDate)) ? $StartDate : MFN ::systemCurrentDate($BranchID);
                        $EndDate = (isset($EndDate) && !empty($EndDate)) ? $EndDate : MFN ::systemCurrentDate($BranchID);
                        

                        // dd($StartDate);
                    ?>

<form  method="POST" data-toggle="validator" novalidate="true">
    @csrf

    <div class="row">
        <div class="col-lg-8 offset-lg-3">
            <!-- Html View Load  -->
            {!! HTML::forCompanyFeild('','',false) !!}
        </div>
    </div>

    <!-- Search Option Start -->
    <div class="row align-items-center d-flex justify-content-center">

        
        <div class="col-lg-2">
            <label class="input-title">Start Date</label><br>
            <div class="input-group ghdatepicker">
                <div class="input-group-prepend ">
                    <span class="input-group-text ">
                        <i class="icon wb-calendar round" aria-hidden="true"></i>
                    </span>
                </div>
                <input type="text" class="form-control datepicker-custom" id="StartDate" name="StartDate"
                     placeholder="DD-MM-YYYY" value="{{$StartDate }}">
            </div>
        </div>

        <div class="col-lg-2">
            <label class="input-title">End Date</label><br>
            <div class="input-group ghdatepicker">
                <div class="input-group-prepend ">
                    <span class="input-group-text ">
                        <i class="icon wb-calendar round" aria-hidden="true"></i>
                    </span>
                </div>
                <input type="text" class="form-control datepicker-custom" id="EndDate" name="EndDate"
                    placeholder="DD-MM-YYYY" value="{{$EndDate }}">
            </div>
            <div class="help-block with-errors is-invalid"></div>
        </div>

        <!-- Html View Load For Branch Search -->
        {!! HTML::forBranchFeildSearch($BranchID) !!}

        <div class="col-lg-2">
            <br>
            <a href="javascript:void(0);" id="btnSearch" class="btn btn-primary btn-round">Search</a>
        </div>

        <div class="col-lg-1 text-right">
            <br>
            {{-- @foreach($GlobalRole as $key => $row)
            @if($row['set_status'] == 1) --}}

            <a href="javascript:void(0);" id="btnDayEnd" class="btn btn-danger btn-round" style="color:#000; font-weight:bold;">Execute Day End</a>
            {{-- <button type="submit" name="btnDayEnd" id="submitButton" value="Submit" class="btn btn-danger btn-round"
                style="color:#000; font-weight:bold;"> Execute Day End
            </button> --}}
            {{-- @endif
            @endforeach --}}

        </div>
    </div>
    <!-- Search Option End -->
</form>

<div class="row" style="margin-top:2%;">
    <div class="col-lg-12">
        

        <table class="table w-full table-hover table-bordered table-striped clsDataTable">
            <thead>
                <tr>
                    <th style="width: 5%;">SL</th>
                    <th>Branch Name</th>
                    <th>Branch Code</th>
                    <th>Branch Date</th>
                    <th>Status</th>
                    <th style="width: 10%;" class="text-center">Action</th>
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

        ajaxDataLoad(SDate, EDate, branchID);
    });

    $('#btnDayEnd').click(function() {
        var BranchId = $('#branch_id').val();
        $.ajax({
            method: "GET",
            url: "{{route('mfndayendexecute')}}",
            dataType: "json",
            data: {
                BranchId: BranchId
            },
            success: function(data) {
                // console.log(data.auto,data.name)

                if (data == true) {
                    toastr.success("Successfully Executed Day End");
                    var SDate = $('#StartDate').val();
                    var EDate = $('#EndDate').val();
                    var branchID = $('#branch_id').val();

                    ajaxDataLoad(SDate, EDate, branchID);
                } else if( data == 'month') {
                    swal({
                        icon: 'warning',
                        title: 'Month End',
                        text: 'Please Month End at first!',
                    });
                }else if( data[0] == 'autoprocessFalse') {
                    swal({
                        icon: 'warning',
                        title: 'Auto Process Not Completed',
                        text: 'For samity:'+data[1]+'!',
                    });
                }else{
                    if (data['alert-type'] == 'error') {
                        toastr.error(data['message']);
                    }
                    else{
                        toastr.error("Unsuccessful to Day End Executed");
                    }                    
                }
            }
        });

        
    });
});

function ajaxDataLoad(SDate = null, EDate = null, branchID = null) {
    // console.log(branchID);
    $('.clsDataTable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            "ajax":{ 
                    "url": "{{ url()->current() }}",
                    // "url": "{{route('mfndayendDatatable')}}",
                    "dataType": "json",
                    "type": "post",
                    "data": {
                        SDate: SDate,
                        EDate: EDate,
                        branchID: branchID,
                    }
                },
            "columns": [
                { data: 'slNo',
                name: 'slNo', orderable: false, targets: 1, className: 'text-center'},
                { "data": "branch_name" },
                { "data": "branch_code" },
                { "data": "branch_date" },
                { "data": "status" },
                { "data": "action", name: 'action', className: 'text-center d-print-none', orderable: false, "width": "10%" },
            ],
            'fnRowCallback': function(nRow, aData, Index) {
            $('.btnDelete', nRow).removeClass('btnDelete');

            // var actionHTML = '';

            // if(aData.action !== 0){
            //     actionHTML = '<a href="javascript:void(0)" onclick="fnDelete('+aData.action+');" title="Delete" class=""><i class="icon wb-trash mr-2 blue-grey-600"></i></a>';
            // }

            // $('td:last', nRow).html(actionHTML);
            var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData.action.action_link);
            $('td:last', nRow).html(actionHTML);
            
        }
            // "columnDefs": [ {
            //   "targets": 5,
            //   "createdCell": function (td, cellData, rowData, row, col) {
            //     $(td).addClass("text-center d-print-none");
            //     $(td).closest('tr').attr("cellData", cellData);
            //     $(td).html('');                
            //   }
            // } ]
        });
}

function fnDelete(RowID) {
    /**
     * para1 = link to delete without id
     * para 2 = ajax check link same for all
     * para 3 = id of deleting item
     * para 4 = matching column
     * para 5 = table 1
     * para 6 = table 2
     * para 7 = table 3
     */
     $.ajax({
            method: "GET",
            url: "{{route('deletemfndayend')}}",
            dataType: "text",
            data: {
                RowID: RowID
            },
            success: function(data) {
                if (data == true) {
                    toastr.success("Successfully Deleted");
                    
                    var SDate = $('#StartDate').val();
                    var EDate = $('#EndDate').val();
                    var branchID = $('#branch_id').val();

                    ajaxDataLoad(SDate, EDate, branchID);
                    // console.log(data);
                }else if (data == 'month'){
                    swal({
                        icon: 'warning',
                        title: 'Delete',
                        text: 'Please delete Month End at first!',
                    });
                }else{
                    toastr.error("Unsuccessful Deleting Data");
                }
            }
        });
    // fnDeleteCheck(
    //     "{{url('mfn/day_end/delete/')}}",
    //     "{{url('/ajaxDeleteCheck')}}",
    //     RowID,

    // );
}

$('form').submit(function(event) {
    $(this).find(':submit').attr('disabled', 'disabled');
});
</script>

@endsection