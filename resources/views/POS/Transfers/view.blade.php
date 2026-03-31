@extends('Layouts.erp_master_full_width')
@section('content')

<?php 
use App\Services\CommonService as Common;
?>
<?php
$branchInfo = Common::ViewTableOrder('gnl_branchs', [['is_delete', 0], ['is_active', 1]], ['id', 'branch_name'], ['branch_name', 'ASC']);
?>
<div class="panel">
    <div class="panel-body">
        <table class="table table-striped table-bordered">
            <thead>
                <th colspan="4">Transfer</th>
            </thead>
            <tbody  style="color: #000;">
                <tr>
                    <th width="20%">Branch From</th>
                    <td width="20%">
                        @foreach($branchInfo as $Row)
                        @if($TransferData->branch_from==$Row->id)
                        {{$Row->branch_name}}
                        @endif
                        @endforeach
                    </td>

                    <th width="20%">Transfer No</th>
                    <td width="20%">{{$TransferData->bill_no}}
                    </td>
                </tr>
                <tr>
                    <th width="20%">Transfer Date</th>
                    <td width="20%">{{date('d-m-Y', strtotime($TransferData->transfer_date))}}</td>

                    <th width="20%">Transfer To (Branch) </th>
                    <td width="20%">  @foreach($branchInfo as $Row)
                        @if($TransferData->branch_to==$Row->id)
                        {{$Row->branch_name}}
                        @endif
                        @endforeach </td>
                </tr>
                <tr>
                    <th>Order No</th>
                    <td>{{$TransferData->order_no}}</td>
                    <td>&nbsp</td>
                    <td>&nbsp</td>
                </tr>
            </tbody>
        </table>

        <table
            class="table table-hover table-striped table-bordered text-center"
            id="transferTable">
            <thead>
                <tr>
                    <th width="25%">Product Name</th>
                    <th width="25%">Quantity</th>
                    <th width="25%">Cost Price</th>
                    <th width="25%">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $i = 0;
                    $TableID = "transferTable";

                    $ColumnName = "product_id_arr[]&sys_barcode_arr[]&product_name_arr[]&product_quantity_arr[]&unit_cost_price_arr[]&total_cost_price_arr[]";

                    $ColumnID = "product_id_&sys_barcode_&product_name_&product_quantity_&unit_cost_price_&total_cost_price_&deleteRow_";
                    // 'company_id'=> $CompanyID,
                    $ProductList = Common::ViewTableOrder('pos_products',
                                    ['is_delete' => 0],
                                    ['id', 'product_name', 'cost_price', 'sale_price', 'prod_vat', 'sys_barcode', 'prod_barcode'],
                                    ['product_name', 'ASC']);
                ?>


                @if(count($TransferDataD) > 0)
                @foreach($TransferDataD as $TranData)
                <?php $i++; ?>
                <tr>
                    <td>
                        @foreach($ProductList as $ProductInfo)
                        @if($ProductInfo->id == $TranData->product_id)
                        {{ $ProductInfo->product_name . ' (' . $ProductInfo->sys_barcode . ')' }}
                        @endif
                        @endforeach
                    </td>

                    <td>{{ $TranData->product_quantity }}  </td>

                    <td class="text-right">{{ $TranData->unit_cost_price }}
                    </td>

                    <td class="text-right">{{ $TranData->total_cost_price }}
                    </td>
                </tr>
                @endforeach
                @endif


                <tr>
                    <td width="25%" class="text-right">
                        <h5>Total Quantity</h5>
                    </td>
                    <td class="text-center">
                        {{ $TransferData->total_quantity }}</td>

                    <td width="25%" class="text-right">
                        <h5>Total Amount</h5>
                    </td>
                    <td width="25%" class="text-right">
                        {{ $TransferData->total_amount }}</td>
                </tr>
            </tbody>
        </table>

        <div class="row align-items-center">
            <div class="col-lg-12">
                <div class="form-group d-flex justify-content-center">
                    <div class="example example-buttons">
                      <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round d-print-none">Back</a>
                        <a href="javascript:void(0)" onClick="window.print();" class="btn btn-default btn-round clsPrint d-print-none">Print</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--End Panel 2-->
</div>

@endsection
