@extends('Layouts.erp_master')
@section('content')
<?php
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;


// $SupplierData = Common::ViewTableOrder('pos_suppliers', [['is_delete', 0], ['is_active', 1]], ['id', 'sup_name'], ['sup_name', 'ASC']);
// $GroupData = Common::ViewTableOrder('pos_p_groups', [['is_delete', 0], ['is_active', 1]], ['id', 'group_name'], ['group_name', 'ASC']);
// $CategoryData = Common::ViewTableOrder('pos_p_categories', [['is_delete', 0], ['is_active', 1]], ['id', 'cat_name'], ['cat_name', 'ASC']);
// $SubCatData = Common::ViewTableOrder('pos_p_subcategories', [['is_delete', 0], ['is_active', 1]], ['id', 'sub_cat_name'], ['sub_cat_name', 'ASC']);
// $BrandData = Common::ViewTableOrder('pos_p_brands', [['is_delete', 0], ['is_active', 1]], ['id', 'brand_name'], ['brand_name', 'ASC']);

$StartDate = Common::systemCurrentDate();
$EndDate = Common::systemCurrentDate();
// $BranchID = (isset($BranchID) && !empty($BranchID)) ? $BranchID : Common::getBranchId();
$customerData = Common::ViewTableOrder('pos_customers',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'customer_name','customer_no'],
    ['customer_name', 'ASC']);
$productData = Common::ViewTableOrder('pos_products',
  [['is_delete', 0], ['is_active', 1]],
  ['id', 'product_name','prod_barcode'],
  ['product_name', 'ASC']);


?>
<!-- Search Option Start -->


<!-- Search Option start -->
<div class="row align-items-center pb-10 d-print-none">
    <!-- Html View Load For Branch Search -->
    {!! HTML::forBranchFeildSearch('all') !!}
    <div class="col-lg-2">
        <label class="input-title">Customer</label>
        <div class="input-group">
            <select class="form-control clsSelect2" name="customer_id" id="customer_id">
                <option value="">Select All</option>
                @foreach ($customerData as $row)
                <option value="{{ $row->customer_no }}">{{ $row->customer_name."(".$row->customer_no.")"}}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-lg-2">
        <label class="input-title">Product</label>
        <div class="input-group">
            <select class="form-control clsSelect2" name="product_id" id="product_id">
                <option value="">Select All</option>
                @foreach ($productData as $row)
                <option value="{{ $row->id }}">{{ $row->product_name."(".$row->prod_barcode.")"}}</option>
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


    <!-- <div class="col-lg-2">
        <label class="input-title">Sales Type</label>
        <select class="form-control clsSelect2" name="sales_type" id="sales_type">
            <option value="">Select All</option>
            <option value="1">Cash</option>
            <option value="2">Installment</option>
        </select>
    </div> -->
    <div class="col-lg-2 pt-20 text-center">
        <a href="javascript:void(0)" name="searchButton"
            class="btn btn-primary btn-round" id="salesSearch">Search</a>
    </div>
</div>

