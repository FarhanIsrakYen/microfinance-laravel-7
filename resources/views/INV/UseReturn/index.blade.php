@extends('Layouts.erp_master')
@section('content')
<!-- Page -->
<?php 
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
use App\Services\HrService as HRS;
use App\Services\RoleService as Role;
$StartDate = Common::systemCurrentDate();
$EndDate = Common::systemCurrentDate();
$usesBillNoData = Common::ViewTableOrderIn('inv_use_m',
    [['is_delete', 0], ['is_active', 1]],
    ['branch_id', HRS::getUserAccesableBranchIds()],
    ['id','uses_bill_no'],
    ['uses_bill_no', 'ASC']);
?>

<!-- Search Option start -->
<div class="row align-items-center pb-10 d-print-none">
    <!-- Html View Load For Branch Search -->

    <div class="col-lg-2">
        <label class="input-title">Uses Bill No</label>
        <div class="input-group">
            <select class="form-control clsSelect2" name="uses_bill_no" id="uses_bill_no_id">
                <option value="">Select Option</option>
                @foreach ($usesBillNoData as $row)
                <option value="{{ $row->uses_bill_no}}">{{ $row->uses_bill_no }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-lg-2">
      <label class="input-title">Start Date</label>
        <div class="input-group ghdatepicker">
            <div class="input-group-prepend ">
                <span class="input-group-text ">
                    <i class="icon wb-calendar round" aria-hidden="true"></i>
                </span>
            </div>
            <input type="text" class="form-control round datepicker-custom" id="start_date"
                name="startDate" placeholder="DD-MM-YYYY" value="{{$StartDate}}">
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
                name="endDate" placeholder="DD-MM-YYYY" value="{{$EndDate}}">
        </div>
    </div>

    <div class="col-lg-2 pt-20 text-center">
        <a href="javascript:void(0)" name="searchButton"
            class="btn btn-primary btn-round" id="useRSearch">Search</a>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                <thead>
                    <tr>
                        <th width="5%">SL</th>
                        <th>Return Date</th>
                        <th>Return Bill No</th>
                        <th>Use Bill No</th>
                        <th>Return Quantity</th>
                        <th>Product</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
<!-- End Page -->

<script>

    function ajaxDataLoad(useBillNo = null,sDate = null, eDate = null){

        $('.clsDataTable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            order: [[ 1, "DESC" ]],
            stateSave: true,
            stateDuration: 1800,
            // ordering: false,
            // lengthMenu: [[10, 20, 30, 50], [10, 20, 30, 50]],
            "ajax":{
               "url": "{{route('invUseRList')}}",
               "dataType": "json",
               "type": "post",
               "data":{ _token: "{{csrf_token()}}",
                        useBillNo: useBillNo,
                        sDate: sDate,
                        eDate: eDate
                    }
             },
            columns: [
                { data: 'id', className: 'text-center'},
                { data: 'return_date'},
                { data: 'return_bill_no'},
                { data: 'uses_bill_no'},
                { data: 'total_return_quantity',className: 'text-center'},
                { data: 'product_name', orderable: false},
                { data: 'action', name: 'action', orderable: false, className: 'text-center'},

            ],
            'fnRowCallback': function(nRow, aData, Index) {
                // console.log(aData.sales_bill_no);
                var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData.action.action_link);

                $('td:last', nRow).html(actionHTML);
            }
            // drawCallback: function (oResult) {
            //     $('#TQuantity').html(oResult.json.totalQuantity);
            //     $('#TUnitPrice').html(oResult.json.totalUnitPrice);
            //     $('#TAmount').html(oResult.json.totalAmount);
            // },
        });
    }

    $(document).ready( function () {

        ajaxDataLoad();

        $('#useRSearch').click(function(){

            var useBillNo = $('#uses_bill_no_id').val();
            var sDate = $('#start_date').val();
            var eDate = $('#end_date').val();
            ajaxDataLoad(useBillNo, sDate, eDate);
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
            "{{url('inv/use_return/delete/')}}",
            "{{url('/ajaxDeleteCheck')}}",
            RowID
        );
    }
</script>
@endsection
