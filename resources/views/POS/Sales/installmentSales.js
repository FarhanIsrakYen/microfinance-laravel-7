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

function calcMonth() {
    //copy the date
    var month = $('#inst_month').find("option:selected").attr('month');

    var dt = new Date($('#sys_date_format').val());
    dt.setMonth(dt.getMonth() + Number(month));

    var end = dt;
    var start = new Date($('#sys_date_format').val());
    var weekDiff = Math.floor((end - start + 1) / (1000 * 60 * 60 * 24) / 7);

    $('#total_weeks').val(weekDiff);
}

$('#product_id_0').change(function () {

    if ($(this).val() != '') {

        $('#product_quantity_0').prop('readonly', false);

        var productSalesPrice = $(this).find("option:selected").attr('psprice');

        // $('#sales_price_0').val(productSalesPrice);
        $('#unit_sales_price_0').val(Math.round(productSalesPrice));

        fnTtlProductPrice(0);
        fnCalSalesPriceNInstAmt();
        fnCalAvgInstAmnt(0);

    } else {
        $('#product_quantity_0').prop('readonly', true);
    }
});


$('#inst_month').change(function () {
    var month = $('#inst_month').find("option:selected").attr('month');
    var profit = $('#inst_month').find("option:selected").attr('instProfit');
    var inst_package_id = $('#inst_month').val();

    $('#installment_month').val(month);
    $('#installment_rate').val(profit);
    $('#inst_package_id').val(inst_package_id);
});

$('#installment_type').change(function () {
    $('#inst_type').val($(this).val());
});


//function for calculate sales price
function fnCalSalesPriceNInstAmt() {

    var productSalesPrice = $('#product_id_0').find("option:selected").attr('psprice');
    // var productSalesPrice = $('#sales_price_0').val();

    if (Number(productSalesPrice) > 0) {
        var unitSalesPrice = Number(productSalesPrice) + ((Number(productSalesPrice) * Number($('#installment_rate').val()) / 100));
        $('#unit_sales_price_0').val(Math.round(unitSalesPrice));
        // $('#unit_sales_price_0').val(unitSalesPrice);

        fnTtlProductPrice(0);
        fnCalAvgInstAmnt(0);
    }
}

