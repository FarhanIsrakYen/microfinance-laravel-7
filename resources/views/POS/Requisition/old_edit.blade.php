@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
?>

<form method="post" data-toggle="validator" novalidate="true" id="requisition_form_id">
    @csrf
    <div class="panel">
        <div class="panel-body panel-default">
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-row align-items-center">
                        <label class="col-lg-3 input-title RequiredStar">Requisition No</label>
                        <div class="col-lg-5">
                            <div class="form-group">
                                <div class="input-group ">
                                    <input type="text" class="form-control round" id="requisition_no" name="requisition_no" placeholder="Enter Requisition No." required="required"
                                    value="{{ $requisitionM->requisition_no }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="company_id" value="{{ $requisitionM->company_id }}">

                    <input type="hidden" name="branch_to" id="branch_to" value="{{ $requisitionM->branch_to }}">

                    {!! HTML::forBranchFeild(true, 'branch_from', 'branch_from', Common::getBranchId(), false, 'Requisition From') !!}

                </div>

                <div class="col-lg-6">

                    <div class="form-row align-items-center">
                        <label class="col-lg-3 input-title RequiredStar">Requisiton Date</label>
                        <div class="col-lg-5">
                            <div class="form-group">
                                <div class="input-group ">
                                    <input type="text" name="requisition_date" id="requisition_date" class="form-control round" value="{{ $requisitionM->requisition_date }}" readonly="true" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-body panel-default">
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
                            '{{ base64_encode("pos_p_categories")}}',
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
                    <?php $CategoryList = Common::ViewTableOrder('pos_p_categories',
                                    ['is_delete' => 0],
                                    ['id', 'cat_name'],
                                    ['cat_name', 'ASC']) ?>
                    <select class="form-control clsSelect2"
                         id="prod_cat_id" onchange="fnAjaxSelectBox('prod_sub_cat_id',
                                        this.value,
                            '{{base64_encode("pos_p_subcategories")}}',
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
                    <?php $SubCategoryList = Common::ViewTableOrder('pos_p_subcategories',
                                    ['is_delete' => 0],
                                    ['id', 'sub_cat_name'],
                                    ['sub_cat_name', 'ASC']) ?>
                    <select class="form-control clsSelect2"
                         id="prod_sub_cat_id" onchange="fnAjaxSelectBox('prod_model_id',
                                        this.value,
                            '{{base64_encode("pos_p_models")}}',
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
                <table class="table table-hover table-striped table-bordered w-full text-center table-responsive my-custom-scrollbar" id="requisitionTable">
                    <thead>
                        <tr>
                            <th width="65%" class="RequiredStar">Product Name</th>
                            <th width="30%" class="RequiredStar">Quantity</th>
                            <th width="5%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                            $products = App\Model\POS\Product::where('is_delete', 0)
                                        ->select('id', 'product_name', 'cost_price', 'sale_price', 'prod_vat', 'sys_barcode', 'prod_barcode')
                                        ->orderBy('id', 'ASC')
                                        ->get();

                            $i = 0;

                            $ColumnIDS = "product_id_&product_quantity_";
                        ?>
                        <tr>
                            <td class="input-group barcodeWidth">
                                <select id="product_id_0" class="form-control round clsSelect2" 
                                style="width: 100%;">
                                    <option value="">Select Product</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" pname="{{ $product->product_name }}" pbarcode="{{ $product->prod_barcode }}">{{ $product->product_name." - ".$product->prod_barcode }}</option>
                                    @endforeach
                                </select>
                            </td>

                            <td>
                                <input type="number" id="product_quantity_0" class="form-control round clsQuantity" placeholder="Enter Quantity" value="0"
                                onkeyup="fnTotalQuantity();">
                            </td>

                            <td>
                                <a href="javascript:void(0);"
                                    class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center"
                                    onclick="btnAddNewRow('{{ $ColumnIDS }}');">
                                    <i class="icon wb-plus  align-items-center"></i>
                                </a>
                            </td>
                        </tr>
                        <?php $i = 0; ?>
                        @if(count($requisitionD) > 0)
                        @foreach($requisitionD as $reqD)
                        <?php $i++; ?>
                        <tr>
                            <td class="input-group barcodeWidth">
                            	<input type="text" class="form-control round" value="{{ $reqD->product_name .' ('. $reqD->barcode_no.')' }}" readonly>

                            	<input type="hidden" name="product_id_arr[]" id="product_id_{{ $i }}" value="{{ $reqD->product_id }}">
                            	{{-- <input type="hidden" name="product_name_arr[]" id="product_name_{{ $i }}" value="{{ $reqD->product_name }}"> --}}
                            	{{-- <input type="hidden" name="product_barcode_arr[]" id="product_barcode_{{ $i }}" value="{{ $reqD->barcode_no }}"> --}}
                            </td>

                            <td>
                                <input type="number" id="product_quantity_{{ $i }}" 
                                name="product_quantity_arr[]" class="form-control round clsQuantity" value="{{ $reqD->product_quantity }}" 
                                onkeyup="fnTotalQuantity();">
                            </td>

                            <td>
                                {{-- <a href="javascript:void(0);"
                                    class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center"
                                    onclick="btnAddNewRow('{{ $ColumnIDS }}');">
                                    <i class="icon wb-plus  align-items-center"></i>
                                </a> --}}
                                <a href="javascript:void(0)" class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center" onclick="btnRemoveRow(this);">
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
                                <td width="80%">
                                    <h5>Total Requisiton Quantity</h5>
                                    <input type="hidden" name="total_quantity" id="total_quantity" value="0">
                                </td>
                                <td width="20%" id="tdTotalQuantity" style="font-weight: bold;">0</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row align-items-center">
        <div class="col-lg-1">
            <label for="remarks" class="input-title">Remarks</label>
        </div>
        <div class="col-lg-11">
            <textarea class="form-control round" id="remarks" name="remarks"
                rows="1" placeholder="Enter Remarks"></textarea>
        </div>
    </div>

    <div class="row align-items-center">
        <div class="col-lg-12">
            <div class="form-group d-flex justify-content-center">
                <div class="example example-buttons">
                    <a href="javascript:void(0)" onclick="goBack();"
                              class="btn btn-default btn-round d-print-none">Back</a>

                        <button type="submit" class="btn btn-primary btn-round"
                        id="submit_button_id">Update</button>
                </div>
            </div>
        </div>
    </div>
