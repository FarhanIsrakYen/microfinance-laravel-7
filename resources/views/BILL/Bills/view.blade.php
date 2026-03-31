
<?php 
    use App\Services\CommonService as Common;
    use App\Services\HtmlService as HTML;
    use App\Services\BillService as BILLS;

?>
<!-- The Modal -->

@extends('Layouts.erp_master')
@section('content')

<div class="row d-print-none">
    <div class="col-lg-9 offset-3 mb-2">
        <!-- Html View Load  -->
        {!! HTML::forCompanyFeild($billData->company_id,'disabled') !!}
    </div>
</div>

<div class="table-responsive d-print-none">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th colspan="4">
                    Bill Information
                </th>
            </tr>
        </thead>
        <tbody>

            <tr>
                <td width="25%">Bill No.</td>
                <td width="25%">{{ $billData->bill_no }}</td>

                <td width="25%">Bill Date</td>
                <td width="25%">{{ $billData->bill_date }}</td>
            </tr>
            <tr>
                <td>Customer</td>
                <td>
                    {{ $billData->customer['customer_no'] ? $billData->customer['customer_name']." (".$billData->customer['customer_no'].")" : '' }}
                </td>

                <td>Bill By</td>
                <td>
                    {{ $billData->employee['employee_no'] ? $billData->employee['emp_name']." (".$billData->employee['employee_no'].")" : '' }}
                </td>
            </tr>
            <tr>
                <td>Discount</td>
                <td>{{ $billData->discount_amount }}</td>

                <td>T/A After Discount</td>
                <td class="text-right">{{ $billData->ta_after_discount }}</td>
            </tr>
            <tr>
                <td>Vat</td>
                <td>{{ $billData->vat_amount }}</td>

                <td>Gross Total</td>
                <td class="text-right">{{ $billData->gross_total }}</td>
            </tr>
            <tr>
                <td>Branch</td>
                <td>
                    @if($billData->branch != null)
                    {{ $billData->branch['branch_name']. " (". $billData->branch['branch_code'].")" }}
                    @endif
                </td>
                <td>Remarks</td>
                <td>{{ $billData->remarks }}</td>
            </tr>

        </tbody>
    </table>
</div>


<!-- Product table -->
<table class="table table-hover table-striped table-bordered w-full text-center d-print-none" id="salesTable">
    <thead>
        <tr>
            <th width="45%" class="text-left">Product Name</th>
            <th width="10%" class="text-right">Quantity</th>
            <th width="15%" class="text-right">Sale Price</th>
            <th width="15%" class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>
        <?php
            $productList = Common::ViewTableOrder('bill_products',
                            ['is_delete' => 0],
                            ['id', 'product_name',  'sale_price', 'prod_vat'],
                            ['product_name', 'ASC']);

            $packageList = Common::ViewTableOrder('bill_packages',
                            ['is_delete' => 0],
                            ['id', 'package_name',  'package_price'],
                            ['package_name', 'ASC']);

        ?>

        @if(count($billDataD) > 0)
        @foreach($billDataD as $bDataD)
        <tr>
            <td class="text-left">
                @if($bDataD->product_type == 1)
                @foreach($productList as $productInfo)
                @if($productInfo->id == $bDataD->product_id)
                    {{ $productInfo->product_name  }}
                @endif
                @endforeach
                @endif
                @if($bDataD->product_type == 2)
                @foreach($packageList as $productInfo)
                @if($productInfo->id == $bDataD->product_id)
                {{ $productInfo->package_name }}
                @endif
                @endforeach
                @endif
            </td>

            <td class="text-right">
                {{ $bDataD->product_quantity }}
            </td>

            <td class="text-right">
                {{ $bDataD->product_unit_price }}
            </td>

            <td class="text-right">
                {{ $bDataD->product_sales_price }}
            </td>
        </tr>
        @endforeach
        @endif
        <tr>
            <td class="text-right">
                <h5>Total Quantity</h5>
            </td>
            <td class="text-right">
                <h5>{{ $billData->total_quantity }}</h5>
            </td>
            <td class="text-right">
                <h5>Total Amount</h5>
            </td>
            <td class="text-right">
                <h5>{{ $billData->total_amount }}</h5>
            </td>
        </tr>
    </tbody>
</table>


<div class="row align-items-center d-print-none">
    <div class="col-lg-12">
        <div class="form-group d-flex justify-content-center">
            <div class="example example-buttons">

                <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>

                <a type="button" class="btn btn-default btn-round clsPrint"
                href="{{ url('pos/sales_cash/invoice/'.$billData->bill_no) }}">Invoice
                </a>
            </div>
        </div>
    </div>
</div>
<style type="text/css">
    #invoiceTable tbody tr td, #invoiceTable th {
        padding: .2rem;
        padding-right: .5rem;
    }
    .prWarranty ul {
        list-style-type: none;    
    }

    .prWarranty ul li:before {
        content:'*'; /* Change this to unicode as needed*/
        width: 1em !important;
        margin-left: -1em;
        display: inline-block;
    }
</style>
<script type="text/javascript">
    function invoiceModal(){
        $('.modal').show();
    }
</script>

@endsection
