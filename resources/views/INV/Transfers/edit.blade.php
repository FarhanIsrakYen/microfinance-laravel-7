@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
use App\Services\InvService as INVS;
?>


<form method="post" data-toggle="validator" novalidate="true" id="transfer_form">
    @csrf

    <div class="panel">
        <div class="panel-body">
            <div class="row">
                <div class="col-lg-8 offset-lg-3">
                    <!-- Html View Load  -->
                    {!! HTML::forCompanyFeild($TransferData->company_id) !!}
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8 offset-lg-3">
                    {!! HTML::forBranchFeild(true,'branch_from','branch_from',$TransferData->branch_from,'','Branch From') !!}
                </div>
            </div>

            <div class="row">
                <!--Form Left-->
                <div class="col-lg-6">
                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title RequiredStar">Transfer No</label>
                        <div class="col-lg-7">
                            <div class="input-group">
                                <input type="text" name="bill_no" id="bill_no" class="form-control round"
                                    value="{{$TransferData->bill_no}}" required readonly>
                            </div>
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>


                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-4 input-title RequiredStar">Transfer to &nbsp (Branch)</label>
                        <div class="col-lg-7 input-group">
                            <select class="form-control clsSelect2"
                                    name="branch_to" id="branch_to" required
                                data-error="Please select Branch">
                                <option value="">Select Branch</option>
                                @foreach($BranchData as $BData)
                                <option value="{{ $BData->id }}" @if($TransferData->branch_to ==
                                    $BData->id){{ 'selected' }}@endif>
                                    {{ sprintf("%04d", $BData->branch_code)." - ".$BData->branch_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!--Form Right-->
                <div class="col-lg-6">

                    <div class="form-row form-group align-items-center">
                        <div class="col-lg-4 ">
                            <label class="input-title">Transfer Date</label for="">
                        </div>
                        <div class="col-lg-7 input-group">
                            <div class="input-group-prepend ">
                                <span class="input-group-text ">
                                    <i class="icon wb-calendar round" aria-hidden="true"></i>
                                </span>
                            </div>
                            <?php
                                $TransferDate = new DateTime($TransferData->transfer_date);
                                $TransferDate = (!empty($TransferDate)) ? $TransferDate->
                                                    format('d-m-Y') : date('d-m-Y');
                            ?>
                            <input type="text" id="transfer_date" name="transfer_date"
                                class="form-control round" readonly value="{{ $TransferDate }}">
                        </div>
                    </div>

                    <div class="form-row form-group align-items-center">
                        <div class="col-lg-4">
                            <label class="input-title">Order No</label>
                        </div>
                        <div class="col-lg-7">
                            <input type="text" class="form-control round" placeholder="Enter Order No"
                                name="order_no" id="order_no" value="{{$TransferData->order_no}}">
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

                <label class="col-lg-1 input-title">Group</label>
                <div class="col-lg-2 inputPercentSearch input-group">
                    {{-- Query for get all group --}}
                    <?php $GroupList = Common::ViewTableOrder('inv_p_groups',
                                    ['is_delete' => 0],
                                    ['id', 'group_name'],
                                    ['group_name', 'ASC']) ?>
                    <select class="form-control clsSelect2"
                         id="prod_group_id" onchange="fnAjaxSelectBox(
                                                 'prod_cat_id',
                                                 this.value,
                                     '{{base64_encode('inv_p_categories')}}',
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

                <label class="col-lg-1 input-title">Category</label>
                <div class="col-lg-2 inputPercentSearch input-group">
                    {{-- Query for get all category --}}
                    <?php $CategoryList = Common::ViewTableOrder('inv_p_categories',
                                    ['is_delete' => 0],
                                    ['id', 'cat_name'],
                                    ['cat_name', 'ASC']) ?>
                    <select class="form-control clsSelect2"
                         id="prod_cat_id" onchange="fnAjaxSelectBox(
                                             'prod_sub_cat_id',
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

                <label class="col-lg-1 input-title">Sub Category</label>
                <div class="col-lg-2 inputPercentSearch  input-group">
                    {{-- Query for get all Sub-category --}}
                    <?php $SubCategoryList = Common::ViewTableOrder('inv_p_subcategories',
                                    ['is_delete' => 0],
                                    ['id', 'sub_cat_name'],
                                    ['sub_cat_name', 'ASC']) ?>
                    <select class="form-control clsSelect2"
                         id="prod_sub_cat_id" onchange="fnAjaxSelectBox(
                                             'prod_model_id',
                                             this.value,
                                 '{{base64_encode('inv_p_models')}}',
                                 '{{base64_encode('prod_sub_cat_id')}}',
                                 '{{base64_encode('id,model_name')}}',
                                 '{{url('/ajaxSelectBox')}}');
                                 fnProductLoad();">
                        <option value="">Select</option>
                        @foreach($SubCategoryList as $SBData)
                        <option value="{{ $SBData->id }}">{{ $SBData->sub_cat_name }}</option>
                        @endforeach
                    </select>
                </div>

                <label class="col-lg-1 input-title">Model</label>
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
                    <strong class="text-danger">Current Stock: <label id="current_stock"></label></strong>
                    <input type="hidden" id="stock_quantity_0" value="0">
                </div>
            </div>


            <table class="table table-hover table-striped table-bordered w-full text-center table-responsive" id="transferTable">
                <thead class="scrollHead">
                    <tr>
                        <th width="61%" class="RequiredStar">Product Name</th>
                        <th width="35%" class="RequiredStar">Quantity</th>
                        <th width="4%"></th>
                    </tr>
                </thead>
                <tbody class="scrollBody">
                    <?php
                        $i = 0;
                        $TableID = "transferTable";

                        $ColumnName = "product_id_arr[]&sys_barcode_arr[]&product_name_arr[]&product_quantity_arr[]&unit_cost_price_arr[]&total_cost_price_arr[]";

                        $ColumnID = "product_id_&sys_barcode_&product_name_&product_quantity_&unit_cost_price_&total_cost_price_&deleteRow_";
                        // 'company_id'=> $CompanyID,
                        $ProductList = Common::ViewTableOrder('inv_products',
                                        ['is_delete' => 0],
                                        ['id', 'product_name','product_code'],
                                        ['product_name', 'ASC']);
                    ?>
                    <tr>
                        <td width="61%" class="input-group barcodeWidth onlyNumber">
                            {{-- name="product_id_arr[]" --}}
                            <select id="product_id_0" class="form-control round clsProductSelect"
                                    onchange="fnAjaxCheckStock();">
                                <option value="">Select Product</option>

                                @foreach($ProductList as $ProductInfo)
                                <option value="{{ $ProductInfo->id }}" 
                                    pname="{{ $ProductInfo->product_name }}"
                                    sbarcode="{{ $ProductInfo->product_code }}">
                                    {{ $ProductInfo->product_code ? $ProductInfo->product_name . ' (' . $ProductInfo->product_code . ')' : $ProductInfo->product_name}}
                                </option>
                                @endforeach
                            </select>
                        </td>

                        <td width="35%">
                            {{-- name="product_quantity_arr[]" --}}
                            <input type="number" id="product_quantity_0" class="form-control round clsQuantity text-center"
                                placeholder="Enter Quantity" value="0"
                                onkeyup="fnTotalQuantity(); fnTtlProductPrice(0); fnCheckQuantity(0);" required min="1" readonly>
                        </td>

                        <td width="4%">
                            <a href="javascript:void(0);"
                                class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center clsAddNewRow"
                                onclick="btnAddNewRow('<?= $TableID ?>', '<?= $ColumnName ?>', '<?= $ColumnID ?>', 'TotalRowID');">
                                <i class="icon wb-plus  align-items-center"></i>
                            </a>
                        </td>
                    </tr>

                    @if(count($TransferDataD) > 0)
                        @foreach($TransferDataD as $TranData)
                        <?php $i++; ?>

                        <tr>
                            <td width="61%" class="input-group barcodeWidth">
                                <input type="hidden" id="product_id_{{ $i }}" name="product_id_arr[]" value="{{ $TranData->product_id }}">
                                <input type="hidden" id="stock_quantity_{{ $i }}" value="{{INVS::stockQuantity($TransferData->branch_from,$TranData->product_id) + $TranData->product_quantity}}">

                                @foreach($ProductList as $ProductInfo)
                                @if($ProductInfo->id == $TranData->product_id)
                                <input type="text" class="form-control round"
                                    value="{{ $ProductInfo->product_code ? $ProductInfo->product_name . ' (' . $ProductInfo->product_code . ')' : $ProductInfo->product_name }} "
                                    readonly>
                                @endif
                                @endforeach
                            </td>

                            <td width="35%">
                                <input type="number" name="product_quantity_arr[]" id="product_quantity_{{ $i }}"
                                    class="form-control round clsQuantity text-center" placeholder="Enter Quantity"
                                    value="{{ $TranData->product_quantity }}"
                                    onkeyup="fnTotalQuantity(); fnCheckQuantity({{ $i }});" required min="1">
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
            </table>

            <!-- Row_Count is temporary variable for using row add and delete-->
            <input type="hidden" id="TotalRowID" value="{{ $i }}" />

            <div class="mt-4 table-responsive">
                <table class="table table-striped table-bordered w-full text-center" id="tableQuanAmnt">
                    <tbody>
                        <tr>
                            <td width="45%">
                                <h5>Total Quantity</h5>
                                <input type="hidden" name="total_quantity" id="total_quantity" value="0"
                                    min="1">
                            </td>
                            <td width="10%" id="tdTotalQuantity" style="font-weight: bold;">0</td>

                            <!-- <td width="20%">
                                <h5>Total Amount</h5>
                                <input type="hidden" name="total_amount" id="total_amount" value="0" min="1">
                            </td>
                            <td width="25%" id="tdTotalAmount" class="text-right" style="font-weight: bold;">0</td> -->
                        </tr>
                    </tbody>
                </table>
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
                            @if($TransferData->transfer_date == $SysDate)
                            <button type="submit" class="btn btn-primary btn-round"
                                id="updateButtonforTransfer">Update</button>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--End Panel 2-->
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function() {

        // Generate bill if branche is changed
        $('#branch_from').change(function() {
            fnGenBillNo($('#branch_from').val());
        });

        // Product Load on selecting group,category and sub Category Start
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
        // Product Load on selecting group,category and sub Category End

        //initialization for search select box
        $('.clsProductSelect').select2();

        //initially set 0 into these fields
        $('#current_stock').html(0);
        $('#stock_quantity_0').val(0);

        //set the value which is selected when add new purchase in the branch
        $('#branch_id_hidden').val($('#branch_from').val());

        //get company id into the popup for insert supplier
        $('#company_id').val(this.value);

        //call these function for calculate on load
        fnTotalQuantity();
        fnTotalAmount();
        //end-------------

    });

    /*Generate Transfer No via AjaxController */
    function fnGenBillNo(BranchID) {
        if (BranchID != '') {
            $.ajax({
                method: "GET",
                url: "{{ url('/ajaxGBillTransferInv') }}",
                dataType: "text",
                data: {
                    BranchID: BranchID
                },
                success: function(data) {
                    if (data) {

                        $('#bill_no').val(data);
                    }
                }
            });
        }
    }

    // Function for Loading Product In product Table
    function fnProductLoad() {
        var CompanyID = $('#company_id').val();
        // var SupplierID = $('#supplier_id').val();
        var GroupID = $('#prod_group_id').val();
        var CategoryID = $('#prod_cat_id').val();
        var SubCatID = $('#prod_sub_cat_id').val();
        var ModelID = $('#prod_model_id').val();
        var firstRowFirstColId = $('#transferTable tbody tr td:first-child select').attr('id');

        $.ajax({
            method: "GET",
            url: "{{url('/ajaxProductLTransferInv')}}",
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

    // Onchanging product name set quantity and Cost
    $('#product_id_0').change(function() {

        if ($(this).val() != '') {
            $('#product_quantity_0').prop('readonly', false);
            // var selProdCostPrice = $(this).find("option:selected").attr('pcprice');
            // $('#unit_cost_price_0').val(selProdCostPrice);
            // fnTtlProductPrice(0);
        } else {
            $('#product_quantity_0').prop('readonly', true);
            // $('#unit_cost_price_0').val(0);
        }
    });

    /* Add row Start */
    function btnAddNewRow(TableID, ColumnNameS, ColumnIDS, TotalRowID) {

        var ColumnName = ColumnNameS.split("&");
        var ColumnID = ColumnIDS.split("&");
        /*
            0: "product_id_"
            1: "sys_barcode_"
            2: "product_name_"
            3: "product_quantity_"
            4: "unit_cost_price_"
            5: "total_cost_price_"
            6: "deleteRow_"
         */
        if ($('#' + ColumnID[0] + 0).val() != '' && $('#product_quantity_0').val() > 0 && $('#stock_quantity_0').val() != 0
                && Number($('#stock_quantity_0').val()) >= Number($('#product_quantity_0').val())) {

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
                $('#' + ColumnID[3] + 0).prop('readonly', true);

                $('#current_stock').html(0);
                $('#upcomming_stock').html(0);
                fnTotalQuantity();
                fnTtlProductPrice(rowNumber);


            }else{

                TotalRowCount++;
                $('#' + TotalRowID).val(TotalRowCount);


                var ProductID = $('#' + ColumnID[0] + 0).val();
                var ProductName = $('#' + ColumnID[0] + 0).find("option:selected").attr('pname');
                var ProductBarcode = $('#' + ColumnID[0] + 0).find("option:selected").attr('pbarcode');
                var ProductSysBarcode = $('#' + ColumnID[0] + 0).find("option:selected").attr('sbarcode');
                var ProductCostPrice = $('#' + ColumnID[0] + 0).find("option:selected").attr('pcprice');
                // var ProductQuantity = $('#' + ColumnID[3] + 0).val();
                var ProductAmount = $('#' + ColumnID[5] + 0).val();

                // var StockQuantity = $('#stock_quantity_0').val();



                var html = '<tr>';

                html += '<td class="input-group barcodeWidth" width="35%">';

                html += '<input type="hidden" id="product_id_'+ TotalRowCount +'" name="' + ColumnName[0] + '" value="' + ProductID + '">';
                html += '<input type="hidden" id="stock_quantity_'+ TotalRowCount +'" value="' + StockQuantity + '">';
                html += '<input type="text" class="form-control round" value="' + ProductName + '(' + ProductSysBarcode + ')' +
                    '" readonly >';
                html += '</td>';
                html += '<td width="10%">';

                html += '<input type="number" name="' + ColumnName[3] + '" id="' + ColumnID[3] + TotalRowCount +
                    '" class="form-control round clsQuantity" value="' + ProductQuantity +
                    '" onkeyup="fnTotalQuantity(); fnTtlProductPrice(' + TotalRowCount + ');fnCheckQuantity(' + TotalRowCount + ');" required min="1">';
                html += '</td>';

                html += '<td width="15%">' +
                    '<input type="number" name="' + ColumnName[4] + '" id="' + ColumnID[4] + TotalRowCount +
                    '" class="form-control round" value="' + ProductCostPrice + '" readonly required min="1">' +

                    '</td>';
                html += '<td width="15%">' +
                    '<input type="number" name="' + ColumnName[5] + '" id="' + ColumnID[5] + TotalRowCount +
                    '" class="form-control round ttlAmountCls" value="' + ProductAmount + '" readonly>' +
                    '</td>';
                html += '<td width="5%">' +
                    '<a href="javascript:void(0)" class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center" onclick="btnRemoveRow(this);">' +
                    ' <i class="icon fa fa-times align-items-center"></i>' +
                    '</a>' +
                    '</td>';
                html += '</tr>';

                $('#' + TableID + ' tbody').find('tr:first').after(html);

                $('#' + ColumnID[0] + 0).val('');
                $('#' + ColumnID[0] + 0).trigger('change');
                $('#' + ColumnID[3] + 0).val(0);
                $('#' + ColumnID[4] + 0).val(0);
                $('#' + ColumnID[5] + 0).val(0);
                $('#' + ColumnID[3] + 0).prop('readonly', true);

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
                $('#product_quantity_0').val(0);
                fnTotalQuantity();
                fnTtlProductPrice(0);
            }

        }
    }
    /* Add row End */

    /* Remove row Start */
    function btnRemoveRow(RemoveID) {

        $(RemoveID).closest('tr').remove();
        fnTotalQuantity();
        // fnTotalAmount();
    }
    /* Remove row End */


    /* On selecting company load Transfer from and transfer to Branch */
    // $('#company_id').change(function() {

    //     //function for get Branch data
    //     $('#company_id').val(this.value);

    //     fnAjaxSelectBox(
    //         'branch_from',
    //         this.value,
    //         '{{base64_encode("gnl_branchs")}}',
    //         '{{base64_encode("company_id")}}',
    //         '{{base64_encode("id,branch_name")}}',
    //         '{{url("/ajaxSelectBox")}}',
    //     );

    //     fnAjaxSelectBox(
    //         'branch_to',
    //         this.value,
    //         '{{base64_encode("gnl_branchs")}}',
    //         '{{base64_encode("company_id")}}',
    //         '{{base64_encode("id,branch_name")}}',
    //         '{{url("/ajaxSelectBox")}}',
    //     );

    //     // fnGenTransferNo(this.value);
    // });



    /*Check Stock before Transfer */
    function fnAjaxCheckStock(){

        var BranchId = $('#branch_from').val();
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

                            if ( $('#product_id_'+row).val() == $('#product_id_0').val()){

                                stock = Number($('#stock_quantity_'+ row).val());
                                //console.log($('#product_id_'+row).val());
                                var GivenQnt = Number($('#product_quantity_'+ row).val());
                                totaladdedqnt+= GivenQnt;
                                //console.log(data-totaladdedqnt);
                                //console.log(GivenQnt);

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


    /*Check Quantity with stock */
    function fnCheckQuantity(Row) {

        var StockQuantity = Number($('#stock_quantity_'+ Row).val());
        var TypeQuantity = Number($('#product_quantity_'+ Row).val());

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


    /*get selected sys barcode & product id & set into hidden input fields*/
    function fnGetSelectedValue(RowId) {
        $("#product_id_" + RowId).change(function() {
            var selProdSBar = $(this).children("option:selected").attr('sbarcode');
            var selProdName = $(this).children("option:selected").attr('pname');
            // var selProdCost = $(this).children("option:selected").attr('tcprice');

            $('#sys_barcode_' + RowId).val(selProdSBar);
            $('#product_name_' + RowId).val(selProdName);
            // $('#unit_cost_price_' + RowId).val(selProdCost);
        });
    }

    /* Calculate Total Quantity */
    function fnTotalQuantity() {

        var totalQtn = 0;
       // console.log(totalQtn)
        $('.clsQuantity').each(function() {
            totalQtn = Number(totalQtn) + Number($(this).val());
        });

        $('#total_quantity').val(totalQtn);
        $('#total_qnty').val(totalQtn);
        $('#tdTotalQuantity').html(totalQtn);
    }

    /* Calculate Total Price of Each Product */
    function fnTtlProductPrice(Row) {

        var ProductQtn = $('#product_quantity_' + Row).val();
        var ProductPrice = $('#unit_cost_price_' + Row).val();

        var TotalProductPrice = (Number(ProductQtn) * Number(ProductPrice));

        $('#total_cost_price_' + Row).val(TotalProductPrice.toFixed(2));

        fnTotalAmount();
    }

    /* Calculate Total Price of all products */
    function fnTotalAmount() {

        var totalAmt = 0;
        $('.ttlAmountCls').each(function() {
            totalAmt = Number(totalAmt) + Number($(this).val());
        });

        $('#tdTotalAmount').html(totalAmt.toFixed(2));

        //-------------------------- Total Amount
        $('#total_amount').val(totalAmt.toFixed(2));
        $('#total_amount_t').val(totalAmt.toFixed(2));
    }

    /* Check branch before submit */
    $('#updateButtonforTransfer').on('click', function(event) {
        event.preventDefault();

        var branchFrom = $('#branch_from').val();
        var branchTo = $('#branch_to').val();
        console.log(branchFrom, branchTo);

        if (branchFrom != 1 ) {
            if (branchTo != 1) {

                if (branchFrom == branchTo ){
                    swal({
                            icon: 'error',
                            title: 'Error',
                            text: 'Two Branch Name Cant be same',
                        }

                    );

                }

                else if ($('#total_quantity').val() > 0 && $('#product_id_0').val() == '') {
                    $('#transfer_form').submit();
                }

                else {
                    swal({
                        icon: 'error',
                        title: 'Error',
                        text: 'Total amount must be gratter than zero !!',
                    });
                }
            }

            else if (branchTo == 1 ){
                swal({
                    icon: 'error',
                    title: 'Error',
                    text: 'Access Denied ! You are not authorized in this page',
                    confirmButtonText: "Ok"
                }).then((isConfirm) => {
                  if (isConfirm) {
                    window.location.href = "{{url('inv/transfer')}}";
                  }
                });
            }

        }
        else if (branchFrom == 1 ){
            swal({
                    icon: 'error',
                    title: 'Error',
                    text: 'Access Denied ! You are not authorized in this page',
                    confirmButtonText: "Ok"
            }).then((isConfirm) => {
                if (isConfirm) {
                    window.location.href = "{{url('inv/transfer')}}";
                }
            });
        }
    });

    // Disable Button for multiple click
    $('#transfer_form').submit(function (event) {
        $(this).find(':submit').attr('disabled', 'disabled');
    });

</script>

@endsection
