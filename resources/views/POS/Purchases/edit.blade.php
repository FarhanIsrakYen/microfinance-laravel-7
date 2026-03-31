@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
?>

<!-- Page -->
<form method="post" data-toggle="validator" novalidate="true" id="purchase_form">
    @csrf
    <div class="panel">
        <div class="panel-body">
            <div class="row">
                <div class="col-lg-8 offset-lg-3">
                    <!-- Html View Load  -->
                    {!! HTML::forCompanyFeild($PurchaseData->company_id) !!}
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8 offset-lg-3">
                    {!! HTML::forBranchFeild(false,'branch_id','branch_id',$PurchaseData->branch_id,'disabled','For Branch') !!}
                </div>
            </div>

            <div class="row">
                <!--Form Left-->
                <div class="col-lg-6">
                    <div class="form-row align-items-center">
                        <label class="col-lg-4 input-title">Bill No</label>
                        <div class="col-lg-7 form-group">
                            <div class="input-group">
                                <input type="text" name="bill_no" id="bill_no" class="form-control round"
                                    value="{{ $PurchaseData->bill_no }}" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row align-items-center">
                            <div class="col-lg-4">
                                <label for="textChalanNumber" class="input-title">invoice No</label>
                            </div>
                            <div class="col-lg-7">
                                <input type="text" class="form-control round" placeholder="Enter Invoice No"
                                    name="invoice_no" id="invoice_no" value="{{ $PurchaseData->invoice_no }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row align-items-center">
                            <div class="col-lg-4">
                                <label class="input-title">Supplier</label>
                            </div>
                            <div class="col-lg-7 input-group">

                                <select class="form-control clsSelect2"
                                     name="supplier_id" id="supplier_id"
                                    onchange="fnSupplierNameLoad(); fnProductLoad();" disabled>

                                    <option value="">Select Supplier</option>
                                    @foreach($SupplierData as $SData)
                                    <option value="{{ $SData->id }}" @if($SData->id == $PurchaseData->supplier_id){{ 'selected' }}@endif>{{ $SData->sup_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row align-items-center">
                            <div class="col-lg-4">
                                <label for="textContactPerson" class="input-title">Contact Person</label>
                            </div>
                            <div class="col-lg-7">
                                <input type="text" class="form-control round" placeholder="Enter Contact Person" name="contact_person" id="contact_person"
                                    value="{{ $PurchaseData->contact_person }}" readonly>
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
                                <?php
									$PurchaseDate = new DateTime($PurchaseData->purchase_date);
									$PurchaseDate = (!empty($PurchaseDate)) ? $PurchaseDate->format('d-m-Y') : Common::systemCurrentDate();
								?>
                                <input type="text" name="purchase_date" id="purchase_date"
                                    class="form-control round" value="{{ $PurchaseDate }}" readonly="true">
                            </div>
                        </div>
                    </div>

                    
                    <div class="form-group">
                        <div class="row align-items-center">
                            <div class="col-lg-4">
                                <label for="textDeliveryNumber" class="input-title">Delivery No</label>
                            </div>
                            <div class="col-lg-7">
                                <input type="text" class="form-control round" placeholder="Enter Delivery No"
                                    name="delivery_no" id="delivery_no"
                                    value="{{ $PurchaseData->delivery_no }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row align-items-center">
                            <?php
                                $orders = App\Model\POS\OrderMaster::where([['is_approve', 1], ['is_delivered', 0], ['is_active', 1], ['is_delete', 0]])->get();
                            ?>
                            <div class="col-lg-4">
                                <label class="input-title">Order No</label>
                            </div>
                            <div class="col-lg-7">
                                <select class="form-control clsSelect2" name="order_no"
                                id="order_no" onchange="fnGetOrderProducts()" disabled>
                                    <option value="">Select Order No</option>
                                    @foreach($orders as $order)
                                    <option value="{{ $order->order_no }}" @if($PurchaseData->order_no == $order->order_no) {{ 'selected' }} @endif>{{ $order->order_no }}</option>
                                    @endforeach
                                </select>
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


            <table class="table table-hover table-striped table-bordered w-full text-center table-responsive mt-4"
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
                        $ProductList = Common::ViewTableOrder('pos_products',
                                        ['is_delete' => 0],
                                        ['id', 'product_name', 'cost_price', 'sale_price', 'prod_vat', 'sys_barcode', 'prod_barcode'],
                                        ['product_name', 'ASC']);
                    ?>
                    <tr>
                        <td width="50%" class="input-group barcodeWidth text-left">
                            <!-- {{-- name="product_id_arr[]" --}} -->
                            <select id="product_id_0" class="form-control round clsProductSelect"
                                data-style="btn-outline btn-primary" style="width: 100%;">

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

                        <td width="15%">
                            <!-- {{-- name="product_quantity_arr[]" --}} -->
                            <input type="number" id="product_quantity_0" class="form-control text-center round clsQuantity"
                                placeholder="Enter Quantity" value="0"
                                onkeyup="fnTotalQuantity(); fnTtlProductPrice(0);" required min="1" readonly>
                        </td>

                        <td width="15%">
                            <!-- {{-- name="unit_cost_price_arr[]" --}} -->
                            <input type="number" id="unit_cost_price_0" class="form-control round text-right" value="0"
                                required min="1" readonly>
                        </td>

                        <td width="16%">
                            <!-- {{-- name="total_cost_price_arr[]" --}} -->
                            <input type="number" id="total_cost_price_0" class="form-control round text-right ttlAmountCls"
                                value="0" min="1" readonly>
                        </td>

                        <td width="4%">
                            <a href="javascript:void(0);"
                                class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center clsAddNewRow"
                                onclick="btnAddNewRow('<?= $TableID ?>', '<?= $ColumnName ?>', '<?= $ColumnID ?>', 'TotalRowID');">
                                <i class="icon wb-plus  align-items-center"></i>
                            </a>
                        </td>
                    </tr>

                    @if(count($PurchaseDataD) > 0)
                        @foreach($PurchaseDataD as $PurData)
                        <?php $i++; ?>
                        <tr>
                            <td width="50%" class="input-group barcodeWidth text-left">
                                <input type="hidden" id="product_id_{{ $i }}" name="product_id_arr[]" value="{{ $PurData->product_id }}">

                                @foreach($ProductList as $ProductInfo)
                                @if($ProductInfo->id == $PurData->product_id)
                                <input type="text" class="form-control round"
                                    value="{{ $ProductInfo->product_name . ' (' . $ProductInfo->sys_barcode . ')' }} "
                                    readonly>
                                @endif
                                @endforeach
                            </td>

                            <td width="15%">
                                <input type="number" name="product_quantity_arr[]" id="product_quantity_{{ $i }}"
                                    class="form-control round clsQuantity text-center" placeholder="Enter Quantity"
                                    value="{{ $PurData->product_quantity }}"
                                    onkeyup="fnTotalQuantity(); fnTtlProductPrice({{ $i }});" required min="1">
                            </td>

                            <td width="15%">
                                <input type="number" name="unit_cost_price_arr[]" id="unit_cost_price_{{ $i }}"
                                    class="form-control round text-right" value="{{ $PurData->unit_cost_price }}"
                                    min="1" readonly>
                            </td>

                            <td width="16%">
                                <input type="number" name="total_cost_price_arr[]" id="total_cost_price_{{ $i }}"
                                    class="form-control round ttlAmountCls text-right" value="{{ $PurData->total_cost_price }}"
                                    min="1" readonly>
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
                        <td width="16%" id="tdTotalAmount" class="text-right" style="font-weight: bold;">0.00</td>
                        <td width="4%"></td>
                    </tr>
                </tfoot>
            </table>

            <!-- Row_Count is temporary variable for using row add and delete-->
            <input type="hidden" id="TotalRowID" value="{{ $i }}" />

            <!-- <div class="mt-4 table-responsive">
                <table class="table table-striped table-bordered w-full text-center" id="tableQuanAmnt">
                    <tbody>
                        <tr>
                            <td width="45%">
                                <h5>Total Quantity</h5>
                                <input type="hidden" name="total_quantity" id="total_quantity" value="0"
                                    min="1">
                            </td>
                            <td width="10%"  class="text-center" id="tdTotalQuantity" style="font-weight: bold;">0</td>

                            <td width="20%">
                                <h5>Total Amount</h5>
                                <input type="hidden" name="total_amount" id="total_amount" value="0" min="1">
                            </td>
                            <td width="25%" class="text-right" id="tdTotalAmount" style="font-weight: bold;">0</td>
                        </tr>
                    </tbody>
                </table>
            </div> -->
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
                            <div class="row">
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
                                    class="form-control round text-right" value="{{ $PurchaseData->discount_rate }}"
                                    onkeyup="fnCalDiscount(this.value);">

                                <!-- {{-- hidden fields for discount amount --}} -->
                                <input type="hidden" name="discount_amount" id="discount_amount"
                                    value="{{ $PurchaseData->discount_amount }}">
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
                                <!-- {{-- {{ $PurchaseData->ta_after_discount }} --}} -->
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row align-items-center">
                            <div class="col-lg-4">
                                <label class="input-title">VAT(%)</label>
                            </div>
                            <div class="col-lg-7">
                                <input type="number" name="vat_rate" id="vat_rate" 
                                    class="form-control round text-right"
                                    value="{{ $PurchaseData->vat_rate }}" 
                                    onkeyup="fnCalVat(this.value);">

                                <input type="hidden" name="vat_amount" id="vat_amount"
                                    value="{{ $PurchaseData->vat_amount }}">
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
                                    class="form-control round text-right" value="0" readonly min="1">
                                <!-- {{-- {{ $PurchaseData->total_payable_amount }} --}} -->
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row align-items-center">
                            <div class="col-lg-4">
                                <label class="input-title">Paid Amount</label>
                            </div>
                            <div class="col-lg-7">
                                <input type="number" name="paid_amount" id="paid_amount"
                                    class="form-control round text-right" value="{{ $PurchaseData->paid_amount }}"
                                    onkeyup="fnCalDue(this.value);">
                                    <!-- required min="1" -->
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
                                <!-- {{-- {{ $PurchaseData->due_amount }} --}} -->
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
                            <?php
                                $SysDate = new DateTime(Common::systemCurrentDate());
                                $SysDate = $SysDate->format('Y-m-d');
                            ?>

                            @if($PurchaseData->purchase_date == $SysDate)
                                <button type="submit" class="btn btn-primary btn-round"
                                id="submitButtonforPurchase">Update</button>
                            @endif

                            <!-- <a href="#"><button type="button" class="btn btn-warning btn-round">Next</button></a> -->
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

<script type="text/javascript">

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

                    // $('#'+firstRowFirstColId).html('');
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
        if ($('#' + ColumnID[0] + 0).val() != '' && $('#product_quantity_0').val() > 0) {

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

                fnTotalQuantity();
                fnTtlProductPrice(rowNumber);


            }
            else {
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

                html += '<td class="input-group barcodeWidth text-left" width="35%">';
                html += '<input type="hidden" name="' + ColumnName[0] + '" value="' + ProductID + '">';
                html += '<input type="text" class="form-control round" value="' + ProductName + '(' + ProductSysBarcode + ')' +
                    '" readonly >';
                html += '</td>';
                html += '<td width="10%">';

                html += '<input type="number" name="' + ColumnName[3] + '" id="' + ColumnID[3] + TotalRowCount +
                    '" class="form-control round clsQuantity text-center" value="' + ProductQuantity +
                    '" onkeyup="fnTotalQuantity();fnTtlProductPrice(' + TotalRowCount + ');" required min="1">';
                html += '</td>';

                html += '<td width="15%">' +
                    '<input type="number" name="' + ColumnName[6] + '" id="' + ColumnID[6] + TotalRowCount +
                    '" class="form-control round text-right" value="' + ProductCostPrice + '" readonly required min="1">' +
                    '</td>';
                html += '<td width="15%">' +
                    '<input type="number" name="' + ColumnName[7] + '" id="' + ColumnID[7] + TotalRowCount +
                    '" class="form-control round text-right ttlAmountCls" value="' + ProductAmount + '" readonly>' +
                    '</td>';
                html += '<td width="5%">' +
                    '<a href="javascript:void(0)" class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center clsAddNewRow" id="' +
                    ColumnID[8] + TotalRowCount + '" onclick="btnRemoveRow(this);">' +
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

    /* Pop Up Supplier Start */
    $(document).ready(function() {

        //initialization for search select box
        $('.clsProductSelect').select2();

        $('#current_stock').html(0);

        //get company id into the popup for insert supplier
        $('#company_id').val(this.value);

        //when company is select then the following function is call for get data into their fields.
        // fnAjaxSelectBox(
        //     'supplier_id',
        //     '{{ $PurchaseData->company_id }}',
        //     '{{base64_encode("pos_suppliers")}}',
        //     '{{base64_encode("company_id")}}',
        //     '{{base64_encode("id,sup_name")}}',
        //     '{{url("/ajaxSelectBox")}}',
        //     '{{ $PurchaseData->supplier_id}}'
        // );

        // fnAjaxSelectBox(
        //     'branch_id',
        //     '{{ $PurchaseData->company_id }}',
        //     '{{base64_encode("gnl_branchs")}}',
        //     '{{base64_encode("company_id")}}',
        //     '{{base64_encode("id,branch_name")}}',
        //     '{{url("/ajaxSelectBox")}}',
        //     '{{ $PurchaseData->branch_id}}'
        // );



        // fnAjaxSelectBox(
        //     'prod_group_id',
        //     '{{ $PurchaseData->company_id }}',
        //     '{{base64_encode("pos_p_groups")}}',
        //     '{{base64_encode("company_id")}}',
        //     '{{base64_encode("id,group_name")}}',
        //     '{{url("/ajaxSelectBox")}}'
        // );
        //end

        // fnGetReqProduct();

        //call function for get Total Quantity
        fnTotalQuantity();
        fnTotalAmount();


    });
    /* Pop Up Supplier End */


    /*$('#company_id').change(function () {

        fnProductLoad();
        fnGenBillNo(this.value);
        fnAjaxSelectBox(
                'supplier_id',
                this.value,
                '{{base64_encode("pos_suppliers")}}',
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
        fnAjaxSelectBox(
                'prod_group_id',
                this.value,
                '{{base64_encode("pos_p_groups")}}',
                '{{base64_encode("company_id")}}',
                '{{base64_encode("id,group_name")}}',
                '{{url("/ajaxSelectBox")}}',
                );
    });*/

    // function fnGenBillNo(compID)
    // {
    //     if (compID != '') {
    //         $.ajax({
    //             method: "GET",
    //             url: "{{ url('/ajaxGBillPurchase') }}",
    //             dataType: "text",
    //             data: {compID: compID},
    //             success: function (data) {
    //                 if (data) {
    //                     $('#bill_no').val(data);
    //                 }
    //             }
    //         });
    //     }
    // }

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
                        $('#current_stock').html(data);
                    }
                }
            });
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

    $('#submitButtonforPurchase').on('click', function(event) {
        event.preventDefault();
        if ($('#total_quantity').val() > 0 && $('#product_id_0').val() == '') {
            $('#order_no').removeAttr('disabled');
            $('#purchase_form').submit();
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

    function fnSupplierNameLoad() {
        var SupplierID = $('#supplier_id').val();
        var CompanyID = $('#company_id').val();

        $.ajax({
            method: "GET",
            url: "{{url('/ajaxSupplierNameLoad')}}",
            dataType: "text",
            data: {
                SupplierID: SupplierID,
                CompanyID: CompanyID
            },
            success: function(data) {
                if (data) {
                    console.log(data);
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
                url: "{{url('/ajaxOrderPurchaseProdLoad')}}",
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

        /* Supplier Type */
        $('#supplier_type').change(function() {

            if ($(this).val() == '2') {
                $('#comissionIDinput').show();
            } else {
                $('#comissionIDinput').hide();
            }
        });

        fnGetOrderProducts();

    });

    $('form').submit(function (event) {
        $(this).find(':submit').attr('disabled', 'disabled');
    });
</script>


@endsection
