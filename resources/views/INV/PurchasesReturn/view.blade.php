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
                <!-- <th width="25%">Cost Price</th>
                <th width="25%">Total</th> -->

            </tr>
        </thead>
        <tbody>
         <?php
         $i = 0;
         $TableID = "purchaseTable";

         $ColumnName = "product_id_arr[]&product_code_arr[]&product_bar_arr[]&product_quantity_arr[]&ordered_quantity_arr[]&received_quantity_arr[]&unit_cost_price_arr[]&total_cost_price_arr[]";

         $ColumnID = "product_id_&product_code_&product_bar_&product_quantity_&ordered_quantity_&received_quantity_&unit_cost_price_&total_cost_price_&deleteRow_";
         $ProductList = Common::ViewTableOrder('inv_products',
                         ['is_delete' => 0],
                         ['id', 'product_name', 'cost_price', 'product_code'],
                         ['product_name', 'ASC']);
         ?>



         @if(count($PReturnDataD) > 0)
         @foreach($PReturnDataD as $Data)
         <?php $i++; ?>
         <tr>
             <td width="25%">

                 @foreach($ProductList as $ProductInfo)
                 @if($ProductInfo->id == $Data->product_id)
                 {{ $ProductInfo->product_code ? $ProductInfo->product_name . ' (' . $ProductInfo->product_code . ')' : $ProductInfo->product_name }}
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
             <!-- <td width="25%" class="text-right">{{ $Data->unit_cost_price }}</td>
             <td class="text-right">{{ $Data->total_cost_price }}</td> -->
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

                <!-- <td width="25%" class="text-right">
                    <h5>Total Amount</h5>
                </td>
                <td width="25%" class="text-right" > <h5> {{$PReturnData->total_amount}}</h5></td> -->
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



@endsection
