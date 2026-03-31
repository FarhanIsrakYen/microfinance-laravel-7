
<?php 
    use App\Services\CommonService as Common;
    use App\Services\HtmlService as HTML;
    use App\Services\PosService as POSS;

    $groupInfo = Common::ViewTableFirst('gnl_groups', [['is_delete', 0], ['is_active', 1]], ['id', 'group_name']);
?>
<!-- The Modal -->
@include('elements.pop.invoice_modal')

@extends('Layouts.erp_master')
@section('content')

<div class="row d-print-none">
    <div class="col-lg-9 offset-3 mb-2">
        <!-- Html View Load  -->
        {!! HTML::forCompanyFeild($SalesData->company_id,'disabled') !!}
    </div>
</div>

<div class="table-responsive d-print-none">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th colspan="4">
                    @if($SalesData->sales_type == 1) Cash Sale Information
                    @else
                    Installment Sale Information
                    @endif
                </th>
            </tr>
        </thead>
        <tbody>

            <tr>
                <td width="25%">Bill No.</td>
                <td width="25%">{{ $SalesData->sales_bill_no }}</td>

                @if($SalesData->sales_type == 2)
                <td width="30%">VAT( {{ $SalesData->vat_rate }} %)</td>
                <td class="text-right">{{ $SalesData->vat_amount }}</td>
                @endif

                @if($SalesData->sales_type == 1)
                <td width="30%">Discount({{ $SalesData->discount_rate }} %)</td>
                <td class="text-right">{{ $SalesData->discount_amount }}</td>
                @endif
            </tr>
            <tr>
                <td>Customer</td>
                <td>{{ $SalesData->customer['customer_name']." (".$SalesData->customer['customer_no'].")" }}</td>

                @if($SalesData->sales_type == 2)
                <td>T/A After VAT</td>
                <td class="text-right">
                    <?php
                        $totalAmount = $SalesData->total_amount;
                        $vatAmount = $SalesData->vat_amount;
                        $Amount = $totalAmount + $vatAmount;
                        echo $Amount;
                    ?>
                </td>
                @else
                <td>T/A After Discount</td>
                <td  class="text-right">
                    {{ $SalesData-> ta_after_discount }}
                </td>
                @endif
            </tr>
            <tr>
                <td>Mobile No.</td>
                <td>{{ $SalesData->customer_mobile }}</td>

                @if($SalesData->sales_type == 2)
                <td>Processing Fee</td>
                <td class="text-right">{{ $SalesData->service_charge }}</td>
                @else
                <td>VAT({{ $SalesData->vat_rate }} %)</td>
                <td class="text-right">{{ $SalesData->vat_amount }}</td>
                @endif
            </tr>
            <tr>
                <td>National ID</td>
                <td>{{ $SalesData->customer_nid }}</td>

                <td>Total Payable &nbsp Amount</td>
                <td class="text-right">{{ $SalesData->total_payable_amount }}</td>
            </tr>
            <tr>
                <td>Sales By</td>
                <td>{{ $SalesData->employee['emp_name']. " (". $SalesData->employee['emp_code'].")" }}</td>

                <td>Paid Amount</td>
                <td class="text-right">
                    {{ $SalesData->paid_amount }}
                </td>
            </tr>
            <tr>
                <td>Branch</td>
                <td>
                    @if($SalesData->branch != null)
                    {{ $SalesData->branch['branch_name']. " (". $SalesData->branch['branch_code'].")" }}
                    @endif
                </td>

                <td>Due Amount</td>
                <td class="text-right">{{ $SalesData->due_amount }}</td>
            </tr>

            <tr>
                <?php
                    $SalesDate = new DateTime($SalesData->sales_date);
                    $SalesDate = (!empty($SalesDate)) ? $SalesDate->format('d-m-Y') : date('d-m-Y');
                ?>
                <td>Sales Date</td>
                <td>{{ $SalesDate }}</td>

                @if($SalesData->sales_type == 2)
                <td>Installment Amount</td>
                <td class="text-right">{{ $SalesData->installment_amount }}</td>
                @endif
            </tr>

            <tr>
                <td>VAT Invoice No.</td>
                <td>{{ $SalesData->vat_chalan_no }}</td>
            </tr>

            @if($SalesData->sales_type == 2)
            <tr>
                <td>Month</td>
                <td>
                    @if($SalesData->inst_package != null)
                    {{ $SalesData->inst_package['prod_inst_month'] }}
                    @endif
                </td>
            </tr>
            <tr>
                <td>Installment Type</td>
                <td>
                    @if($SalesData->inst_type != null)
                    {{ $SalesData->inst_type['name'] }}
                    @endif
                </td>
            </tr>
            @endif

            <tr>
                <td>Payment System</td>
                <td>
                    @if($SalesData->payment_system_id == 1) {{ 'Cash' }}
                    @elseif ($SalesData->payment_system_id == 2) {{ 'Others' }}
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
            <th width="15%">Serial No</th>
            <th width="10%" class="text-right">Quantity</th>
            <th width="15%" class="text-right">Sale Price</th>
            <th width="15%" class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>
        <?php
            $ProductList = Common::ViewTableOrder('pos_products',
                            ['is_delete' => 0],
                            ['id', 'product_name', 'cost_price', 'sale_price', 'prod_vat', 'sys_barcode', 'prod_barcode'],
                            ['product_name', 'ASC']);
        ?>

        @if(count($SalesDataD) > 0)
        @foreach($SalesDataD as $SDataD)
        <tr>
            <td class="text-left">
                @foreach($ProductList as $ProductInfo)
                @if($ProductInfo->id == $SDataD->product_id)
                {{ $ProductInfo->product_name . ' (' . $ProductInfo->sys_barcode . ')' }}
                @endif
                @endforeach
            </td>

            <td>
                {{ $SDataD->product_serial_no }}
            </td>

            <td class="text-right">
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
            <td colspan="2" class="text-right">
                <h5>Total Quantity</h5>
            </td>
            <td class="text-right">
                <h5>{{ $SalesData->total_quantity }}</h5>
            </td>
            <td class="text-right">
                <h5>Total Amount</h5>
            </td>
            <td class="text-right">
                <h5>{{ $SalesData->total_amount }}</h5>
            </td>
        </tr>
    </tbody>
