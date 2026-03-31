@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
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
        <form action="{{ url('/pos/purchase/update/'.$PurchaseData->id) }}" method="post" data-toggle="validator"
            novalidate="true" id="purchase_form">
            @csrf
            <div class="panel">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-9 offset-3 mb-2">
                            <!-- Html View Load  -->
                            {!! HTML::forCompanyFeild($PurchaseData->company_id) !!}
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
                                            value="{{ $PurchaseData->bill_no }}" readonly required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row align-items-center">
                                    <div class="col-lg-4">
                                        <label for="textChalanNumber" class="input-title">Invoice No</label>
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
                                    <div class="col-lg-4">
                                        <label for="textOrderNumber" class="input-title">Order No</label>
                                    </div>
                                    <div class="col-lg-7">
                                        <input type="text" class="form-control round" placeholder="Enter Order No"
                                            name="order_no" id="order_no" value="{{ $PurchaseData->order_no }}">
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
                                            name="requisition_no" id="requisition_no"
                                            value="{{ $PurchaseData->requisition_no }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--Form Right-->
                        <div class="col-lg-6">
                            <div class="form-group">
                                <div class="row align-items-center">
                                    <div class="col-lg-4 ">
                                        <label for="datePurchases RequiredStar" class="input-title">Purchase
                                            Date</label>
                                    </div>
                                    <div class="col-lg-7 input-group">
                                        <div class="input-group-prepend ">
                                            <span class="input-group-text ">
                                                <i class="icon wb-calendar round" aria-hidden="true"></i>
                                            </span>
                                        </div>

                                        <?php
