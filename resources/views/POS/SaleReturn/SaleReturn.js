// <script type="text/javascript">
$(document).ready(function () {
    $('.clsProductSelect').select2();

    $('#sales_bill_no').change(function () {
        var SalesBillNo = $('#sales_bill_no').val();
        if (SalesBillNo != '') {

            $.ajax({
                method: "GET",
                // url: "{{ url('./../../ajaxSalebillDetails')}}",
                url: "./../../ajaxSalebillDetails",
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
            // url: "{{ url('/ajaxGBillSR') }}",
            url: "./../../ajaxGBillSR",
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
// </script>
