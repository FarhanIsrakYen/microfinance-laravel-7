@extends('Layouts.erp_master_full_width')
@section('content')

<?php 
use App\Services\CommonService as Common;
?>
<!-- Page -->
<form method="POST" data-toggle="validator" novalidate="true" id="sale_ret_form" autocomplete="off">
    @csrf
    <div class="row">
        <!--Form Left-->
        <div class="col-lg-5">
            <div class="panel">
                <div class="panel-body">
                    <h5 class="text-center">Sales Information</h5>
                    <br>
                    <div class="form-group">
                        <div class="form-row align-items-center">
                            <div class="col-lg-4">
                                <label class="input-title RequiredStar">Sales Bill No</label>
                            </div>
                            <div class="col-lg-7">
                                <select name="sales_bill_no" id="sales_bill_no"
                                    class="form-control round clsProductSelect"
                                        required
                                    data-error="Please enter Sales Bill No.">
                                    <option value="">Select Sale Bill</option>

                                    @foreach($SaleM as $info)
                                    <option value="{{ $info->sales_bill_no }}" branchid="{{$info->branch_id}}">
                                        {{ $info->sales_bill_no  }}
                                    </option>
                                    @endforeach
                                </select>

                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>
                    </div>
                    <!-- {{-- <input type="hidden" id="return_date" name="return_date" value="{{ date('d-m-Y') }}"> --}} -->

                    <input type="hidden" name="company_id" id="company_id" value="">
                    <input type="hidden" name="branch_id" id="branch_id" value="">
                    <!-- {{-- bill no  --}} -->

                    <input type="hidden" name="sales_type" id="sales_type" value="">
                    <input type="hidden" name="sales_id" id="sales_id" value="">


                    <input type="hidden" name="total_sales_payable_amount" id="total_sales_payable_amount"
                        value="">

                    <input type="hidden" name="sales_payment_system_id" id="sales_payment_system_id" value="">
                    <input type="hidden" name="payment_month" id="payment_month" value="">


                    <input type="hidden" name="fiscal_year_id" id="fiscal_year_id" value="">

                    <input type="hidden" name="customer_id" id="customer_id" value="">
                    <input type="hidden" name="customer_barcode" id="customer_barcode" value="">
                    <input type="hidden" name="employee_id" id="employee_id" value="">

                    
                    <div class="form-group toggleClass" style="display: none; margin-bottom: 0.7rem">
                        <div class="form-row align-items-center">
                            <div class="col-lg-4">
                                <label class="input-title">Sale Date</label>
                            </div>
                            <div class="col-lg-7">
                                <!-- {{-- <input type="text"  name="sales_date" id="sales_date" value=""> --}} -->
                                <input type="hidden" class="form-control round" name="sales_date" id="sales_date"
                                    readonly>
                                <label id="sales_date_copy" class="text-black"></label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group toggleClass" style="display: none; margin-bottom: 0.7rem">
                        <div class="form-row align-items-center">
                            <div class="col-lg-4">
                                <label class="input-title">Paid Amount</label>
                            </div>
                            <div class="col-lg-7">
                                <input type="hidden" class="form-control round text-right" name="sales_paid_amount"
                                    id="sales_paid_amount" readonly>
                                <label id="sales_paid_amount_copy" class="text-black"></label>
                            </div>
                        </div>
                    </div>
                    <!-- {{-- <input type="text"  name="sales_paid_amount" id="sales_paid_amount" value="">
                    <input type="text"  name="sales_due_amount" id="sales_due_amount" value=""> --}} -->

                    <div class="form-group toggleClass" style="display: none">
                        <div class="form-row align-items-center">
                            <div class="col-lg-4">
                                <label class="input-title">Due Amount</label>
                            </div>
                            <div class="col-lg-7">
                                <input type="hidden" class="form-control round text-right" name="sales_due_amount"
                                    id="sales_due_amount" readonly>
                                <label id="sales_due_amount_copy" class="text-black"></label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group toggleClass" style="display: none">
                        <div class="row">
                            <div class="table-responsive">

                                <table class="table w-full table-hover table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Quantity</th>
                                            <th>Return Quantity</th>
                                            <th>Unit Price</th>
                                            <th>Total Price</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodydetails">
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--Form Right-->
        
        <div class="col-lg-7 toggleClass" style="display: none;">
            <div class="panel">
                <div class="panel-body">
                    <h5 class="text-center">Sales Return Information</h5>
                    <br>
                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title">Return Date</label>
                        <div class="col-lg-7 input-group">
                            <input type="hidden" id="return_date_h" name="return_date"
                                value="{{ Common::systemCurrentDate() }}">
                            <label id="return_date" class="text-black">{{ Common::systemCurrentDate() }}</label>

                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>

                    <div class="form-group">
                        <div class="form-row align-items-center">
                            <div class="col-lg-4">
                                <label class="input-title">Sales Return Bill NO</label>
                            </div>
                            <div class="col-lg-7">
                                <input type="hidden" class="form-control round" name="return_bill_no" id="return_bill_no" readonly>
                                <label id="return_bill_no_copy" class="text-black"></label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-row align-items-center">
                            <div class="col-lg-4">
                                <label class="input-title">Return Reason</label>
                            </div>
                            <div class="col-lg-7">
                                <textarea class="form-control round" id="return_reason" name="return_reason"
                                    rows="2" placeholder="Enter Description"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <table class="table table-hover table-striped table-bordered w-full text-center" id="salesReturnTable">
                                <thead class="scrollHead">
                                    <tr>
                                        <th width="50%" class="RequiredStar">Product Name</th>
                                        <th width="15%" class="RequiredStar">Quantity</th>
                                        <th width="15%">Sale Price</th>
                                        <th width="16%">Total</th>
                                        <th width="4%"></th>
                                    </tr>
                                </thead>
                                <tbody class="scrollBody">
                                    <?php
                                        $i = 0;
                                        $TableID = "salesReturnTable";

                                        $ColumnName = "product_id_arr[]&product_quantity_arr[]&product_sales_price_arr[]&total_sales_price_arr[]";
                                        $ColumnID = "product_id_&product_quantity_&product_sales_price_&total_sales_price_&deleteRow_";

                                        // $ColumnName = "product_id_arr[]&pro_barcode_arr[]&sys_barcode_arr[]&product_quantity_arr[]&prod_cost_arr[]&received_quantity_arr[]&unit_cost_price_arr[]&total_cost_price_arr[]";
                                        // $ColumnID = "product_id_&pro_barcode_&sys_barcode_&product_quantity_&ordered_quantity_&prod_cost_&unit_cost_price_&total_cost_price_&deleteRow_";
                                    ?>

                                    <tr>
                                        <td width="50%" class="barcodeWidth">
                                            <select id="product_id_0"class="form-control round clsProductSelect" style="width: 100%">
                                                <option value="">Select Product</option>
                                            </select>
                                        </td>

                                        <td width="15%">
                                            <input type="hidden" id="stock_quantity_0" value="0">

                                            <input type="number" id="product_quantity_0"
                                                class="form-control round clsQuantity text-center"
                                                placeholder="Enter Quantity" value="0"
                                                onkeyup="fnCheckQuantity(0); fnTotalQuantity(); fnTtlProductPrice(0);"
                                                min="1" readonly>
                                        </td>

                                        <td width="15%">
                                            <input type="number" id="product_sales_price_0"
                                                class="form-control round text-right" value="0" required min="1" readonly>
                                        </td>

                                        <td width="16%">
                                            <input type="number" id="total_sales_price_0"
                                                class="form-control round ttlAmountCls text-right" value="0" min="1"readonly>
                                        </td>

                                        <td width="4%">
                                            <a href="javascript:void(0);"
                                                class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center clsAddNewRow"
                                                onclick="btnAddNewRow('<?= $TableID ?>', '<?= $ColumnName ?>', '<?= $ColumnID ?>', 'TotalRowID');">
                                                <i class="icon wb-plus  align-items-center"></i>
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="scrollFooter">
                                    <tr>
                                        <td width="50%" style="text-align:right;">
                                            <h5>TOTAL</h5>
                                            <input type="hidden" name="total_quantity" id="total_quantity" value="0"
                                                min="1">
                                        </td>
                                        <td width="15%" id="tdTotalQuantity" style="font-weight: bold;">0</td>
                                        <td width="15%">
                                            <input type="hidden" name="total_amount" id="total_amount" value="0" min="1">
                                            <input type="hidden" class="form-control round text-center" name="total_return_quantity"
                                                id="total_return_quantity" readonly>
                                            <input type="hidden" class="form-control round text-right" name="total_return_amount"
                                                id="total_return_amount" placeholder="0">
                                        </td>
                                        <td width="16%" id="tdTotalAmount" class="text-right" style="font-weight: bold; ">0.00</td>
                                        <td width="4%"></td>
                                    </tr>
                                </tfoot>
                            </table>

                            <!-- Row_Count is temporary variable for using row add and delete-->
                            <input type="hidden" id="TotalRowID" value="0" />

                            
                        </div>
                    </div>
                    <!-- <div class="form-group">
                        <div class="form-row align-items-center">
                            <div class="col-lg-4">
                                <label class="input-title">Sales Return Qty</label>
                            </div>
                            <div class="col-lg-7">
                                <label id="total_return_quantity" class="text-black"></label>
                            </div>
                        </div>
                    </div> -->

                    <!-- {{-- <input type="text"  name="total_return_quantity" id="total_return_quantity" value="">
                    <input type="text"  name="total_return_amount" id="total_return_amount" value="">  --}} -->
                    <!-- {{-- <input type="text"  name="payable_return_amount" id="payable_return_amount" value=""> --}} -->

                    <!-- <div class="form-group">
                        <div class="form-row align-items-center">
                            <div class="col-lg-4">
                                <label class="input-title"> Total Return Amount</label>
                            </div>
                            <div class="col-lg-7">
                                <input type="text" class="form-control round text-right" name="total_return_amount"
                                    id="total_return_amount" placeholder="0">
                            </div>
                        </div>
                    </div> -->
                    <div class="form-group">
                        <div class="form-row align-items-center">
                            <div class="col-lg-4">
                                <label class="input-title RequiredStar"> Payable Return Amount</label>
                            </div>
                            <div class="col-lg-7">
                                <input type="text" class="form-control round text-right" name="payable_return_amount"
                                    id="payable_return_amount" placeholder="0" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- <div class="form-group">
                        <div class="form-row align-items-center">
                            <div class="col-lg-4">
                                <label id="total_return_amount" class="input-title">Return Description</label>
                            </div>
                            <div class="col-lg-7">
                                <textarea class="form-control round" id="return_description"
                                    name="return_description" rows="2"
                                    placeholder="Enter Description"></textarea>
                            </div>
                        </div>
                    </div> -->
                    <div class="form-group">
                        <div class="form-row align-items-center">
                            <div class="col-lg-12 text-center">
                                <div class="example example-buttons">
                                    <a href="javascript:void(0)" onclick="goBack();"
                                    class="btn btn-default btn-round d-print-none">Back</a>
                                    <button type="submit" class="btn btn-primary btn-round"
                                        id="submitButtonforSaleReturn">Save</button></a>
                                    <!-- <a href="#"><button type="button" class="btn btn-warning btn-round">Next</button></a> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<!--End Page -->