<!-- {{-- do not delete commentted code below --}}
        {{-- <div class="row align-items-center d-flex justify-content-center pb-10">

            <label class="input-title">Group</label>
            <div class="col-lg-2">
                <select class="form-control clsSelect2" name="group_id" id="group_id">
                    <option value="">Select one</option>
                    @foreach ($GroupData as $Row)
                    <option value="{{ $Row->id }}">{{ $Row->group_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2">
              <label class="input-title">Category</label>
                <select class="form-control clsSelect2" name="category_id" id="category_id">
                    <option value="">Select one</option>
                    @foreach ($CategoryData as $Row)
                    <option value="{{ $Row->id }}">{{ $Row->cat_name }}</option>
                    @endforeach
                </select>
            </div>

            <label class="input-title">Sub Category</label>
            <div class="col-lg-2">
                <select class="form-control clsSelect2" name="sub_cat_id" id="sub_cat_id">
                    <option value="">Select one</option>
                    @foreach ($SubCatData as $Row)
                    <option value="{{ $Row->id }}">{{ $Row->sub_cat_name }}</option>
                    @endforeach
                </select>
            </div>

            <label class="input-title">Brand</label>
            <div class="col-lg-2">
                <select class="form-control clsSelect2" name="brand_id" id="brand_id">
                    <option value="">Select one</option>
                    @foreach ($BrandData as $Row)
                    <option value="{{ $Row->id }}">{{ $Row->brand_name }}</option>
                    @endforeach
                </select>
            </div>

        </div> --}} -->


<!-- Search Option End -->


<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                <thead>
                    <tr>
                        <th width="3%">SL</th>
                        <th>Date</th>
                        <th>Bill No</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Total Quantity</th>
                        <th class="text-center">Total Amount</th>
                        <!-- <th class="text-center">Paid</th> -->
                        <!-- <th class="text-center">Due</th> -->
                        <th>Branch</th>
                        <th width="10%">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<script>

    function ajaxDataLoad(sDate = null, eDate = null, branchID = null,customerID = null,ProductID = null, salesType = null,
                          pGroupID = null, categoryId = null, subCatID = null, brandID = null){

        $('.clsDataTable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            order: [[ 1, "DESC" ]],
            stateSave: true,
            stateDuration: 300,
            // ordering: false,
            // lengthMenu: [[10, 20, 30, 50], [10, 20, 30, 50]],
            "ajax":{
               "url": "{{route('CashSalesList')}}",
               "dataType": "json",
               "type": "post",
               "data":{ _token: "{{csrf_token()}}",
                        sDate: sDate,
                        eDate: eDate,
                        branchID: branchID,
                        customerID: customerID,
                        ProductID: ProductID,
                        salesType: salesType,
                        pGroupID: pGroupID,
                        categoryId: categoryId,
                        subCatID: subCatID,
                        brandID: brandID
                    }
             },
            columns: [
                { data: 'id', className: 'text-center'},
                { data: 'sales_date'},
                { data: 'sales_bill_no'},
                { data: 'customer_name', orderable: false,},
                { data: 'product_name', orderable: false},
                { data: 'total_quantity', className: 'text-center'},
                { data: 'total_amount', className: 'text-right'},
                // { data: 'paid_amount', className: 'text-right'},
                // { data: 'due_amount', className: 'text-right'},
                { data: 'branch_name', orderable: false},
                { data: 'action', name: 'action', orderable: false, className: 'text-center'},

            ],
            'fnRowCallback': function(nRow, aData, Index) {
                // console.log(aData.sales_bill_no);
                var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData.action.action_link);

                var hiturl = "<?= url('pos/sales_cash/invoice')?>/"+aData.sales_bill_no;
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
            var eDate = $('#end_date').val();ProductID
            var branchID = $('#branch_id').val();
            var customerID = $('#customer_id').val();
            var ProductID = $('#product_id').val();
            var pGroupID = $('#group_id').val();
            var categoryId = $('#category_id').val();
            var subCatID = $('#sub_cat_id').val();
            var brandID = $('#brand_id').val();
            // var salesType = $('#sales_type').val();
            var salesType = 1;

            ajaxDataLoad(sDate, eDate, branchID, customerID, ProductID,  salesType, pGroupID, categoryId, subCatID, brandID);
        });

        // Link to Installment Sale Entry
        var html = '<a target="_blank" href="{{url('pos/sales_installment/add')}}" class="btn btn-sm btn-primary btn-outline btn-round mr-2">';
            html += '<i class="icon wb-link" aria-hidden="true"></i>';
            html += '<span class="hidden-sm-down">&nbsp;Installment Entry</span>';
            html += '</a>';
        $('.page-header-actions').prepend(html);

        
        
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
            "{{url('pos/sales_cash/delete/')}}",
            "{{url('/ajaxDeleteCheck')}}",
            RowID,
            "{{base64_encode('sales_bill_no')}}",
            "{{base64_encode('is_delete,0')}}",
            "{{base64_encode('pos_sales_return_m')}}"
        );
    }

</script>

@endsection