</form>

<!--End Page-->
<script type="text/javascript">

	$(document).ready(function(){
		fnTotalQuantity();
	});

	$('#product_id_0').change(function() {

        if ($(this).val() != '') {
            $('#product_quantity_0').prop('readonly', false);

        } else {
            $('#product_quantity_0').prop('readonly', true);
        }
    });

    function btnAddNewRow(ColumnIDS) {
        
        var ColumnID = ColumnIDS.split("&");
        /*
         * ColumnID[0] = this is ID for input feild Product_id_0
         * ColumnID[1] = this is ID for input feild product_quantity_0
         */
        if ($('#product_id_0').val() != '' && $('#product_quantity_0').val() > 0 ) {

            var TotalRowCount = $('#TotalRowID').val();
            /*
            *marge two row if same product found
            */

            var ProductQuantity = $('#product_quantity_0').val();

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

                $('#product_id_0').val('');
                $('#product_id_0').trigger('change');
                $('#product_quantity_0').val(0);
                $('#product_quantity_' + rowNumber).val(ProductQuantity);

                // fnTotalQuantity();
                // fnTtlProductPrice(rowNumber);
            }

            else {
                TotalRowCount++;
                $('#TotalRowID').val(TotalRowCount);
                var ProductID = $('#product_id_0').val();
                var ProductName = $('#product_id_0').find("option:selected").attr('pname');
                var ProductBarcode = $('#product_id_0').find("option:selected").attr('pbarcode');

                var ProductQuantity = $('#product_quantity_0').val();

                var html = '<tr>';

                html += '<td width="65%">'+
                            '<input type="hidden" id="product_id_'+ TotalRowCount +'" name="product_id_arr[]" value="' + ProductID + '">'+

                            '<input type="hidden" id="product_name_'+ TotalRowCount +'" name="product_name_arr[]" value="' + ProductName + '">'+

                            '<input type="hidden" id="product_barcode_'+ TotalRowCount +'" name="product_barcode_arr[]" value="' + ProductBarcode + '">'+

                            '<input type="text" class="form-control round" value="' + ProductName + '(' + ProductBarcode + ')' + '" readonly >'+
                        '</td>';

                html += '<td width="30%">'+

                            '<input type="number" name="product_quantity_arr[]" id="product_quantity_' + TotalRowCount +
                            '" class="form-control round clsQuantity" value="' + ProductQuantity + '" onkeyup="fnTotalQuantity();fnTtlProductPrice(' + TotalRowCount + ');" required min="1">'+
                        '</td>';

                 html += '<td width="5%">' +
                        '<a href="javascript:void(0)" class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center" onclick="btnRemoveRow(this);">' +
                        '<i class="icon fa fa-times align-items-center"></i>' +
                        '</a>' +
                    '</td>';

                html += '</tr>';

                // $('#' + TableID).append(html);
                $('#requisitionTable tbody').find('tr:first').after(html);

                $('#product_id_0').val('');
                $('#product_id_0').trigger('change');
                $('#product_quantity_0').val(0);
            }
        }

        else {

            if ($('#Product_id_0').val() == '') {
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

        }
    }
    
    /* Remove row Start */
    function btnRemoveRow(RemoveID) {

        $(RemoveID).closest('tr').remove();
        fnTotalQuantity();
    }

    function fnTotalQuantity() {

        var totalQtn = 0;
        $('.clsQuantity').each(function() {
            totalQtn = Number(totalQtn) + Number($(this).val());
        });
        $('#total_quantity').val(totalQtn);
        $('#tdTotalQuantity').html(totalQtn);
    }

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

    $('#submit_button_id').on('click', function(event) {
        event.preventDefault();

        var ttlTableRows = $('#requisitionTable tbody tr').length;

        if ($('#product_id_0').val() == '' && ttlTableRows > 1) {
            
            $('#requisition_form_id').submit();
        } else {

            if($('#product_id_0').val() != ''){
                swal({
                    icon: 'error',
                    title: 'Error',
                    text: 'Product Entry must be empty!!',
                });
            }
            else if(ttlTableRows <= 1){
                swal({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please add product!!',
                });
            }

        }
    });

    $('form').submit(function (event) {
        $(this).find(':submit').attr('disabled', 'disabled');
    });

</script>
@endsection