$PurchaseDate = new DateTime($PurchaseData->purchase_date);
$PurchaseDate = (!empty($PurchaseDate)) ? $PurchaseDate->format('d-m-Y') : date('d-m-Y');
?>
                                        <input type="text" id="purchase_date" name="purchase_date"
                                            class="form-control round" readonly value="{{ $PurchaseDate }}" required>
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
                                            name="contact_person" id="contact_person"
                                            value="{{ $PurchaseData->contact_person }}">
                                    </div>
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
                                            onchange="fnAjaxSelectBox(
                                                                         'branch_id',
                                                                         this.value,
                                                                         '{{base64_encode('gnl_branchs')}}',
                                                                         '{{base64_encode('prod_group_id')}}',
                                                                         '{{base64_encode('id,cat_name')}}',
                                                                         '{{url('/ajaxSelectBox')}}'
                                                                    );">
                                            <option>select option</option>
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
                                        <label class="input-title" for="textBranch">Branch</label>
                                    </div>
                                    <div class="col-lg-7 input-group">
                                        <select class="form-control clsSelect2"
                                             name="branch_id" id="branch_id">
                                            <option>select option</option>
                                            {{-- @foreach ($Branch as $Row)
                                                <option value="{{$Row->id}}"
                                            {{ ($Row->id == $PurchaseData->branch_id) ? 'selected':'' }}>{{$Row->branch_name}}
                                            </option>
                                            @endforeach --}}
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
                            <select class="form-control clsSelect2"
                                 id="prod_group_id" onchange="fnAjaxSelectBox(
                                                         'prod_cat_id',
                                                         this.value,
                                             '{{base64_encode('inv_p_categories')}}',
                                             '{{base64_encode('prod_group_id')}}',
                                             '{{base64_encode('id,cat_name')}}',
                                             '{{url('/ajaxSelectBox')}}'
                                                     );">
                            </select>
                        </div>
                        <div class="col-lg-1 labelPercentSearch">
                            <label for="textCategory" class="input-title">Category</label>
                        </div>
                        <div class="col-lg-2 inputPercentSearch input-group">
                            <select class="form-control clsSelect2"
                                 id="prod_cat_id" onchange="fnAjaxSelectBox(
                                                     'prod_sub_cat_id',
                                                     this.value,
                                         '{{base64_encode('inv_p_subcategories')}}',
                                         '{{base64_encode('prod_cat_id')}}',
                                         '{{base64_encode('id,sub_cat_name')}}',
                                         '{{url('/ajaxSelectBox')}}'
                                                 );">
                                <option value="">Select</option>
                            </select>
                        </div>
                        <div class="col-lg-1 labelPercentSearch">
                            <label for="textSubCategory" class="input-title">Sub Category</label>
                        </div>
                        <div class="col-lg-2 inputPercentSearch  input-group">
                            <select class="form-control clsSelect2"
                                 id="prod_sub_cat_id" onchange="fnAjaxSelectBox(
                                                     'prod_model_id',
                                                     this.value,
                                         '{{base64_encode('inv_p_models')}}',
                                         '{{base64_encode('prod_sub_cat_id')}}',
                                         '{{base64_encode('id,model_name')}}',
                                         '{{url('/ajaxSelectBox')}}');">
                                <option value="">Select</option>

                            </select>
                        </div>
                        <div class="col-lg-1 labelPercentSearch">
                            <label for="textModel" class="input-title">Model</label>
                        </div>
                        <div class="col-lg-2 inputPercentSearch input-group">

                            <select class="form-control clsSelect2"
                                 id="prod_model_id"
                                onchange="fnPurAjaxModelSelect(this.value);">
                                <option value="">Select</option>
                            </select>
                        </div>
                    </div>


                    <div class="table-responsive my-custom-scrollbar">
                        <table class="table table-hover table-striped table-bordered w-full text-center"
                            id="purchaseTable">
                            <thead>
                                <tr>
                                    {{-- <th width="40%">Barcode</th> --}}
                                    <th width="40%">Product Name</th>
                                    <th width="15%">Quantity</th>
                                    <th width="20%">Cost Price</th>
                                    <th width="20%">Total</th>
                                    <th width="5%">Remove</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $i = 0;
                                    $TableID = "purchaseTable";
                                    $ColumnName = "product_id_arr[]&sys_barcode_arr[]&product_name_arr[]&product_quantity_arr[]&ordered_quantity_arr[]&received_quantity_arr[]&unit_cost_price_arr[]&total_cost_price_arr[]";

                                    $ColumnID = "product_id_&sys_barcode_&product_name_&product_quantity_&ordered_quantity_&received_quantity_&unit_cost_price_&total_cost_price_&deleteRow_";
                                ?>
                                @if(count($PurchaseDataD) > 0)
                                @foreach($PurchaseDataD as $PurData)

                                <tr id="purchaseRow_{{ $i }}">

                                    <td class="input-group barcodeWidth">
                                        <select name="product_id_arr[]" id="product_id_{{ $i }}"
                                            class="form-control clsSelect2"
                                            >
                                            <option value="{{ $PurData->product_id }}">
                                                <?php
                                                        $ProductInfo = Common::ViewTableLast('inv_products', ['id' => $PurData->product_id], ['product_name'])
                                                    ?>
                                                {{ $ProductInfo->product_name }}
                                            </option>
                                        </select>
                                    </td>

                                    <td>
                                        {{-- Input for System barcode --}}
                                        {{--  <input type="hidden" name="sys_barcode_arr[]" id="sys_barcode_{{ $i }}"
                                        value="{{ $PurData->barcode_no }}">

                                        Input for Product Name
                                        <input type="hidden" name="product_name_arr[]" id="product_name_{{ $i }}"
                                            value="{{ $PurData->product_name }}"> --}}


                                        <input type="number" name="product_quantity_arr[]"
                                            id="product_quantity_{{ $i }}" class="form-control round clsQuantity"
                                            value="{{ $PurData->product_quantity }}"
                                            onkeyup="fnTotalQuantity(); fnTtlProductPrice({{ $i }});" required min="1">

                                    </td>
                                    {{-- <td>
                                        <input type="hidden" name="ordered_quantity_arr[]" id="ordered_quantity_{{ $i }}"
                                    class="form-control round clsOrderQuantity" value="{{ $PurData->ordered_quantity }}"
                                    onkeyup="fnTotalOrderQuantity();">
                                    </td>
                                    <td>
                                        <input type="hidden" name="received_quantity_arr[]"
                                            id="received_quantity_{{ $i }}" class="form-control round clsRcvQuantity"
                                            value="{{ $PurData->received_quantity }}"
                                            onkeyup="fnTotalReceiveQuantity();">
                                    </td> --}}

                                    <td>
                                        <input type="number" name="unit_cost_price_arr[]" id="unit_cost_price_{{ $i }}"
                                            class="form-control round" value="{{ $PurData->unit_cost_price }}" required
                                            min="1" readonly>
                                        {{-- onkeyup="fnTtlProductPrice({{ $i }});" --}}

                                    </td>

                                    <td>
                                        <input type="number" name="total_cost_price_arr[]"
                                            id="total_cost_price_{{ $i }}" class="form-control round ttlAmountCls"
                                            value="{{ $PurData->total_cost_price }}" readonly>
                                    </td>

                                    <td>
                                        <a href="javascript:void(0)"
                                            class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center clsAddNewRow"
                                            id="deleteRow_{{ $i }}"
                                            onclick="btnRemoveRow(TotalRowID ,purchaseRow_{{ $i }});">
                                            <i class="icon fa fa-times align-items-center"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php $i++;?>
                                @endforeach
                                @else
                                <tr id="purchaseRow_{{ $i }}">
                                    <td class="input-group barcodeWidth">
                                        <select class="form-control clsSelect2"
                                             name="product_id_arr[]"
                                            id="product_id_{{ $i }}" onchange="fnGetSelectedValue({{ $i }});" required>

                                            <option value="">Select Product</option>
                                        </select>
                                    </td>

                                    <td>
                                        {{-- Input for System barcode --}}
                                        {{-- <input type="hidden" name="sys_barcode_arr[]" id="sys_barcode_{{ $i }}">
                                        --}}

                                        {{-- Input for Product Name --}}
                                        {{-- <input type="hidden"  name="product_name_arr[]" id="product_name_{{ $i }}">
                                        --}}

                                        <input type="number" class="form-control round clsQuantity"
                                            placeholder="Enter Quantity" name="product_quantity_arr[]"
                                            id="product_quantity_{{ $i }}" value="0"
                                            onkeyup="fnTotalQuantity(); fnTtlProductPrice({{ $i }});" required min="1">
                                    </td>

                                    {{-- <td>
                                        <input type="hidden" name="ordered_quantity_arr[]" id="ordered_quantity_{{ $i }}"
                                    class="form-control round clsOrderQuantity" value="0"
                                    onkeyup="fnTotalOrderQuantity();">
                                    </td>
                                    <td>
                                        <input type="hidden" name="received_quantity_arr[]"
                                            id="received_quantity_{{ $i }}" class="form-control round clsRcvQuantity"
                                            value="0" onkeyup="fnTotalReceiveQuantity();">
                                    </td> --}}
                                    <td>
                                        <input type="number" name="unit_cost_price_arr[]" id="unit_cost_price_{{ $i }}"
                                            class="form-control round" value="0" required min="1" readonly>
                                        {{-- onkeyup="fnTtlProductPrice({{ $i }});" --}}
                                    </td>

                                    <td>
                                        <input type="number" name="total_cost_price_arr[]"
                                            id="total_cost_price_{{ $i }}" class="form-control round ttlAmountCls"
                                            value="0" min="1" readonly>
                                    </td>

                                    <td>
                                        <a href="javascript:void(0)"
                                            class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center clsAddNewRow"
                                            id="deleteRow_{{ $i }}"
                                            onclick="btnRemoveRow(TotalRowID,purchaseRow_{{ $i }});">
                                            <i class="icon fa fa-times align-items-center"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endif

                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 table-responsive">
                        <table class="table table-striped table-bordered w-full text-center" id="tableQuanAmnt">
                            <tbody>
                                <tr>
                                    <td colspan="2">
                                        <h5>Total Quantity</h5>
                                        <input type="hidden" name="total_quantity" id="total_quantity"
                                            value="{{ $PurchaseData->total_quantity }}">
                                    </td>
                                    <td width="15%" id="tdTotalQuantity" style="font-weight: bold;">0</td>

                                    <td width="20%">
                                        <h5>Total Amount</h5>
                                        <input type="hidden" name="total_amount" id="total_amount"
                                            value="{{ $PurchaseData->total_amount }}">
                                    </td>
                                    <td width="20%" id="tdTotalAmount" style="font-weight: bold;">0</td>

                                    <td width="5%">
                                        <?php
