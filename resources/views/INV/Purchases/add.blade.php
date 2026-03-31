@extends('Layouts.erp_master')
@section('content')
@include('elements.pop.purchase_modal')

<?php
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
use App\Services\InvService as INVS;
?>

<!-- Page -->
<form method="post" data-toggle="validator" novalidate="true" id="purchase_form">
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
                    {!! HTML::forBranchFeild(false,'branch_id','branch_id',null,'','For Branch') !!}
                </div>
            </div>

            <div class="row">
                <!--Form Left-->
                <div class="col-lg-6">
                    <div class="form-row align-items-center">
                        <label class="col-lg-4 input-title RequiredStar" for="groupName">Bill No</label>
                        <div class="col-lg-7 form-group">
                            <div class="input-group">
                                <input type="text" name="bill_no" id="bill_no" class="form-control round"
                                    value="{{ INVS::generateBillPurchase(Common::getBranchId()) }}" required readonly>
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row align-items-center">
                            <div class="col-lg-4">
                                <label for="textChalanNumber" class="input-title">invoice No</label>
                            </div>
                            <div class="col-lg-7">
                                <input type="text" class="form-control round" placeholder="Enter Invoice No"
                                    name="invoice_no" id="invoice_no">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row align-items-center">
                            <div class="col-lg-4">
                                <label class="input-title" for="textSupplier">Supplier</label>
                            </div>
                            <div class="col-lg-6 input-group addSupplier">

                                <select class="form-control clsSelect2"
                                     name="supplier_id" id="supplier_id"
                                    onchange="fnSupplierNameLoad(); fnProductLoad();">
                                    <option value="">Select Supplier</option>
                                    @foreach($SupplierData as $SData)
                                    <option value="{{ $SData->id }}">{{ $SData->sup_name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="supplier_id" id="supplier_id_copy">
                            </div>
                            <div class="addButtonMobile">
                                <div class="text-center">
                                    <a href="javascript:void(0)" class="btn btn-primary btn-sm btn-round"
                                        id="btnAddSupplier" data-toggle="modal"
                                        data-target="#modalSupplierForm">Add</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row align-items-center">
                            <div class="col-lg-4">
                                <label for="textContactPerson" class="input-title">Contact Person</label>
                            </div>
                            <div class="col-lg-7">
                                <input type="text" class="form-control round" placeholder="Enter Contact Person"
                                    name="contact_person" id="contact_person" readonly>
                            </div>
                        </div>
                    </div>


                </div>
                <!--Form Right-->
                <div class="col-lg-6">
                    <div class="form-group">
                        <div class="row align-items-center">
                            <div class="col-lg-4 ">
                                <label for="datePurchases" class="input-title">Purchase Date</label>
                            </div>
                            <div class="col-lg-7 input-group">
                                <div class="input-group-prepend ">
                                    <span class="input-group-text ">
                                        <i class="icon wb-calendar round" aria-hidden="true"></i>
                                    </span>
                                </div>

                                <input type="text" name="purchase_date" id="purchase_date" class="form-control round" value="{{ Common::systemCurrentDate() }}" readonly="true"
                                    required>
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row align-items-center">
                            <div class="col-lg-4">
                                <label for="textDeliveryNumber" class="input-title">Delivery No</label>
                            </div>
                            <div class="col-lg-7">
                                <input type="text" class="form-control round" placeholder="Enter Delivery No"
                                    name="delivery_no" id="delivery_no">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row align-items-center">
                            <?php
                                $orders = App\Model\INV\OrderMaster::where([['is_approve', 1], ['is_delivered', 0], ['is_completed', 0], ['is_active', 1], ['is_delete', 0]])->get();
                            ?>
                            <div class="col-lg-4">
                                <label for="textOrderNumber" class="input-title">Order No</label>
                            </div>
                            <div class="col-lg-7">
                                <select class="form-control clsSelect2"
                                id="order_no" onchange="fnGetOrderProducts()">
                                    <option value="">Select Order No</option>
                                    @foreach($orders as $order)
                                    <option value="{{ $order->order_no }}">{{ $order->order_no }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="order_no" id="order_no_copy">
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

                <div class="col-lg-1 labelPercentSearch ">
                    <label for="textGroup" class="input-title">Group</label>
                </div>
                <div class="col-lg-2 inputPercentSearch input-group">
                    <!-- {{-- Query for get all group --}} -->
                    <?php $GroupList = Common::ViewTableOrder('inv_p_groups',
                                    ['is_delete' => 0],
                                    ['id', 'group_name'],
                                    ['group_name', 'ASC']) ?>
                    <select class="form-control clsSelect2"
                         id="prod_group_id" onchange="fnAjaxSelectBox('prod_cat_id',
                                        this.value,
                            '{{ base64_encode("inv_p_categories")}}',
                            '{{base64_encode("prod_group_id")}}',
                            '{{base64_encode("id,cat_name")}}',
                            '{{url("/ajaxSelectBox")}}');
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
                    <!-- {{-- Query for get all category --}} -->
                    <?php $CategoryList = Common::ViewTableOrder('inv_p_categories',
                                    ['is_delete' => 0],
                                    ['id', 'cat_name'],
                                    ['cat_name', 'ASC']) ?>
                    <select class="form-control clsSelect2"
                         id="prod_cat_id" onchange="fnAjaxSelectBox('prod_sub_cat_id',
                                        this.value,
                            '{{base64_encode("inv_p_subcategories")}}',
                            '{{base64_encode("prod_cat_id")}}',
                            '{{base64_encode("id,sub_cat_name")}}',
                            '{{url("/ajaxSelectBox")}}');
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
                    <!-- {{-- Query for get all Sub-category --}} -->
                    <?php $SubCategoryList = Common::ViewTableOrder('inv_p_subcategories',
                                    ['is_delete' => 0],
                                    ['id', 'sub_cat_name'],
                                    ['sub_cat_name', 'ASC']) ?>
                    <select class="form-control clsSelect2"
                         id="prod_sub_cat_id" onchange="fnAjaxSelectBox('prod_model_id',
                                        this.value,
                            '{{base64_encode("inv_p_models")}}',
                            '{{base64_encode("prod_sub_cat_id")}}',
                            '{{base64_encode("id,model_name")}}',
                            '{{url("/ajaxSelectBox")}}');
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

            <!-- <div class="row">
                <div class="col-lg-6 text-left">
                    <strong class="text-danger">Current Stock: <label id="current_stock"></label></strong>
                    <input type="hidden" id="stock_quantity_0">
                </div>
            </div> -->

            <table class="table table-hover table-striped table-bordered w-full text-center table-responsive"
                id="purchaseTable">
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

                        $ColumnName = "product_id_arr[]&sys_barcode_arr[]&product_name_arr[]&product_quantity_arr[]&ordered_quantity_arr[]&received_quantity_arr[]&unit_cost_price_arr[]&total_cost_price_arr[]";

                        $ColumnID = "product_id_&sys_barcode_&product_name_&product_quantity_&ordered_quantity_&received_quantity_&unit_cost_price_&total_cost_price_&deleteRow_";
                    // 'company_id'=> $CompanyID,
                        $ProductList = Common::ViewTableOrder('inv_products',
                                        ['is_delete' => 0],
                                        ['id', 'product_name', 'cost_price', 'product_code'],
                                        ['product_name', 'ASC']);
                    ?>


                    <tr>
                        <td width="50%" class="input-group barcodeWidth text-left">
                            <select id="product_id_0" class="form-control clsProductSelect" style="width: 100%;">
                                <option value="">Select Product</option>
                                @foreach($ProductList as $ProductInfo)
                                <option value="{{ $ProductInfo->id }}" pcprice="{{ $ProductInfo->cost_price }}"
                                    pname="{{ $ProductInfo->product_name }}"
                                    pcode="{{ $ProductInfo->product_code }}">
                                    {{  $ProductInfo->product_code ? $ProductInfo->product_name ." - " . ' (' . $ProductInfo->product_code . ')' : $ProductInfo->product_name }}
                                </option>
                                @endforeach
                            </select>
                        </td>

                        <td width="15%">
                            <!-- Input for System barcode  -->
                            <!-- <input type="hidden" name="sys_barcode_arr[]" id="sys_barcode_0"> -->

                            <!-- Input for Product Name  -->
                            <!--  <input type="hidden"  name="product_name_arr[]" id="product_name_0">  -->

                            <!-- {{-- name="product_quantity_arr[]"  --}} -->
                            <input type="number" id="product_quantity_0" class="form-control round clsQuantity text-center"
                                placeholder="Enter Quantity" value="0"
                                onkeyup="fnTotalQuantity(); fnTtlProductPrice(0);" required min="1" readonly>
                        </td>

                        <!-- <td>
                                <input type="hidden" name="ordered_quantity_arr[]" id="ordered_quantity_0" class="form-control round clsOrderQuantity"
                                value="0" onkeyup="fnTotalOrderQuantity();">
                            </td> -->
                        <!-- <td>
                                <input type="hidden" name="received_quantity_arr[]" id="received_quantity_0" class="form-control round clsRcvQuantity"
                                value="0" onkeyup="fnTotalReceiveQuantity();">
                            </td> -->
                        <td width="15%">
                            <!-- {{-- name="unit_cost_price_arr[]"  --}} -->
                            <input type="number" id="unit_cost_price_0" class="form-control round text-right" value="0"
                                required min="1" readonly>
                        </td>

                        <td width="16%">
                            <!-- {{-- name="total_cost_price_arr[]"  --}} -->
                            <input type="number" id="total_cost_price_0" class="form-control round ttlAmountCls text-right"
                                value="0" min="1" readonly>
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

        </div>
        <!--End Panel 2-->
    </div>


    <div class="panel">
        <div class="panel-body">
            <div class="row">
                <!--Form Left-->
                <div class="col-lg-6">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <div class="row align-items-center">
                                <div class="col-lg-2">
                                    <label for="textPurchaseRemarks" class="input-title">Remarks</label>
                                </div>
                                <div class="col-lg-9">
                                    <textarea class="form-control" id="textPurchaseRemarks" name="remarks"
                                        rows="14" placeholder="Enter Remarks"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--Form Right-->
                <div class="col-lg-6">

                    <div class="form-group">
                        <div class="row align-items-center">
                            <div class="col-lg-4">
                                <label class="input-title">Discount(%)</label>
                            </div>
                            <div class="col-lg-7">
                                <input type="number" name="discount_rate" id="discount_rate"
                                    class="form-control round" value="0" onkeyup="fnCalDiscount(this.value);"
                                    autocomplete="off">

                                <!-- {{-- hidden fields for discount amount --}} -->
                                <input type="hidden" name="discount_amount" id="discount_amount" value="0">
                            </div>
                        </div>
                    </div>


                    <div class="form-group">
                        <div class="row align-items-center">
                            <div class="col-lg-4">
                                <label class="input-title">T/A After Discount</label>
                            </div>
                            <div class="col-lg-7">
                                <input type="number" name="ta_after_discount" id="ta_after_discount"
                                    class="form-control round text-right" value="0" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row align-items-center">
                            <div class="col-lg-4">
                                <label class="input-title">VAT(%)</label>
                            </div>
                            <div class="col-lg-7">
                                <input type="number" name="vat_rate" id="vat_rate" class="form-control round"
                                    value="0" onkeyup="fnCalVat(this.value);" autocomplete="true">

                                <input type="hidden" name="vat_amount" id="vat_amount" value="0">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row align-items-center">
                            <div class="col-lg-4">
                                <label class="input-title">Total Payable Amount</label>
                            </div>
                            <div class="col-lg-7">
                                <input type="number" name="total_payable_amount" id="total_payable_amount"
                                    class="form-control round text-right" value="0" readonly required min="1">
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row align-items-center">
                            <div class="col-lg-4">
                                <label class="input-title RequiredStar">Paid Amount</label>
                            </div>
                            <div class="col-lg-7">
                                <input type="number" name="paid_amount" id="paid_amount"
                                    class="form-control round text-right" value="0" onkeyup="fnCalDue(this.value);"
                                    required min="1" autocomplete="true">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row align-items-center">
                            <div class="col-lg-4">
                                <label class="input-title">Due Amount</label>
                            </div>
                            <div class="col-lg-7">
                                <input type="number" name="due_amount" id="due_amount"
                                    class="form-control round text-right" value="0" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row align-items-center">
                <div class="col-lg-12">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();"
                                      class="btn btn-default btn-round d-print-none">Back</a>

                                <button type="submit" class="btn btn-primary btn-round"
                                id="submitButtonforPurchase">Save</button>

                        </div>
                    </div>
                </div>
            </div>
            <!--End Panel Body-->
        </div>
        <!--End Panel 3-->
    </div>
