<?php 
use App\Services\CommonService as Common;

?>
<div class="modal" id="myModal">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document" style="max-width:80%;">
        <div class="modal-content">

           <!-- Modal body -->
            <div class="modal-body">
                <div class="customer_copy">
                    <div class="row text-center  d-print-block">
                        <div class="col-lg-12" style="color:#000;">
                            Customer Copy
                        </div>
                    </div>

                    <div class="row">       
                        <div class="col-lg-12" style="font-size: 12px;">
                            <br>
                            <span>
                                <span style="color: black;" class="float-left">
                                    <span>Date: </span>
                                    <span>{{ (new DateTime())->format('d M,Y') }}</span>
                                </span>
                                <span style="color: black;"class="float-right">
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
                                    <span>Customer Name: </span>
                                    <span>{{ $SalesData->customer['customer_name'] }}</span>
                                </span>
                                <span style="color: black;" class="float-right">
                                    <span>Branch Name: </span>
                                    <span>
                                        @if($SalesData->branch != null)
                                            {{ $SalesData->branch['branch_name'] }}
                                        @endif
                                    </span>
                                </span>
                            </span>
                            <br>
                            <span>
                                <span style="color: black;" class="float-left">
                                    <span>Customer Id: </span>
                                    <span>{{ $SalesData->customer['customer_nid'] }}</span>
                                </span>
                                <span style="color: black;" class="float-right">
                                    <span>Address: </span>
                                    <span>{{ $SalesData->branch['branch_addr'] }}</span>
                                </span>
                            </span>
                            <br>

                            <span>
                                <span style="color: black;" class="float-left">
                                    <span>Mobile: </span>
                                    <span>{{ $SalesData->customer['customer_mobile'] }}</span>
                                </span>
                                <span style="color: black;" class="float-right">
                                    <span>C.O No: </span>
                                    <span></span>
                                </span>
                            </span>
                            <br>
                            <span>
                                <span style="color: black;" class="float-left">
                                    <span>Address:</span>
                                    <span></span>
                                </span>
                                <span style="color: black;" class="float-right">
                                    <span>Vat CH No: </span>
                                    <span>{{ $SalesData->vat_chalan_no }}</span>
                                </span>
                            </span>

                        </div>
                    </div>
                    <table class="table table-hover table-striped table-bordered w-full" id="invoiceTable">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">SL.</th>
                                <th width="10%" class="text-center">Product Name</th>
                                <th width="10%" class="text-center">Barcode</th>
                                <th width="10%">Serial No</th>
                                <th width="8%" class="text-center">Brand Name</th>
                                <th width="5%" class="text-center">Installment</th>
                                <th width="5%" class="text-center">Quantity</th>
                                <th width="8%" class="text-center">Price</th>
                                <th width="26%" class="text-center">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $i = 1 ;
                                $ProductList = Common::ViewTableOrder('pos_products',
                                                ['is_delete' => 0],
                                                ['id', 'product_name', 'cost_price', 'sale_price', 'prod_vat', 'sys_barcode', 'prod_barcode'],
                                                ['product_name', 'ASC']);
                                $ProductBrand = Common::ViewTableOrder('pos_p_brands',
                                                ['is_delete' => 0],
                                                ['id', 'brand_name'],
                                                ['brand_name', 'ASC']);
                            ?>

                            @if(count($SalesDataD) > 0)
                            @foreach($SalesDataD as $SDataD)
                            <tr>
                                <td class="text-center">{{ $i++ }}</td>
                                <td class="text-left">
                                    @foreach($ProductList as $ProductInfo)
                                    @if($ProductInfo->id == $SDataD->product_id)
                                    {{ $ProductInfo->product_name  }}
                                    @endif
                                    @endforeach
                                </td>

                                <td class="text-left">
                                    @foreach($ProductList as $ProductInfo)
                                    @if($ProductInfo->id == $SDataD->product_id)
                                    {{  $ProductInfo->sys_barcode  }}
                                    @endif
                                    @endforeach
                                </td>

                                <td>
                                    {{ $SDataD->product_serial_no }}
                                </td>
                                <td>
                                    @foreach($ProductBrand as $ProductInfo)
                                    @if($ProductInfo->id == $SDataD->product_id)
                                    {{  $ProductInfo->brand_name  }}
                                    @endif
                                    @endforeach
                                </td>
                                <td class="text-right"> 
                                    @if($SalesData->sales_type == 2)
                                    {{ $SalesData->installment_amount }}
                                    @endif
                                </td>

                                <td class="text-right">
                                    {{ $SDataD->product_quantity }}
                                </td>

                                <td class="text-right">
                                    {{ $SDataD->product_unit_price }}
                                </td>

                                <td class="text-right">
                                    {{ $SDataD->total_amount }}
                                </td>
                            </tr>
                            @endforeach
                            @endif
                            <tr>
                                <td colspan="4" rowspan="7">
                                    <ul class="text-left prWarranty">
                                        <li>This Product Warranty is : </li>
                                        <li>This Product Service Warranty is : </li>
                                        <li>Servicing Fee : </li>
                                        <li>We Provide : </li>
                                    </ul>
                                </td>
                                <td colspan="4" class="text-left">
                                    Total Amount
                                </td>
                                <td class="text-right">
                                    {{ $SalesData->total_amount }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-left">
                                    Discount Amount
                                </td>
                                <td class="text-right">
                                    {{ $SalesData->total_amount }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-left">
                                    Vat Amount
                                </td>
                                <td class="text-right">
                                    {{ $SalesData->total_amount }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-left">
                                    Processing Fee
                                </td>
                                <td class="text-right">
                                    @if($SalesData->sales_type == 2)
                                    {{ $SalesData->service_charge }}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-left">
                                    Grand Total
                                </td>
                                <td class="text-right">
                                    {{ $SalesData->total_amount }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-left">
                                    Pay Amount
                                </td>
                                <td class="text-right">
                                    {{ $SalesData->paid_amount }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-left">
                                    Due
                                </td>
                                <td class="text-right">
                                    {{ $SalesData->due_amount }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="9" class="text-center">
                                    In Words:
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="office_copy mt-4">
                    <div class="row text-center  d-print-block">
                        <div class="col-lg-12" style="color:#000;">
                            Office Copy
                        </div>
                    </div>

                    <div class="row">       

                        <div class="col-lg-12" style="font-size: 12px;">
                            <br>
                            <span>
                                <span style="color: black;" class="float-left">
                                    <span>Date: </span>
                                    <span>{{ (new DateTime())->format('d M,Y') }}</span>
                                </span>
                                <span style="color: black;"class="float-right">
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
                                    <span>Customer Name: </span>
                                    <span>{{ $SalesData->customer['customer_name'] }}</span>
                                </span>
                                <span style="color: black;" class="float-right">
                                    <span>Branch Name: </span>
                                    <span>
                                        @if($SalesData->branch != null)
                                            {{ $SalesData->branch['branch_name'] }}
                                        @endif
                                    </span>
                                </span>
                            </span>
                            <br>
                            <span>
                                <span style="color: black;" class="float-left">
                                    <span>Customer Id: </span>
                                    <span>{{ $SalesData->customer['customer_nid'] }}</span>
                                </span>
                                <span style="color: black;" class="float-right">
                                    <span>Address: </span>
                                    <span>{{ $SalesData->branch['branch_addr'] }}</span>
                                </span>
                            </span>
                            <br>

                            <span>
                                <span style="color: black;" class="float-left">
                                    <span>Mobile: </span>
                                    <span>{{ $SalesData->customer['customer_mobile'] }}</span>
                                </span>
                                <span style="color: black;" class="float-right">
                                    <span>C.O No: </span>
                                    <span></span>
                                </span>
                            </span>
                            <br>
                            <span>
                                <span style="color: black;" class="float-left">
                                    <span>Address:</span>
                                    <span></span>
                                </span>
                                <span style="color: black;" class="float-right">
                                    <span>Vat CH No: </span>
                                    <span>{{ $SalesData->vat_chalan_no }}</span>
                                </span>
                            </span>

                        </div>
                    </div>
                    <table class="table table-hover table-striped table-bordered w-full" id="invoiceTable">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">SL.</th>
                                <th width="10%" class="text-center">Product Name</th>
                                <th width="10%" class="text-center">Barcode</th>
                                <th width="10%">Serial No</th>
                                <th width="8%" class="text-center">Brand Name</th>
                                <th width="5%" class="text-center">Installment</th>
                                <th width="5%" class="text-center">Quantity</th>
                                <th width="8%" class="text-center">Price</th>
                                <th width="26%" class="text-center">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $i = 1 ;
                                $ProductList = Common::ViewTableOrder('pos_products',
                                                ['is_delete' => 0],
                                                ['id', 'product_name', 'cost_price', 'sale_price', 'prod_vat', 'sys_barcode', 'prod_barcode'],
                                                ['product_name', 'ASC']);
                                $ProductBrand = Common::ViewTableOrder('pos_p_brands',
                                                ['is_delete' => 0],
                                                ['id', 'brand_name'],
                                                ['brand_name', 'ASC']);
                            ?>

                            @if(count($SalesDataD) > 0)
                            @foreach($SalesDataD as $SDataD)
                            <tr>
                                <td class="text-center">{{ $i++ }}</td>
                                <td class="text-left">
                                    @foreach($ProductList as $ProductInfo)
                                    @if($ProductInfo->id == $SDataD->product_id)
                                    {{ $ProductInfo->product_name  }}
                                    @endif
                                    @endforeach
                                </td>

                                <td class="text-left">
                                    @foreach($ProductList as $ProductInfo)
                                    @if($ProductInfo->id == $SDataD->product_id)
                                    {{  $ProductInfo->sys_barcode  }}
                                    @endif
                                    @endforeach
                                </td>

                                <td>
                                    {{ $SDataD->product_serial_no }}
                                </td>
                                <td>
                                    @foreach($ProductBrand as $ProductInfo)
                                    @if($ProductInfo->id == $SDataD->product_id)
                                    {{  $ProductInfo->brand_name  }}
                                    @endif
                                    @endforeach
                                </td>
                                <td class="text-right"> 
                                    @if($SalesData->sales_type == 2)
                                    {{ $SalesData->installment_amount }}
                                    @endif
                                </td>

                                <td class="text-right">
                                    {{ $SDataD->product_quantity }}
                                </td>

                                <td class="text-right">
                                    {{ $SDataD->product_unit_price }}
                                </td>

                                <td class="text-right">
                                    {{ $SDataD->total_amount }}
                                </td>
                            </tr>
                            @endforeach
                            @endif
                            <tr>
                                <td colspan="4" rowspan="7">
                                    <ul class="text-left prWarranty">
                                        <li>This Product Warranty is : </li>
                                        <li>This Product Service Warranty is : </li>
                                        <li>Servicing Fee : </li>
                                        <li>We Provide : </li>
                                    </ul>
                                </td>
                                <td colspan="4" class="text-left">
                                    Total Amount
                                </td>
                                <td class="text-right">
                                    {{ $SalesData->total_amount }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-left">
                                    Discount Amount
                                </td>
                                <td class="text-right">
                                    {{ $SalesData->total_amount }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-left">
                                    Vat Amount
                                </td>
                                <td class="text-right">
                                    {{ $SalesData->total_amount }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-left">
                                    Processing Fee
                                </td>
                                <td class="text-right">
                                    @if($SalesData->sales_type == 2)
                                    {{ $SalesData->service_charge }}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-left">
                                    Grand Total
                                </td>
                                <td class="text-right">
                                    {{ $SalesData->total_amount }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-left">
                                    Pay Amount
                                </td>
                                <td class="text-right">
                                    {{ $SalesData->paid_amount }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-left">
                                    Due
                                </td>
                                <td class="text-right">
                                    {{ $SalesData->due_amount }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="9" class="text-center">
                                    In Words:
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

          <!-- Modal footer -->
          <div class="modal-footer">
            <button type="button" class="btn btn-danger d-print-none" data-dismiss="modal" onclick="window.print()">Print</button>
          </div>

        </div>
    </div>
</div>