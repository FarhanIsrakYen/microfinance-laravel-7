@extends('Layouts.erp_master_full_width')
@section('content')
<!-- Page -->
<form method="POST" data-toggle="validator" novalidate="true" id="sale_ret_form" autocomplete="off">
    @csrf
    <div class="row">
        <!--Form Left-->
        <div class="col-lg-5">
            <div class="panel">
                <div class="panel-body">
                    <h5 class="text-center">Product Uses Information</h5>
                    <br>
                    <div class="form-group">
                        <div class="form-row align-items-center">
                            <div class="col-lg-4">
                                <label class="input-title">Uses Bill No</label>
                            </div>
                            <div class="col-lg-7">
                                <input type="hidden" name="uses_bill_no" id="uses_bill_no" class="form-control round"
                                value="{{ $SaleRM->uses_bill_no }}">
                                <label class="text-black">{{ $SaleRM->uses_bill_no }}</label>
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>
                    </div>
                    {{-- <input type="hidden" id="return_date" name="return_date" value="{{ date('d-m-Y') }}"> --}}

                    <input type="hidden" name="company_id" id="company_id" value="{{ $SaleRM->company_id }}">
                    <input type="hidden" name="branch_id" id="branch_id" value="{{ $SaleRM->branch_id }}">
                    {{-- bill no  --}}

                    <input type="hidden" name="sales_type" id="sales_type" value="{{ $SaleRM->sales_type }}">
                    <input type="hidden" name="sales_id" id="sales_id" value="{{ $SaleRM->sales_id }}">

                    {{-- <input type="hidden"  name="uses_date" id="uses_date" > --}}

                    <input type="hidden" name="total_sales_payable_amount" id="total_sales_payable_amount"
                        value="{{ $SaleRM->total_sales_payable_amount }}">

                    <input type="hidden" name="sales_payment_system_id" id="sales_payment_system_id"
                        value="{{ $SaleRM->sales_payment_system_id }}">
                    <input type="hidden" name="payment_month" id="payment_month"
                        value="{{ $SaleRM->payment_month }}">


                    <input type="hidden" name="fiscal_year_id" id="fiscal_year_id"
                        value="{{ $SaleRM->fiscal_year_id }}">

                    <input type="hidden" name="customer_id" id="customer_id" value="{{ $SaleRM->customer_id }}">
                    <input type="hidden" name="customer_barcode" id="customer_barcode"
                        value="{{ $SaleRM->customer_barcode }}">
                    <input type="hidden" name="employee_id" id="employee_id" value="{{ $SaleRM->employee_id }}">

                    <div class="form-group" style="margin-bottom: 0.7rem">
                        <div class="form-row align-items-center">
                            <div class="col-lg-4">
                                <label class="input-title">Uses Date</label>
                            </div>
                            <div class="col-lg-7">
                                <input type="hidden" class="form-control round" name="uses_date" id="uses_date"
                                    value="{{ $SaleRM->uses_date }}" readonly>
                                <label id="uses_date_copy" class="text-black">{{ $SaleRM->uses_date }}</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="table-responsive">
                                <table class="table w-full table-hover table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Quantity</th>
                                            <th>Return Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodydetails"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>

        <!--Form Right-->
        <div class="col-lg-7">
            <div class="panel">
                <div class="panel-body">
                    <h5 class="text-center">Uses Return Information</h4>
                    <br>
                    <div class="form-row form-group align-items-center">
                        <div class="col-lg-4">
                            <label class="input-title">Return Date</label>
                        </div>
                        <div class="col-lg-7 input-group">
                            <?php
                                $Date = new DateTime($SaleRM->return_date);
                                $Date = (!empty($Date)) ? $Date->
                                                    format('d-m-Y') : date('d-m-Y');
                            ?>
                            <input type="hidden" id="return_date" name="return_date"
                                class="form-control round" readonly value="{{ $Date }}">
                            <label class="text-black">{{ $Date }}</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-row align-items-center">
                            <div class="col-lg-4">
                                <label for="SalesReturnInfo" class="input-title">Uses Return Bill NO</label>
                            </div>
                            <div class="col-lg-7">
                                <input type="hidden" class="form-control round" name="return_bill_no"
                                    id="return_bill_no" value="{{ $SaleRM->return_bill_no }}" readonly>
                                <label class="text-black">{{ $SaleRM->return_bill_no }}</label>
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
                                        <th width="60%" class="RequiredStar">Product Name</th>
                                        <th width="36%" class="RequiredStar">Quantity</th>
                                        <th width="4%"></th>
                                    </tr>
                                </thead>
                                <tbody class="scrollBody">
                                    <?php
                                        $i = 0;
                                        $TableID = "salesReturnTable";

                                        $ColumnName = "product_id_arr[]&pro_barcode_arr[]&sys_barcode_arr[]&product_quantity_arr[]&prod_cost_arr[]&received_quantity_arr[]&unit_cost_price_arr[]&total_cost_price_arr[]";
                                        $ColumnID = "product_id_&pro_barcode_&sys_barcode_&product_quantity_&ordered_quantity_&prod_cost_&unit_cost_price_&total_cost_price_&deleteRow_";
                                    ?>
                                    <tr>
                                        <td width="60%" class="barcodeWidth">
                                            <select id="product_id_0" class="form-control round clsProductSelect" style="width: 100%">
                                                <option value="">Select Product</option>
                                            </select>
                                        </td>

                                        <td width="36%">
                                            <!-- Input for System barcode  -->
                                            <!-- <input type="hidden" name="sys_barcode_arr[]" id="sys_barcode_0"> -->

                                            <!-- Input for Product Name  -->
                                            <!--  <input type="hidden"  name="product_name_arr[]" id="product_name_0">  -->
                                            <input type="hidden" id="stock_quantity_0" value="0">

                                            <input type="number" id="product_quantity_0"
                                                class="form-control round clsQuantity text-center"
                                                placeholder="Enter Quantity" value="0"
                                                onkeyup="fnCheckQuantity(0); fnTotalQuantity(); fnTtlProductPrice(0);"
                                                required min="1" readonly>
                                        </td>

                                        <td width="4%">
                                            <a href="javascript:void(0);"
                                                class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center clsAddNewRow"
                                                onclick="btnAddNewRow('<?= $TableID ?>', '<?= $ColumnName ?>', '<?= $ColumnID ?>', 'TotalRowID');">
                                                <i class="icon wb-plus  align-items-center"></i>
                                            </a>
                                        </td>
                                    </tr>

                                    @if(count($SaleRD) > 0)
                                    @foreach($SaleRD as $Data)
                                    <?php $i++; ?>
                                    <tr>
                                        <td width="60%" class="barcodeWidth">
                                            <input type="hidden" name="product_id_arr[]"
                                                id="product_id_{{ $i }}" value="{{ $Data->product_id }}">

                                            <input type="text" class="form-control round"
                                                value="{{ $Data->product['product_name'] }}" readonly>
                                        </td>

                                        <td width="36%">
                                            <input type="hidden" name="pro_barcode_arr[]"
                                                id="pro_barcode_{{ $i }}" class="form-control round "
                                                value="{{ $Data->product_barcode }}">
                                            <input type="hidden" name="sys_barcode_arr[]"
                                                id="sys_barcode_{{ $i }}" class="form-control round "
                                                value="{{ $Data->product_system_barcode }}">
                                            <input type="hidden" name="prod_cost_arr[]" id="prod_cost_{{ $i }}"
                                                class="form-control round "
                                                value="{{ $Data->product_cost_price }}">

                                            <input type="hidden" id="stock_quantity_0" value="0">

                                            <input type="number" name="product_quantity_arr[]"
                                                id="product_quantity_{{ $i }}"
                                                class="form-control round clsQuantity text-center"
                                                placeholder="Enter Quantity"
                                                value="{{ $Data->product_quantity }}"
                                                onkeyup="fnTotalQuantity();fnCheckQuantity({{$i}}); fnTtlProductPrice(0);"
                                                required min="1" readonly>
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
                                        <td width="60%" style="text-align:right;">
                                            <h5>TOTAL</h5>
                                            <input type="hidden" name="total_quantity" id="total_quantity" value="0"
                                                min="1">
                                        </td>
                                        <td width="36%" id="tdTotalQuantity" style="font-weight: bold;">0</td>
                                        <td width="4%"></td>
                                    </tr>
                                </tfoot>
                            </table>

                            <!-- Row_Count is temporary variable for using row add and delete-->
                            <input type="hidden" id="TotalRowID" value="{{ $i}}" />

                        </div>
                    </div>

                   <input type="hidden"  name="total_return_quantity" id="total_return_quantity" value="">
                    
                    <div class="form-group">
                        <div class="form-row align-items-center">
                            <div class="col-lg-12 text-center">
                                <div class="example example-buttons">
                                    <a href="javascript:void(0)" onclick="goBack();"
                                    class="btn btn-default btn-round d-print-none">Back</a>
                                    <button type="submit" class="btn btn-primary btn-round"
                                        id="submitButtonforSaleReturn">Update</button></a>
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
    .text-black {
        color: #000;
    }
