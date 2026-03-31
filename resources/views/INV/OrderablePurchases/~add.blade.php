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
        <h4 class="">Purchase Entry</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Transaction</a></li>
            <li class="breadcrumb-item"><a href="{{ url('/pos/purchase') }}">Purchases</a></li>
            <li class="breadcrumb-item active">Entry</li>
        </ol>
    </div>

    <div class="page-content">
        <form action="{{ url('/pos/purchase/new') }}" method="post" data-toggle="validator" novalidate="true"
            id="purchase_form">
            @csrf
            <div class="panel">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-9 offset-3 mb-2">
                            <!-- Html View Load  -->
                            {!! HTML::forCompanyFeild() !!}
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
                                            value="{{ POSS::generateBillPurchase(Common::getBranchId()) }}" required readonly>
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
                                    <div class="col-lg-4">
                                        <label for="textOrderNumber" class="input-title">Order No</label>
                                    </div>
                                    <div class="col-lg-7">
                                        <input type="text" class="form-control round" placeholder="Enter Order No"
                                            name="order_no" id="order_no">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row align-items-center">
                                    <div class="col-lg-4">
                                        <label for="textRequisitionNumber" class="input-title">Requisition No</label>
                                    </div>
                                    <div class="col-lg-7">
                                        <input type="text" class="form-control round" placeholder="Enter Requisition No"
                                            name="requisition_no" id="requisition_no">
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
                                        <label class="input-title RequiredStar" for="textSupplier">Supplier</label>
                                    </div>
                                    <div class="col-lg-6 input-group addSupplier">

                                        <select class="form-control clsSelect2"
                                             name="supplier_id" id="supplier_id"
                                            onchange="fnSupplierNameLoad(); fnProductLoad();" required>
                                            <option value="">Select Supplier</option>
                                            @foreach($SupplierData as $SData)
                                            <option value="{{ $SData->id }}">{{ $SData->sup_name }}</option>
                                            @endforeach
                                        </select>
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


                            <div class="form-group">
                                <div class="row align-items-center">
                                    <div class="col-lg-4">
                                        <label class="input-title" for="textBranch">For Branch</label>
                                    </div>
                                    <div class="col-lg-7 input-group">
                                        <select class="form-control clsSelect2"
                                             name="branch_id" id="branch_id"
                                            onchange="fnGenBillNo(this.value);">
                                            <option value="0">{{ sprintf("%04d", 0)." - "}}Head Office</option>
                                            @foreach($BranchData as $BData)
                                            <option value="{{ $BData->id }}">
                                                {{ sprintf("%04d", $BData->branch_code)." - ".$BData->branch_name }}
                                            </option>
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
                            {{-- Query for get all category --}}
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
                            {{-- Query for get all Sub-category --}}
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

                    <div class="row">
                        <div class="col-lg-6 text-left">
                            <strong class="text-danger" style="color: #3e8ef7;">Current Stock: <label id="current_stock"></label></strong>
                        </div>
                    </div>

                    <table class="table table-hover table-striped table-bordered w-full text-center table-responsive my-custom-scrollbar"
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
                            $ProductList = Common::ViewTableOrder('inv_products',
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
                                    <!-- Input for System barcode  -->
                                    <!-- <input type="hidden" name="sys_barcode_arr[]" id="sys_barcode_0"> -->

                                    <!-- Input for Product Name  -->
                                    <!--  <input type="hidden"  name="product_name_arr[]" id="product_name_0">  -->

                                    {{-- name="product_quantity_arr[]"  --}}
                                    <input type="number" id="product_quantity_0" class="form-control round clsQuantity"
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
                                        <!-- <input type="hidden" name="total_ordered_quantity" id="total_ordered_quantity" value="0"> -->
                                        <!-- <input type="hidden" name="total_received_quantity" id="total_received_quantity" value="0"> -->
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

                                        {{-- hidden fields for discount amount --}}
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
                                            class="form-control round" value="0" readonly>
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
                                        <label class="input-title RequiredStar">Total Payable Amount</label>
                                    </div>
                                    <div class="col-lg-7">
                                        <input type="number" name="total_payable_amount" id="total_payable_amount"
                                            class="form-control round" value="0" readonly required min="1">
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="row align-items-center">
                                    <div class="col-lg-4">
                                        <label class="input-title">Paid Amount</label>
                                    </div>
                                    <div class="col-lg-7">
                                        <input type="number" name="paid_amount" id="paid_amount"
                                            class="form-control round" value="0" onkeyup="fnCalDue(this.value);"
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
                                            class="form-control round" value="0" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>



                    <div class="row align-items-center">
                        <div class="col-lg-12">
                            <div class="form-group d-flex justify-content-center">
                                <div class="example example-buttons">
                                    <a href="{{ url('/pos/purchase') }}" class="btn btn-default btn-round">Close</a>
                                    <button type="submit" class="btn btn-primary btn-round"
                                        id="submitButtonforPurchase">Save</button>
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
        <!--End Page Content-->

        <div class="modal fade" id="modalSupplierForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header text-center">
                        <h4 class="modal-title font-weight-bold text-center">Supplier Entry</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body mx-3">
                        <form enctype="multipart/form-data" method="post" action="" data-toggle="validator"
                            novalidate="true" id="supModalFormId">
                            @csrf
                            <input type="hidden" id="csrf" name="_token" value="{{csrf_token()}}">



                            <div class="row">
                                <div class="col-lg-12">
                                    <input type="hidden" name="company_id" value="{{ Common::getCompanyId() }}">
                                  
                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title RequiredStar">SUPPLIER NAME</label>
                                        <div class="col-lg-8">
                                            <div class="form-group">
                                                <div class="input-group ">
                                                    <input type="text" class="form-control round" placeholder="Enter Supplier Name" name="sup_name" id="sup_name" required data-error="Please enter supplier name.">
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title RequiredStar">SUPPLIER TYPE</label>
                                        <div class="col-lg-8 form-group">
                                            <div class="input-group">
                                                <select class="form-control"  
                                                    name="supplier_type" id="supplier_type" required
                                                    data-error="Select Supplier Type.">
                                                    <option value="">Select Type</option>
                                                    <option value="1">PURCHASE</option>
                                                    <option value="2">COMISSION</option>

                                                </select>
                                            </div>
                                            <div class="help-block with-errors is-invalid"></div>
                                        </div>
                                    </div>
                            
                                    <div id="comissionIDinput" style="display:none;">
                                        <div class="form-row align-items-center" >
                                            <label class="col-lg-4 input-title RequiredStar">COMISSION</label>
                                            <div class="col-lg-8">
                                                <div class="form-group">
                                                    <div class="input-group ">
                                                        <input type="number" class="form-control round" placeholder="Enter Comission Percentage."
                                                        name="comission_percent" id="comission_percent">
                                                    </div>
                                                    <div class="help-block with-errors is-invalid"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title RequiredStar">Supplier's Company</label>
                                        <div class="col-lg-8">
                                            <div class="form-group">
                                                <div class="input-group ">
                                                    <input type="text" class="form-control round" placeholder="Enter Company Name" name="sup_comp_name" id="sup_comp_name" required data-error="Please enter supplier's company name.">
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title RequiredStar">Email</label>
                                        <div class="col-lg-8">
                                            <div class="form-group">
                                                <div>
                                                    <input type="email" class="form-control round" id="sup_email" name="sup_email" placeholder="Enter Email" required data-error="Please enter email.">
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title RequiredStar">Mobile</label>
                                        <div class="col-lg-8">
                                            <div class="form-group">
                                                <div class="input-group ">
                                                    <input type="number" class="form-control round" id="sup_phone" name="sup_phone" placeholder="Enter Phone Number" required data-error="Please enter mobile.">
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title">Address</label>
                                        <div class="col-lg-8">
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <textarea class="form-control round" id="sup_addr" name="sup_addr" rows="2" placeholder="Enter Address"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title">Website</label>
                                        <div class="col-lg-8">
                                            <div class="form-group">
                                                <div class="input-group ">
                                                    <input type="text" class="form-control round" name="sup_web_add" id="sup_web_add" placeholder="https://example.com" >
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title">Description</label>
                                        <div class="col-lg-8">
                                            <div class="form-group">
                                                <div class="input-group ">
                                                    <textarea class="form-control round fix-size" id="sup_desc" name="sup_desc" rows="2" placeholder="Enter Description"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title">Reference No</label>
                                        <div class="col-lg-8">
                                            <div class="form-group">
                                                <div class="input-group ">
                                                    <input type="text" class="form-control round" id="sup_ref_no" name="sup_ref_no" placeholder="Enter Reference No">
                                                </div>
                                                {{-- <div class="help-block with-errors is-invalid"></div> --}}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title">Attentions</label>
                                        <div class="col-lg-8">
                                            <div class="form-group">
                                                <div class="input-group ">
                                                    <textarea class="form-control round" rows="2" id="sup_attentionA" name="sup_attentionA" placeholder="Enter Attentions."></textarea>
                                                </div>
                                                {{-- <div class="help-block with-errors is-invalid"></div> --}}
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            
                        
                        </form>
                    </div>
                    <div class="modal-footer d-flex justify-content-center">
                        <div class="row align-items-center">
                            <div class="col-lg-12">
                                <div class="form-group d-flex justify-content-center">
                                    <div class="example example-buttons">
                                        <a href="javascript:void(0)" class="btn btn-default btn-round"
                                            data-dismiss="modal">Close</a>
                                        <a href="javascript:void(0)" class="btn btn-primary btn-round"
                                            id="submitButtonSupPOP">Submit</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--End Page-->
</div>

<script type="text/javascript">

$(document).ready(function() {

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
                url: "{{ url('/popUpSupplierData') }}",
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
                title: 'Oops...',
                text: 'Please fillup all fields!',
            });
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
    if ($('#' + ColumnID[0] + 0).val() != '' && $('#product_quantity_0').val() > 0) {

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



        var html = '<tr>';

        html += '<td class="input-group barcodeWidth" width="35%">';
        // html +='<select class="form-control clsSelect2"  name="' + ColumnName[0] + '" id="' + ColumnID[0] + TotalRowCount + '" onchange="fnGetSelectedValue(' + TotalRowCount + ');" required >'+
        //         '<option value="">Select Product</option>'+
        //         '</select>';
        html += '<input type="hidden" name="' + ColumnName[0] + '" value="' + ProductID + '">';
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
            '" onkeyup="fnTotalQuantity();fnTtlProductPrice(' + TotalRowCount + ');" required min="1">';
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
        $('#' + TableID + ' tbody').find('tr:first').after(html);

        $('#' + ColumnID[0] + 0).val('');
        $('#' + ColumnID[0] + 0).trigger('change');
        $('#' + ColumnID[3] + 0).val(0);
        $('#' + ColumnID[6] + 0).val(0);
        $('#' + ColumnID[7] + 0).val(0);
        $('#' + ColumnID[3] + 0).prop('readonly', true);

        $('#current_stock').html(0);

        $('#prod_group_id').val('');
        $('#prod_cat_id').val('');
        $('#prod_sub_cat_id').val('');
        $('#prod_model_id').val('');

        $('#prod_group_id').trigger('change');
        $('#prod_cat_id').trigger('change');
        $('#prod_sub_cat_id').trigger('change');
        $('#prod_model_id').trigger('change');
    } else {

        if ($('#' + ColumnID[0] + 0).val() == '') {
            swal({
                icon: 'error',
                title: 'Oops...',
                text: 'Please select product!',
            });
        } else {
            swal({
                icon: 'error',
                title: 'Oops...',
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

// /*upcomming stock set from product quantity*/
// $('#product_quantity_0').keyup(function(){
//     $('#upcomming_stock').html($(this).val());
// });

/* Pop Up Supplier Start */
$(document).ready(function() {

    $('.clsProductSelect').select2();
    $('#current_stock').html(0);

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

function fnSupplierNameLoad() {
    var SupplierID = $('#supplier_id').val();
    // var CompanyID = $('#company_id').val();

    $.ajax({
        method: "GET",
        url: "{{url('/ajaxSupplierNameLoad')}}",
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

$('#submitButtonforPurchase').on('click', function(event) {
    event.preventDefault();
    if ($('#total_payable_amount').val() > 0 && $('#product_id_0').val() == '') {

        $('#purchase_form').submit();
    } else {

        if ($('#total_payable_amount').val() <= 0) {
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