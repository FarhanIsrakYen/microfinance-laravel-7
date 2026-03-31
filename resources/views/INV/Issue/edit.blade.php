@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
use App\Services\InvService as INVS;
?>

<form method="POST" data-toggle="validator" novalidate="true" id="issue_form">
    @csrf
    <div class="panel">
        <div class="panel-body">

        <div class="row">
            <div class="col-lg-8 offset-lg-3">
                <!-- Html View Load  -->
                {!! HTML::forCompanyFeild($Issuem->company_id) !!}
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8 offset-lg-3">
                {!! HTML::forBranchFeild(false,'branch_from','branch_from',$Issuem->branch_from,'','Branch From') !!}


            </div>
        </div>

        <div class="row">
            <!--Form Left-->
            <div class="col-lg-6">
                <div class="form-row align-items-center">
                    <label class="col-lg-3 input-title">Bill No</label>
                    <div class="col-lg-5">
                        <div class="form-group">
                            <div class="input-group ">
                                <input type="text" class="form-control round"
                                    placeholder="Enter Issue No." name="bill_no" id="bill_no"
                                    value="{{$Issuem->bill_no}}" required readonly>
                            </div>
                        </div>
                    </div>
                </div>

                {!! HTML::forBranchFeild(true,'branch_to','branch_to',$Issuem->branch_to,'disabled','Branch To') !!}


            </div>
            <!--    end left -->

            <!--Form Right-->
            <div class="col-lg-6">

                <div class="form-row form-group align-items-center">
                    <div class="col-lg-3 ">
                        <label class="input-title">Issue Date</label for="">
                    </div>
                    <div class="col-lg-5 input-group">
                        <div class="input-group-prepend ">
                            <span class="input-group-text ">
                                <i class="icon wb-calendar round" aria-hidden="true"></i>
                            </span>
                        </div>
                        <?php
                            $IssuemDate = new DateTime($Issuem->issue_date);
                            $IssuemDate = (!empty($IssuemDate)) ? $IssuemDate->format('d-m-Y') : date('d-m-Y');
                        ?>
                        <input type="text" id="issue_date" name="issue_date"
                            class="form-control round" readonly value="{{ $IssuemDate }}">
                    </div>
                </div>

                <div class="form-row align-items-center">
                    <div class="col-lg-3 input-title">
                        <label for="textRequisitionNumber" class="input-title RequiredStar">Requisition No</label>
                    </div>
                    <div class="col-lg-5">
                        <select class="form-control clsSelect2" name="requisition_no"
                        id="requisition_no" required onchange="fnGetReqProduct()">
                            <option value="">Select Requisition No</option>
                        </select>
                    </div>
                </div>
            </div>
            <!--  end Right    -->
        </div>

        </div>
    </div>

    <div class="panel">
        <div class="panel-body">
            <div class="row">

                <div class="col-lg-1 labelPercentSearch">
                    <label for="textGroup" class="input-title">Group</label>
                </div>
                <div class="col-lg-2 inputPercentSearch input-group">
                    {{-- Query for get all group --}}
                    <?php $GroupList = Common::ViewTableOrder('inv_p_groups',
                            ['is_delete' => 0],
                            ['id', 'group_name'],
                            ['group_name', 'ASC']) ?>
                    <select class="form-control clsSelect2"
                            id="prod_group_id" onchange="fnAjaxSelectBox('prod_cat_id',
                                this.value,
                    '{{ base64_encode('inv_p_categories')}}',
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
                    <?php $CategoryList = Common::ViewTableOrder('inv_p_categories',
                            ['is_delete' => 0],
                            ['id', 'cat_name'],
                            ['cat_name', 'ASC']) ?>
                    <select class="form-control clsSelect2"
                            id="prod_cat_id" onchange="fnAjaxSelectBox('prod_sub_cat_id',
                                this.value,
                    '{{base64_encode('inv_p_subcategories')}}',
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
                    <?php $SubCategoryList = Common::ViewTableOrder('inv_p_subcategories',
                            ['is_delete' => 0],
                            ['id', 'sub_cat_name'],
                            ['sub_cat_name', 'ASC']) ?>
                    <select class="form-control clsSelect2"
                            id="prod_sub_cat_id" onchange="fnAjaxSelectBox('prod_model_id',
                                this.value,
                    '{{base64_encode('inv_p_models')}}',
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
                    <?php $ModelList = Common::ViewTableOrder('inv_p_models',
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

            <div class="row mt-4">
                <div class="col-lg-6 text-left">
                    <strong class="text-danger" style="color: #3e8ef7;">Current Stock: <label id="current_stock"></label></strong>
                    <input type="hidden" id="stock_quantity_0">
                </div>
                {{-- <div class="col-lg-6 text-right">
                    <strong style="color: #3e8ef7;">Upcomming Stock:<label id="upcomming_stock"></label></strong>
                </div> --}}
            </div>


          
            <table class="table table-hover table-striped table-bordered w-full text-center table-responsive" id="issueTable">
                <thead class="scrollHead">
                    <tr>
                        <th width="61%" class="RequiredStar">Product Name</th>
                        <th width="35%" class="RequiredStar">Quantity</th>
                        <th width="4%"></th>
                    </tr>
                </thead>
                <tbody class="scrollBody">
                    <?php
                        $i = 0;
                        $TableID = "issueTable";

                        $ColumnName = "product_id_arr[]&sys_barcode_arr[]&product_name_arr[]&product_quantity_arr[]&unit_cost_price_arr[]&total_cost_price_arr[]";

                        $ColumnID = "product_id_&sys_barcode_&product_name_&product_quantity_&unit_cost_price_&total_cost_price_&deleteRow_";
                        // 'company_id'=> $CompanyID,
                        $ProductList = Common::ViewTableOrder('inv_products',
                                        ['is_delete' => 0],
                                        ['id', 'product_name', 'cost_price', 'product_code'],
                                        ['product_name', 'ASC']);
                    ?>
                    <tr>
                        <td width="61%" class="input-group barcodeWidth text-left">
                            {{-- name="product_id_arr[]" --}}
                            <select id="product_id_0" class="form-control round clsProductSelect"
                                    onchange="fnAjaxCheckStock();" style="width: 100%">
                                <option value="">Select Product</option>

                                @foreach($ProductList as $ProductInfo)
                                <option value="{{ $ProductInfo->id }}"
                                    pname="{{ $ProductInfo->product_name }}"
                                    sbarcode="{{ $ProductInfo->product_code }}">
                                    {{ $ProductInfo->product_code ? $ProductInfo->product_name . ' (' . $ProductInfo->product_code . ')' : $ProductInfo->product_name }}
                                </option>
                                @endforeach
                            </select>
                        </td>

                        <td width="35%">
                            {{-- name="product_quantity_arr[]" --}}
                            <input type="number" id="product_quantity_0"
                                class="form-control round clsQuantity text-center" placeholder="Enter Quantity"
                                value="0" onkeyup="fnTotalQuantity(); fnCheckQuantity(0);" required
                                min="1" readonly>
                        </td>

                        <td width="4%">
                            <a href="javascript:void(0);"
                                class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center clsAddNewRow"
                                onclick="btnAddNewRow('<?= $TableID ?>', '<?= $ColumnName ?>', '<?= $ColumnID ?>', 'TotalRowID');">
                                <i class="icon wb-plus  align-items-center"></i>
                            </a>
                        </td>
                    </tr>

                    @if(count($Issued) > 0)
                    @foreach($Issued as $Data)
                    <?php $i++; ?>
                    <tr>
                        <td width="61%" class="input-group barcodeWidth">
                            <input type="hidden" id="product_id_{{ $i }}" name="product_id_arr[]" value="{{ $Data->product_id }}">
                            <input type="hidden" id="stock_quantity_{{ $i }}" value="{{(double)INVS::stockQuantity($Issuem->branch_from,$Data->product_id ) + (double)$Data->product_quantity}}">

                            @foreach($ProductList as $ProductInfo)
                            @if($ProductInfo->id == $Data->product_id)
                            <input type="text" class="form-control round"
                                value="{{ $ProductInfo->product_code ? $ProductInfo->product_name . ' (' . $ProductInfo->product_code . ')' : $ProductInfo->product_name }} "
                                readonly>
                            @endif
                            @endforeach
                        </td>

                        <td width="35%">
                            <input type="number" name="product_quantity_arr[]"
                                id="product_quantity_{{ $i }}" class="form-control round clsQuantity text-center"
                                placeholder="Enter Quantity" value="{{ $Data->product_quantity }}"
                                onkeyup="fnTotalQuantity(); fnCheckQuantity({{$i}});" required min="1">
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
                        <td width="61%" style="text-align:right;">
                            <h5>TOTAL</h5>
                            <input type="hidden" name="total_quantity" id="total_quantity" value="0"
                                min="1">
                        </td>
                        <td width="35%" id="tdTotalQuantity" style="font-weight: bold;">0</td>
                        <td width="4%"></td>
                    </tr>
                </tfoot>
            </table>
            <!-- Row_Count is temporary variable for using row add and delete-->
            <input type="hidden" id="TotalRowID" value="{{ $i }}" />

            <div class="row align-items-center">
                <div class="col-lg-12">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();"
                                    class="btn btn-default btn-round d-print-none">Back</a>
                                @if(date('d-m-Y', strtotime($Issuem->issue_date)) == Common::systemCurrentDate())
                                <button type="submit" class="btn btn-primary btn-round"
                                    id="submitButton">Update</button>
                                @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<!-- End Page -->
<script>
$(document).ready(function() {
    $('.clsProductSelect').select2();


    //initially set 0 into these fields
    $('#current_stock').html(0);
    $('#stock_quantity_0').val(0);
    $('#upcomming_stock').html(0);

    // $('#company_id').change(function(){
    //     fnAjaxSelectBox(
    //     'prod_group_id',
    //     this.value,
    //     '{{base64_encode("inv_p_groups")}}',
    //     '{{base64_encode("company_id")}}',
    //     '{{base64_encode("id,group_name")}}',
    //     '{{url("/ajaxSelectBox")}}',
    //     );

    // });
    
    fnGetReqProduct()

    fnTotalQuantity();
    // fnTotalAmount();
});

// product load function
function fnProductLoad() {
    var CompanyID = $('#company_id').val();
    var SupplierID = $('#supplier_id').val();
    var GroupID = $('#prod_group_id').val();
    var CategoryID = $('#prod_cat_id').val();
    var SubCatID = $('#prod_sub_cat_id').val();
    var ModelID = $('#prod_model_id').val();
    var firstRowFirstColId = $('#issueTable tbody tr td:first-child select').attr('id');


    $.ajax({
        method: "GET",
        url: "{{url('/ajaxProductLPurchaseInv')}}",
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
                // $('#product_id_0').html('');
                // $('#'+firstRowFirstColId).append(data);
                // $('#'+firstRowFirstColId).trigger('change');

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
        var selProdRemQtn = $(this).find("option:selected").attr('remainQtn');
        $('#unit_cost_price_0').val(selProdCostPrice);
        $('#product_quantity_0').val(selProdRemQtn);

        fnTotalQuantity();
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
        0: "product_id_"
        1: "sys_barcode_"
        2: "product_name_"
        3: "product_quantity_"
        4: "unit_cost_price_"
        5: "total_cost_price_"
        6: "deleteRow_"
     */
    if ($('#' + ColumnID[0] + 0).val() != '' && $('#product_quantity_0').val() > 0 && $('#stock_quantity_0').val() != 0 && Number($('#stock_quantity_0').val()) >= Number($('#product_quantity_0').val()) &&  Number($('#product_id_0').find("option:selected").attr('remainqtn') >= Number($('#product_quantity_0').val()))) {

        var TotalRowCount = $('#' + TotalRowID).val();
        /*
        marge two row if same product found
        */

        var ProductQuantity = $('#' + ColumnID[3] + 0).val();
        var StockQuantity = $('#stock_quantity_0').val();
        //if no stock off StockQuantity

        var flag = false;

        var rowNumber = 0;

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
            $('#upcomming_stock').html(0);
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
            // var ProductQuantity = $('#' + ColumnID[3] + 0).val();
            var ProductAmount = $('#' + ColumnID[5] + 0).val();

            // var StockQuantity = $('#stock_quantity_0').val();



            var html = '<tr>';

            html += '<td class="input-group barcodeWidth" width="35%">';

            html += '<input type="hidden" id="product_id_'+ TotalRowCount +'" name="' + ColumnName[0] + '" value="' + ProductID + '">';
            html += '<input type="hidden" id="stock_quantity_'+ TotalRowCount +'" value="' + StockQuantity + '">';
            html += '<input type="hidden" id="remain_quantity_'+ TotalRowCount +'" value="' + remainQtn + '">';
            html += '<input type="text" class="form-control round" value="' + ProductName + '(' + ProductSysBarcode + ')' +
                '" readonly >';
            html += '</td>';
            html += '<td width="10%">';

            html += '<input type="number" name="' + ColumnName[3] + '" id="' + ColumnID[3] + TotalRowCount +
                '" class="form-control round clsQuantity" value="' + ProductQuantity +
                '" onkeyup="fnTotalQuantity(); fnTtlProductPrice(' + TotalRowCount + ');fnCheckQuantity(' + TotalRowCount + '); fnCheckRequisitionQtn(this.value, '+TotalRowCount+')"" required min="1">';
            html += '</td>';

            // html += '<td width="15%">' +
            //     '<input type="number" name="' + ColumnName[4] + '" id="' + ColumnID[4] + TotalRowCount +
            //     '" class="form-control round" value="' + ProductCostPrice + '" readonly required min="1">' +

            //     '</td>';
            // html += '<td width="15%">' +
            //     '<input type="number" name="' + ColumnName[5] + '" id="' + ColumnID[5] + TotalRowCount +
            //     '" class="form-control round ttlAmountCls" value="' + ProductAmount + '" readonly>' +
            //     '</td>';
            html += '<td width="5%">' +
                '<a href="javascript:void(0)" class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center" onclick="btnRemoveRow(this);">' +
                ' <i class="icon fa fa-times align-items-center"></i>' +
                '</a>' +
                '</td>';
            html += '</tr>';

            $('#' + TableID + ' tbody').find('tr:first').after(html);

            $('#' + ColumnID[0] + 0).val('');
            $('#' + ColumnID[0] + 0).trigger('change');
            $('#' + ColumnID[3] + 0).val(0);
            $('#' + ColumnID[4] + 0).val(0);
            $('#' + ColumnID[5] + 0).val(0);
            $('#' + ColumnID[3] + 0).prop('readonly', true);

            $('#current_stock').html(0);
            $('#upcomming_stock').html(0);


        }

        //////////////////////////
        fnDisableUnchangeableFields();

    } else {

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
        else{
            swal({
                icon: 'error',
                title: 'Error',
                text: 'Quantity must be less than or equal ' + $('#product_id_0').find("option:selected").attr('remainqtn'),
            });
        }

    }
}
/* Add row End */

function fnDisableUnchangeableFields(){

//transfer data into hidden fields
$('#branch_to_copy').val($('#branch_to').val());
$('#requisition_no_copy').val($('#requisition_no').val());

//disabled fields
$('#branch_to').prop('disabled', true);
$('#requisition_no').prop('disabled', true);
}

function fnCheckRequisitionQtn(current_qtn, row){

if(current_qtn > Number($('#remain_quantity_'+ row).val())){
    swal({
        icon: 'error',
        title: 'Error',
        text: 'Quantity must be less than or equal ' + $('#remain_quantity_'+ row).val(),
    });

    $('#product_quantity_'+ row).val($('#remain_quantity_'+ row).val());
}
}

/* Remove row Start */
function btnRemoveRow(RemoveID) {

    $(RemoveID).closest('tr').remove();
    fnTotalQuantity();
    // fnTotalAmount();
}
/* Remove row End */

function fnGetSelectedValue(RowId) {

    var price = $("#product_id_" + RowId).children("option:selected").attr('pcprice');
    $("#product_price_" + RowId).val(price);

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
    $('#total_cost_price_' + Row).val(TotalProductPrice.toFixed(2));
    // fnTotalAmount();
}

function fnTotalAmount() {

    var totalAmt = 0;
    $('.ttlAmountCls').each(function() {
        totalAmt = Number(totalAmt) + Number($(this).val());
    });
    $('#tdTotalAmount').html(totalAmt.toFixed(2));
    //-------------------------- Total Amount
    $('#total_amount').val(totalAmt.toFixed(2));

    // //--------------------------- T/A After Discount
    // fnCalDiscount($('#discount_rate').val());

    // //-----------------------------calculate vat amount
    // fnCalVat($('#vat_rate').val());

    // //-----------------------------calculate Due amount
    // fnCalDue($('#paid_amount').val());
}

$('#submitButton').on('click', function(event) {
    event.preventDefault();
    if($('#branch_from').val()==$('#branch_to').val()){
        swal({
                icon: 'error',
                title: 'Error',
                text: 'Two Branch Name Cant be same',
            });

    }else{
        if(Number($('#branch_from').val()) != 1 ){
            swal({
                    icon: 'error',
                    title: 'Error',
                    text: 'Access Denied ! You are not authorized in this page',
                    confirmButtonText: "Ok"
            }).then((isConfirm) => {
                if (isConfirm) {
                    window.location.href = "{{url('inv/issue')}}";
                }
            });
        }else{
             if ($('#total_quantity').val() > 0 && $('#product_id_0').val() == '') {
                $('#branch_to').prop('disabled', false);
                $('#issue_form').submit();
            } else {
                if ($('#total_quantity').val() <= 0) {
                    swal({
                        icon: 'error',
                        title: 'Error',
                        text: 'Total Quantity must be gratter than zero !!',
                    });
                } else{
                    swal({
                        icon: 'error',
                        title: 'Error',
                        text: 'Add the selected Product Or Remove it !!',
                    });
                }

            }


        }

    }

});


function fnGenBillNo(BranchId) {
    if (BranchId != '') {
        $.ajax({
            method: "GET",
            url: "{{ url('/ajaxGBillIssueInv') }}",
            dataType: "text",
            data: {
                BranchId: BranchId
            },
            success: function(data) {
                if (data) {
                    $('#bill_no').val(data);
                }
            }
        });
    }
}

function fnAjaxCheckStock(){

    var BranchId = $('#branch_from').val();
    var ProductId = $('#product_id_0').val();

    if (BranchId != '' && ProductId != '') {

        $.ajax({
            method: "GET",
            url: "{{ url('/ajaxCheckStockInv') }}",
            dataType: "text",
            data: {
                BranchId: BranchId,
                ProductId: ProductId
            },
            success: function(data) {
                if (data) {
                    var stock = data;

                    var TotalRowID =  $('#TotalRowID').val();
                    var row ;
                    var totaladdedqnt = 0 ;
                    for (row = 1; row <= TotalRowID; row++) {

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
/*upcomming stock set from product quantity*/
$('#product_quantity_0').keyup(function(){

    $('#upcomming_stock').html($(this).val());
});


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
        $('#product_quantity_'+ Row).val(0);
        fnTotalQuantity();
        fnTtlProductPrice(Row);
    }
}

function fnGetReqProduct() {

    var reqNo = '{{ $Issuem->requisition_no }}';

    if(reqNo != '')
    {
        $.ajax({
            method: "GET",
            url: "{{url('/ajaxReqIssueProdLoadInv')}}",
            dataType: "text",
            data: {
                reqNo: reqNo,
            },
            success: function(data) {

                if (data) {
                    $('#product_id_0')
                    .find('option')
                    .remove()
                    .end()
                    .append(data);

                    $('#requisition_no').prop('disabled', true);
                }
            }
        });
    }
}


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

    fnGetReqProduct();

});

$(document).ready(function() {
    $('#branch_from').change(function() {
        fnGenBillNo($('#branch_from').val());
    });

    var branchTo = $('#branch_to').val();

    if(branchTo != ''){
        $.ajax({
            method: "GET",
            url: "{{url('/ajaxReqLoadIssueInv')}}",
            dataType: "text",
            data: {
                branchId: $('#branch_to').val(),
                selRequisition: '{{ $Issuem->requisition_no }}'
            },
            success: function(data) {
                if (data) {
                    $('#requisition_no')
                    .find('option')
                    .remove()
                    .end()
                    .append(data);
                }
            }
        });
    }

    $('#branch_to').change(function() {
        $.ajax({
            method: "GET",
            url: "{{url('/ajaxReqLoadIssueInv')}}",
            dataType: "text",
            data: {
                branchId: $('#branch_to').val(),
                selRequisition: $('#requisition_no').val()
            },
            success: function(data) {
                if (data) {
                    $('#requisition_no')
                    .find('option')
                    .remove()
                    .end()
                    .append(data);
                }
            }
        });
    });
});

$('form').submit(function (event) {
    $(this).find(':submit').attr('disabled', 'disabled');
});

</script>
@endsection
