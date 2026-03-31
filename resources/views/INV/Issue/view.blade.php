@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\CommonService as Common;
?>
<!-- Page -->
<?php
$branchInfo = Common::ViewTableOrder('gnl_branchs', [['is_delete', 0], ['is_active', 1]], ['id', 'branch_name'], ['branch_name', 'ASC']);
?>

<table class="table table-striped table-bordered">
  <thead>
    <th colspan="4">Product Issue</th>
  </thead>
  <tbody  style="color: #000;">
      <tr>
          <th width="20%">Branch From</th>
          <td width="20%">
            @foreach($branchInfo as $Row)
            @if($Issuem->branch_from==$Row->id)
            {{$Row->branch_name}}
            @endif
            @endforeach
          </td>

          <th width="20%">Bill No</th>
          <td width="20%">{{$Issuem->bill_no}}
          </td>
      </tr>
      <tr>
          <th width="20%">Issue Date</th>
          <td width="20%">{{date('d-m-Y', strtotime($Issuem->issue_date))}}</td>

          <th width="20%">Branch To </th>
          <td width="20%">  @foreach($branchInfo as $Row)
            @if($Issuem->branch_to==$Row->id)
            {{$Row->branch_name}}
            @endif
            @endforeach </td>
      </tr>
      <tr>
          <th width="20%">Order No</th>
          <td width="20%">{{$Issuem->order_no}}</td>

          <th width="20%">&nbsp </th>
          <td width="20%">&nbsp</td>
      </tr>
  </tbody>
</table>
<table class="table table-hover table-striped table-bordered w-full text-center my-custom-scrollbar"
    id="issueTable">
    <thead>
        <tr>
            <!-- <th width="40%">Barcode</th> -->
            <th width="25%">Product Name</th>
            <th width="25%">Quantity</th>
            <!--<th width="10%" class="RequiredStar">Order Quantity</th>-->
            <!--<th width="10%" class="RequiredStar">Receive Quantity</th>-->
            <!-- <th width="25%">Cost Price</th>
            <th width="25%">Total</th> -->
        </tr>
    </thead>
    <tbody>
        <?php
        $i = 0;
        $TableID = "issueTable";

        $ColumnName = "product_id_arr[]&sys_barcode_arr[]&product_name_arr[]&product_quantity_arr[]&ordered_quantity_arr[]&received_quantity_arr[]&unit_cost_price_arr[]&total_cost_price_arr[]";

        $ColumnID = "product_id_&sys_barcode_&product_name_&product_quantity_&ordered_quantity_&received_quantity_&unit_cost_price_&total_cost_price_&deleteRow_";

        $ProductList = Common::ViewTableOrder('inv_products',
                        ['is_delete' => 0],
                        ['id', 'product_name', 'cost_price', 'product_code'],
                        ['product_name', 'ASC']);
    ?>


        @if(count($Issued) > 0)
        @foreach($Issued as $Data)
        <?php $i++; ?>
        <tr>
          <td class="text-left">
                @foreach($ProductList as $ProductInfo)
                @if($ProductInfo->id == $Data->product_id)
                    {{ $ProductInfo->product_code ? $ProductInfo->product_name . ' (' . $ProductInfo->product_code . ')' : $ProductInfo->product_name }}
                @endif
                @endforeach
            </td>

            <td>
              {{ $Data->product_quantity }}
            </td>

            <!-- <td class="text-right">{{ $Data->unit_cost_price }}

            <td class="text-right">{{ $Data->total_cost_amount }} -->
            </td>


        </tr>
        @endforeach
        @endif
    </tbody>
    <tfoot>
        <tr>
            <td width="25%" class="text-right">
                <h5>Total Quantity</h5>
            </td>
            <td width="25%" >{{$Issuem->total_quantity}}</td>

            <!-- <td width="25%" class="text-right">
                <h5>Total Amount</h5>
            </td>
            <td width="25%" class="text-right">{{$Issuem->total_amount}}</td> -->
        </tr>
    </tfoot>
</table>
<div class="row align-items-center">
    <div class="col-lg-12">
        <div class="form-group d-flex justify-content-center">
            <div class="example example-buttons">
                <a href="javascript:void(0)" onclick="goBack();"
                          class="btn btn-default btn-round d-print-none">Back</a>
                    <a href="javascript:void(0)" onClick="window.print();" class="btn btn-default btn-round clsPrint">Print</a>
            </div>
        </div>
    </div>
</div>


<script>
$(document).ready(function() {
    $('.clsProductSelect').select2();
    fnTotalQuantity();
    fnTotalAmount();
});

// product load function
function fnProductLoad() {
     var CompanyID = $('#company_id').val();
    var SupplierID = $('#supplier_id').val();
    var GroupID = $('#prod_group_id').val();
    var CategoryID = $('#prod_cat_id').val();
    var SubCatID = $('#prod_sub_cat_id').val();
    var ModelID = $('#prod_model_id').val();
    var firstRowFirstColId = $('#issueTable tbody tr td:first-child select').attr('id');

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
                // $('#product_id_0').html('');
                // $('#'+firstRowFirstColId).append(data);
                // $('#'+firstRowFirstColId).trigger('change');

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

    console.log(Row);

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

</script>
@endsection
