@extends('Layouts.erp_master')
@section('content')
<!-- Page -->
<div class="page">
    <div class="page-header">
        <label class="">New Issue Entry</label>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0)">Transaction</a></li>
            <li class="breadcrumb-item"><a href="index.php?PageId=IssueList">Issue</a></li>
            <li class="breadcrumb-item active">Entry</li>
        </ol>
    </div>

    <div class="page-content">
        <form action="{{url('/pos/issue/update/'.$Issuem->id)}}" method="POST" data-toggle="validator" novalidate="true"
            id="form_id">
            @csrf
            <div class="panel">
                <div class="panel-body">


                    <div class="row ">
                        <div class="col-lg-12 offset-lg-1">
                            <!-- Html View Load  -->
                            <input type="hidden" id="issue_date" name="issue_date" value="{{ $Issuem->issue_date }}">
                            {!! HTML::forCompanyFeild($Issuem->company_id) !!}


                            <div class="row">
                                <!--Form Left-->
                                <div class="col-lg-6">
                                    {{-- <div class="form-row align-items-center ">

                                        <label class="col-lg-3 input-title RequiredStar">Branch From</label>

                                        <div class="col-lg-5">
                                            <div class="form-group">
                                                <div class="input-group ">
                                                    <select class="form-control clsSelect2"
                                                        name="branch_from" id="branch_from" required
                                                        data-error="Please select Product group"
                                                        onchange="fnGenBillNo(this.value);">
                                                        <option value="0">{{ sprintf("%04d", 0)." - "}}Head Office
                                                        </option>
                                                        @foreach($BranchData as $BData)
                                                        <option value="{{ $BData->id }}">
                                                            {{ sprintf("%04d", $BData->branch_code)." - ".$BData->branch_name }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                        </div>
                                    </div> --}}
                                    <input type="hidden" name="branch_from" id="branch_from" 
                                    value="{{ Common::getBranchId() }}">

                                    <div class="form-row align-items-center ">
                                        <label class="col-lg-3 input-title RequiredStar">Branch To</label>

                                        <div class="col-lg-5">
                                            <div class="form-group">
                                                <div class="input-group ">
                                                    <select class="form-control clsSelect2" 
                                                            name="branch_to" id="branch_to" required
                                                        data-error="Please select Product group"
                                                        onchange="fnGenBillNo(this.value);">
                                                        <option value="">Select Branch</option>
                                                        @foreach($BranchData as $BData)
                                                        <option value="{{ $BData->id }}"
                                                            {{ ($Issuem->branch_to == $BData->id) ? 'selected="selected"' : '' }}>
                                                            {{ sprintf("%04d", $BData->branch_code)." - ".$BData->branch_name }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center ">

                                        <label class="col-lg-3 input-title">Branch Order No</label>
                                        <div class="col-lg-5">
                                            <div class="form-group">
                                                <div class="input-group ">
                                                    <input type="text" class="form-control round"
                                                        placeholder="Enter Issue Order No" name="branch_order_no"
                                                        id="branch_order_no" value="{{$Issuem->branch_order_no}}">
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                        </div>
                                    </div>



                                    <!--    end left -->
                                </div>

                                <!--Form Right-->
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

                                    <div class="form-row align-items-center ">

                                        <label class="col-lg-3 input-title">Order No</label>
                                        <div class="col-lg-5">
                                            <div class="form-group">
                                                <div class="input-group ">
                                                    <input type="text" class="form-control round" name="order_no"
                                                        id="order_no" placeholder="Enter Order No."
                                                        value="{{$Issuem->order_no}}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!--  end Right    -->
                                </div>


                            </div>



                        </div>
                    </div>

                </div>
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
                            <strong class="text-danger" style="color: #3e8ef7;">Current Stock: <label id="current_stock"></label></strong>
                            <input type="hidden" id="stock_quantity_0">
                        </div>
                        {{-- <div class="col-lg-6 text-right">
                            <strong style="color: #3e8ef7;">Upcomming Stock:<label id="upcomming_stock"></label></strong>
                        </div> --}}
                    </div>


                    <div class="table-responsive">
                        <table
                            class="table table-hover table-striped table-bordered w-full text-center table-responsive my-custom-scrollbar"
                            id="purchaseTable">
                            <thead>
                                <tr>
                                    <!-- <th width="40%">Barcode</th> -->
                                    <th width="35%" class="RequiredStar">Product Name</th>
                                    <th width="10%" class="RequiredStar">Quantity</th>
                                    <!--<th width="10%" class="RequiredStar">Order Quantity</th>-->
                                    <!--<th width="10%" class="RequiredStar">Receive Quantity</th>-->
                                    <th width="15%">Cost Price</th>
                                    <th width="15%">Total</th>
                                    <th width="5%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                            $i = 0;
                            $TableID = "purchaseTable";

                            $ColumnName = "product_id_arr[]&sys_barcode_arr[]&product_name_arr[]&product_quantity_arr[]&ordered_quantity_arr[]&received_quantity_arr[]&unit_cost_price_arr[]&total_cost_price_arr[]";

                            $ColumnID = "product_id_&sys_barcode_&product_name_&product_quantity_&ordered_quantity_&received_quantity_&unit_cost_price_&total_cost_price_&deleteRow_";
                            // 'company_id'=> $CompanyID,
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

                                    <td>
                                        <!-- Input for System barcode  -->
                                        <!-- <input type="hidden" name="sys_barcode_arr[]" id="sys_barcode_0"> -->

                                        <!-- Input for Product Name  -->
                                        <!--  <input type="hidden"  name="product_name_arr[]" id="product_name_0">  -->
                                        {{-- name="product_quantity_arr[]" --}}
                                        <input type="number" id="product_quantity_0"
                                            class="form-control round clsQuantity" placeholder="Enter Quantity"
                                            value="0" onkeyup="fnTotalQuantity(); fnTtlProductPrice(0); fnCheckQuantity(0);" required
                                            min="1" readonly>
                                    </td>

                                    <!-- <td>
                                    <input type="hidden" name="ordered_quantity_arr[]" id="ordered_quantity_0" class="form-control round clsOrderQuantity"
                                    value="0" onkeyup="fnTotalOrderQuantity();">
                                </td> -->
                                    <!-- <td>
                                    <input type="hidden" name="received_quantity_arr[]" id="received_quantity_0" class="form-control round clsRcvQuantity"
                                    value="0" onkeyup="fnTotalReceiveQuantity();">
                                </td> -->
                                    <td>
                                        {{-- name="unit_cost_price_arr[]" --}}
                                        <input type="number" id="unit_cost_price_0" class="form-control round" value="0"
                                            required min="1" readonly>
                                    </td>

                                    <td>
                                        {{-- name="total_cost_price_arr[]" --}}
                                        <input type="number" id="total_cost_price_0"
                                            class="form-control round ttlAmountCls" value="0" min="1" readonly>
                                    </td>

                                    <td>
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
                                    <td class="input-group barcodeWidth">
                                        <input type="hidden" name="product_id_arr[]" value="{{ $Data->product_id }}">

                                        @foreach($ProductList as $ProductInfo)
                                        @if($ProductInfo->id == $Data->product_id)
                                        <input type="text" class="form-control round"
                                            value="{{ $ProductInfo->product_name . ' (' . $ProductInfo->sys_barcode . ')' }} "
                                            readonly>
                                        @endif
                                        @endforeach
                                    </td>

                                    <td>
                                        <!-- Input for System barcode  -->
                                        <!-- <input type="hidden" name="sys_barcode_arr[]" id="sys_barcode_0"> -->

                                        <!-- Input for Product Name  -->
                                        <!--  <input type="hidden"  name="product_name_arr[]" id="product_name_0">  -->

                                        <input type="number" name="product_quantity_arr[]"
                                            id="product_quantity_{{ $i }}" class="form-control round clsQuantity"
                                            placeholder="Enter Quantity" value="{{ $Data->product_quantity }}"
                                            onkeyup="fnTotalQuantity(); fnTtlProductPrice({{$i}});" required min="1">
                                    </td>

                                    <!-- <td>
                                            <input type="hidden" name="ordered_quantity_arr[]" id="ordered_quantity_0" class="form-control round clsOrderQuantity"
                                            value="0" onkeyup="fnTotalOrderQuantity();">
                                        </td> -->
                                    <!-- <td>
                                            <input type="hidden" name="received_quantity_arr[]" id="received_quantity_0" class="form-control round clsRcvQuantity"
                                            value="0" onkeyup="fnTotalReceiveQuantity();">
                                        </td> -->
                                    <td>
                                        <input type="number" name="unit_cost_price_arr[]" id="unit_cost_price_{{ $i }}"
                                            class="form-control round" value="{{ $Data->unit_cost_price }}" required
                                            min="1" readonly>
                                    </td>

                                    <td>
                                        <input type="number" name="total_cost_price_arr[]"
                                            id="total_cost_price_{{ $i }}" class="form-control round ttlAmountCls"
                                            value="{{ $Data->total_cost_amount }}" min="1" readonly>
                                    </td>

                                    <td>

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
                                            <!-- <input type="hidden" name="total_ordered_quantity" id="total_ordered_quantity" value="0"> -->
                                            <!-- <input type="hidden" name="total_received_quantity" id="total_received_quantity" value="0"> -->
                                        </td>
                                        <td width="10%" id="tdTotalQuantity" style="font-weight: bold;">0</td>

                                        <td width="20%">
                                            <h5>Total Amount</h5>
                                            <input type="hidden" name="total_amount" id="total_amount" value="0"
                                                min="1">
                                        </td>
                                        <td width="25%" id="tdTotalAmount" style="font-weight: bold;">0</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row ">
                        <div class="col-lg-12 offset-lg-3">

                            <div class="form-row align-items-center ">

                                <div class="col-lg-6">
                                    <div class="form-group d-flex justify-content-center">
                                        <div class="example example-buttons">
                                            <a href="#" class="btn btn-default btn-round">Close</a>
                                            <button type="submit" class="btn btn-primary btn-round"
                                                id="submitButton">Save</button>
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
    </div>

</div>

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
    //     '{{base64_encode("pos_p_groups")}}',
    //     '{{base64_encode("company_id")}}',
    //     '{{base64_encode("id,group_name")}}',
    //     '{{url("/ajaxSelectBox")}}',
    //     );

    // });

    fnTotalQuantity();
    fnTotalAmount();
});

