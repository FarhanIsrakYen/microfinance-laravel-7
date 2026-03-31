@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
$StartDate =  Common::systemCurrentDate();
$EndDate = Common::systemCurrentDate();



$ProjectData = Common::ViewTableOrder('gnl_projects', [['is_delete', 0], ['is_active', 1]], ['id', 'project_name'], ['project_name', 'ASC']);
?>
<!-- Page -->
<div class="row align-items-center pb-10 d-print-none">
    <!-- Html View Load For Branch Search -->
    {!! HTML::forBranchFeildSearch('all') !!}
    <div class="col-lg-2">
        <label class="input-title">Project</label>
        <select class="form-control clsSelect2"  id="project_id">
            <option value="">Select Project</option>
            @foreach ($ProjectData as $Row)
            <option value="{{ $Row->id }}">{{ $Row->project_name}}</option>
            @endforeach
        </select>
    </div>

   <!--  <div class="col-lg-2">
        <label class="input-title">Start Date</label>
        <div class="input-group">
            <input type="text" class="form-control round datepicker-custom" id="start_date"
                name="startDate" placeholder="DD-MM-YYYY">
             <input type="text" class="form-control datepicker-custom" id="start_date" name="StartDate" 
                 placeholder="DD-MM-YYYY" value="{{ $StartDate }}"> 
        </div>
    </div> -->
          <div class="col-lg-2">
      <label class="input-title">Start Date</label>
        <div class="input-group ghdatepicker">
            <div class="input-group-prepend ">
                <span class="input-group-text ">
                    <i class="icon wb-calendar round" aria-hidden="true"></i>
                </span>
            </div>
            <input type="text" class="form-control round datepicker-custom" id="start_date"
                name="startDate" placeholder="DD-MM-YYYY">
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
            <input type="text" class="form-control round datepicker-custom" id="end_date"
                name="endDate" placeholder="DD-MM-YYYY">
        </div>
    </div>
  <!--   <div class="col-lg-2">
        <label class="input-title">End Date</label>
        <div class="input-group">
            <input type="text" class="form-control round datepicker-custom" id="end_date"
                name="endDate" placeholder="DD-MM-YYYY">
             <input type="text" class="form-control datepicker-custom" id="end_date" name="EndDate"
                placeholder="DD-MM-YYYY" value="{{ $EndDate }}">
        </div>
    </div> -->
    @if(Common::getBranchId() == 1)
    <div class="col-lg-2 pt-20 text-center">
        <a href="javascript:void(0)" id="searchButton" name="searchButton" class="btn btn-primary btn-round">Search</a>
    </div>
    @endif
</div>
<div class="row">
    <div class="col-lg-12">
        <table class="table w-full table-hover table-bordered table-striped clsDataTable">
          <thead>
          <tr>
              <th width="3%">SL</th>
              <th>Opening Date</th>
              <th class="text-left">Project</th>
              <th class="text-left">Branch</th>
              <th class="text-right">Debit Amount</th>
              <th class="text-right">Credit Amount</th>
              <th class="text-right">Balance</th>
              <th width="10%">Action</th>
            </tr>
          <!-- <input type="hidden" name="_token" value="MTBsPCor5aR0SMWFOLVkWvayPlRxBsLBjJ02Lyfp"> -->
          </thead>
          <tbody>
            </tbody>
        </table>
    </div>
</div>


<!-- End Page -->
<script>
function ajaxDataLoad(BranchID = null, projectID = null, startDate = null, endDate = null,) {

    $('.clsDataTable').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        stateSave: true,
        stateDuration: 1800,
            "ajax": {
                "url": "{{route('ACCOpeningBalance')}}",
                "dataType": "json",
                "type": "post",
                "data": {
                    _token: "{{csrf_token()}}",
                      BranchID: BranchID,
                      projectID: projectID,
                      startDate: startDate,
                      endDate: endDate,
                }
            },


            columns: [

                {
                    data: 'sl',
                    className: 'text-center'
                },
                {
                    data: 'opening_date',
                    className: 'text-center'
                },
                {
                    data: 'project_name',
                    orderable: false

                },
                {
                    data: 'branch_name',
                    orderable: false
                },
                {
                    data: 'ttl_debit_amt',
                    className: 'text-right'
                },
                {
                    data: 'ttl_credit_amt',
                    className: 'text-right'
                },
                {
                    data: 'ttl_balance_amt',
                    className: 'text-right'
                },

                {
                    data: 'action',
                    orderable: false,
                    className: 'text-center'
                },

            ],
            'fnRowCallback': function(nRow, aData, Index) {
                var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData.action.action_link);
                $('td:last', nRow).html(actionHTML);
                // console.log(aData.recordsFiltered);

            },
            drawCallback: function(oResult) {
                if (oResult.json) {

                    var TotalRow = oResult.json.recordsTotal;
                    var current_branch_id = oResult.json.current_branch_id;
                    var access_branch = oResult.json.access_branch;

                    if(current_branch_id === 1 || access_branch.length > 1 || TotalRow === 0){
                        $('.page-header-actions').show();
                    }
                    else{
                        $('.page-header-actions').hide();
                    }

                }
            },
        });
    }

    $(document).ready(function() {
        ajaxDataLoad();

        $('#searchButton').click(function() {
            // console.log('dd');
            var BranchID = $('#branch_id').val();
            var projectID = $('#project_id').val();
            var startDate = $('#start_date').val();
            var endDate = $('#end_date').val();
            ajaxDataLoad(BranchID,projectID,startDate,endDate);
        });
    });

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

        fnDeleteCheck(
            "{{url('acc/acc_ob/delete/')}}",
            "{{url('/ajaxDeleteCheck')}}",
            RowID
        );
    }
    </script>


@endsection
