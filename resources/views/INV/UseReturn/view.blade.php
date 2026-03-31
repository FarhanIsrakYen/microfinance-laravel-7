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
        {!! HTML::forCompanyFeild($useRM->company_id,'disabled') !!}
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
                <td>Return Bill No</td>
                <td>{{$useRM->return_bill_no}}</td>

                <td>Return Date</td>
                <td>{{(new DateTime($useRM->return_date))->format('d-m-Y')}}</td>

            </tr>

            <tr>
                <td>Sales Bill No</td>
                <td>{{$useRM->sales_bill_no }}</td>
                <td>Branch</td>
                <td>
                    @if($useRM->branch != null)
                    {{ $useRM->branch['branch_name']. " (". $useRM->branch['branch_code'].")" }}
                    @endif
                </td>
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
        </tr>
    </thead>
    <tbody>
        <?php
            $ProductList = Common::ViewTableOrder('inv_products',
                            ['is_delete' => 0],
                            ['id', 'product_name', 'product_code'],
                            ['product_name', 'ASC']);
        ?>


        <tr>
            @if(count($useRD) > 0)
            @foreach($useRD as $data)
            <td class="text-left">
                @foreach($ProductList as $ProductInfo)
                @if($ProductInfo->id == $data->product_id)
                {{ $ProductInfo->product_code ? $ProductInfo->product_name . ' (' . $ProductInfo->product_code . ')' : $ProductInfo->product_name}}
                @endif
                @endforeach
            </td>
            @endforeach
            @endif
            <td class="text-center">{{$useRM->total_return_quantity}}
            </td>
        </tr>

        <tr>
            <td colspan="1" class="text-right">
                <h5>Total</h5>
            </td>
            <td class="text-center">
                <h5>{{ $useRM->total_return_quantity }}</h5>
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
