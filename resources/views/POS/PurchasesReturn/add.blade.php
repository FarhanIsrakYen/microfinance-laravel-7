@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use App\Services\HtmlService as HTML;
use App\Services\PosService as POSS;
?>

<form method="post" data-toggle="validator" novalidate="true" id="preturn_form">
    @csrf

    <div class="panel">
        <div class="panel-body">
            <div class="row">
                <div class="col-lg-8 offset-lg-3">
                    <!-- Html View Load  -->
                    {!! HTML::forCompanyFeild() !!}
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8 offset-lg-3">
                    {!! HTML::forBranchFeild(false,'branch_id','branch_id',null,'','Branch Froms') !!}
                </div>
            </div>

            <div class="row">
                <!--Form Left-->
                <div class="col-lg-6">
                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title">Bill No</label>
                        <div class="col-lg-7">
                            <div class="input-group">
                                <input type="text" name="bill_no" id="bill_no" class="form-control round"
                                value="{{ POSS::generateBillPurchaseReturn(Common::getBranchId()) }}" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title RequiredStar">Supplier</label>
                        <div class="col-lg-7 input-group">
                            <select class="form-control clsSelect2"
                                 name="supplier_id" id="supplier_id"
                                required data-error="Please select Supplier">
                                <option value="">Select Supplier</option>
                                @foreach(Common::ViewTableOrderIn('pos_suppliers', ['is_delete'=>0], ['branch_id', HRS::getUserAccesableBranchIds()], ['id','sup_name'], ['sup_name','ASC']) as $rowSup)

                                <option value="{{ $rowSup->id }}">{{ $rowSup->sup_name }}</option>

                                @endforeach
                            </select>
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>

                </div>
                <!--Form Right-->
                <div class="col-lg-6">
                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title">Return Date</label>
                        <div class="col-lg-7 input-group">
                            <div class="input-group-prepend ">
                                <span class="input-group-text ">
                                    <i class="icon wb-calendar round" aria-hidden="true"></i>
                                </span>
                            </div>
                            <input type="hidden" id="return_date_h" name="return_date"
                                value="{{ Common::systemCurrentDate() }}">

                            <input type="text" id="return_date" data-plugin="datepicker"
                                class="form-control round" placeholder="DD-MM-YYYY" value="{{ Common::systemCurrentDate() }}"
                                readonly="true" disabled="true">
                        </div>
                    </div>


                </div>
            </div>

            <!--End Panel Body-->
        </div>
        <!--End Panel 1-->
    </div>

    <div class="panel">
        <div class="panel-body">
            <div class="row">

                <div class="col-lg-1 labelPercentSearch ">
                    <label for="textGroup" class="input-title">Group</label>
                </div>
                <div class="col-lg-2 inputPercentSearch input-group">
                    {{-- Query for get all group --}}
                    <?php $GroupList = Common::ViewTableOrder('pos_p_groups',
                                    ['is_delete' => 0],
                                    ['id', 'group_name'],
                                    ['group_name', 'ASC']) ?>
                    <select class="form-control clsSelect2"
                         id="prod_group_id" onchange="fnAjaxSelectBox('prod_cat_id',
                                        this.value,
                            '{{ base64_encode('pos_p_categories')}}',
                            '{{base64_encode('prod_group_id')}}',
                            '{{base64_encode('id,cat_name')}}',
                            '{{url('/ajaxSelectBox')}}'
                                    );
                                    fnProductLoad();">
                        <option value="">Select</option>
                        @foreach($GroupList as $GData)
                        <option value="{{ $GData->id }}">{{ $GData->group_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-1 labelPercentSearch">
                    <label for="textCategory" class="input-title">Category</label>
                </div>
                <div class="col-lg-2 inputPercentSearch input-group">
                    {{-- Query for get all category --}}
                    <?php $CategoryList = Common::ViewTableOrder('pos_p_categories',
                                    ['is_delete' => 0],
                                    ['id', 'cat_name'],
                                    ['cat_name', 'ASC']) ?>
                    <select class="form-control clsSelect2"
                         id="prod_cat_id" onchange="fnAjaxSelectBox('prod_sub_cat_id',
                                        this.value,
                            '{{base64_encode('pos_p_subcategories')}}',
                            '{{base64_encode('prod_cat_id')}}',
                            '{{base64_encode('id,sub_cat_name')}}',
                            '{{url('/ajaxSelectBox')}}'
                                    );
                                    fnProductLoad();">
                        <option value="">Select</option>
                        @foreach($CategoryList as $CData)
                        <option value="{{ $CData->id }}">{{ $CData->cat_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-1 labelPercentSearch">
                    <label for="textSubCategory" class="input-title">Sub Category</label>
                </div>
                <div class="col-lg-2 inputPercentSearch  input-group">
                    {{-- Query for get all Sub-category --}}
                    <?php $SubCategoryList = Common::ViewTableOrder('pos_p_subcategories',
                                    ['is_delete' => 0],
                                    ['id', 'sub_cat_name'],
                                    ['sub_cat_name', 'ASC']) ?>
                    <select class="form-control clsSelect2"
                         id="prod_sub_cat_id" onchange="fnAjaxSelectBox('prod_model_id',
                                        this.value,
                            '{{base64_encode('pos_p_models')}}',
                            '{{base64_encode('prod_sub_cat_id')}}',
                            '{{base64_encode('id,model_name')}}',
                            '{{url('/ajaxSelectBox')}}'
                                    );
                                    fnProductLoad();">
                        <option value="">Select</option>
                        @foreach($SubCategoryList as $SBData)
                        <option value="{{ $SBData->id }}">{{ $SBData->sub_cat_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-1 labelPercentSearch">
                    <label for="textModel" class="input-title">Model</label>
                </div>
                <div class="col-lg-2 inputPercentSearch input-group">
                    {{-- Query for get all Model --}}
                    <?php $ModelList = Common::ViewTableOrder('pos_p_models',
                                    ['is_delete' => 0],
                                    ['id', 'model_name'],
                                    ['model_name', 'ASC']) ?>
                    <select class="form-control clsSelect2"
                         id="prod_model_id" onchange="fnProductLoad();">
                        <option value="">Select</option>
                        @foreach($ModelList as $MData)
                        <option value="{{ $MData->id }}">{{ $MData->model_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 text-left">
                    <strong class="text-danger">Current Stock: <label id="current_stock"></label></strong>
                    <input type="hidden" id="stock_quantity_0">
                </div>
            </div>

            <table
                class="table table-hover table-striped table-bordered w-full text-center table-responsive" id="purchaseTable">
                <thead class="scrollHead">
                    <tr>
                        <th width="50%" class="RequiredStar">Product Name</th>
                        <th width="15%" class="RequiredStar">Quantity</th>
                        <th width="15%">Cost Price</th>
                        <th width="16%">Total</th>
                        <th width="4%"></th>
                    </tr>
                </thead>
                <tbody class="scrollBody">
                    <?php
                    $i = 0;
                    $TableID = "purchaseTable";

                    $ColumnName = "product_id_arr[]&sys_barcode_arr[]&product_bar_arr[]&product_quantity_arr[]&ordered_quantity_arr[]&received_quantity_arr[]&unit_cost_price_arr[]&total_cost_price_arr[]";

                    $ColumnID = "product_id_&sys_barcode_&product_bar_&product_quantity_&ordered_quantity_&received_quantity_&unit_cost_price_&total_cost_price_&deleteRow_";
                    $ProductList = Common::ViewTableOrder('pos_products',
                                    ['is_delete' => 0],
                                    ['id', 'product_name', 'cost_price', 'sale_price', 'prod_vat', 'sys_barcode', 'prod_barcode'],
                                    ['product_name', 'ASC']);
                    ?>


                    <tr>
                        <td width="50%" class="input-group barcodeWidth text-left">
                            <!-- {{-- name="product_id_arr[]" --}} -->
                            <select id="product_id_0" class="form-control round clsProductSelect"
                                onchange="fnAjaxCheckStock();" style="width: 100%;">
                                <option value="">Select Product</option>

                                @foreach($ProductList as $ProductInfo)
                                <option value="{{ $ProductInfo->id }}"
                                    pcprice="{{ $ProductInfo->cost_price }}"
                                    pname="{{ $ProductInfo->product_name }}"
                                    sbarcode="{{ $ProductInfo->sys_barcode }}"
                                    pbarcode="{{ $ProductInfo->prod_barcode }}">
                                    {{ $ProductInfo->product_name . ' (' . $ProductInfo->sys_barcode . ')' }}
                                </option>
                                @endforeach
                            </select>
                        </td>

                        <td width="15%">

                            <!-- {{-- name="product_quantity_arr[]"  --}} -->
                            <input type="number" id="product_quantity_0"
                                class="form-control round clsQuantity text-center" placeholder="Enter Quantity"
                                value="0" onkeyup="fnTotalQuantity(); fnTtlProductPrice(0); fnCheckQuantity(0);" required
                                min="1" readonly>
                        </td>
                        <td width="15%">
                            <!-- {{-- name="unit_cost_price_arr[]"  --}} -->
                            <input type="number" id="unit_cost_price_0" class="form-control round text-right" value="0"
                                required min="1" readonly>
                        </td>

                        <td width="16%">
                            <!-- {{-- name="total_cost_price_arr[]"  --}} -->
                            <input type="number" id="total_cost_price_0"
                                class="form-control round ttlAmountCls text-right" value="0" min="1" readonly>
                        </td>

                        <td width="4%">
                            <a href="javascript:void(0);"
                                class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center"
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
                            <h5>Amount</h5>
                            <input type="hidden" name="total_amount" id="total_amount" value="0" min="1">
                        </td>
                        <td width="16%" id="tdTotalAmount" class="text-right" style="font-weight: bold; ">0.00</td>
                        <td width="4%"></td>
                    </tr>
                </tfoot>
            </table>

            <!-- Row_Count is temporary variable for using row add and delete-->
            <input type="hidden" id="TotalRowID" value="0" />

            <div class="row align-items-center">
                <div class="col-lg-12">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();"
                                class="btn btn-default btn-round d-print-none">Back</a>

                                <button type="submit" class="btn btn-primary btn-round"
                                id="submitButtonforPurReturn">Save</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!--End Panel 2-->
    </div>

</form>

<script type="text/javascript">
$(document).ready(function() {

    $('.clsProductSelect').select2();

    //initially set 0 into these fields
    $('#current_stock').html(0);
    $('#stock_quantity_0').val(0);

    $('#company_id').change(function() {

        fnAjaxSelectBox(
            'supplier_id',
            this.value,
            '{{base64_encode("pos_suppliers")}}',
            '{{base64_encode("company_id")}}',
            '{{base64_encode("id,sup_name")}}',
            '{{url("/ajaxSelectBox")}}',
        );
    });


});

/* On selecting company load Supplier and Branch */

// product load function
function fnProductLoad() {
    var CompanyID = $('#company_id').val();
    var SupplierID = $('#supplier_id').val();
    var GroupID = $('#prod_group_id').val();
    var CategoryID = $('#prod_cat_id').val();
    var SubCatID = $('#prod_sub_cat_id').val();
    var ModelID = $('#prod_model_id').val();



    $.ajax({
        method: "GET",
        url: "{{url('/ajaxProductLPurchase')}}",
        dataType: "text",
        data: {
            ModelID: ModelID,
            GroupID: GroupID,
            CategoryID: CategoryID,
            SubCatID: SubCatID,
            CompanyID: CompanyID,
            SupplierID: SupplierID
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
// END PORODUCT LOAD

// start first column on change
$('#product_id_0').change(function() {

    if ($(this).val() != '') {
        $('#product_quantity_0').prop('readonly', false);
        var selProdCostPrice = $(this).find("option:selected").attr('pcprice');
        $('#unit_cost_price_0').val(selProdCostPrice);
        fnTtlProductPrice(0);
    } else {
        $('#product_quantity_0').prop('readonly', true);
        $('#unit_cost_price_0').val(0);
        //$('#product_id_0').val('');
    }
});
// end first column on change

/* Add row Start */
function btnAddNewRow(TableID, ColumnNameS, ColumnIDS, TotalRowID) {
    var ColumnName = ColumnNameS.split("&");
    var ColumnID = ColumnIDS.split("&");
    /*
     * ColumnID[0] = this is ID for input feild Product_id_0
     * ColumnID[3] = this is ID for input feild product_quantity_0
     * ColumnID[6] = this is ID for input feild unit_cost_price_0
     * ColumnID[7] = this is ID for input feild total_cost_price_0
     */
    if ($('#' + ColumnID[0] + 0).val() != '' && $('#product_quantity_0').val() > 0 && Number($('#stock_quantity_0').val()) != 0
            && Number($('#stock_quantity_0').val()) >= Number($('#product_quantity_0').val())) {


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

            // console.log('test');
            if ( $('#product_id_'+ row).val() == $('#product_id_0').val()){
                flag = true;
                rowNumber = row;
            }
        }


        if (flag === true){

            // console.log('text');

            ProductQuantity = Number(ProductQuantity) + Number($('#product_quantity_' + rowNumber).val());

            //if no stock comment out stock check
            // stock check start
            var stock = Number($('#stock_quantity_' + rowNumber).val());

            if(stock >= ProductQuantity){
                $('#product_quantity_' + rowNumber).val(ProductQuantity);
            }
            else{

                swal({
                    icon: 'error',
                    title: 'Error',
                    text: 'Stock must be less than or equal ' + stock,
                });

                $('#product_quantity_' + rowNumber).val(stock);
            }
             // stock check End



            //$('#product_quantity_' + rowNumber).val(ProductQuantity);
            $('#' + ColumnID[0] + 0).val('');
            $('#' + ColumnID[0] + 0).trigger('change');
            $('#' + ColumnID[3] + 0).val(0);
            $('#' + ColumnID[4] + 0).val(0);
            $('#' + ColumnID[5] + 0).val(0);
            $('#' + ColumnID[3] + 0).prop('readonly', true);

            $('#current_stock').html(0);
            fnTotalQuantity();
            fnTtlProductPrice(rowNumber);
        }
        else{

            TotalRowCount++;
            $('#' + TotalRowID).val(TotalRowCount);
            var ProductID = $('#' + ColumnID[0] + 0).val();
            var ProductName = $('#' + ColumnID[0] + 0).find("option:selected").attr('pname');
            var ProductBarcode = $('#' + ColumnID[0] + 0).find("option:selected").attr('pbarcode');
            var ProductSysBarcode = $('#' + ColumnID[0] + 0).find("option:selected").attr('sbarcode');
            var ProductCostPrice = $('#' + ColumnID[0] + 0).find("option:selected").attr('pcprice');
            var ProductSalePrice = $('#' + ColumnID[0] + 0).find("option:selected").attr('psprice');
            var ProductQuantity = $('#' + ColumnID[3] + 0).val();
            var ProductAmount = $('#' + ColumnID[7] + 0).val();


            var html = '<tr>';

            html += '<td class="input-group barcodeWidth text-left" width="50%%">';
            // html +='<select class="form-control clsSelect2"  name="' + ColumnName[0] + '" id="' + ColumnID[0] + TotalRowCount + '" onchange="fnGetSelectedValue(' + TotalRowCount + ');" required >'+
            //         '<option value="">Select Product</option>'+
            //         '</select>';
            html += '<input type="hidden" id="product_id_'+TotalRowCount+'" name="' + ColumnName[0] + '" value="' + ProductID + '">';
            html += '<input type="hidden" id="stock_quantity_'+ TotalRowCount +'" value="' + StockQuantity + '">';
            html += '<input type="text" class="form-control round" value="' + ProductName + '(' + ProductSysBarcode + ')' +
                '" readonly >';
            html += '</td>';
            html += '<td width="15%">';
            // Input Feild For sys_barcode
            html += '<input type="hidden" name="' + ColumnName[1] + '" id="' + ColumnID[1] + TotalRowCount + '" value="' +
                ProductSysBarcode + '" >';
            // Input Feild For product_barcode
            html += '<input type="hidden" name="' + ColumnName[2] + '" id="' + ColumnID[2] + TotalRowCount + '" value="' +
                ProductBarcode + '" >';

            html += '<input type="number" name="' + ColumnName[3] + '" id="' + ColumnID[3] + TotalRowCount +
                '" class="form-control round clsQuantity text-center" value="' + ProductQuantity +
                '" onkeyup="fnTotalQuantity();fnTtlProductPrice('+TotalRowCount+'); fnCheckQuantity('+TotalRowCount+');" required min="1">';
            html += '</td>';

            html += '<td width="15%">' +
                '<input type="number" name="' + ColumnName[6] + '" id="' + ColumnID[6] + TotalRowCount +
                '" class="form-control round text-right" value="' + ProductCostPrice + '" readonly required min="1">' +
                '</td>';
            html += '<td width="16%">' +
                '<input type="number" name="' + ColumnName[7] + '" id="' + ColumnID[7] + TotalRowCount +
                '" class="form-control round ttlAmountCls text-right" value="' + ProductAmount + '" readonly>' +
                '</td>';
            html += '<td width="4%">' +
                '<a href="javascript:void(0)" class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center" onclick="btnRemoveRow(this);">' +
                ' <i class="icon fa fa-times align-items-center"></i>' +
                '</a>' +
                '</td>';
            html += '</tr>';

            // $('#' + TableID).append(html);
            $('#' + TableID + ' tbody').find('tr:first').after(html);

            $('#' + ColumnID[0] + 0).val('');
            $('#' + ColumnID[0] + 0).trigger('change');
            $('#' + ColumnID[3] + 0).val(0);
            $('#' + ColumnID[6] + 0).val(0);
            $('#' + ColumnID[7] + 0).val(0);
            $('#' + ColumnID[3] + 0).prop('readonly', true);

            $('#current_stock').html(0);
        }

    }
    else {

        if ($('#' + ColumnID[0] + 0).val() == '') {
            swal({
                icon: 'error',
                title: 'Error',
                text: 'Please select product!',
            });
        }
        else if($('#product_quantity_0').val() <= 0){
            swal({
                icon: 'error',
                title: 'Error',
                text: 'Quantity must be greater than 0!',
            });
        }
        else if ($('#stock_quantity_0').val() == 0) {

            swal({
                icon: 'error',
                title: 'Error',
                text: 'Empty Stock !! Please try next time.',
            });

        }
        else if($('#stock_quantity_0').val() < $('#product_quantity_0').val()) {

            swal({
                icon: 'error',
                title: 'Error',
                text: 'Stock must be less than or equal ' + $('#stock_quantity_0').val(),
            });

            $('#product_quantity_0').val(0);
            fnTotalQuantity();
            fnTtlProductPrice(0);
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

function fnGetSelectedValue(RowId) {

    var price = $("#product_id_" + RowId).children("option:selected").attr('pcprice');
    $("#product_price_" + RowId).val(price);

}

function fnAjaxCheckStock(){

    var BranchId = $('#branch_id').val();
    var ProductId = $('#product_id_0').val();


    // console.log(BranchId);

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

                    // for stock check and update
                    /*
                    set stock_quantity_ id
                    set product_id_  id in full page
                    */
                    var stock = data ;

                    var TotalRowID =  $('#TotalRowID').val();
                    var row ;
                    var totaladdedqnt = 0 ;
                    for (row = 1; row <= TotalRowID; row++) {
                        //console.log($('#product_id_'+row).val());
                        if ( $('#product_id_'+row).val() == $('#product_id_0').val()){
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
    $('#tdTotalQuantity').html(totalQtn);
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

    // //--------------------------- T/A After Discount
    // fnCalDiscount($('#discount_rate').val());

    // //-----------------------------calculate vat amount
    // fnCalVat($('#vat_rate').val());

    // //-----------------------------calculate Due amount
    // fnCalDue($('#paid_amount').val());
}




function fnGenBillNo(BranchId) {
    if (BranchId != '') {
        $.ajax({
            method: "GET",
            url: "{{ url('/ajaxGBillPR') }}",
            dataType: "text",
            data: {
                BranchId: BranchId
            },
            success: function(data) {
                if (data) {
                    $('#bill_no').val(data);
                    // console.log(data);
                }
            }
        });
    }
}

$('#submitButtonforPurReturn').on('click', function(event) {
    event.preventDefault();

    if ($('#total_quantity').val() > 0  && $('#product_id_0').val() == '') {
        $('#preturn_form').submit();
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
</script>



<script type="text/javascript">
$(document).ready(function() {

    $('#prod_group_id').change(function() {
        $('#prod_cat_id').val('');
        $('#prod_cat_id').trigger('change');
        $('#prod_sub_cat_id').val('');
        $('#prod_sub_cat_id').trigger('change');
        $('#prod_model_id').val('');
        $('#prod_model_id').trigger('change');

        fnProductLoad();

    });

    $('#prod_cat_id').change(function() {
        $('#prod_sub_cat_id').val('');
        $('#prod_sub_cat_id').trigger('change');
        $('#prod_model_id').val('');
        $('#prod_model_id').trigger('change');
        fnProductLoad();

    });

    $('#prod_sub_cat_id').change(function() {
        $('#prod_model_id').val('');
        $('#prod_model_id').trigger('change');
        fnProductLoad();
    });

});

$(document).ready(function() {
    $('#branch_id').change(function() {
        fnGenBillNo($('#branch_id').val());
    });
});

$('form').submit(function (event) {
    $(this).find(':submit').attr('disabled', 'disabled');
});

</script>
@endsection