$RowCountID = "RowCountID";
$TotalRowID = "TotalRowID";
?>
                                        <!-- Row_CountP2S7 is temporary variable for using row add and delete-->
                                        <input type="hidden" id="{{ $RowCountID }}" value="{{ $i }}" />
                                        <input type="hidden" id="{{ $TotalRowID }}" value="{{ $i+1 }}" />

                                        <a href="javascript:void(0)"
                                            class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center clsAddNewRow"
                                            id="addrows"
                                            onclick="btnAddNewRow('{{ $TableID }}', '{{ $ColumnName }}', '{{ $ColumnID }}', '{{ $RowCountID }}', '{{ $TotalRowID }}');">
                                            <i class="icon wb-plus  align-items-center"></i>
                                        </a>
                                    </td>
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

                            <div class="form-group">
                                <div class="row align-items-center">
                                    <div class="col-lg-4">
                                        <label class="input-title">Discount(%)</label>
                                    </div>
                                    <div class="col-lg-7">
                                        <input type="number" name="discount_rate" id="discount_rate"
                                            class="form-control round" value="{{ $PurchaseData->discount_rate }}"
                                            onkeyup="fnCalDiscount(this.value);">

                                        {{-- hidden fields for discount amount --}}
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
                                            class="form-control round" value="{{ $PurchaseData->ta_after_discount }}"
                                            readonly>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!--Form Right-->
                        <div class="col-lg-6">

                            <div class="form-group">
                                <div class="row align-items-center">
                                    <div class="col-lg-4">
                                        <label class="input-title">VAT(%)</label>
                                    </div>
                                    <div class="col-lg-7">
                                        <input type="number" name="vat_rate" id="vat_rate" class="form-control round"
                                            value="{{ $PurchaseData->vat_rate }}" onkeyup="fnCalVat(this.value);">

                                        <input type="hidden" name="vat_amount" id="vat_amount"
                                            value="{{ $PurchaseData->vat_amount }}">
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
                                            class="form-control round" value="{{ $PurchaseData->total_payable_amount }}"
                                            readonly required min="1">
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
                                            class="form-control round" value="{{ $PurchaseData->paid_amount }}"
                                            onkeyup="fnCalDue(this.value);" required min="1">
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
                                            class="form-control round" value="{{ $PurchaseData->due_amount }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-2">
                                        <label for="textPurchaseRemarks" class="input-title">Remarks</label>
                                    </div>
                                    <div class="col-lg-9">
                                        <textarea class="form-control round" id="textPurchaseRemarks" name="remarks"
                                            rows="1" placeholder="Enter Remarks">{{ $PurchaseData->remarks }}</textarea>
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
                                    @if($PurchaseData->purchase_date == date('Y-m-d'))
                                    <button type="submit" class="btn btn-primary btn-round"
                                        id="submitButtonforPurchase">Save</button>
                                    @endif

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
                            <div class="row ">
                                <div class="col-lg-12">
                                    <input type="hidden" name="company_id" id="company_id">
                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title RequiredStar">SUPPLIER NAME</label>
                                        <div class="col-lg-8">
                                            <div class="form-group">
                                                <div class="input-group ">
                                                    <input type="text" class="form-control round"
                                                        placeholder="Enter Supplier Name" name="sup_name" id="sup_name"
                                                        required data-error="Please enter supplier name.">
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title RequiredStar">SUPPLIER TYPE</label>
                                        <div class="col-lg-8 form-group">
                                            <div class="input-group">
                                                <select class="form-control clsSelect2" 
                                                    name="supplier_type" id="supplier_type" required
                                                    data-error="Select Supplier Type.">
                                                    <option value="">Select Tyoe</option>
                                                    <option value="PURCHASE">PURCHASE</option>
                                                    <option value="COMISSION">COMISSION</option>

                                                </select>
                                            </div>
                                            <div class="help-block with-errors is-invalid"></div>
                                        </div>
                                    </div>
                                    <div id="comissionIDinput" style="display:none;">
                                        <div class="form-row align-items-center">
                                            <label class="col-lg-4 input-title RequiredStar">COMISSION</label>
                                            <div class="col-lg-8">
                                                <div class="form-group">
                                                    <div class="input-group ">
                                                        <input type="text" class="form-control round"
                                                            placeholder="Enter Comission Percentage."
                                                            name="comission_percent" id="comission_percent" required
                                                            data-error="Please enter Comission Percentage.">
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
                                                    <input type="text" class="form-control round"
                                                        placeholder="Enter Company Name" name="sup_comp_name"
                                                        id="sup_comp_name" required
                                                        data-error="Please enter supplier's company name.">
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
                                                    <input type="email" class="form-control round" id="sup_email"
                                                        name="sup_email" placeholder="Enter Email" required
                                                        data-error="Please enter email.">
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
                                                    <input type="number" class="form-control round" id="sup_phone"
                                                        name="sup_phone" placeholder="Enter Phone Number" required
                                                        data-error="Please enter mobile.">
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
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
/* Pop Up Supplier Start */
$(document).ready(function() {

    //when company is select then the following function is call for get data into their fields.
    fnAjaxSelectBox(
        'supplier_id',
        '{{ $PurchaseData->company_id }}',
        '{{base64_encode("inv_suppliers")}}',
        '{{base64_encode("company_id")}}',
        '{{base64_encode("id,sup_name")}}',
        '{{url("/ajaxSelectBox")}}',
        '{{ $PurchaseData->supplier_id}}'
    );

    fnAjaxSelectBox(
        'branch_id',
        '{{ $PurchaseData->company_id }}',
        '{{base64_encode("gnl_branchs")}}',
        '{{base64_encode("company_id")}}',
        '{{base64_encode("id,branch_name")}}',
        '{{url("/ajaxSelectBox")}}',
        '{{ $PurchaseData->branch_id}}'
    );

    fnAjaxSelectBox(
        'prod_group_id',
        '{{ $PurchaseData->company_id }}',
        '{{base64_encode("inv_p_groups")}}',
        '{{base64_encode("company_id")}}',
        '{{base64_encode("id,group_name")}}',
        '{{url("/ajaxSelectBox")}}'
    );
    //end

    //add new supplier using popup box
    $('#submitButtonSupPOP').on('click', function() {

        var company_id = $('#company_id').val();
        var sup_name = $('#sup_name').val();
        var supplier_type = $('#supplier_type').val();
        var comission_percent = $('#comission_percent').val();
        var sup_comp_name = $('#sup_comp_name').val();
        var sup_email = $('#sup_email').val();
        var sup_phone = $('#sup_phone').val();

        if (company_id != "" && sup_name != "" && supplier_type != "" && sup_comp_name != "" &&
            sup_email != "" && sup_phone != "") {

            $.ajax({
                url: "{{ url('/popUpSupplierData') }}",
                type: "POST",
                data: {
                    _token: $("#csrf").val(),
                    type: 1,
                    company_id: company_id,
                    sup_name: sup_name,
                    supplier_type: supplier_type,
                    comission_percent: comission_percent,
                    sup_comp_name: sup_comp_name,
                    sup_email: sup_email,
                    sup_phone: sup_phone,
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
            alert('Please fill all the field !');
        }
    });


    /* Supplier Type */
    $('#supplier_type').change(function() {

        if ($(this).val() == 'COMISSION') {
            $('#comissionIDinput').show();
        } else {
            $('#comissionIDinput').hide();
        }
    });
    //end---------------

    //call these function for calculate on load
    fnTotalQuantity();
    fnTotalAmount();
    //end-------------

});

/* Pop Up Supplier End */


/* Add row Start */
function btnAddNewRow(TableID, ColumnNames, ColumnIDS, RowCountID, TotalRowID) {

    ColumnName = ColumnNames.split("&");
    ColumnID = ColumnIDS.split("&");
    var countRow = $('#' + TableID + ' tr').length;
    var count = $('#' + RowCountID).val();
    var i = Number(count);

    var FirstRowTD = ColumnID[0];
    var FirstRowTDVal = $('#' + FirstRowTD + i).val();

    if (FirstRowTDVal != '') {

        i++;
        var rowID = 'purchaseRow_' + i;

        var html = '<tr id="' + rowID + '">';

        html += '<td class="input-group barcodeWidth">';
        html +=
            '<select class="form-control clsSelect2"  name="' +
            ColumnName[0] + '" id="' + ColumnID[0] + i + '" onchange="fnGetSelectedValue(' + i + ');" required>' +
            '<option  value="">Select Product</option>' +
            '</select>';
        html += '</td>';

        html += '<td>';

        // // Input Feild For sys_barcode
        // html += '<input type="hidden" name="'+ColumnName[1]+'" id="'+ColumnID[1]+i+'">';
        // // Input Feild For sys_barcode
        // html += '<input type="hidden" name="'+ColumnName[2]+'" id="'+ColumnID[2]+i+'">';

        html += '<input type="number" name="' + ColumnName[3] + '" id="' + ColumnID[3] + i +
            '" class="form-control round clsQuantity" value="0" onkeyup="fnTotalQuantity();fnTtlProductPrice(' + i +
            ');" required min="1">';
        html += '</td>';

        // // input feild for ordered_qtn
        // html += '<td>'+
        //             '<input type="number" name="'+ColumnName[4]+'" id="'+ColumnID[4]+i+'" class="form-control round clsOrderQuantity" value="0" onkeyup="fnTotalOrderQuantity();" required min="1">'+
        //         '</td>';

        // // input feild for received_qtn
        // html += '<td>'+
        //             '<input type="number" name="'+ColumnName[5]+'" id="'+ColumnID[5]+i+'" class="form-control round clsRcvQuantity" value="0" onkeyup="fnTotalReceiveQuantity();" required min="1">'+
        //         '</td>';

        html += '<td>' +
            '<input type="number" name="' + ColumnName[6] + '" id="' + ColumnID[6] + i +
            '" class="form-control round" value="0" readonly required min="1">' +
            // onkeyup="fnTtlProductPrice('+i+');"
            '</td>';

        html += '<td>' +
            '<input type="number" name="' + ColumnName[7] + '" id="' + ColumnID[7] + i +
            '" class="form-control round ttlAmountCls" value="0" readonly>' +
            '</td>';

        html += '<td>' +
            '<a href="javascript:void(0)" class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center clsAddNewRow" id="' +
            ColumnID[8] + i + '" onclick="btnRemoveRow(' + TotalRowID + ',' + rowID + ');">' +
            ' <i class="icon fa fa-times align-items-center"></i>' +
            '</a>' +
            '</td>';

        html += '</tr>';

        $('#' + RowCountID).val(i);
        $('#' + TotalRowID).val(countRow);
        // console.log($('#'+TotalRowID).val());
        // $('#tdTotalAmount').html($('#'+TotalRowID).val());
        $('#' + TableID + ' tbody').find('tr:first').before(html);


        $('#prod_group_id').val('');
        $('#prod_cat_id').val('');
        $('#prod_sub_cat_id').val('');
        $('#prod_model_id').val('');

        $('#prod_group_id').trigger('change');
        $('#prod_cat_id').trigger('change');
        $('#prod_sub_cat_id').trigger('change');
        $('#prod_model_id').trigger('change');
    } else {
        swal({
            icon: 'error',
            title: 'Oops...',
            text: 'Please Select Product!',
        });
    }
}
/* Add row End */

/* Remove row Start */
function btnRemoveRow(totalRows, id) {

    var rowId = $(id).attr('id');
    var totalRowID = $(totalRows).attr('id');
    var totalRow = $('#' + totalRowID).val()

    if (totalRow > 1) {
        var totalRow = totalRow - 1;
        $('#' + totalRowID).val(totalRow);
        $('#' + rowId).remove();
    } else {
        var colId = $('#' + rowId).find('a').attr('id');
        $('#' + colId).attr('disabled', true);
    }


    //function call for when user remove product from table than these fn calculate current product
    fnTotalQuantity();
    fnTotalAmount();
    //end--------------
}

/* Remove row End */

function fnPurAjaxModelSelect(ModelID) {
    var CompanyID = $('#comapny_id').val();
    var SupplierID = $('#supplier_id').val();

    var GroupID = $('#prod_group_id').val();
    var CategoryID = $('#prod_cat_id').val();
    var SubCatID = $('#prod_sub_cat_id').val();


    var firstRowFirstColId = $('#purchaseTable tbody tr td:first-child select').attr('id');

    if (GroupID != '' && CategoryID != '' && SubCatID != '') {

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

                    $('#' + firstRowFirstColId)
                        .empty()
                        .html(data)
                        .trigger('change');
                }
            }
        });
    } else {
        // Message Show Hobe if ModelID, GroupID, CategoryID, SubCatID,CompanyID,SupplierID
        swal({
            icon: 'error',
            title: 'Oops...',
            text: 'Please Select All Selected Fields!',
        });
    }
}

