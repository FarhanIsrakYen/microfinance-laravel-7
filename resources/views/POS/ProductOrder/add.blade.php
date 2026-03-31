@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\CommonService as Common;
use App\Services\PosService as POSS;
?>

<form method="post" id="order_form_id" data-toggle="validator" novalidate="true" autocomplete="off">
	@csrf

	<div class="row">
        <div class="col-lg-6">

            <input type="hidden" name="company_id" value="{{ Common::getCompanyId() }}">

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title">Order No</label>
                <div class="col-lg-5">
                    <div class="form-group">
                        <div class="input-group ">
                            <input type="text" class="form-control round" id="order_no" name="order_no" placeholder="Requisition No" value="{{ POSS::generateBillOrder(Common::getBranchId()) }}" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title">Order Date</label>
                <div class="col-lg-5">
                    <div class="form-group">
                        <div class="input-group ">
                            <input type="text" name="order_date" id="order_date" class="form-control round" value="{{ Common::systemCurrentDate() }}" placeholder="DD-MM-YYYY" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Supplier</label>
                <?php
                	$suppliers = App\Model\POS\Supplier::where([['is_delete', 0], ['is_active', 1]])->get();
                ?>
                <div class="col-lg-5">
                    <div class="form-group">
                        <div class="input-group ">
                            <select class="form-control clsSelect2" name="order_to" id="order_to" required data-error="Please select suppliers">
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $SData)
                                	<option value="{{ $SData->id }}">{{ $SData->sup_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title">Expected Delivery Date</label>
                <div class="col-lg-5">
                    <div class="form-group">
                        <div class="input-group ">
                            <input type="text" class="form-control round datepicker-custom" id="delivery_date" name="delivery_date" autocomplete="off" placeholder="DD-MM-YYYY" data-error="Please select delivery date">
                        </div>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title">Delivery Place</label>
                <?php
                    $branchs = App\Model\GNL\Branch::where([['is_approve', 1], ['is_delete', 0], ['is_active', 1]])->get();
                ?>
                <div class="col-lg-5">
                    <div class="form-group">
                        <div class="input-group ">
                            <select class="form-control clsSelect2" name="delivery_place" id="delivery_place">
                                @foreach($branchs as $row)
                                    <option value="{{ $row->id }}">{{ sprintf("%04d", $row->branch_code).' - '.$row->branch_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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

           
            <table class="table table-hover table-striped table-bordered w-full text-center table-responsive mt-4" id="orderTable">
                <thead class="scrollHead">
                    <tr>
                        <th width="61%" class="RequiredStar">Product Name</th>
                        <th width="35%" class="RequiredStar">Quantity</th>
                        <th width="4%"></th>
                    </tr>
                </thead>
                <tbody class="scrollBody">
                    <?php
						$products = App\Model\POS\Product::where('is_delete', 0)
						    ->select('id', 'product_name', 'cost_price', 'sale_price', 'prod_vat', 'sys_barcode', 'prod_barcode')
						    ->orderBy('id', 'ASC')
						    ->get();

						$i = 0;

						$ColumnIDS = "product_id_&product_quantity_";
						?>
                    <tr>
                        <td width="61%" class="barcodeWidth">
                            <select id="product_id_0" class="form-control round clsSelect2" style="width: 100%;">
                                <option value="">Select Product</option>
                                @foreach($products as $product)
                                <option value="{{ $product->id }}" pname="{{ $product->product_name }}" pbarcode="{{ $product->prod_barcode }}">
                                    {{ $product->product_name." - ".$product->prod_barcode }}
                                </option>
                                @endforeach
                            </select>
                        </td>

                        <td width="35%">
                            <input type="number" class="form-control round clsQuantity text-center" style="text-align:center;"
                                id="product_quantity_0" value="0" readonly onkeyup="fnTotalQuantity();">
                        </td>

                        <td width="4%">
                            <a href="javascript:void(0);"
                                class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center"
                                onclick="btnAddNewRow('{{ $ColumnIDS }}');">
                                <i class="icon wb-plus  align-items-center"></i>
                            </a>
                        </td>
                    </tr>
                </tbody>
                <tfoot class="scrollFooter">
                    <tr>
                        <td width="61%" style="text-align:right;">
                            <h5>TOTAL</h5>
                            <input type="hidden" name="total_quantity" id="total_quantity" value="0"
                                min="1">
                        </td>
                        <td width="35%" id="tdTotalQuantity" style="font-weight: bold;">0</td>
                        <td width="4%"></td>
                    </tr>
                </tfoot>
            </table>

            <!-- Row_Count is temporary variable for using row add and delete-->
            <input type="hidden" id="TotalRowID" value="0" />
        </div>
    </div>



    <br>
    <div class="row align-items-center">
        <div class="col-lg-1">
            <label for="remarks" class="input-title">Remarks</label>
        </div>
        <div class="col-lg-11">
            <textarea class="form-control round" id="remarks" name="remarks"
                rows="1" placeholder="Enter Remarks"></textarea>
        </div>
    </div>
    <br>
{{--     <div class="row align-items-center">
        <div class="col-lg-12">
            <div class="form-group d-flex justify-content-center">
                <div class="example example-buttons">
                    <a href="javascript:void(0)" onclick="goBack();"
                        class="btn btn-default btn-round d-print-none">Back</a>

                    <button type="submit" class="btn btn-primary btn-round" id="submit_button_id">Save</button>
                </div>
            </div>
        </div>
    </div> --}}

    <div class="row">
        <div class="col-lg-12">
            <div class="form-group text-center">
                <div class="example example-buttons">
                    <a href="javascript:void(0)" onclick="goBack();"
                              class="btn btn-default btn-round d-print-none">Back</a>

                        <button type="submit" class="btn btn-primary btn-round"
                        id="submit_button_id">Save</button>
                </div>
            </div>
        </div>
    </div>
</form>
<!--End Page-->

<script type="text/javascript">
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
    if ($('#product_id_0').val() != '' && $('#product_quantity_0').val() > 0) {

        var TotalRowCount = $('#TotalRowID').val();
        /*
         *marge two row if same product found
         */

        var ProductQuantity = $('#product_quantity_0').val();

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

            $('#product_id_0').val('');
            $('#product_id_0').trigger('change');
            $('#product_quantity_0').val(0);
            $('#product_quantity_' + rowNumber).val(ProductQuantity);

            // fnTotalQuantity();
            // fnTtlProductPrice(rowNumber);
        } else {
            TotalRowCount++;
            $('#TotalRowID').val(TotalRowCount);
            var ProductID = $('#product_id_0').val();
            var ProductName = $('#product_id_0').find("option:selected").attr('pname');
            var ProductBarcode = $('#product_id_0').find("option:selected").attr('pbarcode');

            var ProductQuantity = $('#product_quantity_0').val();

            var html = '<tr>';

            html += '<td width="61%" class="barcodeWidth">' +
                '<input type="hidden" id="product_id_' + TotalRowCount + '" name="product_id_arr[]" value="' +
                ProductID + '">' +

                // '<input type="hidden" id="product_name_'+ TotalRowCount +'" name="product_name_arr[]" value="' + ProductName + '">'+

                // '<input type="hidden" id="product_barcode_'+ TotalRowCount +'" name="product_barcode_arr[]" value="' + ProductBarcode + '">'+

                '<input type="text" class="form-control round" value="' + ProductName + '(' + ProductBarcode + ')' +
                '" readonly >' +
                '</td>';

            html += '<td width="35%">' +

                '<input type="number" name="product_quantity_arr[]" id="product_quantity_' + TotalRowCount +
                '" class="form-control round clsQuantity text-center" value="' + ProductQuantity +
                '" onkeyup="fnTotalQuantity();fnTtlProductPrice(' + TotalRowCount + ');" required min="1">' +
                '</td>';

            html += '<td width="4%">' +
                '<a href="javascript:void(0)" class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center" onclick="btnRemoveRow(this);">' +
                ' <i class="icon fa fa-times align-items-center"></i>' +
                '</a>' +
                '</td>';

            html += '</tr>';

            // $('#' + TableID).append(html);
            $('#orderTable tbody').find('tr:first').after(html);

            $('#product_id_0').val('');
            $('#product_id_0').trigger('change');
            $('#product_quantity_0').val(0);
        }
    } else {

        if ($('#Product_id_0').val() == '') {
            swal({
                icon: 'error',
                title: 'Error',
                text: 'Please select product!',
            });
        } else if ($('#product_quantity_0').val() <= 0) {
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
    fnTotalAmount();
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

    var ttlTableRows = $('#orderTable tbody tr').length;

    if ($('#product_id_0').val() == '' && ttlTableRows > 1) {

        $('#order_form_id').submit();
    } else {

        if ($('#product_id_0').val() != '') {
            swal({
                icon: 'error',
                title: 'Error',
                text: 'Product Entry must be empty!!',
            });
        } else if (ttlTableRows <= 1) {
            swal({
                icon: 'error',
                title: 'Error',
                text: 'Please add product!!',
            });
        }
    }
});

$('form').submit(function(event) {
    $(this).find(':submit').attr('disabled', 'disabled');
});

</script>

@endsection
