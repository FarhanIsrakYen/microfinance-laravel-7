@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use App\Services\HtmlService as HTML;
use App\Services\PosService as POSS;
 ?>

<form method="post" data-toggle="validator" novalidate="true" id="sales_form" >
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
                    {!! HTML::forBranchFeild(true,'branch_id','branch_id', $SalesDataM->branch_id,'','Branch Froms') !!}
                </div>
            </div>

            <div class="row">
                <!--Form Left-->
                <div class="col-lg-6">

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title">Bill No</label>
                        <div class="col-lg-7">
                            <div class="input-group">
                                <input type="text" name="sales_bill_no" id="sales_bill_no" 
                                value="{{ $SalesDataM->sales_bill_no }}" class="form-control round" readonly>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="sales_type" value="{{ $SalesDataM->sales_type }}">

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title">Sales Date</label>
                        <div class="col-lg-7 input-group">
                            <div class="input-group-prepend ">
                                <span class="input-group-text ">
                                    <i class="icon wb-calendar round" aria-hidden="true"></i>
                                </span>
                            </div>
                            <?php
                                $SalesDate = new DateTime($SalesDataM->sales_date);
                                $SalesDate = (!empty($SalesDate)) ? $SalesDate->format('d-m-Y') : date('d-m-Y');
                            ?>
                            <input type="text" name="sales_date" id="sales_date" class="form-control round" value="{{ $SalesDate }}" readonly>
                        </div>
                    </div>

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title RequiredStar">Sales By</label>
                        <div class="col-lg-6 input-group">
                            {{-- Query for get all employee --}}
                            <?php $EmpList = Common::ViewTableOrderIn('hr_employees',
                                            ['is_delete' => 0],
                                            ['branch_id', HRS::getUserAccesableBranchIds()],
                                            ['employee_no', 'emp_name','emp_code'],
                                            ['emp_name', 'ASC']);
                            ?>
                            <select class="form-control round browser-default clsSelect2" name="employee_id"
                            id="employee_id" required>
                                <option  value="">Select Employee</option>
                                @foreach($EmpList as $EData)
                                    <option value="{{ $EData->employee_no }}"
                                        @if($EData->employee_no == $SalesDataM->employee_id) {{ 'selected' }} @endif>
                                        {{ $EData->emp_name. " (".$EData->emp_code.")" }}
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

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title">VAT Invoice No.</label>
                        <div class="col-lg-7">
                            <input type="text" class="form-control round" placeholder="Enter Invoice No" name="vat_chalan_no" id="vat_chalan_no" value="{{ $SalesDataM->vat_chalan_no }}">
                        </div>
                    </div>

                </div>
                <!--Form Right-->
                <div class="col-lg-6">

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title RequiredStar">Customer</label>
                        <div class="col-lg-7 input-group addSupplier">
                            {{-- Query for get all Customer --}}
                            <?php $CustList = Common::ViewTableOrderIn('pos_customers',
                                            ['is_delete' => 0],
                                            ['branch_id', HRS::getUserAccesableBranchIds()],
                                            ['id', 'customer_no','customer_name', 'customer_no', 'customer_mobile', 'customer_nid'],
                                            ['customer_name', 'ASC']);
                            ?>
                            <select class="form-control round browser-default clsSelect2" name="customer_id" id="customer_id" required onchange="fnCustDataLoad();">
                                <option  value="">Select Customer</option>
                                @foreach($CustList as $CData)
                                    <option value="{{ $CData->customer_no }}"  
                                    mobile_no="{{ $CData->customer_mobile }}" 
                                    nid_no="{{ $CData->customer_nid }}" 
                                    @if($SalesDataM->customer_id == $CData->customer_no) {{ 'selected' }} @endif>
                                    {{ $CData->customer_name." (".$CData->customer_no.")" }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                        @error('customer_id')
                            <div class="help-block with-errors is-invalid">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title RequiredStar">Mobile</label>
                        <div class="col-lg-7">
                            <!-- {{-- <input type="number" class="form-control round" placeholder="Enter Mobile No" name="customer_mobile" id="customer_mobile_ajx" readonly value="{{ $SalesDataM->customer_mobile }}"> --}} -->
                            <select class="form-control round browser-default clsSelect2" name="customer_mobile" id="customer_mobile_ajx" required onchange="fnCustNIDataLoad();">
                                <option value="">Select Customer Mobile</option>
                                @foreach($CustList as $CData)
                                <option value="{{ $CData->customer_mobile }}"  cust_id="{{ $CData->customer_no }}" nid_no="{{ $CData->customer_nid }}"
                                @if($SalesDataM->customer_mobile == $CData->customer_mobile) {{ 'selected' }} @endif> {{ $CData->customer_mobile }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                        @error('customer_mobile')
                            <div class="help-block with-errors is-invalid">{{ $message }}</div>
                        @enderror
                    </div>


                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title RequiredStar">National id</label>
                        <div class="col-lg-7">
                            {{-- <input type="number" class="form-control round" placeholder="Enter Customer NID" name="customer_nid" id="customer_nid" value="{{ $SalesDataM->customer_nid }}" readonly> --}}
                            <select class="form-control round browser-default clsSelect2" name="customer_nid" id="customer_nid" required onchange="fnCustMDataLoad();">
                                <option value="">Select Customer NID</option>
                                @foreach($CustList as $CData)
                                <option value="{{ $CData->customer_nid }}" cust_id="{{ $CData->customer_no }}" mobile_no="{{ $CData->customer_mobile }}"
                                @if($SalesDataM->customer_nid == $CData->customer_nid) {{ 'selected' }} @endif> {{ $CData->customer_nid }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                        @error('customer_nid')
                            <div class="help-block with-errors is-invalid">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title RequiredStar">Month</label>
                        <div class="col-lg-7 input-group">
                            {{-- Query for get all installment package --}}
                            <?php
                                $InstallmentList = Common::ViewTableOrder('pos_inst_packages',
                                    ['is_delete' => 0],
                                    ['id', 'prod_inst_month', 'prod_inst_profit'],
                                    ['id', 'DESC']);
                            ?>
                            <select id="inst_month" class="form-control round clsSelect2" disabled>
                                <option value="" month="0" instProfit="0">Select Product</option>
                                @foreach($InstallmentList as $InstData)
                                <option value="{{ $InstData->id }}"
                                    instProfit="{{ $InstData->prod_inst_profit }}"
                                    month="{{ $InstData->prod_inst_month }}"
                                    @if($SalesDataM->installment_month == $InstData->prod_inst_month) {{ 'selected' }} @endif>{{ $InstData->prod_inst_month }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" name="inst_package_id" id="inst_package_id" value="{{ $SalesDataM->inst_package_id}}">
                        <input type="hidden" name="installment_month" id="installment_month" value="{{ $SalesDataM->installment_month}}">
                        <input type="hidden" name="installment_rate" id="installment_rate" value="{{ $SalesDataM->installment_rate}}">
                    </div>

                    <?php
                        $SysDate = new DateTime(Common::systemCurrentDate());
                        $SysDate = $SysDate->format('Y-m-d');
                    ?>
                    <input type="hidden" id="sys_date_format" value="{{ $SysDate }}">
                    <input type="hidden" id="total_weeks" value="0">

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title RequiredStar">Installment Type</label>
                        <div class="col-lg-7 input-group">
                            <?php $instTypeList = Common::ViewTableOrder('gnl_installment_type',
                                            ['is_active' => 1],
                                            ['id', 'name'],
                                            ['id', 'ASC']);
                            ?>
                            <select id="installment_type" class="form-control round clsSelect2" disabled>
                                <option value="">Select type</option>
                                @foreach($instTypeList as $instType)
                                        <option value="{{ $instType->id }}" @if($SalesDataM->installment_type == $instType->id) {{ 'selected' }} @endif>{{ $instType->name }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="installment_type" id="inst_type" value="{{ $SalesDataM->installment_type}}">
                        </div>
                    </div>
                </div>
            </div>
            <!--End Panel Body-->
        </div>
        <!--End Panel 1-->
    </div>

    <!-- Panel 2 -->
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
                    <select class="form-control round clsSelect2" id="prod_group_id"
                    onchange="fnAjaxSelectBox('prod_cat_id',
                                            this.value,
                                            '{{ base64_encode('pos_p_categories')}}',
                                            '{{base64_encode('prod_group_id')}}',
                                            '{{base64_encode('id,cat_name')}}',
                                            '{{url('/ajaxSelectBox')}}'
                                            );
                                fnProductLoad();">
                        <option  value="">Select</option>
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
                    <select class="form-control round clsSelect2" id="prod_cat_id"
                    onchange="fnAjaxSelectBox('prod_sub_cat_id',
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
                    <select class="form-control round clsSelect2" id="prod_sub_cat_id"
                    onchange="fnAjaxSelectBox('prod_model_id',
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
                    <select class="form-control round clsSelect2" id="prod_model_id"
                    onchange="fnProductLoad();">
                        <option  value="">Select</option>
                        @foreach($ModelList as $MData)
                            <option value="{{ $MData->id }}">{{ $MData->model_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 text-left">
                    <strong class="text-danger" style="color: #3e8ef7;">Current Stock: <label id="current_stock"></label></strong>
                    <input type="hidden" id="stock_quantity_0" value="0">
                </div>
            </div>


            <table class="table table-hover table-striped table-bordered w-full text-center table-responsive" id="InstSalesTable">
                <thead class="scrollHead">
                    <tr>
                        <th width="25%" class="RequiredStar">Product Name</th>
                        <th width="15%">Serial No</th>
                        <th width="11%" class="RequiredStar">Quantity</th>
                        <th width="15%">Sales Price</th>
                        <th width="15%">Installment Amount</th>
                        <th width="15%">Total Amount</th>
                        <th width="4%"></th>
                    </tr>
                </thead>
                <tbody class="scrollBody">
                    <?php
                        $i = 0;
                        $TableID = "InstSalesTable";

                        $ColumnName = "product_id_arr[]&product_sno_arr[]&product_quantity_arr[]&unit_sales_price_arr[]&avg_inst_amt_arr[]&total_sales_price_arr[]";
                        $ColumnID = "product_id_&product_sno_&product_quantity_&unit_sales_price_&avg_inst_amt_&total_sales_price_";

                        $ProductList = Common::ViewTableOrder('pos_products',
                                        ['is_delete' => 0],
                                        ['id', 'product_name', 'cost_price', 'sale_price', 'prod_vat', 'sys_barcode', 'prod_barcode'],
                                        ['product_name', 'ASC']);
                    ?>
                    <tr>
                        <td width="25%" class="input-group barcodeWidth text-left">
                            <select id="product_id_0" class="form-control round clsSelect2"
                            onchange="fnAjaxCheckStock();">
                                <option value="">Select Product</option>
                                @foreach($ProductList as $ProductInfo)
                                <!-- 15-8-2020 Client & Ali vai er poramorse change kora hoyeche cost_rice to sale_price -->
                                <option value="{{ $ProductInfo->id }}" 
                                    pcprice="{{ $ProductInfo->cost_price }}"
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
                            <input type="text" id="product_sno_0" class="form-control round" placeholder="Serial no">
                        </td>

                        <td width="11%">
                            <input type="number" id="product_quantity_0" class="form-control round clsQuantity text-center"
                                placeholder="Enter Quantity" value="0"
                                onkeyup="fnTotalQuantity(); 
                                    fnTtlProductPrice(0); 
                                    fnCheckQuantity(0); 
                                    fnCalAvgInstAmnt(0);" min="1" readonly>
                        </td>

                        <td width="15%">
                            <input type="number" id="unit_sales_price_0" class="form-control round text-right" value="0"
                                min="1" readonly>
                        </td>

                        <td width="15%">
                            <input type="number" id="avg_inst_amt_0" class="form-control round clsInstAmt text-right" 
                            value="0" readonly min="1">
                        </td>

                        <td width="15%">
                            <input type="number" id="total_sales_price_0" class="form-control round ttlAmountCls text-right"
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

                    @if(count($SalesDataD) > 0)
                        @foreach($SalesDataD as $SDataD)
                            <?php $i++; ?>
                            <tr>
                                <td width="25%" class="barcodeWidth">
                                    <input type="hidden" id="product_id_{{ $i }}"  name="product_id_arr[]" value="{{ $SDataD->product_id }}">
                                    <input type="hidden" id="stock_quantity_{{ $i }}" value="{{(int)POSS::stockQuantity($SalesDataM->branch_id,$SDataD->product_id) + $SDataD->product_quantity}}">
                                    @foreach($ProductList as $ProductInfo)
                                        @if($ProductInfo->id == $SDataD->product_id)
                                            <input type="text" class="form-control round"
                                            value="{{ $ProductInfo->product_name . ' (' . $ProductInfo->sys_barcode . ')' }} " readonly>
                                        @endif
                                    @endforeach
                                </td>

                                <td width="15%">
                                    <!-- Input for System barcode  -->
                                    <input type="hidden" id="product_barcode_{{ $i }}"  name="product_barcode_arr[]" value="{{ $SDataD->product_barcode }}">
                                    <input type="hidden" id="product_system_barcode_{{ $i }}"  name="product_system_barcode_arr[]" value="{{ $SDataD->product_system_barcode }}">

                                    <input type="number" id="product_sno_{{ $i }}" name="product_sno_arr[]"
                                            class="form-control round" placeholder="Enter serial no"
                                            value="{{ $SDataD->product_serial_no }}">
                                </td>
                                <td width="11%">
                                    <input type="number" id="product_quantity_{{ $i }}" name="product_quantity_arr[]"
                                            class="form-control round clsQuantity text-center" placeholder="Enter Quantity"
                                            value="{{ $SDataD->product_quantity }}" 
                                            onkeyup="fnTotalQuantity(); 
                                            fnTtlProductPrice({{ $i }});
                                            fnCheckQuantity({{ $i }});
                                            fnCalAvgInstAmnt({{ $i }});" min="1">
                                </td>

                                <td width="15%">

                                    <input type="hidden" id="product_cost_price_{{ $i }}"  name="product_cost_price_arr[]" value="{{ $SDataD->product_cost_price }}">
                                    
                                    <input type="number" id="unit_sales_price_{{ $i }}" name="unit_sales_price_arr[]"
                                            class="form-control round text-right" value="{{ $SDataD->product_unit_price }}" min="1" readonly>
                                </td>


                                <td width="15%">
                                    <input type="number" id="avg_inst_amt_{{ $i }}" class="form-control round clsInstAmt text-right" value="0" readonly min="1">
                                </td>

                                <td width="15%">
                                    <input type="number" id="total_sales_price_{{ $i }}" name="total_sales_price_arr[]"
                                            class="form-control round ttlAmountCls text-right" value="{{ $SDataD->total_sales_price }}" min="1" readonly>
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
                        <td width="40%" style="text-align:right;">
                            <h5>TOTAL</h5>
                            <input type="hidden" name="total_quantity" id="total_quantity" value="0" min="1">

                            <input type="hidden" name="total_amount" id="total_amount" value="0">
                            <!-- <input type="hidden" name="total_cost_amount" id="total_cost_amount" value="0"> -->
                        </td>
                        <td width="11%" id="tdTotalQuantity" style="font-weight: bold;">0</td>

                        <td width="15%">
                            <h5 style="">Average Amount</h5>
                            <input type="hidden" id="total_inst_amt" value="0" min="1">
                        </td>

                        <td width="15%" id="tdTotalInstAmt" class="text-right" style="font-weight: bold; ">0.00</td>

                        <td width="15%" id="tdTotalAmount" class="text-right" style="font-weight: bold; ">0.00</td>
                        <td width="4%"></td>
                    </tr>
                </tfoot>
            </table>

            <!-- Row_Count is temporary variable for using row add and delete-->
            <input type="hidden" id="TotalRowID" value="{{ $i }}"/>

        </div>
        <!--End Panel 2-->
    </div>
    <!-- End Panel 2 -->

    <div class="panel">
        <div class="panel-body">
            <div class="row">
                <!--Form Left-->
                <div class="col-lg-6">

                </div>

                <!--Form Right-->
                <div class="col-lg-6">

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title">VAT(%)</label>
                        <div class="col-lg-7">
                            <input type="number" name="vat_rate" id="vat_rate" class="form-control round text-right"
                            value="{{ $SalesDataM->vat_rate }}"  onkeyup="fnCalVat(this.value);" autocomplete="true">

                            <input type="hidden" name="vat_amount" id="vat_amount" value="{{ $SalesDataM->vat_amount }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row align-items-center">
                            <div class="col-lg-4">
                                <label class="input-title">T/A After VAT</label>
                            </div>
                            <div class="col-lg-7">
                                <input type="number" name="ta_after_vat" id="ta_after_vat" class="form-control round text-right" 
                                value="{{ $SalesDataM->ta_after_vat }}" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title">Processing Fee</label>
                        <div class="col-lg-7">
                            <input type="number" name="service_charge" id="service_charge" 
                                class="form-control round text-right" 
                                value="{{ $SalesDataM->service_charge }}" 
                                onkeyup="fnCalPrsFee(this.value)">
                        </div>
                    </div>

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title">Total Payable &nbsp Amount</label>
                        <div class="col-lg-7">
                            <input type="number" name="total_payable_amount" id="total_payable_amount" class="form-control round text-right" 
                            value="{{ $SalesDataM->total_payable_amount }}" readonly min="1">
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title RequiredStar">Paid Amount</label>
                        <div class="col-lg-7">
                            <input type="number" name="paid_amount" id="paid_amount" class="form-control round textNumber text-right"
                            value="{{ $SalesDataM->paid_amount }}" 
                            onkeyup="fnCalDue(this.value);" 
                            onblur="fnCheckPaidAmt(this.value); fnCalInstAmt();" required min="1" autocomplete="off">
                            <!-- fnCalInstAmt(this.value); -->
                        </div>
                    </div>

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title">Due Amount</label>
                        <div class="col-lg-7">
                            <input type="number" name="due_amount" id="due_amount" class="form-control round text-right"
                            value="{{ $SalesDataM->due_amount }}" readonly>
                        </div>
                    </div>

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title">Actual Installment Amount</label>
                        <div class="col-lg-7">
                            <input type="number" name="instalment_actual" id="instalment_actual" 
                            class="form-control round text-right" value="{{ $SalesDataM->instalment_actual }}" readonly min="1">
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title">Extra Amount</label>
                        <div class="col-lg-7">
                            <input type="number" name="instalment_extra" id="instalment_extra" 
                            class="form-control round text-right" value="{{ $SalesDataM->instalment_extra }}" readonly min="1">
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title">Installment Amount</label>
                        <div class="col-lg-7">
                            <input type="number" name="installment_amount" id="installment_amount" 
                            class="form-control round text-right" value="{{ $SalesDataM->installment_amount }}" readonly min="1">
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>


                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title RequiredStar">Payment System</label>
                        <div class="col-lg-7 input-group">
                            <select class="form-control round clsSelect2" name="payment_system_id" id="payment_system_id" 
                            required data-error="Select Payment System">
                                <option value="0">Select</option>
                                <option value="1"
                                    @if($SalesDataM->payment_system_id == 1) {{ 'selected' }} @endif>Cash</option>
                                <option value="2"
                                    @if($SalesDataM->payment_system_id == 2) {{ 'selected' }} @endif>Others</option>
                            </select>
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
                            @if(Common::getBranchId() != 1)
                                @if($SalesDataM->sales_date == $SysDate)
                                    <button type="submit" class="btn btn-primary btn-round" id="submitButtonforPurchase">Update</button>
                                @endif
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
<!--End Page Content-->

<script type="text/javascript">

$(document).ready(function () {

   //initially set 0 into these fields
    $('#current_stock').html(0);
    $('#stock_quantity_0').val(0);

    var month = $('#inst_month').find("option:selected").attr('month');
    var profit = $('#inst_month').find("option:selected").attr('instProfit');
    var inst_package_id = $('#inst_month').val();

    $('#installment_month').val(month);
    $('#installment_rate').val(profit);
    $('#inst_package_id').val(inst_package_id);

    $('#inst_type').val($('#installment_type').val());

    //call function for get Total Quantity
    fnTotalQuantity();
    fnTotalAmount();
    fnTotalAvgInstAmount();

    fnCalInstAmt();
});

function fnProductLoad()
{
    var CompanyID = $('#company_id').val();
    var SupplierID = $('#supplier_id').val();
    var GroupID = $('#prod_group_id').val();
    var CategoryID = $('#prod_cat_id').val();
    var SubCatID = $('#prod_sub_cat_id').val();
    var ModelID = $('#prod_model_id').val();
    var firstRowFirstColId = $('#InstSalesTable tbody tr td:first-child select').attr('id');

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
        success: function (data) {
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


$(document).ready( function () {

    // Link to Installment Sale Entry
    var html = '<a target="_blank" href="{{url('pos/sales_cash/add')}}" class="btn btn-sm btn-primary btn-outline btn-round mr-2">';
        html += '<i class="icon wb-link" aria-hidden="true"></i>';
        html += '<span class="hidden-sm-down">&nbsp;Cash Sales Entry</span>';
        html += '</a>';

        html += '<a target="_blank" href="{{url('pos/sales_installment/add')}}" class="btn btn-sm btn-primary btn-outline btn-round mr-2">';
        html += '<i class="icon wb-link" aria-hidden="true"></i>';
        html += '<span class="hidden-sm-down">&nbsp;New Entry</span>';
        html += '</a>';

    $('.page-header-actions').prepend(html);
});
</script>

<script>
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

        actual_inst_amount = Math.round(actual_inst_amount);


        ////////////////////////
        var lastDigit = Number(Math.round(actual_inst_amount).toString().substr(-1));
        var extra_amount = 0;
        var instalment_actual = Number(actual_inst_amount);

        if (lastDigit > 0) {
            extra_amount = (10 - lastDigit) + (Math.round(actual_inst_amount) - actual_inst_amount);
            var inst_amount_temp = actual_inst_amount + extra_amount;
            var last_inst = inst_amount_temp - (extra_amount * (inst_count - 2));

            if (last_inst < 1) {
                if (lastDigit > 0 && lastDigit <= 5) {

                    extra_amount = (5 - lastDigit) + (Math.round(actual_inst_amount) - actual_inst_amount);
                    inst_amount_temp = actual_inst_amount + extra_amount;
                    last_inst = inst_amount_temp - (extra_amount * (inst_count - 2));
                }

                if (lastDigit > 5 && lastDigit <= 9) {

                    extra_amount = (10 - lastDigit) + (Math.round(actual_inst_amount) - actual_inst_amount);
                    inst_amount_temp = actual_inst_amount + extra_amount;
                    last_inst = inst_amount_temp - (extra_amount * (inst_count - 2));
                }

                if (last_inst < 1) {
                    extra_amount = Math.round(actual_inst_amount) - actual_inst_amount;
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
        // $('#paid_amount').val(0);
        //-----------------------------calculate Due amount
        fnCalDue($('#paid_amount').val());
        fnCalInstAmt();
    }

    function fnCalPrsFee(prsFee) {
        var ttlAmtWithPrsFee = Number($('#ta_after_vat').val()) + Number($('#service_charge').val());
        $('#total_payable_amount').val(ttlAmtWithPrsFee.toFixed(2));
        // $('#paid_amount').val(0);
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

        console.log(paid_amt);

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

        console.log($('#paid_amount').val());
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

            if ($('#total_quantity').val() > 0 && $('#product_id_0').val() == '' && paid_amt >= ttlPaidAmt && $('#due_amount').val() >= 0) {
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

</script>

{{-- <script src="{{ asset('../resources/views/POS/Sales/installmentSales.js') }}"></script> --}}

<?php $k=0; ?>
@foreach($SalesDataD as $SDataD)
<?php $k++; ?>
    <script>fnCalAvgInstAmnt({{$k}});</script>
@endforeach

@endsection