// product load function
function fnProductLoad() {
    var CompanyID = $('#company_id').val();
    var SupplierID = $('#supplier_id').val();
    var GroupID = $('#prod_group_id').val();
    var CategoryID = $('#prod_cat_id').val();
    var SubCatID = $('#prod_sub_cat_id').val();
    var ModelID = $('#prod_model_id').val();
    var firstRowFirstColId = $('#purchaseTable tbody tr td:first-child select').attr('id');

    console.log(firstRowFirstColId);

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
    if ($('#' + ColumnID[0] + 0).val() != '' && $('#product_quantity_0').val() > 0
            && $('#stock_quantity_0').val() != 0 && ($('#stock_quantity_0').val() >= $('#product_quantity_0').val())) {

        // var RowCount = $('#' + RowCountID).val();
        // RowCount++;
        // $('#' + RowCountID).val(RowCount);

        // console.log(ProductIDArr);

        // $('#' + ColumnID[0] + 0).val()
        //             var fruits = ["Banana", "Orange", "Apple", "Mango"];
        // var n = fruits.includes("Orangess");
        // if(){

        // }

        var TotalRowCount = $('#' + TotalRowID).val();
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

        var StockQuantity = $('#stock_quantity_0').val();


        var html = '<tr>';

        html += '<td class="input-group barcodeWidth" width="35%">';
        // html +='<select class="form-control clsSelect2"  name="' + ColumnName[0] + '" id="' + ColumnID[0] + TotalRowCount + '" onchange="fnGetSelectedValue(' + TotalRowCount + ');" required >'+
        //         '<option value="">Select Product</option>'+
        //         '</select>';
        html += '<input type="hidden" name="' + ColumnName[0] + '" value="' + ProductID + '">';
        html += '<input type="hidden" id="stock_quantity_'+ TotalRowCount +'" value="' + StockQuantity + '">';
        html += '<input type="text" class="form-control round" value="' + ProductName + '(' + ProductSysBarcode + ')' +
            '" readonly >';
        html += '</td>';
        html += '<td width="10%">';
        // // Input Feild For sys_barcode
        // html += '<input type="hidden" name="' + ColumnName[1] + '" id="' + ColumnID[1] + TotalRowCount + '">';
        // // Input Feild For sys_barcode
        // html += '<input type="hidden" name="' + ColumnName[2] + '" id="' + ColumnID[2] + TotalRowCount + '">';

        html += '<input type="number" name="' + ColumnName[3] + '" id="' + ColumnID[3] + TotalRowCount +
            '" class="form-control round clsQuantity" value="' + ProductQuantity +
            '" onkeyup="fnTotalQuantity(); fnTtlProductPrice(' + TotalRowCount + '); fnCheckQuantity(' + TotalRowCount + ');" required min="1">';
        html += '</td>';
        // // input feild for ordered_qtn
        // html += '<td width="10%">'+
        //             '<input type="number" name="' + ColumnName[4] + '" id="' + ColumnID[4] + TotalRowCount + '" class="form-control round clsOrderQuantity" value="0" onkeyup="fnTotalOrderQuantity();" required min="1">'+
        //         '</td>';

        // // input feild for received_qtn
        // html += '<td width="10%">'+
        //             '<input type="number" name="' + ColumnName[5] + '" id="' + ColumnID[5] + TotalRowCount + '" class="form-control round clsRcvQuantity" value="0" onkeyup="fnTotalReceiveQuantity();" required min="1">'+
        //         '</td>';

        html += '<td width="15%">' +
            '<input type="number" name="' + ColumnName[6] + '" id="' + ColumnID[6] + TotalRowCount +
            '" class="form-control round" value="' + ProductCostPrice + '" readonly required min="1">' +
            // onkeyup="fnTtlProductPrice('+TotalRowCount+');"
            '</td>';
        html += '<td width="15%">' +
            '<input type="number" name="' + ColumnName[7] + '" id="' + ColumnID[7] + TotalRowCount +
            '" class="form-control round ttlAmountCls" value="' + ProductAmount + '" readonly>' +
            '</td>';
        html += '<td width="5%">' +
            '<a href="javascript:void(0)" class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center" onclick="btnRemoveRow(this);">' +
            ' <i class="icon fa fa-times align-items-center"></i>' +
            '</a>' +
            '</td>';
        html += '</tr>';
        
        // $('#' + TableID).append(html);
        $('#' + TableID +' tbody').find('tr:first').after(html);

        $('#' + ColumnID[0] + 0).val('');
        $('#' + ColumnID[0] + 0).trigger('change');
        $('#' + ColumnID[3] + 0).val(0);
        $('#' + ColumnID[6] + 0).val(0);
        $('#' + ColumnID[7] + 0).val(0);
        $('#' + ColumnID[3] + 0).prop('readonly', true);

        $('#current_stock').html(0);
        $('#upcomming_stock').html(0);

    } else {

        if ($('#' + ColumnID[0] + 0).val() == '') {
            swal({
                icon: 'error',
                title: 'Oops...',
                text: 'Please select product!',
            });
        } 
        else if($('#product_quantity_0').val() <= 0){
            swal({
                icon: 'error',
                title: 'Oops...',
                text: 'Quantity must be greater than 0!',
            });
        }
        else if ($('#stock_quantity_0').val() == 0) {

            swal({
                icon: 'error',
                title: 'Oops...',
                text: 'Empty Stock !! Please try next time.',
            });

        } 
        else if($('#stock_quantity_0').val() < $('#product_quantity_0').val()) {

            swal({
                icon: 'error',
                title: 'Oops...',
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

    console.log(Row);

    var ProductQtn = $('#product_quantity_' + Row).val();
    var ProductPrice = $('#unit_cost_price_' + Row).val();
    var TotalProductPrice = (Number(ProductQtn) * Number(ProductPrice));
    $('#total_cost_price_' + Row).val(TotalProductPrice.toFixed(2));
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

    // //--------------------------- T/A After Discount
    // fnCalDiscount($('#discount_rate').val());

    // //-----------------------------calculate vat amount
    // fnCalVat($('#vat_rate').val());

    // //-----------------------------calculate Due amount
    // fnCalDue($('#paid_amount').val());
}

$('#submitButton').on('click', function(event) {
    event.preventDefault();
    if ($('#total_amount').val() > 0 && $('#product_id_0').val() == '') {

        $('#form_id').submit();
    } else {

        if ($('#total_amount').val() <= 0) {
            swal({
                icon: 'error',
                title: 'Oops...',
                text: 'Total payable amount must be gratter than zero !!',
            });
        } else {
            swal({
                icon: 'error',
                title: 'Oops...',
                text: 'Product Entry must be empty!!',
            });
        }

    }
});

function fnGenBillNo(BranchId) {
    if (BranchId != '') {
        $.ajax({
            method: "GET",
            url: "{{ url('/ajaxGBillIssue') }}",
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
                    $('#current_stock').html(data);
                    $('#stock_quantity_0').val(data);

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
            title: 'Oops...',
            text: 'Empty Stock !! Please try next time.',
        });

    } else if(StockQuantity < TypeQuantity) {

        swal({
            icon: 'error',
            title: 'Oops...',
            text: 'Stock must be less than ' + TypeQuantity,
        });
    }
}

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
</script>

@endsection