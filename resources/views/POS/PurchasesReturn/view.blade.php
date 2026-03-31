@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\CommonService as Common;
?>

      
<div class="">

  <table class="table table-striped table-bordered">
      <thead>
          <tr>
              <th colspan="4">
                  Purchase Return Information
              </th>
          </tr>
      </thead>
      <tbody>
          <tr>
              <td width="25%">For Branch</td>
              <td width="25%">{{ $PReturnData->branch['branch_name'] }}</td>
              <td width="25%">Bill No.</td>
              <td width="25%">{{$PReturnData->bill_no}}</td>
          </tr>
          <tr>
              <td>Supplier </td>
              <td>{{ $PReturnData->supplier['sup_name']}}</td>
              <td>Return Date</td>
              <td><input type="hidden" id="return_date_h" name="return_date"
                  value="{{ $PReturnData->return_date }}">{{date('d-m-Y', strtotime($PReturnData->return_date)) }}</td>
          </tr>
      </tbody>
  </table>
    <table class="table table-hover table-striped table-bordered w-full "
        id="purchaseTable">
        <thead>
            <tr>
                <!-- <th width="40%">Barcode</th> -->
                <th width="25%">Product Name</th>
                <th width="25%">Quantity</th>
                <!--<th width="10%" class="RequiredStar">Order Quantity</th>-->
                <!--<th width="10%" class="RequiredStar">Receive Quantity</th>-->
                <th width="25%">Cost Price</th>
                <th width="25%">Total</th>

            </tr>
        </thead>
        <tbody>
         <?php
         $i = 0;
         $TableID = "purchaseTable";

         $ColumnName = "product_id_arr[]&sys_barcode_arr[]&product_bar_arr[]&product_quantity_arr[]&ordered_quantity_arr[]&received_quantity_arr[]&unit_cost_price_arr[]&total_cost_price_arr[]";

         $ColumnID = "product_id_&sys_barcode_&product_bar_&product_quantity_&ordered_quantity_&received_quantity_&unit_cost_price_&total_cost_price_&deleteRow_";
         $ProductList = Common::ViewTableOrder('pos_products',
                         ['is_delete' => 0],
                         ['id', 'product_name', 'cost_price', 'sale_price', 'prod_vat', 'sys_barcode', 'prod_barcode'],
                         ['product_name', 'ASC']);
         ?>



         @if(count($PReturnDataD) > 0)
         @foreach($PReturnDataD as $Data)
         <?php $i++; ?>
         <tr>
             <td width="25%">

                 @foreach($ProductList as $ProductInfo)
                 @if($ProductInfo->id == $Data->product_id)
                 {{ $ProductInfo->product_name . ' (' . $ProductInfo->sys_barcode . ')' }}
                 @endif
                 @endforeach
             </td>

             <td width="25%" class="text-center">{{ $Data->product_quantity }}</td>

             <!-- <td>
                     <input type="hidden" name="ordered_quantity_arr[]" id="ordered_quantity_0" class="form-control round clsOrderQuantity"
                     value="0" onkeyup="fnTotalOrderQuantity();">
                 </td> -->
             <!-- <td>
                     <input type="hidden" name="received_quantity_arr[]" id="received_quantity_0" class="form-control round clsRcvQuantity"
                     value="0" onkeyup="fnTotalReceiveQuantity();">
                 </td> -->
             <td width="25%" class="text-right">{{ $Data->unit_cost_price }}</td>
             <td class="text-right">{{ $Data->total_cost_price }}</td>
         </tr>
         @endforeach
         @endif
     </tbody>
        <tfoot>
            <tr>
                <td width="25%" class="text-right">
                    <h5>Total Quantity</h5>
                </td>
                <td width="25%" class="text-center"><h5>{{$PReturnData->total_quantity}}</h5></td>

                <td width="25%" class="text-right">
                    <h5>Total Amount</h5>
                </td>
                <td width="25%" class="text-right" > <h5> {{$PReturnData->total_amount}}</h5></td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="row align-items-center">
    <div class="col-lg-12">
        <div class="form-group d-flex justify-content-center">
            <div class="example example-buttons">
              <a href="javascript:void(0)" onclick="goBack();"
                  class="btn btn-default btn-round d-print-none">Back</a>
                <a href="javascript:void(0)" onClick="window.print();"
                 class="btn btn-default btn-round clsPrint d-print-none">Print</a>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