function fnGenBillNo(compID) {
    if (compID != '') {
        $.ajax({
            method: "GET",
            url: "{{ url('/ajaxGBillPurchase') }}",
            dataType: "text",
            data: {
                compID: compID
            },
            success: function(data) {
                if (data) {
                    $('#bill_no').val(data);
                }
            }
        });
    }
}

/*get selected sys barcode & product id & set into hidden input fields*/
function fnGetSelectedValue(RowId) {
    // var selProdSBar = $("#product_id_"+RowId).children("option:selected").attr('sbarcode');
    // var selProdName = $("#product_id_"+RowId).children("option:selected").attr('pname');
    var selProdCostPrice = $("#product_id_" + RowId).children("option:selected").attr('pcprice');

    // $('#sys_barcode_'+RowId).val(selProdSBar);
    // $('#product_name_'+RowId).val(selProdName);
    $('#unit_cost_price_' + RowId).val(selProdCostPrice);

    fnTtlProductPrice(RowId);

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

    $('#total_order_quantity').val(totalOrderQtn);
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
    //--------------------------- T/A After Discount
    $('#ta_after_discount').val(totalAmt);
    //---------------------------- Gross Total
    $('#total_payable_amount').val(totalAmt);
}

function fnCalDiscount(discountVal) {
    var TotalAmount = $('#total_amount').val();

    var TAAfterDiscount = (Number(TotalAmount) - ((Number(TotalAmount) * Number(discountVal)) / 100));

    //set the discount amount into discount_amount fields
    $('#discount_amount').val(((Number(TotalAmount) * Number(discountVal)) / 100));

    $('#ta_after_discount').val(TAAfterDiscount);
    //---------------------------- Gross Total
    $('#total_payable_amount').val(TAAfterDiscount);
}

function fnCalVat(vatVal) {
    var TAAfterDiscount = $('#ta_after_discount').val();

    var GrossAmount = (Number(TAAfterDiscount) + ((Number(TAAfterDiscount) * Number(vatVal)) / 100));

    $('#vat_amount').val(((Number(TAAfterDiscount) * Number(vatVal)) / 100));

    $('#total_payable_amount').val(GrossAmount);
}

function fnCalDue(paidAmount) {
    var GrossAmount = $('#total_payable_amount').val();
    var DueAmount = (Number(GrossAmount) - Number(paidAmount));

    $('#due_amount').val(DueAmount);
}


$('#submitButtonforPurchase').on('click', function(event) {
    event.preventDefault();

    if ($('#total_payable_amount').val() > 0) {
        $('#purchase_form').submit();
    } else {
        swal({
            icon: 'error',
            title: 'Oops...',
            text: 'Total payable amount must be gratter than zero !!',
        });
    }
});
</script>

<?php $j = 0;?>
@foreach($PurchaseDataD as $Pdata)

<script type="text/javascript">
fnTtlProductPrice({
    {
        $j
    }
});
</script>

<?php $j++;
?>
@endforeach
@endsection