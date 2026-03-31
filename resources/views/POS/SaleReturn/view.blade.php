<?php
    use App\Services\CommonService as Common;
    use App\Services\HtmlService as HTML;
    use App\Services\PosService as POSS;

    $groupInfo = Common::ViewTableFirst('gnl_groups', [['is_delete', 0], ['is_active', 1]], ['id', 'group_name']);

?>
@extends('Layouts.erp_master')
@section('content')

<div class="row d-print-none">
    <div class="col-lg-9 offset-3 mb-2">
        <!-- Html View Load  -->
        {!! HTML::forCompanyFeild($SaleRM->company_id,'disabled') !!}
    </div>
</div>
<div class="table-responsive d-print-none">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th colspan="4">
                    Sales Return Information
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="25%">Return Bill No</td>
                <td>{{$SaleRM->return_bill_no}}</td>

                <td width="30%">Paid Amount</td>
                <td class="text-right">{{ $SaleRM->sales_paid_amount }}</td>

            </tr>

            <tr>
                <td width="30%">Return Date</td>

                <td>{{(new DateTime($SaleRM->return_date))->format('d-m-Y')}}</td>


                <td>Due Amount</td>
                <td class="text-right">{{ $SaleRM->sales_due_amount }}</td>
            </tr>
            <tr>
                <td>Branch</td>
                <td>
                    @if($SaleRM->branch != null)
                    {{ $SaleRM->branch['branch_name']. " (". $SaleRM->branch['branch_code'].")" }}
                    @endif
                </td>

                <td>Payable Return Amount</td>
                <td class="text-right">{{$SaleRM->payable_return_amount}}
                </td>
            </tr>
            <tr>
                <td width="25%">Sales Bill No</td>
                <td width="30%" colspan="3">{{$SaleRM->sales_bill_no }}</td>
            </tr>

            <tr>
                <td>Sales By</td>
                <td colspan="3">{{ $SaleRM->employee['emp_name']. " (". $SaleRM->employee['emp_code'].")" }} </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Product table -->
<table class="table table-hover table-striped table-bordered w-full text-center d-print-none" id="salesTable">
    <thead>
        <tr>
            <th width="45%" class="text-left">Product Name</th>
            <th width="20%"> Return Quantity</th>
            <th width="15%" class="text-right" colspan="2">Return Amount</th>
        </tr>
    </thead>
    <tbody>
        <?php
            $ProductList = Common::ViewTableOrder('pos_products',
                            ['is_delete' => 0],
                            ['id', 'product_name', 'cost_price', 'sale_price', 'prod_vat', 'sys_barcode', 'prod_barcode'],
                            ['product_name', 'ASC']);
        ?>


        <tr>
            @if(count($SaleRD) > 0)
            @foreach($SaleRD as $SalesRD)
            <td class="text-left">
                @foreach($ProductList as $ProductInfo)
                @if($ProductInfo->id == $SalesRD->product_id)
                {{ $ProductInfo->product_name . ' (' . $ProductInfo->sys_barcode . ')' }}
                @endif
                @endforeach
            </td>
            @endforeach
            @endif
            <td class="text-center">{{$SaleRM->total_return_quantity}}
            </td>

            <td class="text-right" colspan="2">{{$SaleRM->total_return_amount}}
            </td>
        </tr>

        <tr>
            <td colspan="1" class="text-right">
                <h5>Total</h5>
            </td>
            <td class="text-center">
                <h5>{{ $SaleRM->total_return_quantity }}</h5>
            </td>
            <td class="text-right">
                <h5>{{ $SaleRM->total_return_amount }}</h5>
            </td>
        </tr>
    </tbody>
</table>

<div class="row align-items-center d-print-none">
    <div class="col-lg-12">
        <div class="form-group d-flex justify-content-center">
            <div class="example example-buttons">

                <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                </a>
            </div>
        </div>
    </div>
</div>
<style type="text/css">
    #invoiceTable tbody tr td,
    #invoiceTable th {
        padding: .2rem;
        padding-right: .5rem;
    }

    .prWarranty ul {
        list-style-type: none;
    }

    .prWarranty ul li:before {
        content: '*';
        /* Change this to unicode as needed*/
        width: 1em !important;
        margin-left: -1em;
        display: inline-block;
    }
</style>

@endsection