$(document).ready(function() {

    $('.clsProductSelect').select2();
    $('#company_id').change(function() {

        fnAjaxSelectBox(
            'supplier_id',
            this.value,
            '{{base64_encode("pos_suppliers")}}',
            '{{base64_encode("company_id")}}',
            '{{base64_encode("id,sup_name")}}',
            '{{url("/ajaxSelectBox")}}',
        );
    });
    fnTotalQuantity();
    fnTotalAmount();


});

/* On selecting company load Supplier and Branch */

// product load function
function fnProductLoad() {
    var CompanyID = $('#company_id').val();
    var SupplierID = $('#supplier_id').val();
    var GroupID = $('#prod_group_id').val();
    var CategoryID = $('#prod_cat_id').val();
    var SubCatID = $('#prod_sub_cat_id').val();
    var ModelID = $('#prod_model_id').val();



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
// END PORODUCT LOAD

// start first column on change
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
// end first column on change

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
        // Input Feild For sys_barcode
        html += '<input type="hidden" name="' + ColumnName[1] + '" id="' + ColumnID[1] + TotalRowCount + '" value="' +
            ProductSysBarcode + '" >';
        // Input Feild For product_barcode
        html += '<input type="hidden" name="' + ColumnName[2] + '" id="' + ColumnID[2] + TotalRowCount + '" value="' +
            ProductBarcode + '" >';

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
        $('#' + TableID).append(html);

        $('#' + ColumnID[0] + 0).val('');
        $('#' + ColumnID[0] + 0).trigger('change');
        $('#' + ColumnID[3] + 0).val(0);
        $('#' + ColumnID[6] + 0).val(0);
        $('#' + ColumnID[7] + 0).val(0);
        $('#' + ColumnID[3] + 0).prop('readonly', true);
    } else {

        if ($('#' + ColumnID[0] + 0).val() == '') {
            swal({
                icon: 'error',
                title: 'Error',
                text: 'Please select product!',
            });
        } else {
            swal({
                icon: 'error',
                title: 'Error',
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

function fnGetSelectedValue(RowId) {

    var price = $("#product_id_" + RowId).children("option:selected").attr('pcprice');
    $("#product_price_" + RowId).val(price);

}

function fnTotalQuantity() {

    var totalQtn = 0;
    $('.clsQuantity').each(function() {
        totalQtn = Number(totalQtn) + Number($(this).val());
    });
    $('#total_quantity').val(totalQtn);
    $('#tdTotalQuantity').html(totalQtn);
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

    // //--------------------------- T/A After Discount
    // fnCalDiscount($('#discount_rate').val());

    // //-----------------------------calculate vat amount
    // fnCalVat($('#vat_rate').val());

    // //-----------------------------calculate Due amount
    // fnCalDue($('#paid_amount').val());
}




function fnGenBillNo(BranchId) {
    if (BranchId != '') {
        $.ajax({
            method: "GET",
            url: "{{ url('/ajaxGBillPR') }}",
            dataType: "text",
            data: {
                BranchId: BranchId
            },
            success: function(data) {
                if (data) {
                    $('#bill_no').val(data);
                    // console.log(data);
                }
            }
        });
    }
}






$('#submitButtonforPurReturn').on('click', function(event) {
    event.preventDefault();

    if ($('#total_amount').val() > 0) {
        $('#preturn_form').submit();
    } else {
        swal({
            icon: 'error',
            title: 'Error',
            text: 'Total payable amount must be gratter than zero !!',
        });
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