<style type="text/css">
    .text-black{
        color: #000;
    }
</style>

<script>
    // <script type="text/javascript">
    $(document).ready(function () {
        $('.clsProductSelect').select2();

        $('#sales_bill_no').change(function () {
            var SalesBillNo = $('#sales_bill_no').val();
            if (SalesBillNo != '') {

                $.ajax({
                    method: "GET",
                    url: "{{ url('/ajaxSalebillDetails')}}",
                    // url: "./../../ajaxSalebillDetails",
                    dataType: "json",
                    data: {
                        SalesBillNo: SalesBillNo
                    },
                    success: function (data) {
                        if (data) {
                            $('#SaleDate').val(data.master.sales_date);
                            $('#PaidAmount').val(data.master.paid_amount);
                            $('#DueAmount').val(data.master.due_amount);


                            $('#tbodydetails').html(data.tbody);
                            // $('#salesReturnTable').empty();
                            $('#salesReturnTable tbody tr:not(:first)').remove();
                            $("#tbodydetails tr td:nth-child(2), td:nth-child(3)").css("text-align", "center");
                            $("#tbodydetails tr td:nth-child(4), td:nth-child(5)").css("text-align", "right");

                            // if (data.remainqnt > 0) {
                            $('#product_id_0')
                                .find('option')
                                .remove()
                                .end()
                                .append(data.option);
                            // }

                            $('#company_id').val(data.master.company_id);

                            $('#branch_id').val(data.master.branch_id);
                            $('#sales_type').val(data.master.sales_type);
                            $('#sales_id').val(data.master.id);
                            $('#customer_id').val(data.master.customer_id);
                            $('#employee_id').val(data.master.employee_id);
                            $('#sales_date').val(data.master.sales_date);
                            $('#sales_date_copy').html(data.master.sales_date);
                            $('#customer_barcode').val(data.master.customer_barcode);
                            $('#total_sales_payable_amount').val(data.master.total_payable_amount);
                            $('#sales_paid_amount').val(data.master.paid_amount);
                            $('#sales_paid_amount_copy').html(data.master.paid_amount);
                            $('#sales_due_amount').val(data.master.due_amount);
                            $('#sales_due_amount_copy').html(data.master.due_amount);
                            $('#sales_payment_system_id').val(data.master.payment_system_id);
                            $('#payment_month').val(data.master.payment_month);
                            $('#fiscal_year_id').val(data.master.fiscal_year_id);

                            fnGenBillNo(data.master.branch_id);
                            //data.details.forEach(SaperateItem);

                            $('.toggleClass').show('slow');
                        }
                    }
                });
            }

        });

    });

    $('#product_id_0').change(function () {

        if ($(this).val() != '') {
            $('#product_quantity_0').prop('readonly', false);

            var selProdCostPrice = $(this).find("option:selected").attr('pcprice');
            var selProdSalesPrice = $(this).find("option:selected").attr('psprice');

            var stockQnt = Number($(this).find("option:selected").attr('pquantity')) -
                Number($(this).find("option:selected").attr('retquantity'));

            $('#stock_quantity_0').val(stockQnt);

            $('#product_cost_price_0').val(selProdCostPrice);
            $('#product_sales_price_0').val(selProdSalesPrice);

            fnTtlProductPrice(0);
        } else {
            $('#product_quantity_0').prop('readonly', true);
            $('#product_cost_price_0').val(0);

            $('#product_sales_price_0').val(0);

            $('#stock_quantity_0').val(0);
        }


    });

    /* Add row Start */
    function btnAddNewRow(TableID, ColumnNameS, ColumnIDS, TotalRowID) {
        var ColumnName = ColumnNameS.split("&");
        var ColumnID = ColumnIDS.split("&");

        // $ColumnName = "product_id_arr[]&product_quantity_arr[]&product_sales_price_arr[]&total_sales_price_arr[]";
        // $ColumnID = "product_id_&product_quantity_&product_sales_price_&total_sales_price_&deleteRow_";


        if ($('#' + ColumnID[0] + 0).val() != '' && $('#product_quantity_0').val() > 0) {

            var TotalRowCount = $('#' + TotalRowID).val();

            var ProductQuantity = $('#' + ColumnID[1] + 0).val();

            // var stockqnt = $('#' + ColumnID[0] + 0).find("option:selected").attr('pquantity');

            var stockqnt = Number($('#' + ColumnID[0] + 0).find("option:selected").attr('pquantity')) - Number($('#' + ColumnID[0] + 0).find("option:selected").attr('retquantity'));
            var rowNumber = 0;
            var flag = false;

            for (var row = 1; row <= TotalRowCount; row++) {
                if ($('#product_id_' + row).val() == $('#product_id_0').val()) {
                    flag = true;
                    rowNumber = row;
                }
            }

            if (flag === true) {
                ProductQuantity = Number(ProductQuantity) + Number($('#product_quantity_' + rowNumber).val());

                //if no stock comment out stock check
                // stock check start
                var stock = stockqnt;

                if (stock >= ProductQuantity) {
                    $('#product_quantity_' + rowNumber).val(ProductQuantity);
                } else {

                    swal({
                        icon: 'error',
                        title: 'Error',
                        text: 'Quantity must be less than or equal ' + stock,
                    });

                    $('#product_quantity_' + rowNumber).val(stock);
                }
                // s

                fnTotalQuantity();
                fnTtlProductPrice(rowNumber);

                $('#' + ColumnID[0] + 0).val('');
                $('#' + ColumnID[0] + 0).trigger('change');
                $('#' + ColumnID[1] + 0).val(0);
                $('#' + ColumnID[2] + 0).val(0);
                $('#' + ColumnID[3] + 0).val(0);
                $('#' + ColumnID[1] + 0).prop('readonly', true);

            } else {
                TotalRowCount++;
                $('#' + TotalRowID).val(TotalRowCount);
                var ProductID = $('#' + ColumnID[0] + 0).val();
                var ProductName = $('#' + ColumnID[0] + 0).find("option:selected").attr('pname');
                var ProductBarcode = $('#' + ColumnID[0] + 0).find("option:selected").attr('pbarcode');
                var ProductSysBarcode = $('#' + ColumnID[0] + 0).find("option:selected").attr('sbarcode');
                var ProductCostPrice = $('#' + ColumnID[0] + 0).find("option:selected").attr('pcprice');
                var ProductSalePrice = $('#' + ColumnID[0] + 0).find("option:selected").attr('psprice');
                var Productqnt = $('#' + ColumnID[0] + 0).find("option:selected").attr('pquantity');
                var ProductQuantity = $('#' + ColumnID[1] + 0).val();
                var ProductAmount = $('#' + ColumnID[3] + 0).val();


                var html = '<tr>';

                html += '<td class="barcodeWidth" width="50%">';

                html += '<input type="hidden" id="product_id_' + TotalRowCount + '"  name="' + ColumnName[0] + '" value="' + ProductID + '">';
                html += '<input type="hidden" id="stock_quantity_' + TotalRowCount + '" value="' + stockqnt + '">';

                html += '<input type="text" class="form-control round" pquantity="' + Productqnt + '" id="' + ColumnID[0] +
                    TotalRowCount + '" value="' + ProductName + '" readonly>';
                html += '</td>';

                html += '<td width="15%">';
                // Input Feild For sys_barcode
                html += '<input type="hidden" name="product_barcode_arr[]"  " value="' + ProductBarcode + '">';

                // Input Feild For sys_barcode
                html += '<input type="hidden" name="product_system_barcode_arr[]" value="' + ProductSysBarcode + '">';

                html += '<input type="hidden" name="product_cost_price_arr[]" value="' + ProductCostPrice + '">';

                html += '<input type="number" name="' + ColumnName[1] + '" id="' + ColumnID[1] + TotalRowCount +
                    '" class="form-control round clsQuantity text-center" value="' + ProductQuantity + '" onkeyup="fnCheckQuantity(' +
                    TotalRowCount + ');fnTotalQuantity();fnTtlProductPrice(' + TotalRowCount + ');" required min="1">';
                html += '</td>';


                html += '<td width="15%">' +
                    '<input type="number" name="' + ColumnName[2] + '" id="' + ColumnID[2] + TotalRowCount +
                    '" class="form-control round text-right" value="' + ProductSalePrice + '" readonly min="1">' +
                    '</td>';

                html += '<td width="16%">' +
                    '<input type="number" name="' + ColumnName[3] + '" id="' + ColumnID[3] + TotalRowCount +
                    '" class="form-control round ttlAmountCls text-right" value="' + ProductAmount + '" readonly>' +
                    '</td>';

                html += '<td width="4%">' +
                    '<a href="javascript:void(0)" class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center" onclick="btnRemoveRow(this);">' +
                    ' <i class="icon fa fa-times align-items-center"></i>' +
                    '</a>' +
                    '</td>';
                html += '</tr>';

                $('#' + TableID).append(html);

                $('#' + ColumnID[0] + 0).val('');
                $('#' + ColumnID[0] + 0).trigger('change');
                $('#' + ColumnID[1] + 0).val(0);
                $('#' + ColumnID[2] + 0).val(0);
                $('#' + ColumnID[3] + 0).val(0);
                $('#' + ColumnID[1] + 0).prop('readonly', true);

                fnTotalQuantity();
                fnTtlProductPrice(TotalRowCount);
            }

        } else {

            if ($('#' + ColumnID[0] + 0).val() == '') {
                swal({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please select product!',
                });
            } else {
                swal({
                    icon: 'error',
                    title: 'Error',
                    text: 'Quantity must be greater than 0!',
                });
            }

        }
    }
    /* Add
    /* Add row End */

    /* Remove row Start */
    function btnRemoveRow(RemoveID) {

        $(RemoveID).closest('tr').remove();
        fnTotalQuantity();
        fnTotalAmount();
    }
    /* Remove row End */

    function fnTtlProductPrice(Row) {

        var ProductQtn = $('#product_quantity_' + Row).val();
        var ProductPrice = $('#product_sales_price_' + Row).val();

        var TotalProductPrice = (Number(ProductQtn) * Number(ProductPrice));
        $('#total_sales_price_' + Row).val(TotalProductPrice);

        fnTotalAmount();
    }


    function fnGenBillNo(BranchId) {
        if (BranchId != '') {

            $.ajax({
                method: "GET",
                url: "{{ url('/ajaxGBillSR') }}",
                // url: "./../../ajaxGBillSR",
                dataType: "text",
                data: {
                    BranchId: BranchId
                },
                success: function (data) {
                    if (data) {
                        // console.log('testbrancj');
                        $('#return_bill_no').val(data);
                        $('#return_bill_no_copy').html(data);
                    }
                }
            });
        }
    }

    function fnTotalQuantity() {

        // var qnt = $(this).find("option:selected").attr('pquantity');

        var totalQtn = 0;
        $('.clsQuantity').each(function () {
            totalQtn = Number(totalQtn) + Number($(this).val());
        });
        $('#total_quantity').val(totalQtn);
        $('#product_quantity').val(totalQtn);

        $('#total_return_quantity').val(totalQtn);
        $('#tdTotalQuantity').html(totalQtn);
    }

    function fnCheckQuantity(Row) {
        var chkFlag = true;

        var qnt = Number($('#stock_quantity_' + Row).val());

        var TotalRowID = $('#TotalRowID').val();
        var row;
        var totaladdedqnt = 0;

        for (row = 1; row <= TotalRowID; row++) {

            if ($('#product_id_' + row).val() == $('#product_id_0').val()) {
                totaladdedqnt += Number($('#product_quantity_' + row).val());
            }
        }
        qnt -= totaladdedqnt;

        if (Row === 0) {

            if (Number($('#product_quantity_0').val()) > qnt) {
                chkFlag = false;
                $('#product_quantity_0').val(qnt);
            }

        } else {
            if (Number($('#product_quantity_' + Row).val()) > qnt) {
                chkFlag = false;
                $('#product_quantity_' + Row).val(qnt);
            }

        }

        if (chkFlag == false) {
            swal({
                icon: 'error',
                title: 'Error',
                text: 'Quantity Must be Less than Sold !!',
            });
        }
    }

    function fnTotalAmount() {

        var totalAmt = 0;
        $('.ttlAmountCls').each(function () {
            totalAmt = Number(totalAmt) + Number($(this).val());
        });
        $('#tdTotalAmount').html(totalAmt);
        //-------------------------- Total Amount
        $('#total_amount').val(totalAmt);
        $('#total_return_amount').val(totalAmt);

        var due = $('#sales_due_amount').val();

        var payable = totalAmt - due;

        $('#payable_return_amount').val(payable);

    }

    $('#submitButtonforSaleReturn').on('click', function (event) {
        event.preventDefault();

        if ($('#total_quantity').val() > 0 && $('#product_id_0').val() == '') {

            $('#sale_ret_form').submit();
        } else {
            if ($('#total_quantity').val() <= 0) {
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

    $('form').submit(function (event) {
        $(this).find(':submit').attr('disabled', 'disabled');
    });

</script>

{{-- <script src="{{ asset('../resources/views/POS/SaleReturn/SaleReturn.js') }}"></script> --}}

@endsection
