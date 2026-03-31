@extends('Layouts.erp_master')
@section('content')
@include('elements.pop.purchase_modal')

<?php 
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
use App\Services\BillService as BILLS;
?>

<!-- Page -->
<form method="post" data-toggle="validator" novalidate="true" id="agreement_form" autocomplete="off">
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
                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title">Agreement No</label>
                        <div class="col-lg-7">
                            <div class="input-group">
                                <input type="text" name="agreement_no" id="agreement_no" class="form-control round"
                                    value="{{ $agreementData->agreement_no }}" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title RequiredStar">Customer</label>
                        <div class="col-lg-7 input-group">
                            <select class="form-control clsSelect2" name="customer_id" id="customer_id" 
                                required style="width: 100%">
                                <option value="">Select Customer</option>
                                @foreach($customerData as $cData)
                                <option value="{{ $cData->customer_no }}" {{ $cData->customer_no == $agreementData->customer_id ? 'selected' : '' }}>{{ $cData->customer_name. ' - ' . $cData->customer_no }}</option>
                                @endforeach
                            </select>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>
                    </div>

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title">Sales By</label>
                        <div class="col-lg-7 input-group">
                            <?php $salesByList = Common::ViewTableOrder('hr_employees',
                                            [['is_active', 1], ['is_delete', 0]],
                                            ['id','emp_name','emp_code'],
                                            ['emp_code', 'ASC']) ?>

                            <select class="form-control clsSelect2" name="sales_by" id="sales_by" style="width: 100%">
                                <option value="">Select</option>
                                @foreach($salesByList as $row)
                                <option value="{{ $row->id }}" {{ $agreementData->sales_by == $row->id ? 'selected' : '' }}>
                                    {{ $row->emp_code ? $row->emp_name . ' - '. $row->emp_code : $row->emp_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>


                </div>
                <!--Form Right-->
                <div class="col-lg-6">

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title">Agreement Date</label>
                        <div class="col-lg-7">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="icon wb-calendar round" aria-hidden="true"></i>
                                    </span>
                                </div>
                                <input type="text" name="agreement_date" id="agreement_date" class="form-control round" value="{{ $agreementData->agreement_date }}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title">Service Start Date</label>
                        <div class="col-lg-7">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="icon wb-calendar round" aria-hidden="true"></i>
                                    </span>
                                </div>
                                <input type="text" name="service_start_date" id="service_start_date"
                                value="{{ $agreementData->service_start_date }}" 
                                class="form-control round datepicker-custom" autocomplete="off"
                                placeholder="DD-MM-YYYY" required data-error="Please Select Service Start Date">
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>
                    </div>

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title">Service By</label>
                        <div class="col-lg-7 input-group">
                            <?php $serviceByList = Common::ViewTableOrder('hr_employees',
                                            [['is_active', 1], ['is_delete', 0]],
                                            ['id','emp_name','emp_code'],
                                            ['emp_code', 'ASC']) ?>

                            <select class="form-control clsSelect2" name="service_by" id="service_by" style="width: 100%">
                                <option value="">Select</option>
                                @foreach($serviceByList as $row)
                                <option value="{{ $row->id }}" {{ $agreementData->service_by == $row->id ? 'selected' : '' }}>
                                    {{ $row->emp_code ? $row->emp_name . ' - '. $row->emp_code : $row->emp_name }}
                                </option>
                                @endforeach
                            </select>
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

            <div class="text-right">
                <strong>Total Amount: <label id="tdTotalAmount">{{ $agreementData->total_amount }}</label></strong>
                <input type="hidden" name="total_amount" id="total_amount" value="{{ $agreementData->total_amount }}" min="1">
            </div>

            <table class="table table-hover table-striped table-bordered w-full text-center table-responsive my-custom-scrollbar"
                id="agreementTable">
                <thead>
                    <tr>
                        <th width="60%" class="RequiredStar">Product Name</th>
                        <!-- <th width="10%" class="RequiredStar">Quantity</th> -->
                        <th width="20%" class="RequiredStar">One Time Fee</th>
                        <th width="20%" class="RequiredStar">Monthly Fee</th>
                        <!-- <th width="5%"></th> -->
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 0;
                    $TableID = "agreementTable";

                    $ColumnName = "product_id_arr[]&product_type_arr[]&product_name_arr[]&product_quantity_arr[]&ordered_quantity_arr[]&received_quantity_arr[]&license_fee_arr[]&service_fee_arr[]";

                    $ColumnID = "product_id_&product_type_&product_name_&product_quantity_&ordered_quantity_&received_quantity_&license_fee_&service_fee_&deleteRow_";
                // 'company_id'=> $CompanyID,
                    $productList = Common::ViewTableOrder('bill_products',
                                    ['is_delete' => 0],
                                    ['id', 'product_name',  'sale_price', 'prod_vat'],
                                    ['product_name', 'ASC']);

                    $packageList = Common::ViewTableOrder('bill_packages',
                                    ['is_delete' => 0],
                                    ['id', 'package_name',  'package_price'],
                                    ['package_name', 'ASC']);
                    ?>
                    <!-- <tr>
                        <td class="barcodeWidth text-left">
                            <select id="product_id_0" class="form-control clsProductSelect" style="width: 100%;">
                                <option value="">Select Product/Package</option>
                                <optgroup label="Products">
                                    @foreach($productList as $productInfo)
                                    <option value="{{ $productInfo->id }}"
                                        type="1"
                                        pcprice="{{ $productInfo->sale_price }}"
                                        pname="{{ $productInfo->product_name }}">
                                        {{ $productInfo->product_name  }}
                                    </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Packages">
                                    @foreach($packageList as $productInfo)
                                    <option value="{{ $productInfo->id }}"
                                        type="2"
                                        pcprice="{{ $productInfo->package_price }}"
                                        pname="{{ $productInfo->package_name }}">
                                        {{ $productInfo->package_name }}
                                    </option>
                                    @endforeach
                                </optgroup>
                                
                            </select>
                        </td>

                        <td>
                            <input type="number" id="license_fee_0" class="form-control round licenseFeeClass text-right" value="0"
                                required min="1" readonly onkeyup="fnTtlProductPrice(0);">
                        </td>

                        <td>
                            <input type="number" id="service_fee_0" class="form-control round serviceFeeClass text-right"
                                value="0" min="1" readonly onkeyup="fnTtlProductPrice(0);">

                            <input type="hidden" id="product_type_0" class="form-control round text-right" readonly>
                        </td>

                        <td>
                            <a href="javascript:void(0);"
                                class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center"
                                onclick="btnAddNewRow('<?= $TableID ?>', '<?= $ColumnName ?>', '<?= $ColumnID ?>', 'TotalRowID');">
                                <i class="icon wb-plus align-items-center"></i>
                            </a>
                        </td>
                    </tr> -->

                    @if(count($agreementDataD) > 0)
                        @foreach($agreementDataD as $agreement)
                        <?php $i++; ?>
                        <tr>
                            <td class="barcodeWidth text-left">
                                <input type="hidden" id="product_id_{{ $i }}" name="product_id_arr[]" 
                                value="{{ $agreement->product_id }}">
                                @if($agreement->product_type == 1)
                                @foreach($productList as $productInfo)
                                @if($productInfo->id == $agreement->product_id)
                                <input type="text" class="form-control round"
                                    value="{{ $productInfo->product_name  }} "
                                    readonly>
                                @endif
                                @endforeach
                                @endif
                                @if($agreement->product_type == 2)
                                @foreach($packageList as $productInfo)
                                @if($productInfo->id == $agreement->product_id)
                                <input type="text" class="form-control round"
                                    value="{{ $productInfo->package_name  }} "
                                    readonly>
                                @endif
                                @endforeach
                                @endif
                            </td>

                            <td>
                                <input type="number" name="license_fee_arr[]" id="license_fee_{{ $i }}"
                                    class="form-control round clsQuantity text-right" placeholder="Enter License Fee"
                                    value="{{ $agreement->license_fee }}"
                                    onkeyup="fnTtlProductPrice({{ $i }});" required min="1">
                            </td>

                            <td>
                                <input type="number" name="service_fee_arr[]" id="service_fee_{{ $i }}"
                                    class="form-control round text-right" value="{{ $agreement->service_fee }}" required
                                    min="1">

                                <input type="hidden" name="product_type_arr[]" id="product_type_{{ $i }}" class="form-control round text-right" readonly 
                                value="{{ $agreement->product_type }}"> 
                            </td>

                            <!-- <td>
                                <a href="javascript:void(0)"
                                    class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center clsAddNewRow"
                                    id="" onclick="btnRemoveRow( {{ $i }} );">
                                    <i class="icon fa fa-times align-items-center"></i>
                                </a>
                            </td> -->
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>

            <!-- Row_Count is temporary variable for using row add and delete-->
            <input type="hidden" id="TotalRowID" value="{{ count($agreementDataD) }}" />

            <!-- <div class="mt-4 table-responsive">
                <table class="table table-striped table-bordered w-full text-center" id="tableQuanAmnt">
                    <tbody>
                        <tr>
                            <td width="60%">
                                <h5>Total Amount</h5>
                                <input type="hidden" name="total_amount" id="total_amount" value="{{ $agreementData->total_amount }}" min="1">
                            </td>
                            <td width="25%" id="tdTotalAmount" class="text-right" style="font-weight: bold;">{{ $agreementData->total_amount }}</td>
                        </tr>
                    </tbody>
                </table>
            </div> -->
        </div>
        <!--End Panel 2-->
    </div>


    <div class="row align-items-center">
        <div class="col-lg-12">
            <div class="form-group d-flex justify-content-center">
                <div class="example example-buttons">
                    <a href="javascript:void(0)" onclick="goBack();"
                              class="btn btn-default btn-round d-print-none">Back</a>

                        <button type="submit" class="btn btn-primary btn-round"
                        id="submitButton">Update</button>

                </div>
            </div>
        </div>
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
                    url: "{{ url('pos/purchase/popUpSupplierData') }}",
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
        var firstRowFirstColId = $('#agreementTable tbody tr td:first-child select').attr('id');

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

    $('#product_id_0').change(function() {

        if ($(this).val() != '') {
            $('#license_fee_0,#service_fee_0').prop('readonly', false);
            var selProdCostPrice = $(this).find("option:selected").attr('pcprice');

            var productType = $(this).find("option:selected").attr('type');

            $('#product_type_0').val(productType);
            $('#license_fee_0').val(selProdCostPrice);
            $('#total_amount').val( Number($('#total_amount').val()) + Number(selProdCostPrice) );
            $('#tdTotalAmount').html( (Number($('#total_amount').val())).toFixed(2) ) ;

            // fnTtlProductPrice(0);
        } else {
            $('#license_fee_0,#service_fee_0').prop('readonly', true);
            $('#license_fee_0,#service_fee_0').val(0);
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
         * ColumnID[6] = this is ID for input feild license_fee_0
         * ColumnID[7] = this is ID for input feild service_fee_0
         */
        if ($('#' + ColumnID[0] + 0).val() != '') {

            var TotalRowCount = $('#' + TotalRowID).val();
            /*
            marge two row if same product found
            */

            var test = $('#' + ColumnID[1] + 0).val();

            var oneTimeFee = $('#' + ColumnID[6] + 0).val();
            var monthlyFee = $('#' + ColumnID[7] + 0).val();

            var flag = false;

            var rowNumber = 0;

            for (var row = 1; row <= TotalRowCount; row++) {
                if ( $('#product_id_'+row).val() == $('#product_id_0').val()){
                    flag = true;
                    rowNumber = row ;
                }
            }


            if (flag === true){

                oneTimeFee = Number(oneTimeFee) + Number($('#license_fee_' + rowNumber).val());
                monthlyFee = Number(monthlyFee) + Number($('#service_fee_' + rowNumber).val());

                $('#license_fee_' + rowNumber).val(oneTimeFee);
                $('#service_fee_' + rowNumber).val(monthlyFee);
                $('#' + ColumnID[0] + 0).val('');
                $('#' + ColumnID[0] + 0).trigger('change');
                $('#' + ColumnID[3] + 0).val(0);
                $('#' + ColumnID[1] + 0).val(test);
                $('#' + ColumnID[5] + 0).val(0);
                $('#' + ColumnID[7] + 0).val(0);
                $('#' + ColumnID[3] + 0).prop('readonly', true);

            }

            else {
                TotalRowCount++;
                $('#' + TotalRowID).val(TotalRowCount);
                var ProductID = $('#' + ColumnID[0] + 0).val();
                var ProductName = $('#' + ColumnID[0] + 0).find("option:selected").attr('pname');
                // var prod_qtn = $('#' + ColumnID[0] + 0).find("option:selected").attr('prod_qtn');
                // var ProductBarcode = $('#' + ColumnID[0] + 0).find("option:selected").attr('pbarcode');
                var ProductType = $('#' + ColumnID[0] + 0).find("option:selected").attr('type');
                var ProductCostPrice = $('#' + ColumnID[0] + 0).find("option:selected").attr('pcprice');
                // var ProductSalePrice = $('#' + ColumnID[0] + 0).find("option:selected").attr('psprice');
                // var ProductQuantity = $('#' + ColumnID[3] + 0).val();
                var ProductAmount = $('#' + ColumnID[7] + 0).val();

                var html = '<tr>';

                html += '<td class="barcodeWidth" width="35%">';
                html += '<input type="hidden" id="product_id_'+ TotalRowCount +'" name="' + ColumnName[0] + '" value="' + ProductID + '">';

                html += '<input type="text" class="form-control round text-left" value="' + ProductName +'" readonly >';

                html += '<input type="hidden" id="product_type_'+ TotalRowCount +'" name="' + ColumnName[1] + '" value="' + ProductType + '">';

                html += '<td width="15%">' +
                    '<input type="number" name="' + ColumnName[6] + '" id="' + ColumnID[6] + TotalRowCount +
                    '" class="form-control round text-right" value="' + ProductCostPrice + '" readonly required min="1">' +
                    '</td>';
                html += '<td width="15%">' +
                    '<input type="number" name="' + ColumnName[7] + '" id="' + ColumnID[7] + TotalRowCount +
                    '" class="form-control round text-right" value="' + ProductAmount + '" readonly>' +
                    '</td>';
                html += '<td width="5%">' +
                    '<a href="javascript:void(0)" class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center" onclick="btnRemoveRow(' + TotalRowCount + ');">' +
                    ' <i class="icon fa fa-times align-items-center"></i>' + '</a>' +'</td>';

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


        }
    }
    /* Add row End */

    /* Remove row Start */
    function btnRemoveRow(RemoveID) {

        fnTotalAmount(RemoveID);
        $('#product_id_' + RemoveID).closest('tr').remove();
        $('#TotalRowID').val( Number($('#TotalRowID').val()) - Number(1) );
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
            'customer_id',
            this.value,
            '{{base64_encode("bill_customers")}}',
            '{{base64_encode("company_id")}}',
            '{{base64_encode("id,customer_name")}}',
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
        //         '{{base64_encode("pos_p_groups")}}',
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


    function fnTtlProductPrice(Row) {

        var ttlRow = $('#TotalRowID').val();
        var totalProductPrice = 0;
        var serviceFee = 0;

        var i;
        for (i = 0; i <= ttlRow; ++i) {
            var licenseFee = $('#license_fee_' + i).val();
            if (i > 0) {
                serviceFee = $('#service_fee_' + i).val();
            }
            totalProductPrice = (Number(serviceFee) + Number(licenseFee) + Number(totalProductPrice));  
        }
        $('#total_amount').val(totalProductPrice);
        $('#tdTotalAmount').html(totalProductPrice.toFixed(2));
        fnTotalAmount();

    }

    function fnTotalAmount(Row = null) {

        var totalAmt = $('#total_amount').val();
        
        if (Row) {
            var licenseFee = $('#license_fee_' + Row).val();
            var serviceFee = $('#service_fee_' + Row).val();
            totalAmt = ( Number(totalAmt) - Number(serviceFee) - Number(licenseFee));
        }

        $('.serviceFeeClass').each(function() {
            totalAmt = Number(totalAmt) + Number($(this).val());
        });
        $('#tdTotalAmount').html(totalAmt.toFixed(2));
        //-------------------------- Total Amount
        $('#total_amount').val(totalAmt.toFixed(2));
    }

    $('#submitButton').on('click', function(event) {
        event.preventDefault();

        if ($('#total_amount').val() > 0) {

            $('#agreement_form').submit();
        } else {

            if ($('#total_amount').val() <= 0) {
                swal({
                    icon: 'error',
                    title: 'Error',
                    text: 'Total Amount must be gratter than zero !!',
                });
            } 

            // else {
            //     swal({
            //         icon: 'error',
            //         title: 'Error',
            //         text: 'Product Entry must be empty!!',
            //     });
            // }

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
