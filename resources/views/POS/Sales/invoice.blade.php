@extends('Layouts.erp_master_full_width')
@section('content')

<?php
    use App\Services\CommonService as Common;
    use App\Services\HtmlService as HTML;
    use App\Services\PosService as POSS;
?>

<!-- <div class="w-full"> -->
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
                            <strong>{{ (new DateTime($SalesData->sales_date))->format('d/m/Y') }}</strong>
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
                            <span>{{ $SalesData->sales_bill_no }}</span>
                        </span>
                    </span>
                    <br>

                    <span>
                        <span style="color: black;" class="float-left">
                            <span>Customer: </span>
                            <span>{{ $SalesData->customer['customer_name']."(".$SalesData->customer['customer_no'].")" }}</span>
                        </span>
                        <span style="color: black;" class="float-right">
                            <span>Branch: </span>
                            <span>
                                @if($SalesData->branch != null)
                                {{ $SalesData->branch['branch_name']."(".$SalesData->branch['branch_code'].")" }}
                                @endif
                            </span>
                        </span>
                    </span>
                    <br>
                    <span>
                        <span style="color: black;" class="float-left">
                            <span>Mobile: </span>
                            <span>{{ $SalesData->customer['customer_mobile'] }}</span>
                        </span>
                        <span style="color: black;" class="float-right">
                            <span>Address: </span>
                            <span>{{ $SalesData->branch['branch_addr'] }}</span>
                        </span>
                    </span>
                    <br>

                    <!-- <span>
                        <span style="color: black;" class="float-left">
                            <span>Address:</span>
                            <span></span>
                        </span>
                        <span style="color: black;" class="float-right">
                            <span>C.O No: </span>
                            <span></span>
                        </span>
                    </span>
                    <br> -->
                    <span>

                        <span style="color: black;" class="float-right">
                            <span>Vat CH No: </span>
                            <span>{{ $SalesData->vat_chalan_no }}</span>
                        </span>
                    </span>

                </div>
            </div>
            <!-- <br> -->
            <table class="table table-hover table-striped table-bordered w-full" id="invoiceTable">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">SL</th>
                        <th width="33%" class="text-center">Product Name</th>
                        <th width="15%">Serial No</th>
                        <th width="15%" class="text-center">Brand Name</th>
                        <!-- <th width="8%" class="text-center">Installment</th> -->
                        <th width="8%" class="text-center">Quantity</th>
                        <th width="9%" class="text-center">Unit Price</th>
                        <th width="15%" class="text-center">Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1 ;?>

                    @if(count($SalesDataD) > 0)
                    @foreach($SalesDataD as $SDataD)
                    <tr>
                        <td class="text-center">{{ $i++ }}</td>
                        <td class="text-left">
                            @foreach($ProductData as $ProductInfo)
                            @if($ProductInfo->id == $SDataD->product_id)
                            {{ $ProductInfo->product_name." (". $ProductInfo->prod_barcode.")" }}
                            @endif
                            @endforeach
                        </td>

                        <td>
                            {{ $SDataD->product_serial_no }}
                        </td>
                        <td>
                            @foreach($ProductData as $ProductInfo)
                            @if($ProductInfo->id == $SDataD->product_id)
                            {{  $ProductInfo->brand->brand_name  }}
                            @endif
                            @endforeach
                        </td>
                        <!-- <td class="text-right">
                            @if($SalesData->sales_type == 2)
                            {{ $SalesData->installment_amount }}
                            @endif
                        </td> -->

                        <td class="text-center">
                            {{ $SDataD->product_quantity }}
                        </td>

                        <td class="text-right">
                            {{ $SDataD->product_unit_price }}
                        </td>

                        <td class="text-right">
                            {{ $SDataD->total_sales_price }}
                        </td>
                    </tr>
                    @endforeach
                    @endif
                    <tr>
                        <td colspan="4" @if($SalesData->sales_type == 2) rowspan="7" @else rowspan="6" @endif >
                            <!-- <ul class="text-left prWarranty">
                                <li>This Product Warranty is : </li>
                                <li>This Product Service Warranty is : </li>
                                <li>Servicing Fee : </li>
                                <li>We Provide : </li>
                            </ul> -->
                        </td>
                        <td colspan="2" class="text-left">
                            Total Amount
                        </td>
                        <td class="text-right">
                            {{ $SalesData->total_amount }}
                        </td>
                    </tr>

                    @if($SalesData->sales_type == 1)
                    <tr>
                        <td colspan="2" class="text-left">
                            Discount ({{ $SalesData->discount_rate }} %)
                        </td>
                        <td class="text-right">
                            {{ $SalesData->discount_amount }}
                        </td>
                    </tr>
                    @endif

                    <tr>
                        <td colspan="2" class="text-left">
                            VAT ({{ $SalesData->vat_rate }} %)
                        </td>
                        <td class="text-right">
                            {{ $SalesData->vat_amount }}
                        </td>
                    </tr>

                    @if($SalesData->sales_type == 2)
                    <tr>
                        <td colspan="2" class="text-left">
                            Processing Fee
                        </td>
                        <td class="text-right">
                            {{ $SalesData->service_charge }}
                        </td>
                    </tr>
                    @endif

                    <tr>
                        <td colspan="2" class="text-left">
                            Grand Total
                        </td>
                        <td class="text-right">
                            {{ $SalesData->total_payable_amount }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-left">
                            Pay Amount
                        </td>
                        <td class="text-right">
                            {{ $SalesData->paid_amount }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-left">
                            Due Amount
                        </td>
                        <td class="text-right">
                            {{ $SalesData->due_amount }}
                        </td>
                    </tr>
                    @if($SalesData->sales_type == 2)
                    <tr>
                        <td colspan="2" class="text-left">
                            Installment Amount
                        </td>
                        <td class="text-right">
                            {{ $SalesData->installment_amount }}
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="8">
                            <b>In Words:</b> 
                            <span>{{ Common::numberToWord($SalesData->total_amount) }}</span>
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
                            <strong>{{ (new DateTime($SalesData->sales_date))->format('d/m/Y') }}</strong>
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
                            <span>{{ $SalesData->sales_bill_no }}</span>
                        </span>
                    </span>
                    <br>

                    <span>
                        <span style="color: black;" class="float-left">
                            <span>Customer: </span>
                            <span>{{ $SalesData->customer['customer_name']."(".$SalesData->customer['customer_no'].")" }}</span>
                        </span>
                        <span style="color: black;" class="float-right">
                            <span>Branch: </span>
                            <span>
                                @if($SalesData->branch != null)
                                {{ $SalesData->branch['branch_name']."(".$SalesData->branch['branch_code'].")" }}
                                @endif
                            </span>
                        </span>
                    </span>
                    <br>
                    <span>
                        <span style="color: black;" class="float-left">
                            <span>Mobile: </span>
                            <span>{{ $SalesData->customer['customer_mobile'] }}</span>
                        </span>
                        <span style="color: black;" class="float-right">
                            <span>Address: </span>
                            <span>{{ $SalesData->branch['branch_addr'] }}</span>
                        </span>
                    </span>
                    <br>

                    <!-- <span>
                        <span style="color: black;" class="float-left">
                            <span>Address:</span>
                            <span></span>
                        </span>
                        <span style="color: black;" class="float-right">
                            <span>C.O No: </span>
                            <span></span>
                        </span>
                    </span>
                    <br> -->
                    <span>

                        <span style="color: black;" class="float-right">
                            <span>Vat CH No: </span>
                            <span>{{ $SalesData->vat_chalan_no }}</span>
                        </span>
                    </span>

                </div>
            </div>
            <!-- <br> -->
            <table class="table table-hover table-striped table-bordered w-full" id="invoiceTable">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">SL</th>
                        <th width="33%" class="text-center">Product Name</th>
                        <th width="15%">Serial No</th>
                        <th width="15%" class="text-center">Brand Name</th>
                        <!-- <th width="8%" class="text-center">Installment</th> -->
                        <th width="8%" class="text-center">Quantity</th>
                        <th width="9%" class="text-center">Unit Price</th>
                        <th width="15%" class="text-center">Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1 ;?>

                    @if(count($SalesDataD) > 0)
                    @foreach($SalesDataD as $SDataD)
                    <tr>
                        <td class="text-center">{{ $i++ }}</td>
                        <td class="text-left">
                            @foreach($ProductData as $ProductInfo)
                            @if($ProductInfo->id == $SDataD->product_id)
                            {{ $ProductInfo->product_name." (". $ProductInfo->prod_barcode.")" }}
                            @endif
                            @endforeach
                        </td>

                        <td>
                            {{ $SDataD->product_serial_no }}
                        </td>
                        <td>
                            @foreach($ProductData as $ProductInfo)
                            @if($ProductInfo->id == $SDataD->product_id)
                            {{  $ProductInfo->brand->brand_name  }}
                            @endif
                            @endforeach
                        </td>
                        <!-- <td class="text-right">
                            @if($SalesData->sales_type == 2)
                            {{ $SalesData->installment_amount }}
                            @endif
                        </td> -->

                        <td class="text-center">
                            {{ $SDataD->product_quantity }}
                        </td>

                        <td class="text-right">
                            {{ $SDataD->product_unit_price }}
                        </td>

                        <td class="text-right">
                            {{ $SDataD->total_sales_price }}
                        </td>
                    </tr>
                    @endforeach
                    @endif
                    <tr>
                        <td colspan="4" @if($SalesData->sales_type == 2) rowspan="7" @else rowspan="6" @endif>
                            <!-- <ul class="text-left prWarranty">
                                <li>This Product Warranty is : </li>
                                <li>This Product Service Warranty is : </li>
                                <li>Servicing Fee : </li>
                                <li>We Provide : </li>
                            </ul> -->
                        </td>
                        <td colspan="2" class="text-left">
                            Total Amount
                        </td>
                        <td class="text-right">
                            {{ $SalesData->total_amount }}
                        </td>
                    </tr>
                    @if($SalesData->sales_type == 1)
                    <tr>
                        <td colspan="2" class="text-left">
                            Discount ({{ $SalesData->discount_rate }} %)
                        </td>
                        <td class="text-right">
                            {{ $SalesData->discount_amount }}
                        </td>
                    </tr>
                    @endif

                    <tr>
                        <td colspan="2" class="text-left">
                            Vat ({{ $SalesData->vat_rate }} %)
                        </td>
                        <td class="text-right">
                            {{ $SalesData->vat_amount }}
                        </td>
                    </tr>

                    @if($SalesData->sales_type == 2)
                    <tr>
                        <td colspan="2" class="text-left">
                            Processing Fee
                        </td>
                        <td class="text-right">
                            {{ $SalesData->service_charge }}
                        </td>
                    </tr>
                    @endif

                    <tr>
                        <td colspan="2" class="text-left">
                            Grand Total
                        </td>
                        <td class="text-right">
                            {{ $SalesData->total_payable_amount }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-left">
                            Pay Amount
                        </td>
                        <td class="text-right">
                            {{ $SalesData->paid_amount }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-left">
                            Due Amount
                        </td>
                        <td class="text-right">
                            {{ $SalesData->due_amount }}
                        </td>
                    </tr>

                    @if($SalesData->sales_type == 2)
                    <tr>
                        <td colspan="2" class="text-left">
                            Installment Amount
                        </td>
                        <td class="text-right">
                            {{ $SalesData->installment_amount }}
                        </td>
                    </tr>
                    @endif
                    
                    <tr>
                        <td colspan="8">
                            <b>In Words:</b>
                            <span>{{ Common::numberToWord($SalesData->total_amount) }}</span>
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