</table>

@if($SalesData->sales_type == 2)

<div class="mt-4 table-responsive">
    <table class="table table-striped table-bordered w-full text-center">
        <thead>
            <tr>
                <th colspan="10" class="text-left">Instalment Schedule</th>
            </tr>
            <tr>
                <th width="3%" rowspan="2">#</th>
                <th width="10%" rowspan="2">Date</th>
                <th width="10%" rowspan="2">Day</th>
                <th width="30%" colspan="3">Amount</th>

                <th width="10%" rowspan="2">
                    Principle Amount
                </th>
                <th width="10%" rowspan="2">
                    Profit
                </th>
                <th width="10%" rowspan="2">
                    Collections
                </th>
                <th width="10%" rowspan="2">
                    Status
                </th>
            </tr>
            <tr>
                <th width="10%">Actual</th>
                <th width="10%">Extra</th>
                <th width="10%">Installment</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $scheduleList = POSS::installmentSchedule($SalesData->company_id, $SalesData->branch_id,null,
                    $SalesData->sales_date, $SalesData->installment_type, $SalesData->installment_month);

                $branchDate = new DateTime(Common::systemCurrentDate($SalesData->branch_id, 'pos'));

                $collectionAmount = DB::table('pos_collections')
                    ->where([['is_delete', 0], ['is_active', 1], 
                        ['branch_id', $SalesData->branch_id], 
                        ['sales_bill_no', $SalesData->sales_bill_no]])
                    ->whereDate('collection_date', '<=', $branchDate->format('Y-m-d'))
                    ->sum('collection_amount');
                
                $total_collection_amount = ($collectionAmount) ? $collectionAmount : 0;

                $ttl_actual_amount = 0;
                $ttl_extra_amount = 0;
                $ttl_inst_amount = 0;
                $ttl_principal_amount = 0;
                $ttl_installment_profit = 0;
                $ttl_paid_amount = 0;

            $i = 1;
            $paid_amt = $SalesData->paid_amount - $SalesData->vat_amount - $SalesData->service_charge;
            // //
            $remain_amount = $SalesData->total_amount - $paid_amt;

            ///////////////////////////////////////////////////////////
            $actual_amount_final = ($remain_amount / (count($scheduleList) - 1));
            $actual_amount_final = number_format($actual_amount_final, 2, '.', '');

            // $last3Digits = (int) substr(ceil($actual_amount_final), -3);
            // $last2Digits = (int) substr(ceil($actual_amount_final), -2);
            $lastDigit = (int) substr(ceil($actual_amount_final), -1);
            $extra_amount = 0;

            if($lastDigit > 0){
                $extra_amount = (10 - $lastDigit) + (ceil($actual_amount_final) - $actual_amount_final);
                $inst_amount_temp = $actual_amount_final + $extra_amount;
                $last_inst = $inst_amount_temp - ($extra_amount * (count($scheduleList)-2));

                if($last_inst < 1){
                    if($lastDigit > 0 && $lastDigit <= 5){

                        $extra_amount = (5 - $lastDigit) + (ceil($actual_amount_final) - $actual_amount_final);
                        $inst_amount_temp = $actual_amount_final + $extra_amount;
                        $last_inst = $inst_amount_temp - ($extra_amount * (count($scheduleList)-2));
                    }

                    if($lastDigit > 5 && $lastDigit <= 9){

                        $extra_amount = (10 - $lastDigit) + (ceil($actual_amount_final) - $actual_amount_final);
                        $inst_amount_temp = $actual_amount_final + $extra_amount;
                        $last_inst = $inst_amount_temp - ($extra_amount * (count($scheduleList)-2));
                    }

                    if($last_inst < 1){
                        $extra_amount = ceil($actual_amount_final) - $actual_amount_final;
                        $inst_amount_temp = $actual_amount_final + $extra_amount;
                    }
                }
            }

            $extra_amount = number_format($extra_amount, 2, '.', '');

            $extra_amount_temp = $extra_amount;
            $extra_amount = 0;

            $actual_amount_final_temp = $actual_amount_final;
            $actual_amount_final = 0;
            //////////////////////////////////////////////////////////////
            ?>

            @foreach($scheduleList as $CData)
            <?php
            $CData = new DateTime($CData);
            $firstFlag = FALSE;
            $lastFlag = FALSE;

            $fullPaidFlag = FALSE;
            $partialPaidFlag = FALSE;
            $advancePaidFlag = FALSE;
            $pendingFlag = FALSE;

            $collection_amount = 0;
            $inst_amount = 0;

            // $actual_amount_final = ($SalesData->total_amount / count($scheduleList));

            $actual_amount_final = $actual_amount_final_temp;
            $extra_amount = $extra_amount_temp;

            if($SalesData->sales_date == $CData->format('Y-m-d')){
                $firstFlag = TRUE;
            }

            if($i == count($scheduleList)){
                $lastFlag = TRUE;
            }

            if($firstFlag){
                // //
                $actual_amount_final = $paid_amt;
                $actual_amount_final = number_format($actual_amount_final, 2, '.', '');
                
                $extra_amount = $paid_amt - $actual_amount_final;
                $extra_amount = number_format($extra_amount, 2, '.', '');
            }

            if($lastFlag){
                $extra_amount = -$ttl_extra_amount;
                $extra_amount = number_format($extra_amount, 2, '.', '');

                // $actual_amount_final = $SalesData->total_amount - ($actual_amount_final * (count($scheduleList)-1));

                $actual_amount_final = $remain_amount - ($actual_amount_final * (count($scheduleList)-2));
                $actual_amount_final = number_format($actual_amount_final, 2, '.', '');
            }

            $inst_amount = $actual_amount_final + $extra_amount;
            $inst_amount = number_format($inst_amount, 2, '.', '');

            /////////////////////////////////// 
            $principal_amount = ($inst_amount / (100 + $SalesData->installment_rate)) * 100;
            $installment_profit = $inst_amount - $principal_amount;

            ////////////////////////////

            ## Collection Calculation
            if($total_collection_amount < 0){
                $total_collection_amount = 0;
            }
            $total_collection_amount = $total_collection_amount - $inst_amount;
            $temp_collected_amount = $total_collection_amount;

            if($temp_collected_amount >= 0){
                $fullPaidFlag = TRUE;
                $collection_amount = $inst_amount;

                if($CData->format('Y-m-d') > $branchDate->format('Y-m-d')){
                    $advancePaidFlag = TRUE;
                    $fullPaidFlag = FALSE;
                }
            }
            else if($temp_collected_amount < 0 && abs($temp_collected_amount) < $inst_amount){
                $partialPaidFlag = TRUE;
                $collection_amount = $inst_amount - abs($temp_collected_amount);

                if($CData->format('Y-m-d') > $branchDate->format('Y-m-d')){
                    $advancePaidFlag = TRUE;
                    $partialPaidFlag = FALSE;
                }
            }
            else{
                if($CData->format('Y-m-d') < $branchDate->format('Y-m-d')){
                    $pendingFlag = TRUE;
                }
            }

            $collection_amount = number_format($collection_amount, 2, '.', '');

            $ttl_actual_amount += $actual_amount_final;
            $ttl_inst_amount += $inst_amount;
            $ttl_extra_amount += $extra_amount;
            $ttl_principal_amount += $principal_amount;
            $ttl_installment_profit += $installment_profit;
            $ttl_paid_amount += $collection_amount;
            ?>
            <tr>
                <td>{{ $i++ }}</td>
                <td>{{ $CData->format('d-m-Y')}}</td>
                <td>{{ $CData->format('l')}}</td>
                <td class="text-right">{{ $actual_amount_final }}</td>
                <td class="text-right">{{ $extra_amount }}</td>
                <td class="text-right">{{ $inst_amount }}</td>
                <td class="text-right">{{ number_format($principal_amount, 2) }}</td>
                <td class="text-right">{{ number_format($installment_profit, 2) }}</td>
                <td class="text-right">
                    @if($collection_amount > 0)
                    {{ $collection_amount }}
                    @endif
                </td>
                <td>
                    @if($fullPaidFlag == TRUE)
                        <i class="fa fa-check-circle" style="font-size:15px;" aria-hidden="true"></i>
                    @elseif($partialPaidFlag == TRUE)
                        <p style="color:#3e8ef7;">Partial</p>
                    @elseif($advancePaidFlag == TRUE)
                        <p style="color:#3e8ef7;">Advanced</p>
                    @elseif($pendingFlag == TRUE)
                        <i class="fa fa-times-circle" style="font-size:15px;" aria-hidden="true"></i>
                    @else
                        <a href="javascript:void(0)">
                            <i class="fa fa-plus-circle" aria-hidden="true"></i>
                            Reschedule
                        </a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-right"><b>Total</b></th>
                <th class="text-right"><b>{{ number_format($ttl_actual_amount,2) }}</b></th>
                <th class="text-right"><b>{{ number_format($ttl_extra_amount,2) }}</b></th>
                <th class="text-right"><b>{{ number_format($ttl_inst_amount,2) }}</b></th>
                <th class="text-right"><b>{{ number_format($ttl_principal_amount,2) }}</b></th>
                <th class="text-right"><b>{{ number_format($ttl_installment_profit,2) }}</b></th>
                <th class="text-right"><b>{{ number_format($ttl_paid_amount,2) }}</b></th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</div>
@endif

<div class="row align-items-center d-print-none">
    <div class="col-lg-12">
        <div class="form-group d-flex justify-content-center">
            <div class="example example-buttons">

                <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>

                <!-- <a href="javascript:void(0)" onclick="window.print();" class="btn btn-default btn-round clsPrint">Invoice</a> -->
                <a type="button" class="btn btn-default btn-round clsPrint"
                @if($SalesData->sales_type == 1)
                href="{{ url('pos/sales_cash/invoice/'.$SalesData->sales_bill_no) }}"
                @else href="{{ url('pos/sales_installment/invoice/'.$SalesData->sales_bill_no) }}"
                @endif>Invoice
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