/* Add row Start */
function btnAddNewRow(TableID, ColumnNameS, ColumnIDS, TotalRowID) {

    var ColumnName = ColumnNameS.split("&");
    var ColumnID = ColumnIDS.split("&");

    // var instAntWithPfee = Number($('#inst_amount_0').val()) + 300;
    // var firstInst = Number($('#fst_Installment_0').val());

    // $ColumnName = "product_id_arr[]&sys_barcode_arr[]&product_name_arr[]&product_sno_arr[]&product_quantity_arr[]&sales_price_arr[]&unit_sales_price_arr[]&total_cost_price_arr[]";
    // $ColumnID = "product_id_&sys_barcode_&product_name_&product_sno_&product_quantity_&sales_price_&unit_sales_price_&avg_inst_amt_&total_cost_price_";

    // $ColumnName = "product_id_arr[]&product_sno_arr[]&product_quantity_arr[]&unit_sales_price_arr[]&avg_inst_amt_arr[]&total_sales_price_arr[]";
    // $ColumnID = "product_id_&product_sno_&product_quantity_&unit_sales_price_&avg_inst_amt_&total_sales_price_";

    /*
     * ColumnID[0] = this is ID for input feild Product_id_0
     * ColumnID[1] = this is ID for input feild product_sno_0
     * ColumnID[2] = this is ID for input feild product_quantity_0
     * ColumnID[3] = this is ID for input feild unit_sales_price_0
     * ColumnID[4] = this is ID for input feild avg_inst_amt_0
     * ColumnID[5] = this is ID for input feild total_sales_price_0
     */
    if ($('#Product_id_0').val() != '' &&
        $('#product_quantity_0').val() > 0 &&
        $('#inst_month').val() != '' &&
        $('#installment_type').val() != '' &&
        Number($('#stock_quantity_0').val()) != 0 &&
        Number($('#stock_quantity_0').val()) >= Number($('#product_quantity_0').val())
    ) {

        var TotalRowCount = $('#' + TotalRowID).val();
        /*
        marge two row if same product found
        */

        var ProductQuantity = $('#product_quantity_0').val();
        var StockQuantity = $('#stock_quantity_0').val();
        //if no stock off StockQuantity

        var flag = false;
        var rowNumber = 0;

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
            var stock = Number($('#stock_quantity_' + rowNumber).val());

            if (stock >= ProductQuantity) {
                $('#product_quantity_' + rowNumber).val(ProductQuantity);
            } else {
                swal({
                    icon: 'error',
                    title: 'Error',
                    text: 'Stock must be less than or equal ' + stock,
                });

                $('#product_quantity_' + rowNumber).val(stock);
            }

            // stock check End
            $('#product_id_0').val('');
            $('#product_id_0').trigger('change');
            $('#product_sno_0').val('');
            $('#product_quantity_0').val(0);
            $('#unit_sales_price_0').val(0);
            $('#avg_inst_amt_0').val(0);
            $('#total_sales_price_0').val(0);
            $('#product_quantity_0').prop('readonly', true);

            // $('#inst_month').prop('disabled', true);

            $('#current_stock').html(0);
            fnTotalQuantity();
            fnTtlProductPrice(rowNumber);
            fnCalAvgInstAmnt(rowNumber);
        } else {

            TotalRowCount++;
            $('#' + TotalRowID).val(TotalRowCount);

            var ProductID = $('#product_id_0').val();
            var ProductName = $('#product_id_0').find("option:selected").attr('pname');
            var ProductBarcode = $('#product_id_0').find("option:selected").attr('pbarcode');
            var ProductSysBarcode = $('#product_id_0').find("option:selected").attr('sbarcode');
            var ProductCostPrice = $('#product_id_0').find("option:selected").attr('pcprice');
            // var ProductSalePrice = $('#product_id_0').find("option:selected").attr('psprice');
            var ProductSNo = $('#product_sno_0').val();

            var ProductSalePrice = $('#unit_sales_price_0').val();
            var instAmnt = $('#avg_inst_amt_0').val();
            var totalAmt = $('#total_sales_price_0').val();

            var html = '<tr>';

            html += '<td>';
            html += '<input type="hidden" id="product_id_' + TotalRowCount + '" name="' + ColumnName[0] + '" value="' + ProductID + '">';
            html += '<input type="hidden" id="stock_quantity_' + TotalRowCount + '" value="' + StockQuantity + '">';
            html += '<input type="hidden" name="product_barcode_arr[]" value="' + ProductBarcode + '" >';
            html += '<input type="hidden" name="product_system_barcode_arr[]" value="' + ProductSysBarcode + '" >';


            html += '<input type="text" class="form-control round" value="' + ProductName + '(' + ProductSysBarcode + ')' +
                '" readonly >';
            html += '</td>';

            //product serial no
            html += '<td>' +
                '<input type="text" name="' + ColumnName[1] + '" id="' + ColumnID[1] + TotalRowCount +
                '" class="form-control round" value="' + ProductSNo + '">' +
                '</td>';

            //product quantity
            html += '<td>';
            html += '<input type="number" name="' + ColumnName[2] + '" id="' + ColumnID[2] + TotalRowCount +
                '" class="form-control round clsQuantity text-center" value="' + ProductQuantity +
                '" onkeyup="fnTotalQuantity();fnTtlProductPrice(' + TotalRowCount + '); fnCheckQuantity(' + TotalRowCount + ');fnCalAvgInstAmnt(' + TotalRowCount + ');" min="1">';
            html += '</td>';

            html += '<td>' +
                //product cost price
                '<input type="hidden" name="product_cost_price_arr[]" value="' + ProductCostPrice + '">' +

                //product unit sale price
                '<input type="number" name="' + ColumnName[3] + '" id="' + ColumnID[3] + TotalRowCount +
                '" class="form-control round text-right" value="' + ProductSalePrice + '" readonly min="1">' +
                '</td>';

            //installment amount
            html += '<td>' +
                '<input type="number" id="' + ColumnID[4] + TotalRowCount + '" class="form-control round clsInstAmt text-right" value="' + instAmnt + '" readonly>' +
                '</td>';

            //total amount
            html += '<td>' +
                '<input type="number" name="' + ColumnName[5] + '" id="' + ColumnID[5] + TotalRowCount +
                '" class="form-control round text-right ttlAmountCls" value="' + totalAmt + '" readonly>' +
                '</td>';

            //btn remove
            html += '<td>' +
                '<a href="javascript:void(0)" class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center" onclick="btnRemoveRow(this);">' +
                ' <i class="icon fa fa-times align-items-center"></i>' +
                '</a>' +
                '</td>';
            html += '</tr>';

            $('#' + TableID + ' tbody').find('tr:first').after(html);

            $('#product_id_0').val('');
            $('#product_id_0').trigger('change');
            $('#product_sno_0').val('');
            $('#product_quantity_0').val(0);
            $('#unit_sales_price_0').val(0);
            $('#avg_inst_amt_0').val(0);
            $('#total_sales_price_0').val(0);
            $('#product_quantity_0').prop('readonly', true);

            $('#inst_month').prop('disabled', true);
            $('#installment_type').prop('disabled', true);

            $('#current_stock').html(0);

            $('#prod_group_id').val('');
            $('#prod_cat_id').val('');
            $('#prod_sub_cat_id').val('');
            $('#prod_model_id').val('');

            $('#prod_group_id').trigger('change');
            $('#prod_cat_id').trigger('change');
            $('#prod_sub_cat_id').trigger('change');
            $('#prod_model_id').trigger('change');
        }
    } else {

        if ($('#' + ColumnID[0] + 0).val() == '') {
            swal({
                icon: 'error',
                title: 'Error',
                text: 'Please select product!',
            });
        } else if ($('#product_quantity_0').val() == 0) {
            swal({
                icon: 'error',
                title: 'Error',
                text: 'Quantity must be greater than 0!',
            });
        } else if ($('#inst_month').val() == '') {
            swal({
                icon: 'error',
                title: 'Error',
                text: 'Please select month!',
            });
        } else if ($('#installment_type').val() == '') {
            swal({
                icon: 'error',
                title: 'Error',
                text: 'Please select installment type!',
            });
        } else if (Number($('#stock_quantity_0').val()) == 0) {

            swal({
                icon: 'error',
                title: 'Error',
                text: 'Empty Stock !! Please try next time.',
            });
            $('#product_quantity_0').val(0);

        } else if (Number($('#stock_quantity_0').val()) < Number($('#product_quantity_0').val())) {

            swal({
                icon: 'error',
                title: 'Error',
                text: 'Stock must be less than or equal ' + $('#stock_quantity_0').val(),
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
    fnTotalAvgInstAmount();
}
/* Remove row End */

function fnCheckQuantity(Row) {

    var StockQuantity = Number($('#stock_quantity_' + Row).val());
    var TypeQuantity = Number($('#product_quantity_' + Row).val());

    var chkFlag = true;

    if (StockQuantity === 0) {

        swal({
            icon: 'error',
            title: 'Error',
            text: 'Empty Stock !! Please try next time.',
        });
        $('#product_quantity_0').val(0);

    } else if (StockQuantity < TypeQuantity) {

        swal({
            icon: 'error',
            title: 'Error',
            text: 'Stock must be less than or equal ' + StockQuantity,
        });
        $('#product_quantity_' + Row).val(StockQuantity);
        fnTotalQuantity();
        fnTtlProductPrice(Row);
        fnCalAvgInstAmnt(Row);
    }
}

function fnTotalQuantity() {

    var totalQtn = 0;
    $('.clsQuantity').each(function () {
        totalQtn = Number(totalQtn) + Number($(this).val());
    });
    $('#total_quantity').val(totalQtn);
    $('#tdTotalQuantity').html(totalQtn);
}

function fnTtlProductPrice(Row) {

    var ProductQtn = $('#product_quantity_' + Row).val();
    var ProductPrice = $('#unit_sales_price_' + Row).val();
    var TotalProductPrice = (Number(ProductQtn) * Number(ProductPrice));
    $('#total_sales_price_' + Row).val(Math.round(TotalProductPrice));
    //call the fn for calculat total amount
    fnTotalAmount();
}

function fnTotalAmount() {

    var totalAmt = 0;
    $('.ttlAmountCls').each(function () {
        totalAmt = Number(totalAmt) + Number($(this).val());
    });
    $('#tdTotalAmount').html(totalAmt.toFixed(2));
    //-------------------------- Total Amount
    $('#total_amount').val(totalAmt.toFixed(2));

    //-----------------------------calculate vat amount
    fnCalVat($('#vat_rate').val());

    //-----------------------------calculate Due amount
    fnCalDue($('#paid_amount').val());
}

function fnCalInstAmt() {

    calcMonth();

    var month_count = $('#installment_month').val();
    // var due_amount = $('#due_amount').val();


    // hasib chance 
    var due_amount = Number($('#total_amount').val()) - (Number($('#paid_amount').val()) - Number($('#service_charge').val()) - Number($('#vat_amount').val()));
    var total_amount_if_paid_zero = Number($('#total_amount').val());




    var week_count = $('#total_weeks').val();
    var installment_type = $('#installment_type').val();
    var actual_inst_amount = 0;
    // console.log(due_amount);
    // console.log(Number($('#paid_amount').val()) - Number($('#service_charge').val()) - Number($('#vat_amount').val()));

    var total_payable_amount = Number($('#total_payable_amount').val());

    var inst_count = 1;

    if (installment_type == 1) {
        if (month_count != 0) {
            inst_count = Number(month_count);
            // actual_inst_amount = Number(due_amount) / (Number(month_count) - 1);
            // actual_inst_amount = Number(total_payable_amount) / (Number(month_count));
        }
    }

    if (installment_type == 2) {
        if (month_count != 0) {
            inst_count = Number(week_count);

            // actual_inst_amount = Number(due_amount) / (Number(week_count) - 1);
            // actual_inst_amount = Number(total_payable_amount) / (Number(week_count));
        }
    }


    if ($('#paid_amount').val() > 0) {
        actual_inst_amount = Number(due_amount) / (Number(inst_count) - 1);
    } else {

        actual_inst_amount = Number(total_amount_if_paid_zero) / (Number(inst_count));
    }


    ////////////////////////
    var lastDigit = Number(Math.ceil(actual_inst_amount).toString().substr(-1));
    var extra_amount = 0;
    var instalment_actual = Number(actual_inst_amount);

    if (lastDigit > 0) {
        extra_amount = (10 - lastDigit) + (Math.ceil(actual_inst_amount) - actual_inst_amount);
        var inst_amount_temp = actual_inst_amount + extra_amount;
        var last_inst = inst_amount_temp - (extra_amount * (inst_count - 2));

        if (last_inst < 1) {
            if (lastDigit > 0 && lastDigit <= 5) {

                extra_amount = (5 - lastDigit) + (Math.ceil(actual_inst_amount) - actual_inst_amount);
                inst_amount_temp = actual_inst_amount + extra_amount;
                last_inst = inst_amount_temp - (extra_amount * (inst_count - 2));
            }

            if (lastDigit > 5 && lastDigit <= 9) {

                extra_amount = (10 - lastDigit) + (Math.ceil(actual_inst_amount) - actual_inst_amount);
                inst_amount_temp = actual_inst_amount + extra_amount;
                last_inst = inst_amount_temp - (extra_amount * (inst_count - 2));
            }

            if (last_inst < 1) {
                extra_amount = Math.ceil(actual_inst_amount) - actual_inst_amount;
            }
        }
    }

    extra_amount = Number(extra_amount);
    actual_inst_amount = actual_inst_amount + extra_amount;

    // var actual_inst_amount = (Number($('#due_amount').val()) / (Number($('#installment_month').val() - 1)));

    $('#installment_amount').val(actual_inst_amount.toFixed(2));
    $('#instalment_actual').val(instalment_actual.toFixed(2));
    $('#instalment_extra').val(extra_amount.toFixed(2));
}


function fnCalAvgInstAmnt(Row) {

    calcMonth();
    var month = $('#installment_month').val();

    var avg_inst_amt = 0;

    if ($('#installment_type').val() == 1) {
        if (month != 0) {
            avg_inst_amt = Number($('#total_sales_price_' + Row).val()) / Number($('#installment_month').val());
        } else {
            avg_inst_amt = 0;
        }
    }

    if ($('#installment_type').val() == 2) {

        if (month != 0) {
            avg_inst_amt = Number($('#total_sales_price_' + Row).val()) / Number($('#total_weeks').val());
        } else {
            avg_inst_amt = 0;
        }
    }

    if (avg_inst_amt != 0) {
        $('#avg_inst_amt_' + Row).val(Math.round(avg_inst_amt));
    }

    fnTotalAvgInstAmount();
}

function fnTotalAvgInstAmount() {

    var totalInstAmt = 0;
    $('.clsInstAmt').each(function () {
        totalInstAmt = Math.round(Number(totalInstAmt) + Number($(this).val()));
    });

    $('#tdTotalInstAmt').html(totalInstAmt);
    //-------------------------- Total Amount
    $('#total_inst_amt').val(totalInstAmt);

    fnCalInstAmt();

}

function fnCalVat(vatVal) {
    var TotalAmount = $('#total_amount').val();
    // console.log($('#total_amount').val());
    var GrossAmount = (Number(TotalAmount) + ((Number(TotalAmount) * Number(vatVal)) / 100));
    $('#vat_amount').val(((Number(TotalAmount) * Number(vatVal)) / 100));

    $('#ta_after_vat').val(GrossAmount.toFixed(2));

    $('#total_payable_amount').val(GrossAmount.toFixed(2));

    // calculate total amount with processing fee
    fnCalPrsFee($('#service_charge').val());
    $('#paid_amount').val(0);
    //-----------------------------calculate Due amount
    fnCalDue($('#paid_amount').val());
    fnCalInstAmt();
}

function fnCalPrsFee(prsFee) {
    var ttlAmtWithPrsFee = Number($('#ta_after_vat').val()) + Number($('#service_charge').val());
    $('#total_payable_amount').val(ttlAmtWithPrsFee.toFixed(2));
    $('#paid_amount').val(0);
    //-----------------------------calculate Due amount
    fnCalDue($('#paid_amount').val());
    fnCalInstAmt();
}

function fnCalDue(paidAmount) {
    var GrossAmount = $('#total_payable_amount').val();
    var DueAmount = (Number(GrossAmount) - Number(paidAmount));

    $('#due_amount').val(DueAmount.toFixed(2));
}

function fnCheckPaidAmt(paid_amt) {

    paid_amt = Number(paid_amt);

    var ttlPaidAmt = Number($('#total_inst_amt').val()) + Number($('#vat_amount').val()) + Number($('#service_charge').val());

    // var ttlPaidAmt = Number($('#vat_amount').val()) + Number($('#service_charge').val());
    var total_payable_amount = Number($('#total_payable_amount').val());

    // console.log($('#vat_amount').val());

    if (ttlPaidAmt > paid_amt) {
        swal({
            icon: 'error',
            title: 'Error',
            text: 'Paid amount must be greater than or equal ' + Math.round(ttlPaidAmt),
        });

        $('#paid_amount').val(0);
        fnCalDue(0);

    } else if (total_payable_amount < paid_amt) {
        swal({
            icon: 'error',
            title: 'Error',
            text: 'Paid amount must be smaller than or equal ' + Math.round(total_payable_amount),
        });

        $('#paid_amount').val(0);
        fnCalDue(0);
    }
}

$('#submitButtonforPurchase').on('click', function (event) {
    event.preventDefault();

    // Button Disable
    $(this).prop('disabled', true);

    var branchID = $('#branch_id').val();
    if (branchID != 1) {

        var ttlPaidAmt = Number($('#total_inst_amt').val()) + Number($('#vat_amount').val()) + Number($('#service_charge').val());

        // var ttlPaidAmt = Number($('#vat_amount').val()) + Number($('#service_charge').val());
        var paid_amt = Number($('#paid_amount').val());
        var total_payable_amount = Number($('#total_payable_amount').val());

        if ($('#total_quantity').val() > 0 && $('#product_id_0').val() == '' && paid_amt >= ttlPaidAmt && $('#due_amount').val() > 0) {
            $('#sales_form').submit();

            $(this).prop('disabled', false);
        } else {
            // Button Enable
            $(this).prop('disabled', false);

            if ($('#total_quantity').val() <= 0) {
                swal({
                    icon: 'error',
                    title: 'Error',
                    text: 'Total Quantity must be greater than zero !!',
                });
            } else if (paid_amt < ttlPaidAmt) {
                swal({
                    icon: 'error',
                    title: 'Error',
                    text: 'Paid amount must be greater than or equal ' + Math.round(ttlPaidAmt),
                });

                $('#paid_amount').val(0);
                fnCalDue(0);
            } else if (total_payable_amount < paid_amt) {
                swal({
                    icon: 'error',
                    title: 'Error',
                    text: 'Paid amount must be smaller than or equal ' + Math.round(total_payable_amount),
                });

                $('#paid_amount').val(0);
                fnCalDue(0);
            } else if ($('#product_id_0').val() != '') {

                swal({
                    icon: 'error',
                    title: 'Error',
                    text: 'Product Entry must be empty!!',
                });
            } else if ($('#due_amount').val() < 0) {
                swal({
                    icon: 'error',
                    title: 'Error',
                    text: 'Due amount cannot be less than 0!!',
                });
            }
        }
    } else if (branchID == 1) {
        $(this).prop('disabled', false);
        swal({
            icon: 'error',
            title: 'Error',
            text: 'Access Denied ! You are not authorized in this page',
            confirmButtonText: "Ok"
        }).then((isConfirm) => {
            if (isConfirm) {
                window.location.href = "{{url('pos/sales_installment')}}";
            }
        });
    }
});
