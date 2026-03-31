@extends('Layouts.erp_master')
@section('content')
<?php 
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;


$customerData = Common::ViewTableOrder('bill_customers', 
                [['is_delete', 0], ['is_active', 1]], 
                ['id', 'customer_name','customer_no'], 
                ['customer_name', 'ASC']);

$StartDate = Common::systemCurrentDate();
$EndDate = Common::systemCurrentDate();

?>
<!-- Search Option Start -->


<!-- Search Option start -->
<div class="row align-items-center pb-10 d-print-none">

    <div class="col-lg-2">
        <label class="input-title">Customer</label>
        <select class="form-control clsSelect2" name="customer_no" id="customer_no">
            <option value="">Select</option>
            @foreach($customerData as $cData)
                <option value="{{ $cData->customer_no }}">{{ $cData->customer_name . '(' . $cData->customer_no . ')' }}</option>
            @endforeach
        </select>
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
            class="btn btn-primary btn-round" id="salesSearch">Search</a>
    </div>
</div>
<!-- Search Option End -->


<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                <thead>
                    <tr>
                        <th width="3%">SL</th>
                        <th width="10%">Date</th>
                        <th width="15%">Bill No</th>
                        <th width="20%">Customer</th>
                        <th width="7%">Total Quantity</th>
                        <th width="10%" class="text-center">Total Amount</th>
                        <!-- <th>Branch</th> -->
                        <th width="12%">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<script>

    function ajaxDataLoad(sDate = null, eDate = null, branchID = null, customerNo = null){

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
               "url": "{{route('cashBillList')}}",
               "dataType": "json",
               "type": "post",
               "data":{ _token: "{{csrf_token()}}",
                        sDate: sDate,
                        eDate: eDate,
                        branchID: branchID,
                        customerNo: customerNo
                    }
             },
            columns: [
                { data: 'id', className: 'text-center', width: '3%'},
                { data: 'bill_date'},
                { data: 'bill_no'},
                { data: 'customer_name', orderable: false},
                { data: 'total_quantity', className: 'text-center'},
                { data: 'total_amount', className: 'text-right'},
                // { data: 'branch_name', orderable: false},
                { data: 'action', name: 'action', orderable: false, className: 'text-center'},

            ],
            'fnRowCallback': function(nRow, aData, Index) {
                // console.log(aData.bill_no);
                var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData.action.action_link);
                
                var hiturl = "<?= url('bill/cash_bill/invoice')?>/"+aData.bill_no;
                actionHTML += '<a href="'+hiturl+'" title="Invoice" class="btnView"><i class="fa fa-file-powerpoint-o"></i></a>';
                
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

        $('#salesSearch').click(function(){

            var sDate = $('#start_date').val();
            var eDate = $('#end_date').val();
            var branchID = $('#branch_id').val();
            var customerNo = $('#customer_no').val();
            ajaxDataLoad(sDate, eDate, branchID, customerNo);
        });
    });

    // Delete Data
    function fnDelete(RowID) {

        // console.log('test');
        // return false;
        /**
         * para 1 = link to delete without id
         * para 2 = ajax check link same for all
         * para 3 = id of deleting item
         * para 4 = matching column
         * para 5 = condition2
         * para 6 = table 1
         * para 7 = table 2
         * para 8 = table 3
         */
         /*Common::ViewTableOrder('pos_p_subcategories', [['is_delete', 0], ['is_active', 1]], ['id', 'sub_cat_name'], ['sub_cat_name', 'ASC']);*/


        fnDeleteCheck(
            "{{url('bill/cash_bill/delete/')}}",
            "{{url('/ajaxDeleteCheck')}}",
            RowID,
            "{{base64_encode('bill_no')}}",
            "{{base64_encode('is_delete,0')}}",
            // "{{base64_encode('pos_sales_return_m')}}"
        );
    }

</script>

@endsection
