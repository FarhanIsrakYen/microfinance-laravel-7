@extends('Layouts.erp_master_full_width')
@section('content')

<?php
    use App\Services\CommonService as Common;
    use App\Services\HtmlService as HTML;
    use App\Services\PosService as POSS;

    // dd($billData);
?>

<!-- <div class="w-full"> -->
@foreach($billData as $billData)
<div class="panel">
    <div class="panel-body">

        <div class="customer_copy">
            <div class="row text-center  d-print-block">
                <div class="col-lg-12" style="color:#000; font-weight:700;">
                    Customer Copy
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12" style="font-size: 12px;">
                    <br>
                    <span>
                        <span style="color: black;" class="float-left">
                            <strong>Date: </strong>
                            <strong>{{ (new DateTime($billData->bill_date))->format('d/m/Y') }}</strong>
                        </span>
                        <span style="color: black;" class="float-right">
                            @if(!empty($groupInfo->group_logo))
                            <img src="{{asset('assets/images/logo.png')}}">
                            @else
                            <img src="{{asset('assets/images/logo-blue.png')}}">
                            @endif
                        </span>
                    </span>
                    <br>

                    <span>
                        <span style="color: black;" class="float-left">
                            <span>Bill No: </span>
                            <span>{{ $billData->bill_no }}</span>
                        </span>
                    </span>
                    <br>

                    <span>
                        <span style="color: black;" class="float-left">
                            <span>Customer: </span>
                            <span>{{ $billData->customer['customer_name']."(".$billData->customer['customer_no'].")" }}</span>
                        </span>
                        <span style="color: black;" class="float-right">
                            <span>Branch: </span>
                            <span>
                                @if($billData->branch != null)
                                {{ $billData->branch['branch_name']."(".$billData->branch['branch_code'].")" }}
                                @endif
                            </span>
                        </span>
                    </span>
                    <br>
                    <!-- <span>
                        <span style="color: black;" class="float-right">
                            <span>Vat CH No: </span>
                            <span>{{ $billData->vat_chalan_no }}</span>
                        </span>
                    </span> -->

                </div>
            </div>
            <!-- <br> -->
            <table class="table table-hover table-striped table-bordered w-full" id="invoiceTable">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">SL</th>
                        <th width="33%" class="text-center">Product Name</th>
                        <!-- <th width="15%">Serial No</th>
                        <th width="15%" class="text-center">Brand Name</th> -->
                        <!-- <th width="8%" class="text-center">Installment</th> -->
                        <th width="8%" class="text-center">Quantity</th>
                        <th width="9%" class="text-center">Unit Price</th>
                        <th width="15%" class="text-center">Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1 ;?>

                    @if(count($billDataD) > 0)
                    @foreach($billDataD as $bDataD)
                    @if($bDataD->product_type == 1)
                    @foreach($productData as $productInfo)
                    @if($productInfo->id == $bDataD->product_id)
                    <tr>
                        <td class="text-center">{{ $i++ }}</td>
                        <td class="text-left">
                            
                            {{ $productInfo->product_code ? $productInfo->product_name." (". $productInfo->product_code.")" : $productInfo->product_name }}
                            
                        </td>

                        <td class="text-center">
                            {{ $bDataD->product_quantity }}
                        </td>

                        <td class="text-right">
                            {{ $bDataD->product_unit_price }}
                        </td>

                        <td class="text-right">
                            {{ $bDataD->product_sales_price }}
                        </td>
                    </tr>
                    @endif
                    @endforeach
                    @else
                    @foreach($packageData as $productInfo)
                    @if($productInfo->id == $bDataD->product_id)
                    <tr>
                        <td class="text-center">{{ $i++ }}</td>
                        <td class="text-left">
                            {{ $productInfo->package_name }}
                        </td>

                        <td class="text-center">
                            {{ $bDataD->product_quantity }}
                        </td>

                        <td class="text-right">
                            {{ $bDataD->product_unit_price }}
                        </td>

                        <td class="text-right">
                            {{ $bDataD->product_sales_price }}
                        </td>
                    </tr>
                    @endif
                    @endforeach
                    @endif
                    @endforeach
                    @endif
                    <tr>
                        <td colspan="2" rowspan="5">
                            <span>Remarks:</span> 
                            <span>{{ $billData->remarks }}</span>
                        </td>
                        <td colspan="2" class="text-left">
                            Total Amount
                        </td>
                        <td class="text-right">
                            {{ $billData->total_amount }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-left">
                            Discount ({{ $billData->discount_rate }} %)
                        </td>
                        <td class="text-right">
                            {{ $billData->discount_amount }}
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2" class="text-left">
                            VAT ({{ $billData->vat_rate }} %)
                        </td>
                        <td class="text-right">
                            {{ $billData->vat_amount }}
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2" class="text-left">
                             Service Charge
                        </td>
                        <td class="text-right">
                            {{ $billData->service_charge }}
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2" class="text-left">
                            Gross Total
                        </td>
                        <td class="text-right">
                            @if($billData->agreement_no != '')
                            {{ $billData->service_charge }}
                            @else
                            {{ $billData->gross_total }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td colspan="8">
                            <b>In Words:</b>
                            <span>
                                @if($billData->agreement_no != '')
                                {{ Common::numberToWord($billData->service_charge) }}
                                @else
                                {{ Common::numberToWord($billData->total_amount) }}
                                @endif
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- <br> -->
        <hr>

        <div class="office_copy">
            <div class="row text-center  d-print-block">
                <div class="col-lg-12" style="color:#000; font-weight:700;">
                    Office Copy
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12" style="font-size: 12px;">
                    <br>
                    <span>
                        <span style="color: black;" class="float-left">
                            <strong>Date: </strong>
                            <strong>{{ (new DateTime($billData->bill_date))->format('d/m/Y') }}</strong>
                        </span>
                        <span style="color: black;" class="float-right">
                            @if(!empty($groupInfo->group_logo))
                            <img src="{{asset('assets/images/logo.png')}}">
                            @else
                            <img src="{{asset('assets/images/logo-blue.png')}}">
                            @endif
                        </span>
                    </span>
                    <br>

                    <span>
                        <span style="color: black;" class="float-left">
                            <span>Bill No: </span>
                            <span>{{ $billData->bill_no }}</span>
                        </span>
                    </span>
                    <br>

                    <span>
                        <span style="color: black;" class="float-left">
                            <span>Customer: </span>
                            <span>{{ $billData->customer['customer_name']."(".$billData->customer['customer_no'].")" }}</span>
                        </span>
                        <span style="color: black;" class="float-right">
                            <span>Branch: </span>
                            <span>
                                @if($billData->branch != null)
                                {{ $billData->branch['branch_name']."(".$billData->branch['branch_code'].")" }}
                                @endif
                            </span>
                        </span>
                    </span>
                    <br>

                </div>
            </div>
            <!-- <br> -->
            <table class="table table-hover table-striped table-bordered w-full" id="invoiceTable">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">SL</th>
                        <th width="33%" class="text-center">Product Name</th>
                        <th width="8%" class="text-center">Quantity</th>
                        <th width="9%" class="text-center">Unit Price</th>
                        <th width="15%" class="text-center">Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1 ;?>

                    @if(count($billDataD) > 0)
                    @foreach($billDataD as $bDataD)
                    @if($bDataD->product_type == 1)
                    @foreach($productData as $productInfo)
                    @if($productInfo->id == $bDataD->product_id)
                    <tr>
                        <td class="text-center">{{ $i++ }}</td>
                        <td class="text-left">
                            
                            {{ $productInfo->product_code ? $productInfo->product_name." (". $productInfo->product_code.")" : $productInfo->product_name }}
                            
                        </td>

                        <td class="text-center">
                            {{ $bDataD->product_quantity }}
                        </td>

                        <td class="text-right">
                            {{ $bDataD->product_unit_price }}
                        </td>

                        <td class="text-right">
                            {{ $bDataD->product_sales_price }}
                        </td>
                    </tr>
                    @endif
                    @endforeach
                    @else
                    @foreach($packageData as $productInfo)
                    @if($productInfo->id == $bDataD->product_id)
                    <tr>
                        <td class="text-center">{{ $i++ }}</td>
                        <td class="text-left">
                            {{ $productInfo->package_name }}
                        </td>

                        <td class="text-center">
                            {{ $bDataD->product_quantity }}
                        </td>

                        <td class="text-right">
                            {{ $bDataD->product_unit_price }}
                        </td>

                        <td class="text-right">
                            {{ $bDataD->product_sales_price }}
                        </td>
                    </tr>
                    @endif
                    @endforeach
                    @endif
                    @endforeach
                    @endif
                    <tr>
                        <td colspan="2" rowspan="5">
                            <span>Remarks:</span> 
                            <span>{{ $billData->remarks }}</span>
                        </td>
                        <td colspan="2" class="text-left">
                            Total Amount
                        </td>
                        <td class="text-right">
                            {{ $billData->total_amount }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-left">
                            Discount ({{ $billData->discount_rate }} %)
                        </td>
                        <td class="text-right">
                            {{ $billData->discount_amount }}
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2" class="text-left">
                            VAT ({{ $billData->vat_rate }} %)
                        </td>
                        <td class="text-right">
                            {{ $billData->vat_amount }}
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2" class="text-left">
                             Service Charge
                        </td>
                        <td class="text-right">
                            {{ $billData->service_charge }}
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2" class="text-left">
                            Gross Total
                        </td>
                        <td class="text-right">
                            @if($billData->agreement_no != '')
                            {{ $billData->service_charge }}
                            @else
                            {{ $billData->gross_total }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td colspan="8">
                            <b>In Words:</b>
                            <span>
                                @if($billData->agreement_no != '')
                                {{ Common::numberToWord($billData->service_charge) }}
                                @else
                                {{ Common::numberToWord($billData->total_amount) }}
                                @endif
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="row align-items-center d-print-none">
            <div class="col-lg-12">
                <div class="form-group d-flex justify-content-center">
                    <div class="example example-buttons">

                        <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>

                        <a href="javascript:void(0)" onclick="window.print();"
                            class="btn btn-default btn-round clsPrint">Print</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endforeach
<!-- </div> -->

<style>
    hr {
        border-top: 1px dotted #000;
    }
@media print {
    @page {
        /* size: landscape; */
        margin: 5px !important;
    }

    /* .table > thead th {
            border: 0px solid #000!important;
        }

        .table > tbody td {
            border: 0px solid #000!important;
        }

        .table > tfoot td {
            border: 0px solid #000!important;
        } */

}
</style>

@endsection
