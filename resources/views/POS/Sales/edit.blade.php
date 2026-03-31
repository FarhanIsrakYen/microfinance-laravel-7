@extends('Layouts.erp_master_full_width')
@section('content')
<?php 
use App\Services\CommonService as Common;
use App\Services\HrService as HRS; 
use App\Services\HtmlService as HTML;
use App\Services\PosService as POSS;
?>

<form method="post" data-toggle="validator" novalidate="true" id="sales_form">
    @csrf
    <div class="row">
        <div class="col-lg-9 pr-0">
            
            <div class="row">
                <div class="col-lg-8 offset-lg-3">
                    <!-- Html View Load  -->
                    {!! HTML::forCompanyFeild($SalesData->company_id) !!}
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8 offset-lg-3">
                    {!! HTML::forBranchFeild(false,'branch_id','branch_id',$SalesData->branch_id,'','Branch') !!}
                </div>
            </div>
            
            <div class="panel">
                <div class="panel-body">
                    <div class="row pt-4">
                        <div class="col-lg-3">
                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title">Customer</label>
                                <div class="col-lg-8 input-group addSupplier">

                                    <?php
                                        $CustList = Common::ViewTableOrderIn('pos_customers',
                                                    ['is_delete' => 0],
                                                    ['branch_id', HRS::getUserAccesableBranchIds()],
                                                    ['id', 'customer_no','customer_name', 'customer_no', 'customer_mobile', 'customer_nid'],
                                                    ['customer_name', 'ASC']);
                                        // dd($CustList);
                                    ?>
                                    <select class="form-control round browser-default clsSelect2" name="customer_id" id="customer_id" onchange="fnCustDataLoad();">
                                        <option  value="">Select Customer</option>
                                        @foreach($CustList as $CData)
                                            <option value="{{ $CData->customer_no }}"  
                                            mobile_no="{{ $CData->customer_mobile }}" 
                                            nid_no="{{ $CData->customer_nid }}" 
                                            @if($SalesData->customer_id == $CData->customer_no) {{ 'selected' }} @endif>
                                            {{ $CData->customer_name." (".$CData->customer_no.")" }}
                                            </option>
                                        @endforeach
                                    </select>

                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title">Mobile</label>
                                <div class="col-lg-8">
                                    <!-- {{--  <input type="number" class="form-control round" placeholder="Enter Mobile No"
                                        name="customer_mobile" id="customer_mobile_ajx" required> --}} -->
                                    <select class="form-control round browser-default clsSelect2" name="customer_mobile" id="customer_mobile_ajx" onchange="fnCustNIDataLoad();">
                                        <option value="">Select Customer Mobile</option>
                                        @foreach($CustList as $CData)
                                        <option value="{{ $CData->customer_mobile }}"  cust_id="{{ $CData->customer_no }}" nid_no="{{ $CData->customer_nid }}"
                                        @if($SalesData->customer_mobile == $CData->customer_mobile) {{ 'selected' }} @endif> {{ $CData->customer_mobile }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!-- <div class="col-lg-3">
                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title">National id</label>
                                <div class="col-lg-8">
                                    <select class="form-control round browser-default clsSelect2" name="customer_nid"
                                    id="customer_nid" onchange="fnCustMDataLoad();">
                                        <option value="">Select Customer NID</option>
                                        @foreach($CustList as $CData)
                                        <option value="{{ $CData->customer_nid }}" cust_id="{{ $CData->customer_no }}" mobile_no="{{ $CData->customer_mobile }}"
                                        @if($SalesData->customer_nid == $CData->customer_nid) {{ 'selected' }} @endif> {{ $CData->customer_nid }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div> 
                        </div> -->
                    </div>
                </div>
            </div>
            <div class="panel">
                <div class="panel-body">
                    <div class="row pt-4 pb-4">
                        <div class="col-lg-1 labelPercentSearch ">
                            <label for="textGroup" class="input-title">Group</label>
                        </div>
                        <div class="col-lg-2 inputPercentSearch input-group">
                            <!-- {{-- Query for get all group --}} -->
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
                            <!-- {{-- Query for get all category --}} -->
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
                            <!-- {{-- Query for get all Sub-category --}} -->
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
                            <!-- {{-- Query for get all Model --}} -->
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
                        <div class="col-md-6">
                            <strong class="text-danger"><span>Current Stock:</span> <label id="current_stock"></label></strong>
                            <input type="hidden" id="stock_quantity_0">
                        </div>
                    </div>

                    <table class="table table-hover table-striped table-bordered w-full text-center table-responsive" id="salesTable">
                        <thead class="scrollHead">
                            <tr>
                                <th width="35%" class="RequiredStar">Product Name</th>
                                <th width="15%" class="RequiredStar">Quantity</th>
                                <th width="15%">Sale Price</th>
                                <th width="15%">Total</th>
                                <th width="16%">Serial No</th>
                                <th width="4%"></th>
                            </tr>
                        </thead>
                        <tbody class="scrollBody">
                            <?php
                                $i = 0;
                                $TableID = "salesTable";

                                $ColumnName = "product_id_arr[]&sys_barcode_arr[]&product_name_arr[]&product_quantity_arr[]&product_barcode_arr[]&product_cost_price_arr[]&unit_sale_price_arr[]&product_sales_price_arr[]&product_serial_arr[]";

                                $ColumnID = "product_id_&sys_barcode_&product_name_&product_quantity_&product_barcode_&product_cost_price_&unit_sale_price_&product_sales_price_&product_serial_&deleteRow_";

                                $ProductList = Common::ViewTableOrder('pos_products',
                                                ['is_delete' => 0],
                                                ['id', 'product_name', 'cost_price', 'sale_price', 'prod_vat', 'sys_barcode', 'prod_barcode'],
                                                ['product_name', 'ASC']);
                            ?>
                            <tr>
                                <td width="35%" class="barcodeWidth text-left">
                                    {{-- name="product_id_arr[]" --}}
                                    <select id="product_id_0" class="form-control round clsProductSelect"
                                            onchange="fnAjaxCheckStock();" style="width: 100%">
                                        <option value="">Select Product</option>

                                        @foreach($ProductList as $ProductInfo)
                                        <option value="{{ $ProductInfo->id }}" pcprice="{{ $ProductInfo->cost_price }}"
                                            psprice="{{ $ProductInfo->sale_price }}"
                                            pname="{{ $ProductInfo->product_name }}"
                                            sbarcode="{{ $ProductInfo->sys_barcode }}"
                                            pbarcode="{{ $ProductInfo->prod_barcode }}">
                                            {{ $ProductInfo->product_name . ' (' . $ProductInfo->sys_barcode . ')' }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td width="15%">
                                    <!-- Input for System barcode  -->
                                    <input type="hidden" id="sys_barcode_0">

                                    <!-- Input for Product Name  -->
                                    <input type="hidden" id="product_name_0">

                                    <!-- Input for Product barcode  -->
                                    <input type="hidden" id="product_barcode_0">

                                    <!-- {{-- name="product_quantity_arr[]" --}} -->
                                    <input type="number" id="product_quantity_0" class="form-control round clsQuantity text-center"
                                        placeholder="Enter Quantity" value="0"
                                        onkeyup="fnTotalQuantity(); fnTtlProductPrice(0); fnCheckQuantity(0);" min="1" readonly>
                                </td>

                                <td width="15%">
                                    <!-- {{-- name="product_cost_price_arr[]" --}} -->
                                    <input type="hidden" id="product_cost_price_0" class="form-control round text-right" value="0"
                                        min="1" readonly>

                                    <!-- {{-- name="unit_sale_price_arr[]" --}} -->
                                    <input type="number" id="unit_sale_price_0" class="form-control round text-right" value="0"
                                        min="1" readonly>
                                </td>

                                <td width="15%">
                                    <!-- {{-- name="product_sale_price_arr[]" --}} -->
                                    <input type="number" id="product_sales_price_0"
                                        class="form-control round ttlAmountCls text-right" value="0" min="1" readonly>
                                </td>

                                <td width="16%">
                                    <!-- {{-- name="product_serial_arr[]"  --}} -->
                                    <input type="text" id="product_serial_0" class="form-control round text-left">
                                </td>

                                <td width="4%">
                                    <a href="javascript:void(0);"
                                        class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center clsAddNewRow"
                                        onclick="btnAddNewRow('<?= $TableID ?>', '<?= $ColumnName ?>', '<?= $ColumnID ?>', 'TotalRowID');">
                                        <i class="icon wb-plus  align-items-center"></i>
                                    </a>
                                </td>
                            </tr>

                            @if(count($SalesDataD) > 0)
                                @foreach($SalesDataD as $SDataD)
                                <?php $i++; ?>
                                <tr>
                                    <td width="35%" class="barcodeWidth">
                                        <input type="hidden" id="product_id_{{ $i }}" name="product_id_arr[]" value="{{ $SDataD->product_id }}">
                                        <input type="hidden" id="stock_quantity_{{ $i }}" value="{{(int)POSS::stockQuantity($SalesData->branch_id,$SDataD->product_id) + $SDataD->product_quantity}}">

                                        @foreach($ProductList as $ProductInfo)
                                        @if($ProductInfo->id == $SDataD->product_id)
                                        <input type="text" class="form-control round"
                                            value="{{ $ProductInfo->product_name . ' (' . $ProductInfo->sys_barcode . ')' }} "
                                            readonly style="height:auto">
                                        @endif
                                        @endforeach
                                    </td>

                                    <td  width="15%">
                                        <!-- Input for System barcode  -->
                                        <input type="hidden" name="sys_barcode_arr[]" value="{{ $SDataD->product_system_barcode }}" id="sys_barcode_{{ $i }}">

                                        <!-- Input for Product Name  -->
                                        <input type="hidden" name="product_name_arr[]"   id="product_name_{{ $i }}">

                                        <!-- Input for Product barcode  -->
                                        <input type="hidden" name="product_barcode_arr[]" value="{{ $SDataD->product_barcode }}" id="product_barcode_{{ $i }}">

                                        <input type="number" name="product_quantity_arr[]" id="product_quantity_{{ $i }}"
                                            class="form-control round clsQuantity text-center" placeholder="Enter Quantity"
                                            value="{{ $SDataD->product_quantity }}"
                                            onkeyup="fnTotalQuantity(); fnTtlProductPrice({{ $i }}); fnCheckQuantity({{ $i }})" required min="1">
                                    </td>

                                    <td width="15%">
                                        <!-- {{-- name="product_cost_price_arr[]" --}} -->
                                        <input type="hidden" name="product_cost_price_arr[]"
                                            id="product_cost_price_{{ $i }}" 
                                            value="{{ $SDataD->product_cost_price }}">

                                        <input type="number" name="unit_sale_price_arr[]" id="unit_sale_price_{{ $i }}"
                                            class="form-control round text-right" value="{{ $SDataD->product_unit_price }}"
                                            min="1" readonly>
                                    </td>

                                    <td width="15%">
                                        <input type="number" name="product_sales_price_arr[]"
                                            id="product_sales_price_{{ $i }}" class="form-control round ttlAmountCls text-right"
                                            value="{{ $SDataD->total_sales_price }}" min="1" readonly>
                                    </td>

                                    <td width="16%">
                                        <!-- {{-- name="product_serial_arr[]"  --}} -->
                                        <input type="text" name="product_serial_arr[]" id="product_serial_{{ $i }}"
                                            class="form-control round text-left" value="{{ $SDataD->product_serial_no }}">
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
                                <td width="35%" style="text-align:right;">
                                    <h5>TOTAL</h5>
                                    <input type="hidden" name="total_quantity" id="total_quantity" value="0"
                                        min="1">
                                </td>
                                <td width="15%" id="tdTotalQuantity" style="font-weight: bold;">0</td>
                                <td width="15%">
                                    <h5>Amount</h5>
                                    <input type="hidden" name="total_amount" id="total_amount" value="0">
                                    <!-- <input type="hidden" name="total_cost_amount" id="total_cost_amount" value="0"> -->
                                </td>
                                <td width="15%" id="tdTotalAmount" class="text-right" style="font-weight: bold; ">0.00</td>
                                <td width="20%"></td>
                            </tr>
                        </tfoot>
                    </table>

                    <!-- Row_Count is temporary variable for using row add and delete-->
                    <input type="hidden" id="TotalRowID" value="{{ $i }}" />

                </div>
                <!--End Panel 2-->
            </div>
        </div>

        <div class=" col-lg-3">
            <div class="panel">
                <div class="panel-body p-4">

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-5 input-title">Sales Date</label>
                        <div class="col-lg-7 input-group">
                            <!-- <div class="input-group-prepend ">
                                <span class="input-group-text ">
                                    <i class="icon wb-calendar round" aria-hidden="true"></i>
                                </span>
                            </div> -->
                            <?php
                                $SalesDate = new DateTime($SalesData->sales_date);
                                $SalesDate = (!empty($SalesDate)) ? $SalesDate->format('d-m-Y') : date('d-m-Y');
                            ?>
                            <input type="hidden" name="sales_date" id="sales_date" class="form-control round"
                                value="{{ $SalesDate }}" readonly>
                            <label id="sales_date" class="text-black">{{ $SalesDate }}</label>
                        </div>
                    </div>

                    <input type="hidden" name="sales_type" id="sales_type" value="1">
                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-5 input-title">Bill No</label>
                        <div class="col-lg-7">
                            <div class="input-group">
                                <input type="hidden" name="sales_bill_no" id="sales_bill_no"
                                    class="form-control round" value="{{ $SalesData->sales_bill_no }}" readonly>
                                <label id="sales_bill_no" class="text-black">
                                    {{ $SalesData->sales_bill_no }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-5 input-title">VAT Invoice No.</label>
                        <div class="col-lg-7">
                            <input type="text" class="form-control round" placeholder="Enter Invoice No"
                                name="vat_chalan_no" id="vat_chalan_no" value="{{ $SalesData->vat_chalan_no }}">
                        </div>
                    </div>


                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-5 input-title RequiredStar">Sales By</label>
                        <div class="col-lg-7 input-group">
                            <!-- {{-- Query for get all Employee --}} -->
                            <?php
                                $EmpList = Common::ViewTableOrderIn('hr_employees',
                                            ['is_delete' => 0],
                                            ['branch_id', HRS::getUserAccesableBranchIds()],
                                            ['employee_no', 'emp_name','emp_code'],
                                            ['emp_name', 'ASC']);
                            ?>
                            <select required class="form-control clsSelect2"
                                    name="employee_id" id="employee_id">
                                <option value="">Select Employee</option>
                                @foreach($EmpList as $eData)
                                    <option value="{{ $eData->employee_no }}" 
                                    @if($SalesData->employee_id == $eData->employee_no) {{ 'selected' }} @endif >
                                    {{ $eData->emp_name. " (". $eData->emp_code.")" }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <br>
                        <div class="help-block with-errors is-invalid"></div>
                        @error('employee_id')
                            <div class="help-block with-errors is-invalid">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="panel">
                <div class="panel-body" style="padding: 18px;">
                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-5 input-title">Discount(%)</label>
                        <div class="col-lg-7">
                            <input type="number" name="discount_rate" id="discount_rate"
                                class="form-control round text-right" value="{{ $SalesData->discount_rate }}"
                                onkeyup="fnCalDiscount(this.value);">

                            <!-- {{-- hidden fields for discount amount --}} -->
                            <input type="hidden" name="discount_amount" id="discount_amount" value="0">
                        </div>
                    </div>


                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-5 input-title">T/A After Discount</label>
                        <div class="col-lg-7">
                            <input type="number" name="ta_after_discount" id="ta_after_discount"
                                class="form-control round text-right" value="0" readonly>
                        </div>
                    </div>

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-5 input-title">VAT(%)</label>
                        <div class="col-lg-7">
                            <input type="number" name="vat_rate" id="vat_rate" class="form-control round text-right"
                                value="{{ $SalesData->vat_rate }}" onkeyup="fnCalVat(this.value);">

                            <input type="hidden" name="vat_amount" id="vat_amount"
                                value="{{ $SalesData->vat_amount }}">
                        </div>
                    </div>

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-5 input-title RequiredStar">Total Payable &nbsp &nbsp Amount</label>
                        <div class="col-lg-7">
                            <input type="number" name="total_payable_amount" id="total_payable_amount" class="form-control round text-right"
                            value="{{ $SalesData->total_payable_amount }}" readonly min="1">

                            {{-- paid amount --}}
                            <input type="hidden" name="paid_amount" id="paid_amount" class="form-control round text-right" value="{{ $SalesData->paid_amount }}"
                            onkeyup="fnCalDue(this.value);" readonly>


                            {{-- due amount --}}
                            <input type="hidden" name="due_amount" id="due_amount" class="form-control round text-right" value="{{ $SalesData->due_amount }}" readonly>
                        </div>
                    </div>

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-5 input-title RequiredStar">Payment System</label>
                        <div class="col-lg-7 input-group">
                            <select class="form-control clsSelect2" name="payment_system_id" id="payment_system_id" 
                            required data-error="Select Payment System">
                                <option value="1" @if($SalesData->payment_system_id ==
                                    1){{ 'selected' }}@endif>Cash</option>
                                <option value="2" @if($SalesData->payment_system_id ==
                                    2){{ 'selected' }}@endif>Others</option>
                            </select>
                        </div>
                    </div>

                    {{-- <div class="form-row form-group align-items-center">
                        <label class="col-lg-5 input-title">Paid Amount</label>
                        <div class="col-lg-7">
                            <input type="number" name="paid_amount" id="paid_amount" class="form-control round text-right" 
                            value="{{ $SalesData->paid_amount }}" onkeyup="fnCalDue(this.value);" readonly>
                        </div>
                    </div>

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-5 input-title">Due Amount</label>
                        <div class="col-lg-7">
                            <input type="number" name="due_amount" id="due_amount" class="form-control round text-right" value="{{ $SalesData->due_amount }}" readonly>
                        </div>
                    </div> --}}
                    <div class="col-lg-12 pt-4">
                        <div class="d-flex justify-content-center">
                            <a href="javascript:void(0)" onclick="goBack();"
                                    class="btn btn-default btn-round d-print-none mr-2">Back</a>
                            @if(Common::getBranchId() != 1)
                                @if(date('d-m-Y', strtotime($SalesData->sales_date)) == Common::systemCurrentDate())
                                <button type="submit" class="btn btn-primary btn-round"
                                    id="updateButtonforSales">Update</button>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- <div class="panel">
        <div class="panel-body">
            <div class="row align-items-center">
                <div class="col-lg-12">
                    <div class="d-flex justify-content-center">
                        <a href="javascript:void(0)" onclick="goBack();"
                                class="btn btn-default btn-round d-print-none mr-2">Back</a>
                        @if(Common::getBranchId() != 1)
                            @if(date('d-m-Y', strtotime($SalesData->sales_date)) == Common::systemCurrentDate())
                            <button type="submit" class="btn btn-primary btn-round"
                                id="updateButtonforSales">Update</button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div> -->

</form>
</div>
<!--End Page Content-->

<!-- Pop Up Modal Load  -->
@include('elements.pop.customeradd')
<style type="text/css">
    #pageName {
        margin-top: 0;
    }
     .panel-body {
        padding: 8px 8px; 
    }
    .text-black{
        color: #000;
        padding-left: 15px;
    }
    .form-group-custom {
        margin-bottom: 0.429rem;
    }
    /* .form-control, .input-group-text {
        height: 20px; 
    } */
    .page-content {
        padding: 0px 20px;
    }
    .scrollBody {
        height: 260px;
    }
</style>

<script type="text/javascript">
    /* Pop Up Supplier Start */
    $(document).ready(function() {

        $('.clsProductSelect').select2();

        //initially set 0 into these fields
        $('#current_stock').html(0);
        $('#stock_quantity_0').val(0);

        // Generate bill on changing branch Id
        $('#branch_id').change(function() {
            fnGenBillNo($('#branch_id').val());
        });


        // Load Product On selection of prod Group,Cat and Sub Cat Start
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
        // Load Product On selection of prod Group,Cat and Sub Cat End

        //call function for get Total Quantity
        fnTotalQuantity();
        fnTotalAmount();

        fnCalDiscount({{ $SalesData->discount_rate }});
        fnCalVat({{ $SalesData->vat_rate }});
    });
    /* Pop Up Supplier End*/


    /* Addrow Start */
    function btnAddNewRow(TableID, ColumnNameS, ColumnIDS, TotalRowID) {

        var ColumnName = ColumnNameS.split("&");
        var ColumnID = ColumnIDS.split("&");

        // console.log(ColumnID);

        /*
        0: "product_id_"
        1: "sys_barcode_"
        2: "product_name_"
        3: "product_quantity_"
        4: "product_barcode_"
        5: "product_cost_price_"
        6: "product_unit_price_"
        7: "product_sales_price_"
        8: "product_serial_"
        9: "deleteRow_"
        */
        if ($('#' + ColumnID[0] + 0).val() != '' && $('#product_quantity_0').val() > 0 && $('#stock_quantity_0').val() != 0
                && ($('#stock_quantity_0').val() >= $('#product_quantity_0').val())) {

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
                $('#' + ColumnID[6] + 0).val(0);
                $('#' + ColumnID[7] + 0).val(0);
                $('#' + ColumnID[8] + 0).val('');
                $('#' + ColumnID[3] + 0).prop('readonly', true);

                $('#current_stock').html(0);
                fnTotalQuantity();
                fnTtlProductPrice(rowNumber);

                fnCalDiscount($('#discount_rate').val());
                fnCalVat($('#vat_rate').val());
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

                var ProductSerial = $('#' + ColumnID[8] + 0).val();
                // console.log(ProductSerial)
                var StockQuantity = $('#stock_quantity_0').val();


                var rowID = 'purchaseRow_' + TotalRowCount;
                var html = '<tr id="' + rowID + '">';

                html += '<td class="barcodeWidth" width="35%">';
                html += '<input type="hidden" id="product_id_'+ TotalRowCount +'" name="' + ColumnName[0] + '" value="' + ProductID + '">';
                html += '<input type="hidden" id="stock_quantity_'+ TotalRowCount +'" value="' + StockQuantity + '">';
                html += '<input type="text" class="form-control round" value="' + ProductName + '(' + ProductSysBarcode + ')' +
                    '" readonly style="height:auto">';

                html += '</td>';
                html += '<td width="15%">';

                // Input Feild For sys_barcode
                html += '<input type="hidden" name="' + ColumnName[1] + '" id="' + ColumnID[1] + TotalRowCount + '" value="' +
                    ProductSysBarcode + '">';
                // Input Feild For Product Name
                html += '<input type="hidden" name="' + ColumnName[2] + '" id="' + ColumnID[2] + TotalRowCount + '" value="' +
                    ProductName + '">';

                html += '<input type="number" name="' + ColumnName[3] + '" id="' + ColumnID[3] + TotalRowCount +
                    '" class="form-control round clsQuantity text-center" value="' + ProductQuantity +
                    '" onkeyup="fnTotalQuantity();fnTtlProductPrice(' + TotalRowCount + '); fnCheckQuantity(' + TotalRowCount + ');" required min="1">';
                html += '</td>';

                // Input Feild For Product Barcode
                html += '<input type="hidden" name="' + ColumnName[4] + '" id="' + ColumnID[4] + TotalRowCount + '" value="' +
                    ProductBarcode + '">';

                // Input Feild For Product Cost Price
                html += '<input type="hidden" name="' + ColumnName[5] + '" id="' + ColumnID[5] + TotalRowCount + '" value="' +
                    ProductCostPrice + '">';

                html += '<td  width="15%">';

                html += '<input type="number" name="' + ColumnName[6] + '" id="' + ColumnID[6] + TotalRowCount +
                    '" class="form-control round text-right" value="' + ProductSalePrice + '" readonly required min="1">' +
                    // onkeyup="fnTtlProductPrice('+TotalRowCount+');"
                    '</td>';

                html += '<td  width="15%">' +
                    '<input type="number" name="' + ColumnName[7] + '" id="' + ColumnID[7] + TotalRowCount +
                    '" class="form-control round ttlAmountCls text-right" value="' + ProductAmount + '" readonly>' +
                    '</td>';

                html += '<td  width="16%">' +
                    '<input type="text" name="' + ColumnName[8] + '" id="' + ColumnID[8] + TotalRowCount +
                    '" class="form-control round" value="' + ProductSerial + '">' +
                    '</td>';

                html += '<td  width="4%">' +
                    '<a href="javascript:void(0)" class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center clsAddNewRow" id="' +
                    ColumnID[9] + TotalRowCount + '" onclick="btnRemoveRow(this);">' +
                    ' <i class="icon fa fa-times align-items-center"></i>' +
                    '</a>' +
                    '</td>';

                html += '</tr>';

                // $('#' + TableID).append(html);
                $('#' + TableID +' tbody').find('tr:first').after(html);

                $('#' + ColumnID[0] + 0).val('');
                $('#' + ColumnID[0] + 0).trigger('change');
                // $('#' + ColumnID[0] + 0).trigger('change');
                $('#' + ColumnID[3] + 0).val(0);
                $('#' + ColumnID[8] + 0).val('');
                $('#' + ColumnID[7] + 0).val(0);
                $('#' + ColumnID[6] + 0).val(0);
                $('#' + ColumnID[5] + 0).val(0);
                $('#' + ColumnID[8] + 0).val('');
                $('#' + ColumnID[3] + 0).prop('readonly', true);
                $('#' + ColumnID[8] + 0).prop('readonly', true);

                $('#current_stock').html(0);

                fnCalDiscount($('#discount_rate').val());
                fnCalVat($('#vat_rate').val());
            }
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

    // Load Products in table
    function fnProductLoad() {
        var CompanyID = $('#company_id').val();
        var CustomerID = $('#customer_id').val();
        var GroupID = $('#prod_group_id').val();
        var CategoryID = $('#prod_cat_id').val();
        var SubCatID = $('#prod_sub_cat_id').val();
        var ModelID = $('#prod_model_id').val();

        var firstRowFirstColId = $('#purchaseTable tbody tr td:first-child select').attr('id');

        $.ajax({
            method: "GET",
            url: "{{url('/ajaxProductLSales')}}",
            dataType: "text",
            data: {
                ModelID: ModelID,
                GroupID: GroupID,
                CategoryID: CategoryID,
                SubCatID: SubCatID,
                CompanyID: CompanyID,
                CustomerID: CustomerID
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


    // On selecting a product, set quantity, cost
    $('#product_id_0').change(function() {

        if ($(this).val() != '') {
            $('#product_quantity_0').prop('readonly', false);
            $('#product_serial_0').val('');
            $('#product_serial_0').prop('readonly', false);

            var selProdCostPrice = $(this).find("option:selected").attr('pcprice');
            $('#product_cost_price_0').val(selProdCostPrice);

            var selProdSalePrice = $(this).find("option:selected").attr('psprice');
            $('#unit_sale_price_0').val(selProdSalePrice);

            fnTtlProductPrice(0);
        } else {
            $('#product_quantity_0').prop('readonly', true);
            $('#product_serial_0').prop('readonly', true);
        }
    });

    // Check Stock
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

    // Check Quantity
    function fnCheckQuantity(Row) {

        var StockQuantity = Number($('#stock_quantity_'+ Row).val());
        var TypeQuantity = Number($('#product_quantity_'+ Row).val());
        //console.log(Number($('#product_stock_'+ Row).val()));

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

    // Generate Bill No
    function fnGenBillNo(BranchId)
    {
        if (BranchId != '') {
            $.ajax({
                method: "GET",
                url: "{{ url('/ajaxGBillSales') }}",
                dataType: "text",
                data: {BranchId: BranchId},
                success: function (data) {
                    if (data) {
                        $('#sales_bill_no').val(data);
                    }
                }
            });
        }
    }

    // Calculate Total Quantity
    function fnTotalQuantity() {

        var totalQtn = 0;
        $('.clsQuantity').each(function() {
            totalQtn = Number(totalQtn) + Number($(this).val());
        });
        $('#total_quantity').val(totalQtn);
        $('#tdTotalQuantity').html(totalQtn);
    }

    // Calculate Total Price Of each Product
    function fnTtlProductPrice(Row) {

        var ProductQtn = $('#product_quantity_' + Row).val();
        var ProductPrice = $('#unit_sale_price_' + Row).val();
        var TotalProductPrice = (Number(ProductQtn) * Number(ProductPrice));
        $('#product_sales_price_' + Row).val(TotalProductPrice.toFixed(2));
        fnTotalAmount();
    }

    // Calculate Total Price Of all Product
    function fnTotalAmount() {

        var totalAmt = 0;
        $('.ttlAmountCls').each(function() {
            totalAmt = Number(totalAmt) + Number($(this).val());
        });
        $('#tdTotalAmount').html(totalAmt.toFixed(2));
        //-------------------------- Total Amount
        $('#total_amount').val(totalAmt.toFixed(2));
        //--------------------------- T/A After Discount
        $('#ta_after_discount').val(totalAmt.toFixed(2));
        //---------------------------- Gross Total
        $('#total_payable_amount').val(totalAmt.toFixed(2));
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

    function fnCustomerMobileLoad() {
        var CustomerID = $('#customer_id').val();
        // var CompanyID = $('#company_id').val();

        $.ajax({
            method: "GET",
            url: "{{url('/ajaxCustomerMobileLoad')}}",
            dataType: "text",
            data: {
                CustomerID: CustomerID,
            },
            success: function(data) {
                if (data) {
                    console.log(data);
                    $('#customer_mobile').val(data);
                }
            }
        });
    }


    function fnCustomerNIDLoad() {
        var CustomerID = $('#customer_id').val();
        // var CompanyID = $('#company_id').val();

        $.ajax({
            method: "GET",
            url: "{{url('/ajaxCustomerNIDLoad')}}",
            dataType: "text",
            data: {
                CustomerID: CustomerID,
            },
            success: function(data) {
                if (data) {
                    console.log(data);
                    $('#customer_nid').val(data);
                }
            }
        });
    }

    function fnCustDataLoad() {

        var CustomerID = $('#customer_id').val();
        var CustomerM = $('#customer_mobile_ajx').val();
        var CustomerNID = $('#customer_nid').val();
        var selectedData;

        if (CustomerID != '') {
            selectedData = CustomerID;
        }
        else if (CustomerM != '') {
            selectedData = CustomerM;
        }
        else if (CustomerNID != '') {
            selectedData = CustomerNID;
        }

        $.ajax({
            method: "GET",
            url: "{{url('/ajaxCustDataLoad')}}",
            dataType: "JSON",
            data: {
                selectedData: selectedData,
            },
            success: function(data) {
                if (data) {

                    if (CustomerID != '') {

                        $('#customer_mobile_ajx')
                                    .find('option')
                                    .remove()
                                    .end()
                                    .append('<option value="' + data.customer_mobile + '">' + data.customer_mobile + '</option>');

                        $('#customer_nid')
                                    .find('option')
                                    .remove()
                                    .end()
                                    .append('<option value="' + data.customer_nid + '">' + data.customer_nid + '</option>');
                    }
                    else if (CustomerM != '') {

                        $('#customer_id')
                                    .find('option')
                                    .remove()
                                    .end()
                                    .append('<option value="' + data.id + '">' + data.customer_name + '</option>');

                        $('#customer_nid')
                                    .find('option')
                                    .remove()
                                    .end()
                                    .append('<option value="' + data.customer_nid + '">' + data.customer_nid + '</option>');
                    }
                    else if (CustomerNID != '') {
                        $('#customer_id')
                                    .find('option')
                                    .remove()
                                    .end()
                                    .append('<option value="' + data.id + '">' + data.customer_name + '</option>');
                        $('#customer_mobile_ajx')
                                    .find('option')
                                    .remove()
                                    .end()
                                    .append('<option value="' + data.customer_mobile + '">' + data.customer_mobile + '</option>');
                    }

                }
            }
        });
    }

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

    $('#updateButtonforSales').on('click', function(event) {
        event.preventDefault();

        $(this).prop('disabled', true);

        var branchID = $('#branch_id').val();

        if (branchID != 1) {

            if ($('#total_quantity').val() > 0) {
                $('#sales_form').submit();
                $(this).prop('disabled', false);

            } else {
                $(this).prop('disabled', false);
                swal({
                    icon: 'error',
                    title: 'Error',
                    text: 'Total Quantity must be gratter than zero !!',
                });
            }
        }
        else if (branchID == 1){
            $(this).prop('disabled', false);
            swal({
                icon: 'error',
                title: 'Error',
                text: 'Access Denied ! You are not authorized in this page',
                confirmButtonText: "Ok"
            }).then((isConfirm) => {
                if (isConfirm) {
                    window.location.href = "{{url('pos/sales_cash')}}";
                }
            });

        }
    });

    // // Disable Button for multiple click
    // $('#sales_form').submit(function (event) {
    //     $(this).find(':submit').attr('disabled', 'disabled');
    // });

    $(document).ready( function () {

        // Link to Installment Sale Entry
        var html = '<a target="_blank" href="{{url('pos/sales_installment/add')}}" class="btn btn-sm btn-primary btn-outline btn-round mr-2">';
            html += '<i class="icon wb-link" aria-hidden="true"></i>';
            html += '<span class="hidden-sm-down">&nbsp;Installment Entry</span>';
            html += '</a>';

            html += '<a target="_blank" href="{{url('pos/sales_cash/add')}}" class="btn btn-sm btn-primary btn-outline btn-round mr-2">';
            html += '<i class="icon wb-link" aria-hidden="true"></i>';
            html += '<span class="hidden-sm-down">&nbsp;New Entry</span>';
            html += '</a>';

        $('.page-header-actions').prepend(html);
    });
</script>


@endsection