</form>
<!--End Page-->
</div>

<script type="text/javascript">

    $(document).ready(function() {

        // Pop Up Modal for adding supplier
        $('#submitButtonSupPOP').click(function() {

            // var company_id = $('#company_id').val();
            var sup_name = $('#sup_name').val();
            var supplier_type = $('#supplier_type').val();
            var comission_percent = $('#comission_percent').val();
            var sup_comp_name = $('#sup_comp_name').val();
            var sup_email = $('#sup_email').val();
            var sup_phone = $('#sup_phone').val();
            var sup_addr = $('#sup_addr').val();
            var sup_web_add = $('#sup_web_add').val();
            var sup_desc = $('#sup_desc').val();
            var sup_ref_no = $('#sup_ref_no').val();
            var sup_attentionA = $('#sup_attentionA').val();



            if (sup_name != "" && supplier_type != "" && sup_comp_name != "" && sup_email != "" &&
                sup_phone != "") {

                $.ajax({
                    url: "{{ url('inv/purchase/popUpSupplierData') }}",
                    type: "POST",
                    data: {
                        _token: $("#csrf").val(),
                        type: 1,
                        sup_name: sup_name,
                        supplier_type: supplier_type,
                        comission_percent: comission_percent,
                        sup_comp_name: sup_comp_name,
                        sup_email: sup_email,
                        sup_phone: sup_phone,
                        sup_addr: sup_addr,
                        sup_web_add: sup_web_add,
                        sup_desc: sup_desc,
                        sup_ref_no: sup_ref_no,
                        sup_attentionA: sup_attentionA,
                    },
                    cache: false,
                    success: function(dataResult) {

                        var dataResult = JSON.parse(dataResult);
                        if (dataResult.statusCode == 200) {

                            $('#modalSupplierForm').modal('toggle');
                            $("#supModalFormId").trigger("reset");
                            swal("Successfully Inserted!", "", "success");
                        } else if (dataResult.statusCode == 201) {

                            swal("Unsuccessfully to Insert!", "", "error");
                        }

                    }
                });
            } else {
                swal({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please fillup all fields!',
                });
                $('#modalSupplierForm').find(".form-control:invalid").first().focus(); // show all data error
                $('#modalSupplierForm').find(".form-control:invalid").focusout();  // Focus on first error
            }
        });
        /* Supplier Type */
        $('#supplier_type').change(function() {

            if ($(this).val() == '2') {
                $('#comissionIDinput').show();
            } else {
                $('#comissionIDinput').hide();
            }
        });

    });


    //Load Products in Product Table
    function fnProductLoad() {
        var CompanyID = $('#company_id').val();
        var SupplierID = $('#supplier_id').val();
        var GroupID = $('#prod_group_id').val();
        var CategoryID = $('#prod_cat_id').val();
        var SubCatID = $('#prod_sub_cat_id').val();
        var ModelID = $('#prod_model_id').val();
        var firstRowFirstColId = $('#purchaseTable tbody tr td:first-child select').attr('id');

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
            var pQtn = $(this).find("option:selected").attr('prod_qtn');
            $('#unit_cost_price_0').val(selProdCostPrice);
            $('#product_quantity_0').val(pQtn);

            fnTotalQuantity();
            fnTtlProductPrice(0);
        } else {
            $('#product_quantity_0').prop('readonly', true);
            $('#unit_cost_price_0').val(0);
            //$('#product_id_0').val('');
        }
    });

    var ProductIDArr = [];

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

       if ($('#' + ColumnID[0] + 0).val() != '' && $('#product_quantity_0').val() > 0 &&
       (Number($('#product_id_0').find("option:selected").attr('prod_qtn') >= Number($('#product_quantity_0').val())) || $('#order_no').val() == '' ) ) {

            var TotalRowCount = $('#' + TotalRowID).val();
            /*
            marge two row if same product found
            */

            var ProductQuantity = $('#' + ColumnID[3] + 0).val();

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

                $('#product_quantity_' + rowNumber).val(ProductQuantity);
                $('#' + ColumnID[0] + 0).val('');
                $('#' + ColumnID[0] + 0).trigger('change');
                $('#' + ColumnID[3] + 0).val(0);
                $('#' + ColumnID[4] + 0).val(0);
                $('#' + ColumnID[5] + 0).val(0);
                $('#' + ColumnID[7] + 0).val(0);
                $('#' + ColumnID[3] + 0).prop('readonly', true);

                // $('#current_stock').html(0);
                // $('#upcomming_stock').html(0);

                fnTotalQuantity();
                fnTtlProductPrice(rowNumber);
            }

            else {
                TotalRowCount++;
                $('#' + TotalRowID).val(TotalRowCount);
                var ProductID = $('#' + ColumnID[0] + 0).val();
                var ProductName = $('#' + ColumnID[0] + 0).find("option:selected").attr('pname');
                var prod_qtn = $('#' + ColumnID[0] + 0).find("option:selected").attr('prod_qtn');
                // var ProductBarcode = $('#' + ColumnID[0] + 0).find("option:selected").attr('pbarcode');
                var productCode = $('#' + ColumnID[0] + 0).find("option:selected").attr('pcode');
                var ProductCostPrice = $('#' + ColumnID[0] + 0).find("option:selected").attr('pcprice');
                var ProductSalePrice = $('#' + ColumnID[0] + 0).find("option:selected").attr('psprice');
                var ProductQuantity = $('#' + ColumnID[3] + 0).val();
                var ProductAmount = $('#' + ColumnID[7] + 0).val();

                var html = '<tr>';

                html += '<td class="input-group barcodeWidth" width="35%">';
                html += '<input type="hidden" id="product_id_'+ TotalRowCount +'" name="' + ColumnName[0] + '" value="' + ProductID + '">';
                html += '<input type="hidden" id="remain_quantity_'+ TotalRowCount +'" value="' + prod_qtn + '">';
                if(productCode){
                  html += '<input type="text" class="form-control round text-left" value="' + ProductName + '(' + productCode + ')' +
                      '" readonly >';
                }else{
                  html += '<input type="text" class="form-control round text-left" value="' + ProductName +  '" readonly >';
                }

                html += '</td>';
                html += '<td width="10%">';

                html += '<input type="number" name="' + ColumnName[3] + '" id="' + ColumnID[3] + TotalRowCount +
                    '" class="form-control round clsQuantity text-center" value="' + ProductQuantity +
                    '" onkeyup="fnTotalQuantity();fnTtlProductPrice(' + TotalRowCount + '); fnCheckRequisitionQtn(this.value, '+TotalRowCount+')"" required min="1">';
                html += '</td>';

                html += '<td width="15%">' +
                    '<input type="number" name="' + ColumnName[6] + '" id="' + ColumnID[6] + TotalRowCount +
                    '" class="form-control round text-right" value="' + ProductCostPrice + '" readonly required min="1">' +
                    '</td>';
                html += '<td width="15%">' +
                    '<input type="number" name="' + ColumnName[7] + '" id="' + ColumnID[7] + TotalRowCount +
                    '" class="form-control round ttlAmountCls text-right" value="' + ProductAmount + '" readonly>' +
                    '</td>';
                html += '<td width="5%">' +
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
            else if($('#product_quantity_0').val() <= 0) {
                swal({
                    icon: 'error',
                    title: 'Error',
                    text: 'Quantity must be greater than 0!',
                });
            }

            else if($('#order_no option:selected').val() != ''){
                swal({
                    icon: 'error',
                    title: 'Error',
                    text: 'Quantity must be less than or equal ' + $('#product_id_0').find("option:selected").attr('prod_qtn'),
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

    function fnDisableUnchangeableFields(){

        //transfer data into hidden fields
        $('#order_no_copy').val($('#order_no').val());
        $('#supplier_id_copy').val($('#supplier_id').val());

        //disabled fields
        $('#order_no').prop('disabled', true);
        $('#supplier_id').prop('disabled', true);
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

    /* Pop Up Supplier Start */
    $(document).ready(function() {

        $('.clsProductSelect').select2();

    });
    /* Pop Up Supplier End */


    $('#company_id').change(function() {

        // fnProductLoad();
        //get company id into the popup for insert supplier
        $('#company_id').val(this.value);

        fnAjaxSelectBox(
            'supplier_id',
            this.value,
            '{{base64_encode("inv_suppliers")}}',
            '{{base64_encode("company_id")}}',
            '{{base64_encode("id,sup_name")}}',
            '{{url("/ajaxSelectBox")}}',
        );

        fnAjaxSelectBox(
            'branch_id',
            this.value,
            '{{base64_encode("gnl_branchs")}}',
            '{{base64_encode("company_id")}}',
            '{{base64_encode("id,branch_name")}}',
            '{{url("/ajaxSelectBox")}}',
        );

        // fnAjaxSelectBox(
        //         'prod_group_id',
        //         this.value,
        //         '{{base64_encode("inv_p_groups")}}',
        //         '{{base64_encode("company_id")}}',
        //         '{{base64_encode("id,group_name")}}',
        //         '{{url("/ajaxSelectBox")}}',
        //         );
    });

    function fnGenBillNo(BranchId) {
        if (BranchId != '') {
            $.ajax({
                method: "GET",
                url: "{{ url('/ajaxGBillPurchase') }}",
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

    function fnCheckQuantity(Row) {

        var TypeQuantity = Number($('#product_quantity_'+ Row).val());
        var chkFlag = true;
    }


    function fnTotalQuantity() {

        var totalQtn = 0;
        $('.clsQuantity').each(function() {
            totalQtn = Number(totalQtn) + Number($(this).val());
        });
        $('#total_quantity').val(totalQtn);
        $('#tdTotalQuantity').html(totalQtn);
    }

    /* When Need Order  Quantity then use this function */
    function fnTotalOrderQuantity() {

        var totalOrderQtn = 0;
        $('.clsOrderQuantity').each(function() {
            totalOrderQtn = Number(totalOrderQtn) + Number($(this).val());
        });
        $('#total_ordered_quantity').val(totalOrderQtn);
    }

    /* When Need Receive Quantity then use this function */
    function fnTotalReceiveQuantity() {

        var totalRcvQtn = 0;
        $('.clsRcvQuantity').each(function() {
            totalRcvQtn = Number(totalRcvQtn) + Number($(this).val());
        });
        //value set for order qtn & rcv qtn
        $('#total_received_quantity').val(totalRcvQtn);
    }

    function fnTtlProductPrice(Row) {

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

        //--------------------------- T/A After Discount
        fnCalDiscount($('#discount_rate').val());

        //-----------------------------calculate vat amount
        fnCalVat($('#vat_rate').val());

        //-----------------------------calculate Due amount
        fnCalDue($('#paid_amount').val());
    }

    function fnCalDiscount(discountVal) {
        var TotalAmount = $('#total_amount').val();
        var TAAfterDiscount = (Number(TotalAmount) - ((Number(TotalAmount) * Number(discountVal)) / 100));

        //set the discount amount into discount_amount fields
        $('#discount_amount').val(((Number(TotalAmount) * Number(discountVal)) / 100));
        $('#ta_after_discount').val(TAAfterDiscount.toFixed(2));

        //---------------------------- Gross Total
        $('#total_payable_amount').val(TAAfterDiscount.toFixed(2));
        // //-----------------------------Due amount
        // $('#due_amount').val(TAAfterDiscount);

        //-----------------------------calculate vat amount
        fnCalVat($('#vat_rate').val());
        //-----------------------------calculate Due amount
        fnCalDue($('#paid_amount').val());
    }

    function fnCalVat(vatVal) {
        var TAAfterDiscount = $('#ta_after_discount').val();
        var GrossAmount = (Number(TAAfterDiscount) + ((Number(TAAfterDiscount) * Number(vatVal)) / 100));
        $('#vat_amount').val(((Number(TAAfterDiscount) * Number(vatVal)) / 100));
        $('#total_payable_amount').val(GrossAmount.toFixed(2));

        //-----------------------------calculate Due amount
        fnCalDue($('#paid_amount').val());
    }

    function fnCalDue(paidAmount) {
        var GrossAmount = $('#total_payable_amount').val();
        var DueAmount = (Number(GrossAmount) - Number(paidAmount));
        $('#due_amount').val(DueAmount.toFixed(2));
    }

    function fnSupplierNameLoad() {
        var SupplierID = $('#supplier_id').val();
        // var CompanyID = $('#company_id').val();

        $.ajax({
            method: "GET",
            url: "{{url('/ajaxSupplierNameLoadInv')}}",
            dataType: "text",
            data: {
                SupplierID: SupplierID,
            },
            success: function(data) {
                if (data) {
                    $('#contact_person').val(data);
                }
            }
        });
    }

    function fnGetOrderProducts() {

        var orderNo = $('#order_no').val();

        if(orderNo != '')
        {
            $.ajax({
                method: "GET",
                url: "{{url('/ajaxOrderPurchaseProdLoadInv')}}",
                dataType: "text",
                data: {
                    orderNo: orderNo,
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
    }

    $('#submitButtonforPurchase').on('click', function(event) {
        event.preventDefault();

        if ($('#total_payable_amount').val() > 0 && $('#product_id_0').val() == '') {

            $('#purchase_form').submit();
        } else {

            if ($('#total_payable_amount').val() <= 0) {
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
