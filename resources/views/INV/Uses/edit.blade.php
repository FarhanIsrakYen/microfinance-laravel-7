@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use App\Services\HtmlService as HTML;
use App\Services\InvService as INVS;
?>

<!-- Page -->
<form method="post" data-toggle="validator" novalidate="true" id="uses_form">
    @csrf
    <div class="panel">
        <div class="panel-body">
            <div class="row">
                <div class="col-lg-8 offset-lg-3">
                    <!-- Html View Load  -->
                    {!! HTML::forCompanyFeild($UseData->company_id) !!}
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8 offset-lg-3">
                    {!! HTML::forBranchFeild(true,'branch_id','branch_id',$UseData->branch_id,'disabled','Branch') !!}
                </div>
            </div>

            <div class="row">
                <!--Form Left-->
                <div class="col-lg-6">
                    <div class="form-row form-group align-items-center">
                        <div class="col-lg-4">
                            <label class="input-title">Bill No</label>
                        </div>
                        <div class="col-lg-7 input-group">
                            <input type="text" name="uses_bill_no" id="uses_bill_no_f" class="form-control round" 
                                value="{{ $UseData->uses_bill_no }}" readonly>

                            <!-- <span id="uses_bill_no">
                                {{ $UseData->uses_bill_no }}
                            </span> -->
                        </div>
                    </div>

                    <div class="form-row form-group align-items-center">
                        <div class="col-lg-4">
                            <label class="input-title">Use Date</label>
                        </div>
                        <div class="col-lg-7 input-group">
                            <?php
                                $SalesDate = new DateTime($UseData->uses_date);
                                $SalesDate = (!empty($SalesDate)) ? $SalesDate->format('d-m-Y') : date('d-m-Y');
                            ?>

                            <input type="text" name="uses_date" id="uses_date_f" class="form-control round"
                                value="{{ $SalesDate }}" readonly>

                            <!-- <span id="uses_date">
                                {{ $SalesDate }}
                            </span> -->
                        </div>
                    </div>

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title">Requisition</label>
                        <div class="col-lg-7 input-group">
                            <?php $RequsitionList = Common::ViewTableOrder('inv_requisitions_emp_m',
                                            [['is_approve', 1], ['is_delete', 0]],
                                            ['requisition_no'],
                                            ['requisition_no', 'ASC']) ?>

                            <select class="form-control clsSelect2" name="requisition_no" id="requisition_no" 
                                style="width: 100%;">

                                <option value="">Select Requisition</option>
                                @foreach($RequsitionList as $Row)
                                <option value="{{ $Row->requisition_no }}" @if($UseData->requisition_no == $Row->requisition_no) {{ 'selected' }} @endif>
                                    {{ $Row->requisition_no }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                </div>
                <!--Form Right-->
                <div class="col-lg-6">

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title RequiredStar">Requisition For</label>
                        <div class="col-lg-7">
                            <div class="input-group">
                                <div class="radio-custom radio-primary col-lg-4">
                                    <input type="radio" class="reqFor" id="r1" 
                                    name="requisition_for" value="1" {{ $UseData->requisition_for == 1 ? 'checked' : '' }}>
                                    <label for="r1">Personal</label>
                                </div>
                                <div class="radio-custom radio-primary col-lg-4">
                                    <input type="radio" class="reqFor" id="r2" 
                                    name="requisition_for" value="2" {{ $UseData->requisition_for == 2 ? 'checked' : '' }}>
                                    <label for="r2">Department</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-row form-group align-items-center" id="empDiv" style="display: none;">
                        <label class="col-lg-4 input-title">Employee</label>
                        <div class="col-lg-7 input-group">
                            <!-- {{-- Query for get all Employee --}} -->
                            <?php $EmpList = Common::ViewTableOrderIn('hr_employees',
                                            ['is_delete' => 0],
                                            ['branch_id', HRS::getUserAccesableBranchIds()],
                                            ['employee_no', 'emp_name', 'emp_code'],
                                            ['emp_name', 'ASC']) ?>

                            <select class="form-control clsSelect2" name="employee_no" id="employee_no" 
                                style="width: 100%;">
                                <option value="">Select Employee</option>
                                @foreach($EmpList as $EData)
                                <option value="{{ $EData->employee_no }}" @if($UseData->employee_no == $EData->employee_no) {{ 'selected' }} @endif>
                                    {{ $EData->emp_name." (".$EData->emp_code.")" }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row form-group align-items-center" id="deptDiv" style="display: none;">
                        <label class="col-lg-4 input-title RequiredStar">Department</label>
                        <div class="col-lg-7 input-group">
                            <?php $DepartmentList = Common::ViewTableOrder('hr_departments',
                                            ['is_delete' => 0],
                                            ['id', 'dept_name'],
                                            ['dept_name', 'ASC']) ?>

                            <select class="form-control clsSelect2" name="department_id" id="department_id" 
                                style="width: 100%"
                                onchange="fnAjaxSelectBox(
                                                         'room_id',
                                                         this.value,
                                                        '{{base64_encode('hr_rooms')}}',
                                                        '{{base64_encode('dept_id')}}',
                                                        '{{base64_encode('id,room_name')}}',
                                                        '{{url('/ajaxSelectBox')}}'
                                            );">

                                <option value="">Select Department</option>
                                @foreach($DepartmentList as $Row)
                                <option value="{{ $Row->id }}" @if($UseData->department_id == $Row->id) {{ 'selected' }} @endif>
                                    {{ $Row->dept_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row form-group align-items-center" id="roomDiv" style="display: none;">
                        <label class="col-lg-4 input-title RequiredStar">Room</label>
                        <div class="col-lg-7">
                            <div class="input-group">
                                <?php $roomList = Common::ViewTableOrder('hr_rooms',
                                                ['is_delete' => 0],
                                                ['id', 'room_name','room_code'],
                                                ['room_name', 'ASC']) ?>
                                <select class="form-control clsSelect2"
                                name="room_id" id="room_id" style="width: 100%;">
                                    <option value="">Select Room</option>
                                    @foreach ($roomList as $Row)
                                    <option value="{{ $Row->id }}" {{ $UseData->room_id == $Row->id ? 'selected' : '' }}>
                                        {{ $Row->room_code ? $Row->room_name. '-' .' ('. $Row->room_code . ')' : $Row->room_name }}</option>
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
                    <select class="form-control clsSelect2" id="prod_group_id" onchange="fnAjaxSelectBox('prod_cat_id',
                                        this.value,
                            '{{ base64_encode('inv_p_categories')}}',
                            '{{base64_encode('prod_group_id')}}',
                            '{{base64_encode('id,cat_name')}}',
                            '{{url('/ajaxSelectBox')}}'
                                    ); fnProductLoad();">
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
                    <select class="form-control clsSelect2" id="prod_cat_id" onchange="fnAjaxSelectBox('prod_sub_cat_id',
                                        this.value,
                            '{{base64_encode('inv_p_subcategories')}}',
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
                    <?php $SubCategoryList = Common::ViewTableOrder('inv_p_subcategories',
                                    ['is_delete' => 0],
                                    ['id', 'sub_cat_name'],
                                    ['sub_cat_name', 'ASC']) ?>
                    <select class="form-control clsSelect2" id="prod_sub_cat_id" onchange="fnAjaxSelectBox('prod_model_id',
                                        this.value,
                            '{{base64_encode('inv_p_models')}}',
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
                    <?php $ModelList = Common::ViewTableOrder('inv_p_models',
                                    ['is_delete' => 0],
                                    ['id', 'model_name'],
                                    ['model_name', 'ASC']) ?>
                    <select class="form-control clsSelect2" id="prod_model_id" onchange="fnProductLoad();">
                        <option value="">Select</option>
                        @foreach($ModelList as $MData)
                        <option value="{{ $MData->id }}">{{ $MData->model_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-lg-6 text-left">
                    <strong class="text-danger">Current Stock: <label id="current_stock"></label></strong>
                    <input type="hidden" id="stock_quantity_0">
                </div>
            </div>

            <table  class="table table-hover table-striped table-bordered w-full text-center table-responsive" id="SalesTable">
                <thead class="scrollHead">
                    <tr>
                        <th width="64%" class="RequiredStar">Product Name</th>
                        <th width="20%" class="RequiredStar">Quantity</th>
                        <th width="20%">Serial No</th>
                        <th width="4%"></th>
                    </tr>
                </thead>
                <tbody class="scrollBody">
                    <?php
                        $i = 0;
                        $TableID = "SalesTable";

                        $ColumnName = "product_id_arr[]&product_quantity_arr[]&product_serial_arr[]";
                        $ColumnID = "product_id_&product_quantity_&product_serial_&deleteRow_";

                        $ProductList = Common::ViewTableOrder('inv_products',
                            ['is_delete' => 0],
                            ['id', 'product_name', 'cost_price', 'product_code'],
                            ['product_name', 'ASC']);
                    ?>
                    <tr>
                        <td width="64%" class="text-left">

                            <select id="product_id_0" class="form-control round clsSelect2"
                                onchange="fnAjaxCheckStock();" style="width: 100%">
                                <option value="">Select Product</option>

                                @foreach($ProductList as $ProductInfo)
                                <option value="{{ $ProductInfo->id }}" pname="{{ $ProductInfo->product_name }}"
                                    pcode="{{ $ProductInfo->product_code }}"
                                    pcprice="{{ $ProductInfo->cost_price }}">
                                    {{ $ProductInfo->product_code ? $ProductInfo->product_name . ' (' . $ProductInfo->product_code . ')' : $ProductInfo->product_name }}
                                </option>
                                @endforeach
                            </select>
                        </td>

                        <td width="20%">

                            <input type="number" id="product_quantity_0"
                                class="form-control round clsQuantity text-center" placeholder="Enter Quantity"
                                value="0" onkeyup="fnTotalQuantity(); fnCheckQuantity(0);" min="1"
                                readonly>
                        </td>

                        <td width="20%">
                            <input type="text" id="product_serial_0" class="form-control round text-left"
                                readonly>
                        </td>

                        <td width="4%">
                            <a href="javascript:void(0);"
                                class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center"
                                onclick="btnAddNewRow('<?= $TableID ?>', '<?= $ColumnName ?>', '<?= $ColumnID ?>', 'TotalRowID');">
                                <i class="icon wb-plus  align-items-center"></i>
                            </a>
                        </td>
                    </tr>

                @if(count($UseDataD) > 0)
                    @foreach($UseDataD as $SDataD)
                    <?php $i++; ?>
                    <tr>
                        <td width="64%" class="">
                            <input type="hidden" id="product_id_{{ $i }}" name="product_id_arr[]" value="{{ $SDataD->product_id }}">
                            <input type="hidden" id="stock_quantity_{{ $i }}" value="{{(int)INVS::stockQuantity($UseData->branch_id,$SDataD->product_id) + $SDataD->product_quantity}}">

                            @foreach($ProductList as $ProductInfo)
                            @if($ProductInfo->id == $SDataD->product_id)
                            <input type="text" class="form-control round"
                                value="{{ $ProductInfo->product_code ? $ProductInfo->product_name . ' (' . $ProductInfo->product_code . ')' : $ProductInfo->product_name }}"
                                readonly>
                            @endif
                            @endforeach
                        </td>

                        <td width="20%">
                            <input type="number" name="product_quantity_arr[]" id="product_quantity_{{ $i }}"
                                class="form-control round clsQuantity text-center" placeholder="Enter Quantity"
                                value="{{ $SDataD->product_quantity }}"
                                onkeyup="fnTotalQuantity(); fnCheckQuantity({{ $i }})" required min="1">
                        </td>

                        <td width="20%">
                            <input type="text" name="product_serial_arr[]" id="product_serial_{{ $i }}"
                                class="form-control round text-left" value="{{ $SDataD->product_serial_no }}">
                        </td>

                        <td width="4%">

                            <a href="javascript:void(0)"
                                class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center clsAddNewRow"
                                onclick="btnRemoveRow(this);">
                                <i class="icon fa fa-times align-items-center"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                @endif
                </tbody>
                <tfoot class="scrollFooter">
                    <tr>
                        <td width="64%" style="text-align:right;">
                            <h5>TOTAL</h5>
                            <input type="hidden" name="total_quantity" id="total_quantity" value="0"
                                min="1">
                        </td>
                        <td width="20%" id="tdTotalQuantity" style="font-weight: bold;">0</td>
                        <td width="24%"></td>
                    </tr>
                </tfoot>
            </table>

            <!-- Row_Count is temporary variable for using row add and delete-->
            <input type="hidden" id="TotalRowID" value="{{ $i }}" />

            <br><br>
            <div class="row align-items-center">
                <div class="col-lg-12">
                    <div class="d-flex justify-content-center">
                        <a href="javascript:void(0)" onclick="goBack();"
                            class="btn btn-default btn-round d-print-none">Back</a>
                        &nbsp;&nbsp;&nbsp;
                        @if(date('d-m-Y', strtotime($UseData->uses_date)) == Common::systemCurrentDate())
                        <button type="submit" class="btn btn-primary btn-round"
                            id="updateButtonforSales">Update</button>
                        @endif
                    </div>
                </div>
            </div>

        </div>
        <!--End Panel 2-->
    </div>

</form>
<!--End Page-->
<script type="text/javascript">
    /* Pop Up Supplier Start */
    $(document).ready(function() {

        if($('.reqFor').is(':checked')) {
            var idTxt = $('.reqFor:checked').val();
            if (idTxt == 1) {
                $('#deptDiv,#roomDiv').hide('slow');
                $('#empDiv').show('slow');
            }
            else {
                $('#empDiv').hide('slow');
                $('#deptDiv,#roomDiv').show('slow');
            }
        }

        $(".reqFor").click(function() {
            var selIdTxt = $(this).val();
            if (selIdTxt == 2) {
                $('#empDiv').hide('slow');
                $('#deptDiv,#roomDiv').show('slow');
            }
            else {
                $('#empDiv').show('slow');
                $('#deptDiv,#roomDiv').hide('slow');
            }
        });

        $('.clsProductSelect').select2();

        //initially set 0 into these fields
        $('#current_stock').html(0);
        $('#stock_quantity_0').val(0);

        // Generate bill on changing branch Id
        // $('#branch_id').change(function() {
        //     fnGenBillNo($('#branch_id').val());
        // });


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


        // $('#department_id').change(function() {
        //     $.ajax({
        //         method: "GET",
        //         url: "{{url('inv/use/ajEmpLoadDeptWise')}}",
        //         dataType: "text",
        //         data: {
        //             DepartmentID: $('#department_id').val(),
        //             CompanyID: $('#company_id').val(),
        //             BranchID: $('#branch_id').val(),
        //             SelValue: {{ $UseData->department_id }}
        //         },
        //         success: function(data) {
        //             if (data) {
        //                 $('#employee_no')
        //                     .find('option')
        //                     .remove()
        //                     .end()
        //                     .append(data);
        //             }
        //         }
        //     });
        // });
        $('#department_id').change(function() {
            $.ajax({
                method: "GET",
                url: "{{url('inv/use/ajEmpLoadDeptWise')}}",
                dataType: "text",
                data: {
                    DepartmentID: $('#department_id').val(),
                    CompanyID: $('#company_id').val(),
                    BranchID: $('#branch_id').val(),
                    SelValue: $('#department_id').val(),
                },
                success: function(data) {
                    if (data) {
                        $('#requisition_no')
                            .find('option')
                            .remove()
                            .end()
                            .append(data);
                    }
                }
            });
        });


        $('#employee_no').change(function() {
            $.ajax({
                method: "GET",
                url: "{{url('inv/use/ajReqLoadEmpWise')}}",
                dataType: "text",
                data: {
                    EmpNo: $('#employee_no').val(),
                    CompanyID: $('#company_id').val(),
                    BranchID: $('#branch_id').val(),
                    SelValue: $('#employee_no').val(),
                },
                success: function(data) {
                    if (data) {

                        $('#requisition_no')
                            .find('option')
                            .remove()
                            .end()
                            .append(data);
                    }
                }
            });
        });

    });
    /* Pop Up Supplier End*/

    // // Generate Bill No
    // function fnGenBillNo(BranchId)
    // {
    //     if (BranchId != '') {
    //         $.ajax({
    //             method: "GET",
    //             url: "{{ url('/ajaxGBillUses') }}",
    //             dataType: "text",
    //             data: {BranchId: BranchId},
    //             success: function (data) {
    //                 if (data) {
    //                     $('#uses_bill_no_f').val(data);
    //                     $('#uses_bill_no').html(data);
    //                 }
    //             }
    //         });
    //     }
    // }


    /* Addrow Start */
    function btnAddNewRow(TableID, ColumnNameS, ColumnIDS, TotalRowID) {

        var ColumnName = ColumnNameS.split("&");
        var ColumnID = ColumnIDS.split("&");

        // console.log(ColumnID);

        /*
        0: "product_id_"
        1: "product_quantity_"
        2: "product_serial_"
        3: "deleteRow_"
        */

        if ($('#' + ColumnID[0] + 0).val() != '' && 
            $('#product_quantity_0').val() > 0 && 
            $('#stock_quantity_0').val() != 0 && 
            Number($('#stock_quantity_0').val()) >= Number($('#product_quantity_0').val())
        ) {

            var TotalRowCount = $('#' + TotalRowID).val();
            /*
            marge two row if same product found
            */

            var ProductQuantity = $('#' + ColumnID[1] + 0).val();
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
                $('#' + ColumnID[1] + 0).val(0);
                $('#' + ColumnID[1] + 0).prop('readonly', true);

                $('#' + ColumnID[2] + 0).val('');

                $('#current_stock').html(0);
                fnTotalQuantity();
            }
            else{
                TotalRowCount++;
                $('#' + TotalRowID).val(TotalRowCount);

                var ProductID = $('#' + ColumnID[0] + 0).val();
                var ProductName = $('#' + ColumnID[0] + 0).find("option:selected").attr('pname');
                var ProductCode = $('#' + ColumnID[0] + 0).find("option:selected").attr('pcode');
                var ProductSerial = $('#' + ColumnID[2] + 0).val();

                var rowID = 'usesRow_' + TotalRowCount;

                var html = '<tr id="' + rowID + '">';
            
                html += '<td class="" width="64%">';
                html += '<input type="hidden" id="product_id_' + TotalRowCount + '" name="' + ColumnName[0] + '" value="' +
                    ProductID + '">';
                html += '<input type="hidden" id="stock_quantity_' + TotalRowCount + '" value="' + StockQuantity + '">';
                if (ProductCode) {
                    html += '<input type="text" class="form-control round" value="' + ProductName + '(' + ProductCode + ')' +
                    '" readonly >';
                }
                else {
                    html += '<input type="text" class="form-control round" value="' + ProductName + '" readonly >';
                }
                html += '</td>';

                html += '<td>';
                html += '<input type="number" name="' + ColumnName[1] + '" id="' + ColumnID[1] + TotalRowCount +
                    '" class="form-control round clsQuantity text-center" value="' + ProductQuantity +
                    '" onkeyup="fnTotalQuantity(); fnCheckQuantity(' + TotalRowCount + ');" required min="1">';
                html += '</td>';

                html += '<td>' +
                    '<input type="text"  name="' + ColumnName[2] + '" id="' + ColumnID[2] + TotalRowCount +
                    '" class="form-control round" value="' + ProductSerial + '">' +
                    '</td>';

                html += '<td>' +
                    '<a href="javascript:void(0)" class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center clsAddNewRow" id="' +
                    ColumnID[3] + TotalRowCount + '" onclick="btnRemoveRow(this);">' +
                    ' <i class="icon fa fa-times align-items-center"></i>' +
                    '</a>' +
                    '</td>';

                html += '</tr>';

                $('#' + TableID + ' tbody').find('tr:first').after(html);

                $('#' + ColumnID[0] + 0).val('');
                $('#' + ColumnID[0] + 0).trigger('change');

                $('#' + ColumnID[1] + 0).val(0);
                $('#' + ColumnID[1] + 0).prop('readonly', true);

                $('#' + ColumnID[2] + 0).val('');

                $('#current_stock').html(0);
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
    }
    /* Remove row End */

    // Load Products in table
    function fnProductLoad() {
        var CompanyID = $('#company_id').val();
        var GroupID = $('#prod_group_id').val();
        var CategoryID = $('#prod_cat_id').val();
        var SubCatID = $('#prod_sub_cat_id').val();
        var ModelID = $('#prod_model_id').val();

        var firstRowFirstColId = $('#SalesTable tbody tr td:first-child select').attr('id');

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
                CustomerID: null
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
                url: "{{ url('/ajaxCheckStockInv') }}",
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
   
        }
    }

    // Calculate Total Quantity
    function fnTotalQuantity() {

        var totalQtn = 0;
        $('.clsQuantity').each(function() {
            totalQtn = Number(totalQtn) + Number($(this).val());
        });

        $('#total_quantity').val(totalQtn);
        // $('#total_qnty').val(totalQtn);
        $('#tdTotalQuantity').html(totalQtn);
    }


    $('#updateButtonforSales').on('click', function(event) {
        event.preventDefault();

        if ($('#total_quantity').val() > 0 && $('#product_id_0').val() == '' && 
            (($('#department_id').val() != '' && $('#room_id').val() != '') || $('#employee_no').val() != '') ) {
                $('#uses_form').submit();
        }
        else {

            if ($('#total_quantity').val() <= 0) {
                swal({
                    icon: 'error',
                    title: 'Error',
                    text: 'No Product Selected !!',
                });
            } 

            else if ($('.reqFor:checked').val() == 2) {
                if ($('#department_id').val() == '') {
                    swal({
                        icon: 'error',
                        title: 'Error',
                        text: 'Please Select Department!!',
                    });
                }
                else if ($('#room_id').val() == '') {
                    swal({
                        icon: 'error',
                        title: 'Error',
                        text: 'Please Select Room!!',
                    });
                }
            }

            else if ($('.reqFor:checked').val() == 1) {
                if ($('#employee_no').val() == '') {
                    swal({
                        icon: 'error',
                        title: 'Error',
                        text: 'Please Select Employee!!',
                    });
                }
            }

            else {
                swal({
                    icon: 'error',
                    title: 'Error',
                    text: 'Product Entry must be empty!!',
                });
            }

        }
    });

    // Disable Button for multiple click
    $('#uses_form').submit(function (event) {
        $(this).find(':submit').attr('disabled', 'disabled');
    });

    $(document).ready(function() {

        var html = '<a target="_blank" href="{{url('inv/use/add')}}" class="btn btn-sm btn-primary btn-outline btn-round mr-2">';
        html += '<i class="icon wb-link" aria-hidden="true"></i>';
        html += '<span class="hidden-sm-down">&nbsp;New Entry</span>';
        html += '</a>';

        $('.page-header-actions').prepend(html);
    });
</script>


@endsection
