@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
use App\Services\PosService as POSS;
?>

<!-- Page -->
<div class="page">
    <div class="page-header">
        <h4 class="">Transfer Entry</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/pos') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="#">Transaction</a></li>
            <li class="breadcrumb-item"><a href="{{ url('/pos/transfer') }}">Transfer</a></li>
            <li class="breadcrumb-item active">Entry</li>
        </ol>
    </div>

    <div class="page-content">
        <form action="{{ url('/pos/transfer/new') }}" method="post" data-toggle="validator" novalidate="true"
            id="transfer_form">
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
                            {!! HTML::forBranchFeild(true,'branch_from','branch_from',null,'','Branch From') !!}
                        </div>
                    </div>
                    
                    <div class="row">
                        <!--Form Left-->
                        <div class="col-lg-6">
                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">Bill No</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <input type="text" name="bill_no" id="bill_no" class="form-control round"
                                            required readonly value="{{ POSS::generateBillTransfer(Common::getBranchId()) }}">
                                    </div>
                                </div>
                                <div class="help-block with-errors is-invalid"></div>
                            </div>

                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">Transfer To &nbsp (Branch)</label>
                                <div class="col-lg-7 input-group">
                                    <select class="form-control clsSelect2"
                                         name="branch_to" id="branch_to" required
                                        data-error="Please select Branch">
                                        <option value="">Select Branch</option>
                                        @foreach($BranchData as $BData)
                                        <option value="{{ $BData->id }}">
                                            {{ sprintf("%04d", $BData->branch_code)." - ".$BData->branch_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                             

                        </div>
                        <!--Form Right-->
                        <div class="col-lg-6">

                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title">Transfer Date</label>
                                <div class="col-lg-7 input-group">
                                    <div class="input-group-prepend ">
                                        <span class="input-group-text ">
                                            <i class="icon wb-calendar round" aria-hidden="true"></i>
                                        </span>
                                    </div>
                                    <input type="hidden" id="transfer_date_h" name="transfer_date"
                                        value="{{ Common::systemCurrentDate() }}">

                                    <input type="text" id="transfer_date" data-plugin="datepicker"
                                        class="form-control round" placeholder="DD-MM-YYYY" 
                                        value="{{ Common::systemCurrentDate() }}"
                                        readonly="true" disabled="true" required>
                                </div>
                                <div class="help-block with-errors is-invalid"></div>
                            </div>

                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title">Order No</label>
                                <div class="col-lg-7 ">
                                    <div class="input-group">
                                        <input type="text" name="order_no" id="order_no" class="form-control round">
                                    </div>
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

                        <label class="col-lg-1 input-title">Group</label>
                        <div class="col-lg-2 inputPercentSearch input-group">
                            {{-- Query for get all group --}}
                            <?php $GroupList = Common::ViewTableOrder('pos_p_groups',
                                            ['is_delete' => 0],
                                            ['id', 'group_name'],
                                            ['group_name', 'ASC']) ?>
                            <select class="form-control clsSelect2"
                                 id="prod_group_id" onchange="fnAjaxSelectBox(
                                                         'prod_cat_id',
                                                         this.value,
                                             '{{base64_encode('pos_p_categories')}}',
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

                        <label class="col-lg-1 input-title">Category</label>
                        <div class="col-lg-2 inputPercentSearch input-group">
                            {{-- Query for get all category --}}
                            <?php $CategoryList = Common::ViewTableOrder('pos_p_categories',
                                            ['is_delete' => 0],
                                            ['id', 'cat_name'],
                                            ['cat_name', 'ASC']) ?>
                            <select class="form-control clsSelect2"
                                 id="prod_cat_id" onchange="fnAjaxSelectBox(
                                                     'prod_sub_cat_id',
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

                        <label class="col-lg-1 input-title">Sub Category</label>
                        <div class="col-lg-2 inputPercentSearch  input-group">
                            {{-- Query for get all Sub-category --}}
                            <?php $SubCategoryList = Common::ViewTableOrder('pos_p_subcategories',
                                            ['is_delete' => 0],
                                            ['id', 'sub_cat_name'],
                                            ['sub_cat_name', 'ASC']) ?>
                            <select class="form-control clsSelect2"
                                 id="prod_sub_cat_id" onchange="fnAjaxSelectBox(
                                                     'prod_model_id',
                                                     this.value,
                                         '{{base64_encode('pos_p_models')}}',
                                         '{{base64_encode('prod_sub_cat_id')}}',
                                         '{{base64_encode('id,model_name')}}',
                                         '{{url('/ajaxSelectBox')}}');
                                         fnProductLoad();">
                                <option value="">Select</option>
                                @foreach($SubCategoryList as $SBData)
                                <option value="{{ $SBData->id }}">{{ $SBData->sub_cat_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <label class="col-lg-1 input-title">Model</label>
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
                        class="table table-hover table-striped table-bordered w-full text-center table-responsive my-custom-scrollbar"
                        id="transferTable">
                        <thead>
                            <tr>
                                <th width="35%" class="RequiredStar">Product Name</th>
                                <th width="10%" class="RequiredStar">Quantity</th>
                                <th width="15%">Cost Price</th>
                                <th width="15%">Total</th>
                                <th width="5%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            $TableID = "transferTable";

                            $ColumnName = "product_id_arr[]&sys_barcode_arr[]&product_name_arr[]&product_quantity_arr[]&unit_cost_price_arr[]&total_cost_price_arr[]";

                            $ColumnID = "product_id_&sys_barcode_&product_name_&product_quantity_&unit_cost_price_&total_cost_price_&deleteRow_";

                            $ProductList = Common::ViewTableOrder('pos_products',
                                            ['is_delete' => 0],
                                            ['id', 'product_name', 'cost_price', 'sale_price', 'prod_vat', 'sys_barcode', 'prod_barcode'],
                                            ['product_name', 'ASC']);
                            ?>


                            <tr>
                                <td class="input-group barcodeWidth">
                                    {{-- name="product_id_arr[]" --}}
                                    <select id="product_id_0" class="form-control round clsProductSelect"
                                         onchange="fnAjaxCheckStock();">
                                        <option value="">Select Product</option>

                                        @foreach($ProductList as $ProductInfo)
                                        <option value="{{ $ProductInfo->id }}" pcprice="{{ $ProductInfo->cost_price }}"
                                            pname="{{ $ProductInfo->product_name }}"
                                            sbarcode="{{ $ProductInfo->sys_barcode }}"
                                            pbarcode="{{ $ProductInfo->prod_barcode }}">
                                            {{ $ProductInfo->product_name . ' (' . $ProductInfo->sys_barcode . ')' }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td>

                                    {{-- name="product_quantity_arr[]"  --}}
                                    <input type="number" id="product_quantity_0" class="form-control round clsQuantity"
                                        placeholder="Enter Quantity" value="0"
                                        onkeyup="fnTotalQuantity(); fnTtlProductPrice(0); fnCheckQuantity(0);" required min="1" readonly>
                                </td>
                                <td>
                                    {{-- name="unit_cost_price_arr[]"  --}}
                                    <input type="number" id="unit_cost_price_0" class="form-control round" value="0"
                                        required min="1" readonly>
                                </td>

                                <td>
                                    {{-- name="total_cost_price_arr[]"  --}}
                                    <input type="number" id="total_cost_price_0" class="form-control round ttlAmountCls"
                                        value="0" min="1" readonly>
                                </td>

                                <td>
                                    <a href="javascript:void(0);"
                                        class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center"
                                        onclick="btnAddNewRow('<?= $TableID ?>', '<?= $ColumnName ?>', '<?= $ColumnID ?>', 'TotalRowID');">
                                        <i class="icon wb-plus  align-items-center"></i>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Row_Count is temporary variable for using row add and delete-->
                    <input type="hidden" id="TotalRowID" value="0" />

                    <div class="mt-4 table-responsive">
                        <table class="table table-striped table-bordered w-full text-center" id="tableQuanAmnt">
                            <tbody>
                                <tr>
                                    <td width="45%">
                                        <h5>Total Quantity</h5>
                                        <input type="hidden" name="total_quantity" id="total_quantity" value="0"
                                            min="1">
                                    </td>
                                    <td width="10%" id="tdTotalQuantity" style="font-weight: bold;">0</td>

                                    <td width="20%">
                                        <h5>Total Amount</h5>
                                        <input type="hidden" name="total_amount" id="total_amount" value="0" min="1">
                                    </td>
                                    <td width="25%" id="tdTotalAmount" style="font-weight: bold;">0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="row align-items-center">
                        <div class="col-lg-12">
                            <div class="form-group d-flex justify-content-center">
                                <div class="example example-buttons">
                                    <a href="{{ url('pos/transfer') }}" class="btn btn-default btn-round">Back</a>
                                    <button type="submit" class="btn btn-primary btn-round"
                                        id="submitButtonforTransfer">Save</button>
                                    <!-- <a href="#"><button type="button" class="btn btn-warning btn-round">Next</button></a> -->
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!--End Panel 2-->
            </div>



        </form>
        <!--End Page Content-->

    </div>
    <!--End Page-->
</div>

<script type="text/javascript">
$(document).ready(function() {

    $('.clsProductSelect').select2();

    //initially set 0 into these fields
    $('#current_stock').html(0);
    $('#stock_quantity_0').val(0);
    $('#upcomming_stock').html(0);

});

function fnProductLoad() {
    var CompanyID = $('#company_id').val();
    // var SupplierID = $('#supplier_id').val();
    var GroupID = $('#prod_group_id').val();
    var CategoryID = $('#prod_cat_id').val();
    var SubCatID = $('#prod_sub_cat_id').val();
    var ModelID = $('#prod_model_id').val();
    var firstRowFirstColId = $('#transferTable tbody tr td:first-child select').attr('id');

    $.ajax({
        method: "GET",
        url: "{{url('/ajaxProductLTransfer')}}",
        dataType: "text",
        data: {
            ModelID: ModelID,
            GroupID: GroupID,
            CategoryID: CategoryID,
            SubCatID: SubCatID,
            CompanyID: CompanyID,
            // SupplierID: SupplierID
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
        var selProdCostPrice = $(this).find("option:selected").attr('pcprice');
        $('#unit_cost_price_0').val(selProdCostPrice);
        fnTtlProductPrice(0);
    } else {
        $('#product_quantity_0').prop('readonly', true);
    }
});

var ProductIDArr = [];

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
    if ($('#' + ColumnID[0] + 0).val() != '' && $('#product_quantity_0').val() > 0 && $('#stock_quantity_0').val() != 0 
            && Number($('#stock_quantity_0').val()) >= Number($('#product_quantity_0').val())) {

        var TotalRowCount = $('#' + TotalRowID).val();
        ///////////////////////////////








        //////////////////////////
        TotalRowCount++;
        $('#' + TotalRowID).val(TotalRowCount);
        var ProductID = $('#' + ColumnID[0] + 0).val();
        var ProductName = $('#' + ColumnID[0] + 0).find("option:selected").attr('pname');
        var ProductBarcode = $('#' + ColumnID[0] + 0).find("option:selected").attr('pbarcode');
        var ProductSysBarcode = $('#' + ColumnID[0] + 0).find("option:selected").attr('sbarcode');
        var ProductCostPrice = $('#' + ColumnID[0] + 0).find("option:selected").attr('pcprice');
        var ProductQuantity = $('#' + ColumnID[3] + 0).val();
        var ProductAmount = $('#' + ColumnID[5] + 0).val();

        var StockQuantity = $('#stock_quantity_0').val();



        var html = '<tr>';

        html += '<td class="input-group barcodeWidth" width="35%">';

        html += '<input type="hidden" id="product_id_'+ TotalRowCount +'" name="' + ColumnName[0] + '" value="' + ProductID + '">';
        html += '<input type="hidden" id="stock_quantity_'+ TotalRowCount +'" value="' + StockQuantity + '">';
        html += '<input type="text" class="form-control round" value="' + ProductName + '(' + ProductSysBarcode + ')' +
            '" readonly >';
        html += '</td>';
        html += '<td width="10%">';

        html += '<input type="number" name="' + ColumnName[3] + '" id="' + ColumnID[3] + TotalRowCount +
            '" class="form-control round clsQuantity" value="' + ProductQuantity +
            '" onkeyup="fnTotalQuantity(); fnTtlProductPrice(' + TotalRowCount + ');fnCheckQuantity(' + TotalRowCount + ');" required min="1">';
        html += '</td>';

        html += '<td width="15%">' +
            '<input type="number" name="' + ColumnName[4] + '" id="' + ColumnID[4] + TotalRowCount +
            '" class="form-control round" value="' + ProductCostPrice + '" readonly required min="1">' +

            '</td>';
        html += '<td width="15%">' +
            '<input type="number" name="' + ColumnName[5] + '" id="' + ColumnID[5] + TotalRowCount +
            '" class="form-control round ttlAmountCls" value="' + ProductAmount + '" readonly>' +
            '</td>';
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


$('#company_id').change(function() {

    


    /* On selecting company load Transfer from and transfer to Branch */
    fnAjaxSelectBox(
        'branch_from',
        this.value,
        '{{base64_encode("gnl_branchs")}}',
        '{{base64_encode("company_id")}}',
        '{{base64_encode("id,branch_name")}}',
        '{{url("/ajaxSelectBox")}}',
    );

    fnAjaxSelectBox(
        'branch_to',
        this.value,
        '{{base64_encode("gnl_branchs")}}',
        '{{base64_encode("company_id")}}',
        '{{base64_encode("id,branch_name")}}',
        '{{url("/ajaxSelectBox")}}',
    );

    // fnAjaxSelectBox(
    //     'prod_group_id',
    //     this.value,
    //     '{{base64_encode("pos_p_groups")}}',
    //     '{{base64_encode("company_id")}}',
    //     '{{base64_encode("id,group_name")}}',
    //     '{{url("/ajaxSelectBox")}}',
    // );
});


/*Generate Transfer No via AjaxController */
function fnGenBillNo(BranchID) {
    if (BranchID != '') {
        $.ajax({
            method: "GET",
            url: "{{ url('/ajaxGBillTransfer') }}",
            dataType: "text",
            data: {
                BranchID: BranchID
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
                    var stock = data ; 

                    var TotalRowID =  $('#TotalRowID').val();
                    var row ;
                    var totaladdedqnt = 0 ;
                    for (row = 1; row <= TotalRowID; row++) {
                        //console.log($('#product_id_'+row).val());
                        if ( $('#product_id_'+row).val() == $('#product_id_0').val()){
                            var GivenQnt = Number($('#product_quantity_'+ row).val());
                            totaladdedqnt+= GivenQnt;
                            console.log(data-totaladdedqnt);
                            //console.log(qntstock);

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
    var CostPrice = $('#unit_cost_price_' + Row).val();
    var ProductCostAmount = (Number(ProductQtn) * Number(CostPrice));
    $('#total_cost_price_' + Row).val(ProductCostAmount.toFixed(2));
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
    $('#total_amount_t').val(totalAmt.toFixed(2));
    // $('#sale_amount_'+ Row).val(totalAmt);
}

// $('#submitButtonforTransfer').on('click', function(event) {
//     event.preventDefault();

//     if($('#total_amount').val() > 0 && $('#product_id_0').val() == '') {
//         $('#transfer_form').submit();
//     }
//     else{

//         swal({
//           icon: 'error',
//           title: 'Error',
//           text: 'Total payable amount must be gratter than zero !!',
//         });
//     }
// });


$('#submitButtonforTransfer').on('click', function(event) {
    event.preventDefault();
    if ($('#total_amount').val() > 0 && $('#product_id_0').val() == '') {

        $('#transfer_form').submit();
    } else {

        if ($('#total_amount').val() <= 0) {
            swal({
                icon: 'error',
                title: 'Error',
                text: 'Total payable amount must be gratter than zero !!',
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

    $('#branch_from').change(function() {
        fnGenBillNo($('#branch_from').val());
    });


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
</script>

@endsection