</style>

<script>
$(document).ready(function() {
    $('.clsProductSelect').select2();
    var usesBillNo = $('#uses_bill_no').val();
    var returnBillNo = $('#return_bill_no').val();

    $('#submitButtonforSaleReturn').removeClass('disabled');
    //  console.log(usesBillNo);
    if (usesBillNo != '') {

        $.ajax({
            method: "GET",
            url: "{{url('/ajaxUsebillDetails')}}",
            dataType: "json",
            data: {
                usesBillNo: usesBillNo,
                returnBillNo: returnBillNo
            },
            success: function(data) {
                if (data) {

                    $('#tbodydetails').html(data.tbody);
                    $( "#tbodydetails tr td:nth-child(2), td:nth-child(3)" ).css("text-align", "center");
                    // $('#product_id_0').html(data.option);

                    $('#product_id_0')
                        .find('option')
                        .remove()
                        .end()
                        .append(data.option);

                }
            }
        });
    }
    fnTotalQuantity();
    fnTotalAmount();

});
</script>

<script type="text/javascript">
$('#product_id_0').change(function() {

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
    /*
     * ColumnID[0] = this is ID for input feild Product_id_0
     * ColumnID[3] = this is ID for input feild product_quantity_0
     * ColumnID[6] = this is ID for input feild unit_cost_price_0
     * ColumnID[7] = this is ID for input feild total_cost_price_0
     *
     * $ColumnName = "product_id_arr[]&pro_barcode_arr[]&sys_barcode_arr[]&product_quantity_arr[]&prod_cost_arr[]&received_quantity_arr[]&unit_cost_price_arr[]&total_cost_price_arr[]";

    $ColumnID = "product_id_&pro_barcode_&sys_barcode_&product_quantity_&ordered_quantity_&prod_cost_&unit_cost_price_&total_cost_price_&deleteRow_";
     */






    if ($('#' + ColumnID[0] + 0).val() != '' && $('#product_quantity_0').val() > 0) {

             var TotalRowCount = $('#' + TotalRowID).val();
            /*
            marge two row if same product found
            */
            // flag = false;

            var ProductQuantity = $('#' + ColumnID[3] + 0).val();
            // var StockQuantity = $('#stock_quantity_0').val();
            var stockqnt = Number($('#' + ColumnID[0] + 0).find("option:selected").attr('pquantity')) - 
                Number($('#' + ColumnID[0] + 0).find("option:selected").attr('retquantity'));
            var rowNumber = 0;
            var flag = false;

            for (var row = 1; row <= TotalRowCount; row++) {
                if ( $('#product_id_'+row).val() == $('#product_id_0').val()){
                    flag = true;
                    rowNumber = row ;
                }
            }

            if (flag === true){
                 ProductQuantity = Number(ProductQuantity) + Number($('#product_quantity_' + rowNumber).val());

                //if no stock comment out stock check
                // stock check start
                var stock = stockqnt;

                if(stock >= ProductQuantity){
                    $('#product_quantity_' + rowNumber).val(ProductQuantity);
                }
                else{

                    swal({
                        icon: 'error',
                        title: 'Error',
                        text: 'Quantity must be less than or equal ' + stock,
                    });

                    $('#product_quantity_' + rowNumber).val(stock);
                }
                 // s

                $('#' + ColumnID[0] + 0).val('');
                $('#' + ColumnID[0] + 0).trigger('change');
                $('#' + ColumnID[3] + 0).val(0);
                $('#' + ColumnID[6] + 0).val(0);
                $('#' + ColumnID[7] + 0).val(0);
                $('#' + ColumnID[3] + 0).prop('readonly', true);
                fnTotalQuantity();
                fnTtlProductPrice(rowNumber);
            }else{
                TotalRowCount++;
                $('#' + TotalRowID).val(TotalRowCount);
                var ProductID = $('#' + ColumnID[0] + 0).val();
                var ProductName = $('#' + ColumnID[0] + 0).find("option:selected").attr('pname');
                var ProductBarcode = $('#' + ColumnID[0] + 0).find("option:selected").attr('pbarcode');
                var ProductSysBarcode = $('#' + ColumnID[0] + 0).find("option:selected").attr('sbarcode');
                var ProductCostPrice = $('#' + ColumnID[0] + 0).find("option:selected").attr('pcprice');
                var ProductSalePrice = $('#' + ColumnID[0] + 0).find("option:selected").attr('psprice');
                var Productqnt = $('#' + ColumnID[0] + 0).find("option:selected").attr('pquantity');
                var ProductQuantity = $('#' + ColumnID[3] + 0).val();
                var ProductAmount = $('#' + ColumnID[7] + 0).val();


                var html = '<tr>';

                html += '<td class="barcodeWidth" width="60%">';
                // html +='<select class="form-control clsSelect2"  name="' + ColumnName[0] + '" id="' + ColumnID[0] + TotalRowCount + '" required readonly >'+
                //         '<option  value="' + ProductID + '" selected  pquantity="' + Productqnt + '">'+ ProductName +'</option>'+
                //         '</select>';
                html += '<input type="hidden" class="form-control round"   name="' + ColumnName[0] + '" value="' + ProductID +
                    '">';

                html += '<input type="hidden" id="stock_quantity_'+ TotalRowCount + '" value="' + stockqnt + '">';

                html += '<input type="text" class="form-control round" pquantity="' + Productqnt + '" id="' + ColumnID[0] +
                    TotalRowCount + '" value="' + ProductName + '" readonly>';
                html += '</td>';
                html += '<td width="36%">';
                // Input Feild For sys_barcode
                html += '<input type="hidden" name="' + ColumnName[1] + '" id="' + ColumnID[1] + TotalRowCount + '" value="' +
                    ProductBarcode + '">';
                // Input Feild For sys_barcode
                html += '<input type="hidden" name="' + ColumnName[2] + '" id="' + ColumnID[2] + TotalRowCount + '" value="' +
                    ProductSysBarcode + '">';
                html += '<input type="hidden" name="' + ColumnName[4] + '" id="' + ColumnID[4] + TotalRowCount + '" value="' +
                    ProductCostPrice + '">';

                html += '<input type="number" name="' + ColumnName[3] + '" id="' + ColumnID[3] + TotalRowCount +
                    '" class="form-control round clsQuantity" value="' + ProductQuantity + '" onkeyup="fnCheckQuantity(' +
                    TotalRowCount + ');fnTotalQuantity();fnTtlProductPrice(' + TotalRowCount + ');" required min="1">';
                html += '</td>';
                // // input feild for ordered_qtn
                // html += '<td width="10%">'+
                //             '<input type="number" name="' + ColumnName[4] + '" id="' + ColumnID[4] + TotalRowCount + '" class="form-control round clsOrderQuantity" value="0" onkeyup="fnTotalOrderQuantity();" required min="1">'+
                //         '</td>';

                // // input feild for received_qtn
                // html += '<td width="10%">'+
                //             '<input type="number" name="' + ColumnName[5] + '" id="' + ColumnID[5] + TotalRowCount + '" class="form-control round clsRcvQuantity" value="0" onkeyup="fnTotalReceiveQuantity();" required min="1">'+
                //         '</td>';

                // html += '<td width="36%">' +
                //     '<input type="number" name="' + ColumnName[6] + '" id="' + ColumnID[6] + TotalRowCount +
                //     '" class="form-control round" value="' + ProductSalePrice + '" readonly required min="1">' +
                //     // onkeyup="fnTtlProductPrice('+TotalRowCount+');"
                //     '</td>';
                // html += '<td width="16%">' +
                //     '<input type="number" name="' + ColumnName[7] + '" id="' + ColumnID[7] + TotalRowCount +
                //     '" class="form-control round ttlAmountCls" value="' + ProductAmount + '" readonly>' +
                //     '</td>';
                html += '<td width="4%">' +
                    '<a href="javascript:void(0)" class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center" onclick="btnRemoveRow(this);">' +
                    ' <i class="icon fa fa-times align-items-center"></i>' +
                    '</a>' +
                    '</td>';
                html += '</tr>';
                $('#' + TableID).append(html);

                $('#' + ColumnID[0] + 0).val('');
                $('#' + ColumnID[0] + 0).trigger('change');
                $('#' + ColumnID[3] + 0).val(0);
                $('#' + ColumnID[6] + 0).val(0);
                $('#' + ColumnID[7] + 0).val(0);
                $('#' + ColumnID[3] + 0).prop('readonly', true);

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
/* Add row End */

/* Remove row Start */
function btnRemoveRow(RemoveID) {

    $(RemoveID).closest('tr').remove();
    fnTotalQuantity();
    fnTotalAmount();
}
/* Remove row End */

function fnGenBillNo(BranchId) {
    if (BranchId != '') {

        $.ajax({
            method: "GET",
            url: "{{ url('/ajaxGBillUR') }}",
            dataType: "text",
            data: {
                BranchId: BranchId
            },
            success: function(data) {
                if (data) {
                    //console.log('testbrancj');
                    $('#return_bill_no').val(data);
                }
            }
        });
    }
}

function fnTotalQuantity() {

    // var qnt = $(this).find("option:selected").attr('pquantity');

    var totalQtn = 0;
    $('.clsQuantity').each(function() {
        totalQtn = Number(totalQtn) + Number($(this).val());
    });
    $('#total_quantity').val(totalQtn);
    $('#product_quantity').val(totalQtn);

    $('#total_return_quantity').val(totalQtn);
    $('#tdTotalQuantity').html(totalQtn);
}

function fnCheckQuantity(Row) {
    var chkFlag = true;

    var qnt = Number($('#stock_quantity_'+ Row).val());

    var TotalRowID =  $('#TotalRowID').val();
    var row ;
    var totaladdedqnt = 0 ;

    for (row = 1; row <= TotalRowID; row++) {

        if ( $('#product_id_'+row).val() == $('#product_id_0').val()){
            totaladdedqnt+= Number($('#product_quantity_'+ row).val());
        }
    }
    qnt -=totaladdedqnt;

    if (Row === 0) {

        if (Number($('#product_quantity_0').val()) > qnt) {
            chkFlag = false;
            $('#product_quantity_0').val(qnt);
        }

    } else {
        if (Number($('#product_quantity_' + Row).val()) > qnt ) {
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

function fnTtlProductPrice(Row) {

    var ProductQtn = $('#product_quantity_' + Row).val();
    var ProductPrice = $('#unit_cost_price_' + Row).val();
    var TotalProductPrice = (Number(ProductQtn) * Number(ProductPrice));
    $('#total_cost_price_' + Row).val(TotalProductPrice);
    fnTotalAmount();
}

function fnTotalAmount() {

    var totalAmt = 0;
    $('.ttlAmountCls').each(function() {
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




$('#submitButtonforSaleReturn').on('click', function(event) {

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
@endsection
