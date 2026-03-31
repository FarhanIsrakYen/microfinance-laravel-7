@extends('Layouts.erp_master_full_width')
@section('content')
<?php 
use App\Services\CommonService as Common;
use App\Services\HrService as HRS; 
use App\Services\HtmlService as HTML;
use App\Services\BillService as BILLS;
?>

<form method="post" data-toggle="validator" novalidate="true" id="bill_form">
    @csrf
    <div class="row">
        <div class="col-lg-9 pr-0">
            
            <div class="row">
                <div class="col-lg-8 offset-lg-3">
                    <!-- Html View Load  -->
                    {!! HTML::forCompanyFeild($billData->company_id) !!}
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8 offset-lg-3">
                    {!! HTML::forBranchFeild(false,'branch_id','branch_id',$billData->branch_id,'','Branch') !!}
                </div>
            </div>
            
            <!-- <div class="panel">
                <div class="panel-body">
                    <div class="row pt-4">
                        <div class="col-lg-3">
                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title">Customer</label>
                                <div class="col-lg-8 input-group addSupplier">

                                    <?php
                                        $CustList = Common::ViewTableOrderIn('bill_customers',
                                                    ['is_delete' => 0],
                                                    ['branch_id', HRS::getUserAccesableBranchIds()],
                                                    ['id','customer_no', 'customer_name', 'customer_no'],
                                                    ['customer_name', 'ASC']);
                                    ?>
                                    <select class="form-control round browser-default clsSelect2" name="customer_id"
                                    id="customer_id" onchange="fnCustDataLoad();">
                                        {{-- onchange="fnCustDataLoad();" --}}
                                        <option value="">Select</option>
                                        @foreach($CustList as $CData)
                                        <option value="{{ $CData->customer_no }}"
                                            @if($billData->customer_id == $CData->customer_no) {{ 'selected' }} @endif>
                                            {{ $CData->customer_name." (".$CData->customer_no.")" }}
                                        </option>
                                        @endforeach
                                    </select>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div> -->

            <div class="panel">
                <div class="panel-body">
                    <!-- <div class="row pt-4 pb-4">
                        <div class="col-lg-1 labelPercentSearch">
                            <label for="textCategory" class="input-title">Category</label>
                        </div>
                        <div class="col-lg-2 inputPercentSearch input-group">
                            <?php $categoryList = Common::ViewTableOrder('bill_p_categories',
                                            ['is_delete' => 0],
                                            ['id', 'cat_name'],
                                            ['cat_name', 'ASC']) ?>
                            <select class="form-control clsSelect2"
                                    id="prod_cat_id" onchange="
                                            fnProductLoad();">
                                <option value="">Select</option>
                                @foreach($categoryList as $CData)
                                <option value="{{ $CData->id }}">{{ $CData->cat_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div> -->

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-1 input-title">Customer</label>
                        <div class="col-lg-2 input-group addSupplier">

                            <?php
                                $CustList = Common::ViewTableOrderIn('bill_customers',
                                            ['is_delete' => 0],
                                            ['branch_id', HRS::getUserAccesableBranchIds()],
                                            ['id','customer_no', 'customer_name', 'customer_no'],
                                            ['customer_name', 'ASC']);
                            ?>
                            <select class="form-control round browser-default clsSelect2" name="customer_id"
                            id="customer_id" onchange="fnCustDataLoad();">
                                {{-- onchange="fnCustDataLoad();" --}}
                                <option value="">Select</option>
                                @foreach($CustList as $CData)
                                <option value="{{ $CData->customer_no }}"
                                    @if($billData->customer_id == $CData->customer_no) {{ 'selected' }} @endif>
                                    {{ $CData->customer_name." (".$CData->customer_no.")" }}
                                </option>
                                @endforeach
                            </select>

                        </div>
                    </div>

                    <table class="table table-hover table-striped table-bordered w-full text-center table-responsive" id="billTable">
                        <thead class="scrollHead">
                            <tr>
                                <th width="51%">
                                    <select class="clsSelect2" style="width: 100%" id="selProduct">
                                        <option value="">Select Product/Package</option>
                                        <option value="1">Product</option>
                                        <option value="2">Package</option>
                                    </select>
                                </th>
                                <th width="15%" class="RequiredStar">Quantity</th>
                                <th width="15%">Amount</th>
                                <th width="15%">Total</th>
                                <th width="4%"></th>
                            </tr>
                        </thead>
                        <tbody class="scrollBody">
                            <?php
                                $i = 0;
                                $TableID = "billTable";

                                $ColumnName = "product_id_arr[]&product_name_arr[]&product_quantity_arr[]&unit_sale_price_arr[]&product_sales_price_arr[]&product_type_arr[]";

                                $ColumnID = "product_id_&product_name_&product_quantity_&unit_sale_price_&product_sales_price_&product_type_&deleteRow_";

                                $productList = Common::ViewTableOrder('bill_products',
                                                ['is_delete' => 0],
                                                ['id', 'product_name',  'sale_price', 'prod_vat'],
                                                ['product_name', 'ASC']);

                                $packageList = Common::ViewTableOrder('bill_packages',
                                                ['is_delete' => 0],
                                                ['id', 'package_name',  'package_price'],
                                                ['package_name', 'ASC']);

                            ?>

                            <tr>
                                <td width="51%" class="barcodeWidth text-left">
                                    <!-- {{-- name="product_id_arr[]" --}} -->

                                    <select id="product_id_0" class="form-control round clsSelect2" style="width: 100%">
                                        <option value="">Select Product</option>
                                    </select>
                                </td>

                                <td width="15%">

                                    <!-- {{-- name="product_quantity_arr[]"  --}} -->
                                    <input type="number" id="product_quantity_0" class="form-control round clsQuantity text-center"
                                        placeholder="Enter Quantity" value="0"
                                        onkeyup="fnTotalQuantity(); fnTtlProductPrice(0); fnCheckQuantity(0);" min="1" readonly>
                                </td>

                                <td width="15%">
                                    <!-- {{-- name="product_cost_price_arr[]"  --}} -->
                                    <!-- <input type="hidden" id="product_cost_price_0" value="0"> -->

                                    <!-- {{-- name="unit_sale_price_arr[]"  --}} -->
                                    <input type="number" id="unit_sale_price_0" class="form-control round text-right" value="0"
                                        min="1" readonly>
                                </td>

                                <td width="15%">
                                    <!-- {{-- name="product_sales_price_arr[]"  --}} -->
                                    <input type="number" id="product_sales_price_0"
                                        class="form-control round ttlAmountCls text-right" value="0" min="1" readonly>
                                    <input type="hidden" id="product_type_0" class="form-control round text-right" readonly>
                                </td>

                                <td width="4%">
                                    <a href="javascript:void(0);"
                                        class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center"
                                        onclick="btnAddNewRow('<?= $TableID ?>', '<?= $ColumnName ?>', '<?= $ColumnID ?>', 'TotalRowID');">
                                        <i class="icon wb-plus  align-items-center"></i>
                                    </a>
                                </td>
                            </tr>
                            @if(count($billDataD) > 0)
                                @foreach($billDataD as $bDataD)
                                <?php $i++; ?>
                                <tr>
                                    <td width="51%" class="barcodeWidth">
                                        <input type="hidden" id="product_id_{{ $i }}" name="product_id_arr[]" 
                                        value="{{ $bDataD->product_id }}">
                                        @if($bDataD->product_type == 1)
                                        @foreach($productList as $productInfo)
                                        @if($productInfo->id == $bDataD->product_id)
                                        <input type="text" class="form-control round"
                                            value="{{ $productInfo->product_name  }} "
                                            readonly>
                                        @endif
                                        @endforeach
                                        @endif
                                        @if($bDataD->product_type == 2)
                                        @foreach($packageList as $productInfo)
                                        @if($productInfo->id == $bDataD->product_id)
                                        <input type="text" class="form-control round"
                                            value="{{ $productInfo->package_name  }} "
                                            readonly>
                                        @endif
                                        @endforeach
                                        @endif
                                    </td>

                                    <td  width="15%">
                                        <input type="number" name="product_quantity_arr[]" id="product_quantity_{{ $i }}"
                                            class="form-control round clsQuantity text-center" placeholder="Enter Quantity"
                                            value="{{ $bDataD->product_quantity }}"
                                            onkeyup="fnTotalQuantity(); fnTtlProductPrice({{ $i }}); fnCheckQuantity({{ $i }})" required min="1">
                                    </td>

                                    <td width="15%">

                                        <input type="number" name="unit_sale_price_arr[]" id="unit_sale_price_{{ $i }}"
                                            class="form-control round text-right" value="{{ $bDataD->product_unit_price }}"
                                            min="1" readonly>
                                    </td>

                                    <td width="15%">
                                        <input type="number" name="product_sales_price_arr[]"
                                            id="product_sales_price_{{ $i }}" class="form-control round ttlAmountCls text-right"
                                            value="{{ $bDataD->product_sales_price }}" min="1" readonly>
                                            
                                        <input type="hidden" name="product_type_arr[]" id="product_type_{{ $i }}" class="form-control round text-right" readonly value="{{ $bDataD->product_type }}">
                                    </td>

                                    <td width="4%">

                                        <a href="javascript:void(0)"
                                            class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center clsAddNewRow"
                                            id="" onclick="btnRemoveRow(this);">
                                            <i class="icon fa fa-times align-items-center"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            @endif
                        </tbody>
                        <tfoot class="scrollFooter">
                            <tr>
                                <td width="51%" style="text-align:right;">
                                    <h5>TOTAL</h5>
                                    <input type="hidden" name="total_quantity" id="total_quantity" value="{{ $billData->total_quantity }}"
                                        min="1">
                                </td>
                                <td width="15%" id="tdTotalQuantity" style="font-weight: bold;">{{ $billData->total_quantity }}</td>
                                <td width="15%">
                                    <h5>Amount</h5>
                                    <input type="hidden" name="total_amount" id="total_amount" value="{{ $billData->total_amount }}" min="1">
                                </td>
                                <td width="15%" id="tdTotalAmount" class="text-right" style="font-weight: bold; ">{{ $billData->total_quantity }}</td>
                                <td width="20%"></td>
                            </tr>
                        </tfoot>
                    </table>

                    <!-- Row_Count is temporary variable for using row add and delete-->
                    <input type="hidden" id="TotalRowID" value="0" />
                </div>
                <!--End Panel 2-->
            </div>
        </div>

        <div class="col-lg-3">
            <div class="panel">
                <div class="panel-body p-4">

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-5 input-title">Bill Date</label>
                        <div class="col-lg-7 input-group">
                            <?php
                                $billDate = new DateTime($billData->bill_date);
                                $billDate = (!empty($billDate)) ? $billDate->format('d-m-Y') : date('d-m-Y');
                            ?>
                            <input type="hidden" name="bill_date" class="form-control round"
                                value="{{ Common::systemCurrentDate() }}" readonly="true">
                            <label id="bill_date" class="text-black">{{ $billDate }}</label>
                        </div>
                    </div>

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-5 input-title">Bill No</label>
                        <div class="col-lg-7">
                            <div class="input-group">
                                <input type="hidden" name="bill_no"
                                    value="{{ $billData->bill_no }}" 
                                    class="form-control round" readonly>
                                <label id="bill_no" class="text-black">
                                    {{ $billData->bill_no }}
                                </label>
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>
                    </div>

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-5 input-title">Bill By</label>
                        <div class="col-lg-7 input-group">
                            <!-- {{-- Query for get all Employee --}} -->
                            <?php $empList = Common::ViewTableOrderIn('hr_employees',
                                            ['is_delete' => 0],
                                            ['branch_id', HRS::getUserAccesableBranchIds()],
                                            ['employee_no', 'emp_name', 'emp_code'],
                                            ['emp_name', 'ASC']) ?>

                            <select class="form-control clsSelect2" name="employee_id" id="employee_id">
                                <option value="">Select</option>
                                @foreach($empList as $eData)
                                <option value="{{ $eData->employee_no }}"
                                    @if($billData->employee_id == $eData->employee_no) {{ 'selected' }} @endif>
                                    {{ $eData->emp_name. " (". $eData->emp_code.")" }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <br>
                        <!-- @error('employee_id')
                            <div class="help-block with-errors is-invalid">{{ $message }}</div>
                        @enderror -->
                    </div>
                </div>
            </div>
            <div class="panel">
                <div class="panel-body" style="padding: 18px;">
                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-5 input-title">Discount(%)</label>
                        <div class="col-lg-7">
                            <input type="number" name="discount_rate" id="discount_rate"
                                class="form-control round text-right" value="{{ $billData->discount_rate }}" 
                                onkeyup="fnCalDiscount(this.value);">

                            <!-- {{-- hidden fields for discount amount --}} -->
                            <input type="hidden" name="discount_amount" id="discount_amount" value="0">
                        </div>
                    </div>


                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-5 input-title">T/A After Discount</label>
                        <div class="col-lg-7">
                            <input type="text" name="ta_after_discount" id="ta_after_discount"
                                class="form-control round text-right" value="{{ $billData->ta_after_discount }}" readonly>
                        </div>
                    </div>

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-5 input-title">VAT(%)</label>
                        <div class="col-lg-7">
                            <input type="number" name="vat_rate" id="vat_rate" class="form-control round text-right"
                                value="{{ $billData->vat_rate }}" onkeyup="fnCalVat(this.value);">

                            <input type="hidden" name="vat_amount" id="vat_amount" value="{{ $billData->vat_amount }}">
                        </div>
                    </div>

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-5 input-title">Gross Total</label>
                        <div class="col-lg-7">
                            <input type="text" name="gross_total" id="gross_total" 
                            class="form-control round text-right" value="{{ $billData->gross_total }}" readonly min="1">
                        </div>
                    </div>

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-5 input-title">Remarks</label>
                        <div class="col-lg-7">
                            <textarea rows="1" name="remarks" id="remarks" class="form-control round" 
                            placeholder="Enter Remarks">{{ $billData->remarks }}</textarea>
                        </div>
                    </div>

                    <div class="col-lg-12 pt-4">
                        <div class="d-flex justify-content-center">
                            <a href="javascript:void(0)" onclick="goBack();"
                                class="btn btn-default btn-round d-print-none">Back</a>
                            <button type="submit" name="submitButtonforPurchase" class="btn btn-primary btn-round ml-2" 
                            id="submitButtonforSales">Update</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</form>
</div>
<!--End Page Content-->


<style type="text/css">
    thead .select2-container--default .select2-selection--single{
        background-color: #17b3a3;
    }
    thead .select2-container--default .select2-selection--single .select2-selection__rendered{
        color: #fff;
    }
    thead .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: #fff transparent transparent transparent;
    }
    thead .select2-container--default .select2-selection--single {
        border: 1px solid #fff;
    }
</style>

<script type="text/javascript">

    $(document).ready(function() {

        $('#selProduct').change(function() {
            var selProd = this.value;
            if (selProd == 1) {
                $.ajax({
                    method: "GET",
                    url: '{{ url("bill/agreement/loadProductForAgreement") }}',
                    dataType: "text",
                    success: function(data) {
                        if (data) {
                            $('#product_id_0').empty().html(data);
                        }
                    }
                });
            }
            else {
                $.ajax({
                    method: "GET",
                    url: '{{ url("bill/agreement/loadPackageForAgreement") }}',
                    dataType: "text",
                    success: function(data) {
                        if (data) {
                            $('#product_id_0').empty().html(data);
                        }
                    }
                });
            }
        });

        $('.cls-select-2').select2({
            dropdownParent: $('#modalCustForm'),
            placeholder: "Select Please",
        });

        //initially set 0 into these fields
        $('#current_stock').html(0);
        $('#stock_quantity_0').val(0);

        /*payment month picker*/
        $("#payment_month").datepicker({
                format: "mm-yyyy",
                viewMode: "months",
                minViewMode: "months"
        });

        // Generate Bill No if branch selected
        $('#branch_id').change(function() {
            fnGenBillNo($('#branch_id').val());
        });

        // Onchanging Product Cat,Load Product
        $('#prod_cat_id').change(function() {
            $('#prod_sub_cat_id').val('');
            $('#prod_sub_cat_id').trigger('change');
            $('#prod_model_id').val('');
            $('#prod_model_id').trigger('change');
            fnProductLoad();

        });

    });

    /* Add row Start */
    function btnAddNewRow(TableID, ColumnNameS, ColumnIDS, TotalRowID) {
        var ColumnName = ColumnNameS.split("&");
        var ColumnID = ColumnIDS.split("&");
        /*
         * ColumnID[0] = this is ID for input feild Product_id_0
         * ColumnID[3] = this is ID for input feild product_quantity_0
         * ColumnID[6] = this is ID for input feild license_fee_0
         * ColumnID[7] = this is ID for input feild service_fee_0
         */
        if ($('#' + ColumnID[0] + 0).val() != '') {

            var TotalRowCount = $('#' + TotalRowID).val();
            /*
            marge two row if same product found
            */

            var test = $('#' + ColumnID[1] + 0).val();
            // alert(test);

            var oneTimeFee = $('#' + ColumnID[6] + 0).val();
            var monthlyFee = $('#' + ColumnID[7] + 0).val();

            var flag = false;

            var rowNumber = 0;

            for (var row = 1; row <= TotalRowCount; row++) {
                if ( $('#product_id_'+row).val() == $('#product_id_0').val()){
                    flag = true;
                    rowNumber = row ;
                }
            }


            if (flag === true){

                oneTimeFee = Number(oneTimeFee) + Number($('#license_fee_' + rowNumber).val());
                monthlyFee = Number(monthlyFee) + Number($('#service_fee_' + rowNumber).val());

                $('#license_fee_' + rowNumber).val(oneTimeFee);
                $('#service_fee_' + rowNumber).val(monthlyFee);
                $('#' + ColumnID[0] + 0).val('');
                $('#' + ColumnID[0] + 0).trigger('change');
                $('#' + ColumnID[3] + 0).val(0);
                $('#' + ColumnID[1] + 0).val(test);
                $('#' + ColumnID[5] + 0).val(0);
                $('#' + ColumnID[7] + 0).val(0);
                $('#' + ColumnID[3] + 0).prop('readonly', true);

            }

            else {
                TotalRowCount++;
                $('#' + TotalRowID).val(TotalRowCount);
                var ProductID = $('#' + ColumnID[0] + 0).val();
                var ProductName = $('#' + ColumnID[0] + 0).find("option:selected").attr('pname');
                // var prod_qtn = $('#' + ColumnID[0] + 0).find("option:selected").attr('prod_qtn');
                // var ProductBarcode = $('#' + ColumnID[0] + 0).find("option:selected").attr('pbarcode');
                var ProductType = $('#' + ColumnID[0] + 0).find("option:selected").attr('type');
                var ProductCostPrice = $('#' + ColumnID[0] + 0).find("option:selected").attr('pcprice');
                // var ProductSalePrice = $('#' + ColumnID[0] + 0).find("option:selected").attr('psprice');
                // var ProductQuantity = $('#' + ColumnID[3] + 0).val();
                var ProductAmount = $('#' + ColumnID[7] + 0).val();

                var html = '<tr>';

                html += '<td class="barcodeWidth" width="35%">';
                html += '<input type="hidden" id="product_id_'+ TotalRowCount +'" name="' + ColumnName[0] + '" value="' + ProductID + '">';

                html += '<input type="text" class="form-control round text-left" value="' + ProductName +'" readonly >';

                html += '<input type="hidden" id="product_type_'+ TotalRowCount +'" name="' + ColumnName[1] + '" value="' + ProductType + '">';

                html += '<td width="15%">' +
                    '<input type="number" name="' + ColumnName[6] + '" id="' + ColumnID[6] + TotalRowCount +
                    '" class="form-control round text-right" value="' + ProductCostPrice + '" readonly required min="1">' +
                    '</td>';
                html += '<td width="15%">' +
                    '<input type="number" name="' + ColumnName[7] + '" id="' + ColumnID[7] + TotalRowCount +
                    '" class="form-control round text-right" value="' + ProductAmount + '" readonly>' +
                    '</td>';
                html += '<td width="5%">' +
                    '<a href="javascript:void(0)" class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center" onclick="btnRemoveRow(' + TotalRowCount + ');">' +
                    ' <i class="icon fa fa-times align-items-center"></i>' + '</a>' +'</td>';

                html += '</tr>';

                // $('#' + TableID).append(html);
                $('#' + TableID + ' tbody').find('tr:first').after(html);

                $('#' + ColumnID[0] + 0).val('');
                $('#' + ColumnID[0] + 0).trigger('change');
                $('#' + ColumnID[3] + 0).val(0);
                $('#' + ColumnID[6] + 0).val(0);
                $('#' + ColumnID[7] + 0).val(0);
                $('#' + ColumnID[3] + 0).prop('readonly', true);
            }

            fnDisableUnchangeableFields();

        }
        else {

            if ($('#' + ColumnID[0] + 0).val() == '') {
                swal({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please select product!',
                });
            }


        }
    }
    /* Add row End */

    /* Remove row Start */
    function btnRemoveRow(RemoveID) {

        $(RemoveID).closest('tr').remove();
        fnTotalQuantity();
        fnTotalAmount();
    }
    /* Remove row End */


    function fnProductLoad() {
        var categoryID = $('#prod_cat_id').val();

        var firstRowFirstColId = $('#billTable tbody tr td:first-child select').attr('id');

        $.ajax({
            method: "GET",
            url: "{{url('/ajaxProductLBills')}}",
            dataType: "text",
            data: {
                categoryID: categoryID
            },
            success: function(data) {
                if (data) {

                    $('#product_id_0')
                        .find('option')
                        .remove()
                        .end()
                        .append(data);
                }
            }
        });
    }

    $('#product_id_0').change(function() {

        if ($(this).val() != '') {
            $('#product_quantity_0').prop('readonly', false);
            $('#product_serial_0').prop('readonly', false);

            var productType = $(this).find("option:selected").attr('type');
            $('#product_type_0').val(productType);

            var selProdCostPrice = $(this).find("option:selected").attr('pcprice');
            $('#unit_sale_price_0').val(selProdCostPrice);

            var selProdSalePrice = $(this).find("option:selected").attr('pcprice');
            $('#unit_sale_price_0').val(selProdSalePrice);

            fnTtlProductPrice(0);
        } else {
            $('#product_quantity_0').prop('readonly', true);
            $('#product_serial_0').prop('readonly', true);
        }
    });

    function fnGenBillNo(BranchId)
    {
        if (BranchId != '') {
            $.ajax({
                method: "GET",
                url: "{{ url('/ajaxGBillForBill') }}",
                dataType: "text",
                data: {BranchId: BranchId},
                success: function (data) {
                    if (data) {
                        $('#bill_no').val(data);
                    }
                }
            });
        }
    }

    function fnAjaxCheckStock(){

        var BranchId = $('#branch_id').val();
        var ProductId = $('#product_id_0').val();

        if (BranchId != '' && ProductId != '') {

            $.ajax({
                method: "GET",
                url: "{{ url('/ajaxCheckStock') }}",
                dataType: "text",
                data: {
                    BranchId: BranchId,
                    ProductId: ProductId
                },
                success: function(data) {
                    if (data) {
                        // console.log(data);

                        // for stock check and update
                        /*
                        set stock_quantity_ id
                        set product_id_  id in full page
                        */
                        var stock = data ;

                        var TotalRowID =  $('#TotalRowID').val();
                        var row ;
                        // console.log(TotalRowID);
                        var totaladdedqnt = 0 ;
                        // if row id zero
                        for (row = 1; row <= TotalRowID; row++) {
                            //console.log($('#product_id_'+row).val());
                            if ( $('#product_id_'+row).val() == $('#product_id_0').val()){
                                // console.log($('#stock_quantity_'+ row).val());
                                stock = Number($('#stock_quantity_'+ row).val());
                                var GivenQnt = Number($('#product_quantity_'+ row).val());
                                totaladdedqnt+= GivenQnt;
                            }
                        }
                        stock -=totaladdedqnt;

                        $('#current_stock').html(stock);
                        $('#stock_quantity_0').val(stock);

                    }
                }
            });
        }
    }

    function fnCheckQuantity(Row) {

        var StockQuantity = Number($('#stock_quantity_'+ Row).val());
        var TypeQuantity = Number($('#product_quantity_'+ Row).val());


        var chkFlag = true;

        if (StockQuantity === 0) {

            swal({
                icon: 'error',
                title: 'Error',
                text: 'Empty Stock !! Please try next time.',
            });

        } else if(StockQuantity < TypeQuantity) {

            swal({
                icon: 'error',
                title: 'Error',
                text: 'Stock must be less than ' + StockQuantity,
            });
            $('#product_quantity_'+ Row).val(StockQuantity);
            fnTotalQuantity();
            fnTtlProductPrice(Row);
        }
    }

    function fnTotalQuantity() {

        var totalQtn = 0;
        $('.clsQuantity').each(function() {
            totalQtn = Number(totalQtn) + Number($(this).val());
        });

        $('#total_quantity').val(totalQtn);
        // $('#total_qnty').val(totalQtn);
        $('#tdTotalQuantity').html(totalQtn);
    }


    function fnTtlProductPrice(Row) {

        var ProductQtn = $('#product_quantity_' + Row).val();
        var ProductPrice = $('#unit_sale_price_' + Row).val();
        var TotalProductPrice = (Number(ProductQtn) * Number(ProductPrice));
        $('#product_sales_price_' + Row).val(TotalProductPrice.toFixed(2));
        fnTotalAmount();
    }

    function fnTotalAmount() {

        var totalAmt = 0;
        $('.ttlAmountCls').each(function() {
            totalAmt = Number(totalAmt) + Number($(this).val());
        });

        $('#tdTotalAmount').html(totalAmt.toFixed(2));
        //-------------------------- Total Amount
        $('#total_amount').val(totalAmt.toFixed(2));
        //--------------------------- T/A After Discount
        $('#ta_after_discount').val(totalAmt.toFixed(2));
        
        //---------------------------- Gross Total
        $('#gross_total').val(totalAmt.toFixed(2));

        //paid amount
        // $('#paid_amount').val(totalAmt.toFixed(2));
    }

    function fnCalDiscount(discountVal) {

        var TotalAmount = $('#total_amount').val();
        var TAAfterDiscount = (Number(TotalAmount) - ((Number(TotalAmount) * Number(discountVal)) / 100));
        //set the discount amount into discount_amount fields
        $('#discount_amount').val(((Number(TotalAmount) * Number(discountVal)) / 100));

        $('#ta_after_discount').val(TAAfterDiscount.toFixed(2));
        
        //---------------------------- Gross Total
        $('#gross_total').val(TAAfterDiscount.toFixed(2));
        

        //paid amount
        // $('#paid_amount').val(TAAfterDiscount.toFixed(2));

        // //-----------------------------Due amount
        // $('#due_amount').val(TAAfterDiscount);

        //-----------------------------calculate vat amount
        fnCalVat($('#vat_rate').val());
        //-----------------------------calculate Due amount
        // fnCalDue($('#paid_amount').val());
    }

    function fnCalVat(vatVal) {

        var TAAfterDiscount = $('#ta_after_discount').val();
        var GrossAmount = (Number(TAAfterDiscount) + ((Number(TAAfterDiscount) * Number(vatVal)) / 100));
        $('#vat_amount').val(((Number(TAAfterDiscount) * Number(vatVal)) / 100));

        $('#gross_total').val(GrossAmount.toFixed(2));
        

        //paid amount
        // $('#paid_amount').val(GrossAmount.toFixed(2));

        //-----------------------------calculate Due amount
        // fnCalDue($('#paid_amount').val());
    }

    // function fnCalDue(paidAmount) {
    //     var GrossAmount = $('#gross_total').val();
    //     var DueAmount = (Number(GrossAmount) - Number(paidAmount));
    //     $('#due_amount').val(DueAmount.toFixed(2));
    // }

    function fnCustomerMobileLoad() {
        var CustomerID = $('#customer_id').val();

        $.ajax({
            method: "GET",
            url: "{{url('/ajaxCustomerMobileLoad')}}",
            dataType: "text",
            data: {
                CustomerID: CustomerID,
            },
            success: function(data) {
                if (data) {
                    console.log(data);
                    $('#customer_mobile').val(data);
                }
            }
        });
    }


    function fnCustomerNIDLoad() {
        var CustomerID = $('#customer_id').val();
        $.ajax({
            method: "GET",
            url: "{{url('/ajaxCustomerNIDLoad')}}",
            dataType: "text",
            data: {
                CustomerID: CustomerID,
            },
            success: function(data) {
                if (data) {
                    $('#customer_nid').val(data);
                }
            }
        });
    }

    function fnCustDataLoad() {

        var MobileNo = $('#customer_id').find('option:selected').attr('mobile_no');
        var NidNo = $('#customer_id').find('option:selected').attr('nid_no');

        if (MobileNo !== $('#customer_mobile_ajx').val()) {
            $('#customer_mobile_ajx').val(MobileNo);
            $('#customer_mobile_ajx').trigger('change');
        }

        if (NidNo !== $('#customer_nid').val()) {
            $('#customer_nid').val(NidNo);
            $('#customer_nid').trigger('change');
        }

        return false;
    }

    function fnCustNIDataLoad() {

        var custId = $('#customer_mobile_ajx').find('option:selected').attr('cust_id');
        var NidNo = $('#customer_mobile_ajx').find('option:selected').attr('nid_no');

        if (custId !== $('#customer_id').val()) {
            $('#customer_id').val(custId);
            $('#customer_id').trigger('change');
        }

        if (NidNo !== $('#customer_nid').val()) {
            $('#customer_nid').val(NidNo);
            $('#customer_nid').trigger('change');
        }

        return false;
    }

    function fnCustMDataLoad() {

        var custId = $('#customer_nid').find('option:selected').attr('cust_id');
        var MobileNo = $('#customer_nid').find('option:selected').attr('mobile_no');

        if (custId !== $('#customer_id').val()) {
            $('#customer_id').val(custId);
            $('#customer_id').trigger('change');
        }

        if (MobileNo !== $('#customer_mobile_ajx').val()) {
            $('#customer_mobile_ajx').val(MobileNo);
            $('#customer_mobile_ajx').trigger('change');
        }

        return false;
    }

    $('#submitButtonforSales').on('click', function(event) {
        event.preventDefault();

        $(this).prop('disabled', true);

        var branchID = $('#branch_id').val();

        if ($('#customer_id').val() != '' && $('#total_quantity').val() > 0 && $('#product_id_0').val() == '') {
            $('#bill_form').submit();
            $(this).prop('disabled', false);
        } else {
            $(this).prop('disabled', false);
            if ($('#customer_id').val() == '') {
                swal({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please Select Customer !!',
                });
            }
            else if ($('#total_quantity').val() <= 0) {
                swal({
                    icon: 'error',
                    title: 'Error',
                    text: 'Total Quantity must be gratter than zero !!',
                });
            } else {
                swal({
                    icon: 'error',
                    title: 'Error',
                    text: 'Product Entry must be empty!!',
                });
            }
        }
    
    });

    // Disable Button for multiple click
    // $('#bill_form').submit(function (event) {
    //     $(this).find(':submit').attr('disabled', 'disabled');
    // });


</script>


@endsection
