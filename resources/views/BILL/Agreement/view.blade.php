@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
?>

<div class="row">
    <div class="col-lg-8 offset-lg-3">
        <!-- Html View Load  -->
        {!! HTML::forCompanyFeild($agreementData->company_id,'disabled') !!}
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
                <td width="25%">Agreement No.</td>
                <td width="25%">{{ $agreementData->agreement_no }}</td>
                <td width="25%">Agreement Date</td>
                <td width="25%">{{ (new Datetime($agreementData->agreement_date))->format('d-m-Y') }}</td>
            </tr>
            <tr>
                <td>Service Start Date</td>
                <td>{{ (new Datetime($agreementData->service_start_date))->format('d-m-Y') }}</td>
                <td>Agreement End Date</td>
                <td>{{ (new Datetime($agreementData->agreement_end_date))->format('d-m-Y') }}</td>
            </tr>
            <tr>
                <td>Sales By</td>
                <td>{{ $agreementData->salesBy['emp_name'] }}</td>
                <td>Customer</td>
                <td>{{ $agreementData->customer['customer_name'] }}</td>
            </tr>
            <tr>
                <td>Service By</td>
                <td>{{ $agreementData->serviceBy['emp_name'] }}</td>
                <td></td>
                <td></td>
            </tr>

        </tbody>
    </table>
    <table class="table table-striped table-bordered"
        id="purchaseTable">
        <thead>
            <tr>
                <th width="40%">Product /Package</th>
                <th width="15%">One Time Fee</th>
                <th width="20%">Monthly Fee</th>
                <!-- <th width="20%">Total</th> -->
            </tr>
        </thead>
        <tbody>
            <?php
                $productList = Common::ViewTableOrder('bill_products',
                                ['is_delete' => 0],
                                ['id', 'product_name', 'sale_price'],
                                ['product_name', 'ASC']);
                $packageList = Common::ViewTableOrder('bill_packages',
                                    ['is_delete' => 0],
                                    ['id', 'package_name',  'package_price'],
                                    ['package_name', 'ASC']);
            ?>
            @if(count($agreementDataD) > 0)
                @foreach($agreementDataD as $agreeD)
                <tr>

                    <td>
                        @if($agreeD->product_type == 1)
                        @foreach($productList as $productInfo)
                            @if($productInfo->id == $agreeD->product_id)
                            {{ $productInfo->product_name  }}
                            @endif
                        @endforeach
                        @endif
                        @if($agreeD->product_type == 2)
                        @foreach($packageList as $productInfo)
                            @if($productInfo->id == $agreeD->product_id)
                            {{ $productInfo->package_name  }}
                            @endif
                        @endforeach
                        @endif
                    </td>

                    <td class="text-center">
                        {{ $agreeD->license_fee }}
                    </td>

                    <td class="text-right">
                        {{ $agreeD->service_fee }}
                    </td>

                </tr>
                @endforeach
            @endif
            <tr>
                <td class="text-right" colspan="2"><h5>Total Amount</h5></td>
                <td class="text-right"><h5>{{ $agreementData->total_amount }}</h5></td>
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
