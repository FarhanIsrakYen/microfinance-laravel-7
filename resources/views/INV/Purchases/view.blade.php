@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
?>

<div class="row">
    <div class="col-lg-8 offset-lg-3">
        <!-- Html View Load  -->
        {!! HTML::forCompanyFeild($PurchaseData->company_id,'disabled') !!}
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th colspan="4">
                    Purchase Information
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="25%">Bill No.</td>
                <td width="25%">{{ $PurchaseData->bill_no }}</td>
                <td width="25%">Discount(%)</td>
                <td width="25%">{{ $PurchaseData->discount_rate }}</td>
            </tr>
            <tr>
                <td>Purchase Date</td>
                <td>{{ date('d-m-Y', strtotime($PurchaseData->purchase_date)) }}</td>
                <td>T/A After Discount</td>
                <td class="text-right">{{ $PurchaseData->ta_after_discount }}</td>
            </tr>
            <tr>
                <td>Invoice No.</td>
                <td>{{ $PurchaseData->invoice_no }}</td>
                <td>Vat(%)</td>
                <td>{{ $PurchaseData->vat_rate }}</td>
            </tr>
            <tr>
                <td>Delivery No.</td>
                <td>{{ $PurchaseData->delivery_no }}</td>
                <td>Total Payable Amount</td>
                <td class="text-right">{{ $PurchaseData->total_payable_amount }}</td>
            </tr>
            <tr>
                <td>Order No</td>
                <td>{{ $PurchaseData->order_no }}</td>
                <td>Paid Amount</td>
                <td class="text-right">{{ $PurchaseData->paid_amount }}</td>
            </tr>
            <tr>
                <td>Requisition No</td>
                <td>{{ $PurchaseData->requisition_no }}</td>
                <td>Due Amount</td>
                <td class="text-right">{{ $PurchaseData->due_amount }}</td>
            </tr>
            <tr>
                <td>Contact Person</td>
                <td>{{ $PurchaseData->contact_person }}</td>
                <td>Supplier</td>
                <td>{{ $PurchaseData->supplier['sup_name'] }}</td>
            </tr>

        </tbody>
    </table>
    <table class="table table-striped table-bordered"
        id="purchaseTable">
        <thead>
            <tr>
                <th width="40%">Product Name</th>
                <th width="15%">Quantity</th>
                <th width="20%">Cost Price</th>
                <th width="20%">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $ProductList = Common::ViewTableOrder('inv_products',
                                ['is_delete' => 0],
                                ['id', 'product_name', 'cost_price', 'product_code'],
                                ['product_name', 'ASC']);
            ?>
            @if(count($PurchaseDataD) > 0)
                @foreach($PurchaseDataD as $PDataD)
                <tr>

                    <td>
                        @foreach($ProductList as $ProductInfo)
                            @if($ProductInfo->id == $PDataD->product_id)
                            {{ $ProductInfo->product_code ? $ProductInfo->product_name . ' (' . $ProductInfo->product_code . ')' : $ProductInfo->product_name  }}
                            @endif
                        @endforeach
                    </td>

                    <td class="text-center">
                        {{ $PDataD->product_quantity }}
                    </td>

                    <td class="text-right">
                        {{ $PDataD->unit_cost_price }}
                    </td>

                    <td class="text-right">
                        {{ $PDataD->total_cost_price }}
                    </td>

                </tr>
                @endforeach
            @endif
            <tr>
                <td class="text-right"><h5>Total Quantity</h5></td>
                <td class="text-center"><h5>{{ $PurchaseData->total_quantity }}</h5></td>
                <td class="text-right"><h5>Total Amount</h5></td>
                <td class="text-right"><h5>{{ $PurchaseData->total_amount }}</h5></td>
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
                <a href="javascript:void(0)" onClick="window.print();" class="btn btn-default btn-round clsPrint d-print-none">Print</a>
            </div>
        </div>
    </div>
</div>

@endsection
