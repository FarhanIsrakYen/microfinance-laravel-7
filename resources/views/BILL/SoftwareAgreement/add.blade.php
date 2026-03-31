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
                        <label class="col-lg-4 input-title RequiredStar">Agreement No</label>
                        <div class="col-lg-7">
                            <div class="input-group">
                                <input type="text" name="agreement_no" id="agreement_no" class="form-control round"
                                    value="{{ BILLS::generateSoftwareAgreementNo(Common::getBranchId()) }}" required readonly>
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>
                    </div>

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title RequiredStar">Customer</label>
                        <div class="col-lg-7">
                            <select class="form-control clsSelect2" name="customer_id" id="customer_id" 
                                required style="width: 100%">
                                <option value="">Select Customer</option>
                                @foreach($customerData as $cData)
                                <option value="{{ $cData->id }}">{{ $cData->customer_name. ' - ' . $cData->customer_no }}</option>
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
                                <option value="{{ $row->id }}">
                                    {{ $row->emp_code ? $row->emp_name . ' - '. $row->emp_code : $row->emp_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title RequiredStar">No Of Branch</label>
                        <div class="col-lg-7">
                            <div class="input-group">
                                <input type="text" name="no_of_branch" id="no_of_branch" class="form-control round textNumber"
                                    required data-error="Please Enter No Of Branch" placeholder="Enter No Of Branch"
                                    onkeyup="checkProduct()">
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>
                    </div> -->

                    

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
                                <input type="text" name="agreement_date" id="agreement_date" class="form-control round" value="{{ Common::systemCurrentDate() }}" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title">Agreement End Date</label>
                        <div class="col-lg-7">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="icon wb-calendar round" aria-hidden="true"></i>
                                    </span>
                                </div>
                                <input type="text" name="agreement_end_date" id="agreement_end_date" class="form-control round 
                                datepicker-custom" placeholder="DD-MM-YYYY">
                            </div>
                        </div>
                    </div>
                    <!-- <div class="form-row">
                        <label class="col-lg-4 input-title">Service Start Date</label>
                        <div class="col-lg-7">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="icon wb-calendar round" aria-hidden="true"></i>
                                    </span>
                                </div>
                                <input type="text" name="service_start_date" id="service_start_date"
                                class="form-control round datepicker-custom" autocomplete="off"
                                placeholder="DD-MM-YYYY" required data-error="Please Select Service Start Date">
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>
                    </div> -->
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
                                <option value="{{ $row->id }}">
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
                <strong>Total Amount: <label id="tdTotalAmount">0</label></strong>
                <input type="hidden" name="total_amount" id="total_amount" value="0" min="1">
            </div>

            <table class="table table-hover table-striped table-bordered w-full text-center table-responsive my-custom-scrollbar"
                id="agreementTable">
                <thead>
                    <tr>
                        <th width="15%" class="" rowspan="2">
                            <select class="clsSelect2" style="width: 100%" id="selProduct">
                                <option value="">Select Product/Package</option>
                                <option value="1">Product</option>
                                <option value="2">Package</option>
                            </select>
                        </th>
                        <th width="7%" class="" rowspan="2">Quantity</th>
                        <th width="8%" class="RequiredStar" rowspan="2">No of Branch</th>
                        <th width="12%" class="RequiredStar" rowspan="2">Service Start &nbsp; Date</th>
                        <th width="27%" class="RequiredStar" colspan="3">License Fee</th>
                        <th width="27%" class="RequiredStar" colspan="3">Service Fee</th>
                        <th width="4%" rowspan="2"></th>
                    </tr>
                    <tr>
                        <th>Head Office</th>
                        <th>Branch</th>
                        <th>Total License Fee</th>
                        <th>Head Office</th>
                        <th>Branch</th>
                        <th>Total Service Fee</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 0;
                    $TableID = "agreementTable";

                    $ColumnName = "product_id_arr[]&product_type_arr[]&product_name_arr[]&product_quantity_arr[]&branch_no_arr[]&service_start_date_arr[]&license_fee_ho_arr[]&license_fee_br_arr[]&license_fee_arr[]&service_fee_ho_arr[]&service_fee_br_arr[]&service_fee_arr[]";

                    $ColumnID = "product_id_&product_type_&product_name_&product_quantity_&branch_no_&service_start_date_&license_fee_ho_&license_fee_br_&license_fee_&service_fee_ho_&service_fee_br_&service_fee_&deleteRow_";
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
                    <tr>
                        <td class="barcodeWidth text-left">
                            <select id="product_id_0" class="form-control clsProductSelect" style="width: 100%;">
                                <option value="">Select Product/Package</option>
                                <!-- <optgroup label="Products">
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
                                </optgroup> -->
                                
                            </select>
                        </td>
                        <td>
                            <!-- {{-- name="product_quantity_arr[]"  --}} -->
                            <input type="number" id="product_quantity_0" class="form-control round licenseFeeClass text-right" value="0"
                                 min="1" readonly>
                        </td>
                        <td>
                            <!-- {{-- name="branch_no_arr[]"  --}} -->
                            <input type="number" id="branch_no_0" class="form-control round licenseFeeClass text-right" value="0"
                                required min="1" readonly onkeyup="fnTtlProductPrice(0);">
                        </td>

                        <td>
                            <!-- {{-- name="service_start_date_arr[]"  --}} -->
                            <input type="text" id="service_start_date_0" class="form-control round datepicker-custom"
                                readonly placeholder="DD-MM-YYYY" required>
                        </td>

                        <td>
                            <!-- {{-- name="license_fee_ho_arr[]"  --}} -->
                            <input type="number" id="license_fee_ho_0" class="form-control round licenseFeeClass text-right" value="0"
                                 min="1" readonly onkeyup="fnTtlProductPrice(0);">
                        </td>
                        <td>
                            <!-- {{-- name="license_fee_br_arr[]"  --}} -->
                            <input type="number" id="license_fee_br_0" class="form-control round licenseFeeClass text-right" value="0"
                                 min="1" readonly onkeyup="checkBranch();fnTtlProductPrice(0);">
                        </td>
                        <td>
                            <!-- {{-- name="license_fee_arr[]"  --}} -->
                            <input type="number" id="license_fee_0" class="form-control round licenseFeeClass text-right" value="0"
                                 min="1" readonly>
                        </td>

                        <td>
                            <!-- {{-- name="service_fee_arr[]"  --}} -->
                            <input type="number" id="service_fee_ho_0" class="form-control round serviceFeeClass text-right"
                                value="0" min="1" readonly onkeyup="fnTtlProductPrice(0);">

                            <input type="hidden" id="product_type_0" class="form-control round text-right" readonly>
                        </td>
                        <td>
                            <!-- {{-- name="service_fee_arr[]"  --}} -->
                            <input type="number" id="service_fee_br_0" class="form-control round serviceFeeClass text-right"
                                value="0" min="1" readonly onkeyup="fnTtlProductPrice(0);">

                            <input type="hidden" id="product_type_0" class="form-control round text-right" readonly>
                        </td>
                        <td>
                            <!-- {{-- name="service_fee_arr[]"  --}} -->
                            <input type="number" id="service_fee_0" class="form-control round serviceFeeClass text-right"
                                value="0" min="1" readonly>

                            <input type="hidden" id="product_type_0" class="form-control round text-right" readonly>
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
                            <td width="25%">
                                <h5>Total License Fee</h5>
                                <input type="hidden" name="total_license_fee" id="total_license_fee" value="0"
                                    min="1">
                            </td>
                            <td width="20%" id="tdTotalLicense" class="text-right" style="font-weight: bold;">0</td>

                            <td width="20%">
                                <h5>Total Service Fee</h5>
                                <input type="hidden" name="total_service_fee" id="total_service_fee" value="0" min="1">
                            </td>
                            <td width="5%" id="tdTotalService" class="text-right" style="font-weight: bold;">0</td>
                        </tr>
                    </tbody>
                </table>
            </div>

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
                        id="submitButton">Save</button>

                </div>
            </div>
        </div>
    </div>
</form>
<!--End Page-->
</div>

<style type="text/css">
    .my-custom-scrollbar{
        position: relative;
        height: 300px;
        overflow: auto;
    }
    thead .select2-container--default .select2-selection--single{
        background-color: #17b3a3;
    }
    thead .select2-container--default .select2-selection--single .select2-selection__rendered{
        color: #fff;
    }
    thead .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: #fff transparent transparent transparent;
    }
    thead .select2-container--default .select2-selection--single {
        border: 1px solid #fff;
    }
</style>

<script type="text/javascript">

    $(document).ready(function() {

        $('.clsProductSelect').select2();

        $('#branch_id').change(function() {
            fnGenBillNo($('#branch_id').val());
        });

        $('#selProduct').change(function() {
            var selProd = this.value;
            if (selProd == 1) {
                $.ajax({
                    method: "GET",
                    url: '{{ url("bill/agreement/loadProductForAgreement") }}',
                    dataType: "text",
                    success: function(data) {
                        if (data) {
                            $('#product_id_0').empty().html(data);
                        }
                    }
                });
            }
            else {
                $.ajax({
                    method: "GET",
                    url: '{{ url("bill/agreement/loadPackageForAgreement") }}',
                    dataType: "text",
                    success: function(data) {
                        if (data) {
                            $('#product_id_0').empty().html(data);
                        }
                    }
                });
            }
        });

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
            // if ($('#no_of_branch').val() == 0) {
            //     swal({
            //         icon: 'warning',
            //         title: 'Warning',
            //         text: 'Please Enter No Of Branch',
            //     });
            //     $('#no_of_branch').focus();
            // }
            // else {
                $('#product_quantity_0,#branch_no_0,#service_start_date_0,#license_fee_ho_0,#license_fee_br_0,#service_fee_ho_0,#service_fee_br_0').prop('readonly', false);
                var selProdCostPrice = $(this).find("option:selected").attr('pcprice');

                var productType = $(this).find("option:selected").attr('type');

                $('#product_type_0').val(productType);
                $('#tdTotalAmount').html( (Number($('#total_amount').val())).toFixed(2) ) ;

                fnTtlProductPrice(0);
            // }
        } else {
            $('#product_quantity_0,#branch_no_0,#service_start_date_0,#license_fee_ho_0,#license_fee_br_0,#service_fee_ho_0,#service_fee_br_0').prop('readonly', true);
            $('#product_quantity_0,#branch_no_0,#service_start_date_0,#license_fee_ho_0,#license_fee_br_0,#service_fee_ho_0,#service_fee_br_0').val(0);
            
        }
    });

    function checkBranch(){
        if ($('#branch_no_0').val() == 0) {
            $('#branch_no_0').focus();
            swal({
                icon: 'warning',
                title: 'Warning',
                text: 'Please Enter No Of Branch',
            });
            
        }
    }

    var ProductIDArr = [];

    /* Add row Start */
    function btnAddNewRow(TableID, ColumnNameS, ColumnIDS, TotalRowID) {
        var ColumnName = ColumnNameS.split("&");
        var ColumnID = ColumnIDS.split("&");
        /*
         * ColumnID[0] = this is ID for input feild Product_id_0
         * ColumnID[3] = this is ID for input feild product_quantity_0
         * ColumnID[4] = this is ID for input feild branch_no_0
         * ColumnID[5] = this is ID for input feild service_start_date_0
         * ColumnID[6] = this is ID for input feild license_fee_ho_0
         * ColumnID[7] = this is ID for input feild license_fee_br_0
         * ColumnID[8] = this is ID for input feild license_fee_0
         * ColumnID[9] = this is ID for input feild service_fee_ho_0
         * ColumnID[10] = this is ID for input feild service_fee_br_0
         * ColumnID[11] = this is ID for input feild service_fee_0
         */
        if ($('#' + ColumnID[0] + 0).val() != '') {

            var TotalRowCount = $('#' + TotalRowID).val();
            /*
            marge two row if same product found
            */

            var test = $('#' + ColumnID[1] + 0).val();
            // alert(test);

            var prodQuantity = $('#' + ColumnID[3] + 0).val();
            var branchNo = $('#' + ColumnID[4] + 0).val();
            var serviceStartDate = $('#' + ColumnID[5] + 0).val();
            var licenseFeeHO = $('#' + ColumnID[6] + 0).val();
            var licenseFeeBR = $('#' + ColumnID[7] + 0).val();
            var licenseFee = $('#' + ColumnID[8] + 0).val();
            var serviceFeeHO = $('#' + ColumnID[9] + 0).val();
            var serviceFeeBR = $('#' + ColumnID[10] + 0).val();
            var serviceFee = $('#' + ColumnID[11] + 0).val();

            // if (branchNo == '') {
            //     swal({
            //         icon: 'warning',
            //         title: 'Warning',
            //         text: 'Please Enter No Of Branch',
            //     });
            // }


            var flag = false;

            var rowNumber = 0;

            for (var row = 1; row <= TotalRowCount; row++) {
                if ( $('#product_id_'+row).val() == $('#product_id_0').val()){
                    flag = true;
                    rowNumber = row ;
                }
            }

            if (flag === true){

                prodQuantity = Number(prodQuantity) + Number($('#product_quantity_' + rowNumber).val());
                branchNo = Number(branchNo) + Number($('#branch_no_' + rowNumber).val());
                serviceStartDate = Number(serviceStartDate) + Number($('#service_start_date' + rowNumber).val());
                licenseFeeHO = Number(licenseFeeHO) + Number($('#license_fee_ho_' + rowNumber).val());
                licenseFeeBR = Number(licenseFeeBR) + Number($('#license_fee_br_' + rowNumber).val());
                licenseFee = Number(licenseFee) + Number($('#license_fee_' + rowNumber).val());
                serviceFeeHO = Number(serviceFeeHO) + Number($('#service_fee_ho_' + rowNumber).val());
                serviceFeeBR = Number(serviceFeeBR) + Number($('#service_fee_br_' + rowNumber).val());
                serviceFee = Number(serviceFee) + Number($('#service_fee_' + rowNumber).val());

                $('#license_fee_ho_' + rowNumber).val(licenseFeeHO);
                $('#license_fee_br_' + rowNumber).val(licenseFeeBR);
                $('#license_fee_' + rowNumber).val(licenseFee);
                $('#service_fee_ho_' + rowNumber).val(serviceFeeHO);
                $('#service_fee_br_' + rowNumber).val(serviceFeeBR);
                $('#service_fee_' + rowNumber).val(serviceFee);
                $('#' + ColumnID[0] + 0).val('');
                $('#' + ColumnID[0] + 0).trigger('change');
                $('#' + ColumnID[3] + 0).val(0);
                $('#' + ColumnID[1] + 0).val(test);
                $('#' + ColumnID[5] + 0).val(0);
                $('#' + ColumnID[7] + 0).val(0);
                $('#' + ColumnID[8] + 0).val(0);
                $('#' + ColumnID[9] + 0).val(0);
                $('#' + ColumnID[10] + 0).val(0);
                $('#' + ColumnID[11] + 0).val(0);
                $('#' + ColumnID[3] + 0).prop('readonly', true);

            }

            else {
                if(branchNo == 0 || serviceStartDate == ''){
                    if (branchNo == 0) {
                        swal({
                            icon: 'warning',
                            title: 'Warning',
                            text: 'Please Enter No Of Branch',
                        });
                    }
                    else if (serviceStartDate == '') {
                        swal({
                            icon: 'warning',
                            title: 'Warning',
                            text: 'Please Enter Service Start',
                        });
                    }
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

                    html += '<td class="barcodeWidth" width="15%">';
                    html += '<input type="hidden" id="product_id_'+ TotalRowCount +'" name="' + ColumnName[0] + '" value="' + ProductID + '">';

                    html += '<input type="text" class="form-control round text-left" value="' + ProductName +'" readonly >';

                    html += '<input type="hidden" id="product_type_'+ TotalRowCount +'" name="' + ColumnName[1] + '" value="' + ProductType + '">';

                    html += '<td width="7%">' +
                        '<input type="number" name="' + ColumnName[3] + '" id="' + ColumnID[3] + TotalRowCount +
                        '" class="form-control round text-right" value="' + prodQuantity  + '" readonly>' +
                        '</td>';

                    html += '<td width="8%">' +
                        '<input type="number" name="' + ColumnName[4] + '" id="' + ColumnID[4] + TotalRowCount +
                        '" class="form-control round text-right" value="' + branchNo  + '" readonly required min="1">' +
                        '</td>';

                    html += '<td width="12%">' +
                        '<input type="text" name="' + ColumnName[5] + '" id="' + ColumnID[5] + TotalRowCount +
                        '" class="form-control round datepicker-custom" value="' + serviceStartDate  + '" readonly required>' +
                        '</td>';

                    html += '<td width="9%">' +
                        '<input type="number" name="' + ColumnName[6] + '" id="' + ColumnID[6] + TotalRowCount +
                        '" class="form-control round text-right" value="' + licenseFeeHO  + '" readonly required min="1">' +
                        '</td>';
                    html += '<td width="9%">' +
                        '<input type="number" name="' + ColumnName[7] + '" id="' + ColumnID[7] + TotalRowCount +
                        '" class="form-control round text-right" value="' + licenseFeeBR + '" readonly>' +
                        '</td>';
                    html += '<td width="9%">' +
                        '<input type="number" name="' + ColumnName[8] + '" id="' + ColumnID[8] + TotalRowCount +
                        '" class="form-control round text-right" value="' + licenseFee + '" readonly required min="1">' +
                        '</td>';
                    html += '<td width="9%">' +
                        '<input type="number" name="' + ColumnName[9] + '" id="' + ColumnID[9] + TotalRowCount +
                        '" class="form-control round text-right" value="' + serviceFeeHO + '" readonly>' +
                        '</td>';
                    html += '<td width="9%">' +
                        '<input type="number" name="' + ColumnName[10] + '" id="' + ColumnID[10] + TotalRowCount +
                        '" class="form-control round text-right" value="' + serviceFeeBR + '" readonly required min="1">' +
                        '</td>';
                    html += '<td width="9%">' +
                        '<input type="number" name="' + ColumnName[11] + '" id="' + ColumnID[11] + TotalRowCount +
                        '" class="form-control round text-right" value="' + serviceFee + '" readonly>' +
                        '</td>';
                    html += '<td width="4%">' +
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
                    $('#' + ColumnID[8] + 0).val(0);
                    $('#' + ColumnID[9] + 0).val(0);
                    $('#' + ColumnID[10] + 0).val(0);
                    $('#' + ColumnID[11] + 0).val(0);
                    $('#' + ColumnID[3] + 0).prop('readonly', true);
                }
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

    function checkProduct(){
        if ($('#product_id_0').val() > 0) {
            $('#license_fee_ho_0,#license_fee_br_0,#service_fee_ho_0,#service_fee_br_0').prop('readonly', false);
        }
    }


    function fnTtlProductPrice(Row) {

        var ttlRow = $('#TotalRowID').val();
        var noOfBranch = $('#branch_no_0').val();
        var totalProductPrice = 0;
        var ttlLicenseFee = 0;
        var ttlServiceFee = 0;
        

        var i;
        for (i = 0; i <= ttlRow; ++i) {
            var licenseFeeHO = $('#license_fee_ho_' + i).val();
            var licenseFeeBR = $('#license_fee_br_' + i).val();
    
            var serviceFeeHO = $('#service_fee_ho_' + i).val();
            var serviceFeeBR = $('#service_fee_br_' + i).val();
            var liceseFee = Number(licenseFeeHO) + Number(licenseFeeBR) * Number(noOfBranch);
            $('#license_fee_' + i).val(liceseFee);

            ttlLicenseFee = Number(ttlLicenseFee) + Number(liceseFee);
            

           var  serviceFee = Number(serviceFeeHO) + Number(serviceFeeBR) * Number(noOfBranch);
            $('#service_fee_' + i).val(serviceFee);

            ttlServiceFee = Number(ttlServiceFee) + Number(serviceFee);
            

            totalProductPrice = (Number(liceseFee) + Number(serviceFee) + Number(totalProductPrice));

            $('#total_license_fee').val(ttlLicenseFee);
            $('#tdTotalLicense').html(ttlLicenseFee.toFixed(2));
            $('#total_service_fee').val(ttlServiceFee);
            $('#tdTotalService').html(ttlServiceFee.toFixed(2));
            $('#total_amount').val(totalProductPrice);
            $('#tdTotalAmount').html(totalProductPrice.toFixed(2));
        }

    }

    // function fnTotalAmount(Row = null) {

    //     var totalAmt = $('#total_amount').val();
    //     var totalLicense = 0;
    //     var totalService = 0;
        
    //     if (Row) {
    //         console.log(Row)
    //         var licenseFee = $('#license_fee_' + Row).val();
    //         var serviceFee = $('#service_fee_' + Row).val();
    //         totalAmt = ( Number(totalAmt) - Number(serviceFee) - Number(licenseFee));
    //         totalLicense = ( Number(totalAmt) - Number(licenseFee));
    //         totalService = ( Number(totalAmt) - Number(serviceFee));
    //         $('#total_license_fee').val(totalLicense);
    //         $('#tdTotalLicense').html(totalLicense.toFixed(2));
    //         $('#total_service_fee').val(totalService);
    //         $('#tdTotalService').html(totalService.toFixed(2));
    //     }

    //     $('.serviceFeeClass').each(function() {
    //         totalAmt = Number(totalAmt) + Number($(this).val());
    //     });
    //     $('#tdTotalAmount').html(totalAmt.toFixed(2));
    //     //-------------------------- Total Amount
    //     $('#total_amount').val(totalAmt.toFixed(2));
    // }

    function fnTotalAmount(Row = null) {

        var totalAmt = $('#total_amount').val();
        var totalLicense = 0;
        var totalService = 0;

        
        if (Row) {
            var licenseFee = $('#license_fee_' + Row).val();
            var serviceFee = $('#service_fee_' + Row).val();
            totalAmt = ( Number(totalAmt) - Number(serviceFee) - Number(licenseFee));
            var total_license_fee = $('#total_license_fee').val();
            var total_service_fee = $('#total_service_fee').val();
            totalLicense = ( Number(total_license_fee) - Number(licenseFee));
            totalService = ( Number(total_service_fee) - Number(serviceFee));
            $('#total_license_fee').val(totalLicense);
            $('#tdTotalLicense').html(totalLicense.toFixed(2));
            $('#total_service_fee').val(totalService);
            $('#tdTotalService').html(totalService.toFixed(2));
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

        if ($('#total_amount').val() > 0 && $('#product_id_0').val() == '') {

            $('#agreement_form').submit();
        } else {

            if ($('#total_amount').val() <= 0) {
                swal({
                    icon: 'error',
                    title: 'Error',
                    text: 'Total amount must be gratter than zero !!',
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

    $('form').submit(function (event) {
        $(this).find(':submit').attr('disabled', 'disabled');
    });
</script>


@endsection
