@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
use App\Services\HrService as HRS;
use App\Services\RoleService as Role;

$StartDate = Common::systemCurrentDate();
$EndDate = Common::systemCurrentDate();
$salesBillNoData = Common::ViewTableOrderIn('pos_sales_return_m',
    [['is_delete', 0], ['is_active', 1]],
    ['branch_id', HRS::getUserAccesableBranchIds()],
    ['id','sales_bill_no'],
    ['sales_bill_no', 'ASC']);

$productData = Common::ViewTableOrder('pos_products',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'product_name', 'prod_barcode'],
    ['product_name', 'ASC']);



?>

<!-- Search Option start -->
<div class="row align-items-center pb-10 d-print-none">
    <!-- Html View Load For Branch Search -->
    {!! HTML::forBranchFeildSearch('all') !!}

    <div class="col-lg-2">
        <label class="input-title">Product</label>
        <select class="form-control clsSelect2" name="product_id" id="product_id">
            <option value="">Select All</option>
            @foreach ($productData as $row)
            <option value="{{ $row->id }}">{{ $row->product_name."(".$row->prod_barcode.")"}}</option>
            @endforeach
        </select>
    </div>
    
    <div class="col-lg-2">
        <label class="input-title">Sales Bill No</label>
        <div class="input-group">
            <select class="form-control clsSelect2" name="sales_bill_no" id="sales_bill_no_id">
                <option value="">Select Option</option>
                @foreach ($salesBillNoData as $row)
                <option value="{{ $row->sales_bill_no}}">{{ $row->sales_bill_no }}</option>
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

    <div class="col-lg-2 pt-20 text-center">
        <a href="javascript:void(0)" name="searchButton"
            class="btn btn-primary btn-round" id="salesRSearch">Search</a>
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
                        <th>Sales Bill No</th>
                        <th>Return Quantity</th>
                        <th>Return Amount</th>
                        <th>Product</th>
                        <th>Branch</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
<!-- End Page -->

<script>

    function ajaxDataLoad(branchID = null, salesBillNo = null,sDate = null, eDate = null,
                          pGroupID = null, categoryId = null, subCatID = null, brandID = null){

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
               "url": "{{route('CashSalesRList')}}",
               "dataType": "json",
               "type": "post",
               "data":{ _token: "{{csrf_token()}}",
                        branchID: branchID,
                        salesBillNo: salesBillNo,
                        sDate: sDate,
                        eDate: eDate,
                        pGroupID: pGroupID,
                        categoryId: categoryId,
                        subCatID: subCatID,
                        brandID: brandID,
                    }
             },
            columns: [
                { data: 'id', className: 'text-center'},
                { data: 'return_date'},
                { data: 'return_bill_no'},
                { data: 'sales_bill_no'},
                { data: 'total_return_quantity',className: 'text-center'},
                { data: 'total_return_amount',className: 'text-right'},
                { data: 'product_name', orderable: false},
                { data: 'branch_name', orderable: false},
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

        $('#salesRSearch').click(function(){

            var branchID = $('#branch_id').val();
            var salesBillNo = $('#sales_bill_no_id').val();
            var sDate = $('#start_date').val();
            var eDate = $('#end_date').val();
            var pGroupID = $('#group_id').val();
            var categoryId = $('#category_id').val();
            var subCatID = $('#sub_cat_id').val();
            var brandID = $('#brand_id').val();
            // var salesBillNo = $('#sales_type').val();
// console.log(salesBillNo);
            ajaxDataLoad(branchID, salesBillNo, sDate, eDate,pGroupID, categoryId, subCatID, brandID);
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
             "{{url('pos/sales_return/delete/')}}",
             "{{url('/ajaxDeleteCheck')}}",
             RowID
        );
    }

</script>

@endsection
