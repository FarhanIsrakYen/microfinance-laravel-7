<div class="panel" id="reportTableDiv">
    <div class="panel-body">
        <div class="row text-center d-print-block">
            <div class="col-lg-12" style="color:#000;">
                <strong>{{ $groupName }}</strong><br>
                <strong>{{ $branch }}</strong><br>
                <span>{{ $branchAddress }}</span><br>
                <span>Daily Collection Component Wise Report</span><br>
                <span>Reporting Date: {{ $date }}</span>
            </div>
        </div>
        <div class="row d-print-none text-right" data-html2canvas-ignore="true">
            <div class="col-lg-12">
                <a href="javascript:void(0)" onClick="window.print();" style="background-color:transparent;border:none;"
                    class="btnPrint mr-2">
                    <i class="fa fa-print fa-lg" style="font-size:20px;"></i>
                </a>
                {{-- <a href="javascript:void(0)" onclick="getPDF();">
                    <i class="fa fa-file-pdf-o fa-lg" style="font-size:20px;"></i>
                </a>
                <a href="javascript:void(0)" onclick="fnDownloadXLSX();">
                    <i class="fa fa-file-excel-o fa-lg" style="font-size:20px; margin-left: 5px;"></i>
                </a> --}}
            </div>
        </div>
        <div class="row">
            <div class="col-xl-6 col-lg-6 col-sm-6 col-md-6 col-6">
                {{-- <span class="d-print-none"><b>Total Row:</b> <span id="totalRowDiv">0</span></span> --}}
            </div>
            <div class="col-xl-6 col-lg-6 col-sm-6 col-md-6 col-6 text-right">
                <span><b>Printed Date:</b> {{ date("d-m-Y") }} </span>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped"  style="font-size: 7px">
                        <thead class="text-center">
                            <tr>
                                <th colspan="2">Field Worker</th>
                                <th colspan="2">Samity</th>
                                <th rowspan="2">Component</th>
                                <th rowspan="2">Total Members</th>
                                <th rowspan="2">Total Loanee</th>
                                <th rowspan="2">Total No. Inset. Payer</th>
                                <th rowspan="2">Total No. Inset. Due</th>
                                <th colspan="{{ $savingProduct  + 1 }}">Savings Deposit</th>
                                <th colspan="{{ $savingProduct + 1 }}">Savings Refund</th>
                                <th colspan="2">Loan Disbursement</th>
                                <th colspan="2">Fully Payment Loan</th>
                                <th rowspan="2">Loan Recoverable</th>
                                <th colspan="7">Loan Collection</th>
                                <th rowspan="2">Today's Due</th>
                                <th rowspan="2">Insurance Premium</th>
                                <th rowspan="2">Today's Total Collection</th>
                                <th rowspan="2">Today's Total Refund</th>
                                <th rowspan="2">Today's Total Net Collection</th>
                            </tr>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>

                                <th>ID</th>
                                <th>Name</th>

                                {!! $thhtml !!}
                                <th>Total</th>
                                {!! $thhtml !!}
                                <th>Total</th>

                                <th>No. Of Person</th>
                                <th>Amount</th>

                                <th>No. Of Person</th>
                                <th>Amount</th>

                                <th>Regular Loan</th>
                                <th>Loan Due</th>
                                <th>Loan Advance</th>
                                <th>Loan Rebate</th>
                                <th>Loan Receive (Pri.)</th>
                                <th>Service Charge</th>
                                <th>Total (P+I)</th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $savingsProduct = DB::table('mfn_savings_product')
                                    ->where('is_delete', 0)
                                    ->count();

                                $totalMembersGrandTotal             = 0;
                                $totalLoaneeGrandTotal              = 0;
                                $totalNoOfPayerGrandTotal           = 0;
                                $totalNoInstDueGrandTotal           = 0;
                                $ldNoOfPersionGrandTotal            = 0;
                                $ldAmountGrandTotal                 = 0;
                                $fplNoOfPersonGrandTotal            = 0;
                                $fplAmountGrandTotal                = 0;
                                $loanRecoverableGrandTotal          = 0;
                                $regularCollectionGrandTotal        = 0;
                                $dueCollectionGrandTotal            = 0;
                                $advanceCollectionGrandTotal        = 0;
                                $rebateAmountGrandTotal             = 0;
                                $loanReceiveGrandTotal              = 0;
                                $serviceChargeGrandTotal            = 0;
                                $totalGrandTotal                    = 0;
                                $todaysDueGrandTotal                = 0;
                                $insurancePremiumGrandTotal         = 0;
                                $todaysTotalCollectionGrandTotal    = 0;
                                $todaysTotalRefundGrandTotal        = 0;
                                $todaysTotalNetCollectionGrandTotal = 0;

                                $totalsavingDepositGrandTotal = 0;
                                $totalsavingRefundGrandTotal = 0;

                            ?>
                                            
                            @foreach ($dailyCollectionDataArr as $row)
                                <tr>
                                    <td rowspan="{{ count($row['samity']) + 1 }}">{{ $row['emp_code'] }}</td>
                                    <td rowspan="{{ count($row['samity']) + 1 }}">{{ $row['emp_name'] }}</td>

                                    @foreach ($row['samity'] as $samity)
                                <?php
                                    $totalMembersGrandTotal             += $samity['totalMembers'];
                                    $totalLoaneeGrandTotal              += $samity['totalLoanee'];
                                    $totalNoOfPayerGrandTotal           += $samity['totalNoOfPayer'];
                                    $totalNoInstDueGrandTotal           += $samity['totalNoInstDue'];
                                    $ldNoOfPersionGrandTotal            += $samity['ldNoOfPersion'];
                                    $ldAmountGrandTotal                 += $samity['ldAmount'];
                                    $fplNoOfPersonGrandTotal            += $samity['fplNoOfPerson'];
                                    $fplAmountGrandTotal                += $samity['fplAmount'];
                                    $loanRecoverableGrandTotal          += $samity['loanRecoverable'];
                                    $regularCollectionGrandTotal        += $samity['regularCollection'];
                                    $dueCollectionGrandTotal            += $samity['dueCollection'];
                                    $advanceCollectionGrandTotal        += $samity['advanceCollection'];
                                    $rebateAmountGrandTotal             += $samity['rebateAmount'];
                                    $loanReceiveGrandTotal              += $samity['loanReceive'];
                                    $serviceChargeGrandTotal            += $samity['serviceCharge'];
                                    $totalGrandTotal                    += $samity['total'];
                                    $todaysDueGrandTotal                += $samity['todaysDue'];
                                    $insurancePremiumGrandTotal         += $samity['insurancePremium'];
                                    $todaysTotalCollectionGrandTotal    += $samity['todaysTotalCollection'];
                                    $todaysTotalRefundGrandTotal        += $samity['todaysTotalRefund'];
                                    $todaysTotalNetCollectionGrandTotal += $samity['todaysTotalNetCollection'];

                                    $totalsavingDeposit = array_sum($samity['savingRefund']);
                                    $totalsavingRefund = array_sum($samity['savingRefund']);
                                    $totalsavingDepositGrandTotal += $totalsavingDeposit;
                                    $totalsavingRefundGrandTotal += $totalsavingRefund;
                                ?>
                                        <tr>
                                            <td class="text-center">{{ $samity['samityCode'] }}</td>
                                            <td class="text-center">{{ $samity['samityName'] }}</td>
                                            <td class="text-center">{{ $samity['component'] }}</td>
                                            <td class="text-center">{{ $samity['totalMembers'] }}</td>
                                            <td class="text-center">{{ $samity['totalLoanee'] }}</td>
                                            <td class="text-center">{{ $samity['totalNoOfPayer'] }}</td>
                                            <td class="text-center">{{ $samity['totalNoInstDue'] }}</td>

                                            <?php $savingProdDiff = $savingProduct - count($samity['savingDeposit']) ?>
                                            @foreach ($samity['savingDeposit'] as $savingDepo)
                                                <td class="text-right">{{ $savingDepo }}</td>
                                            @endforeach
                                            
                                            @if ($savingProdDiff)
                                                @for ($i = 1; $i <= $savingProdDiff; $i++)
                                                    <td class="text-right">0.0</td>
                                                @endfor
                                            @endif
                                            <td class="text-right">{{ $totalsavingDeposit }}</td>
                                            
                                            <?php $savingProdDiff = $savingProduct - count($samity['savingRefund']) ?>
                                            @foreach ($samity['savingRefund'] as $sreFund)
                                                <td class="text-right">{{ $sreFund }}</td>
                                            @endforeach
                                            @if ($savingProdDiff)
                                                @for ($i = 1; $i <= $savingProdDiff; $i++)
                                                    <td class="text-right">0.0</td>
                                                @endfor
                                            @endif
                                            <td class="text-right">{{ $totalsavingRefund }}</td>
                                            
                                            <td class="text-center">{{ $samity['ldNoOfPersion'] }}</td>
                                            <td class="text-right">{{ $samity['ldAmount'] }}</td>
                                            <td class="text-center">{{ $samity['fplNoOfPerson'] }}</td>
                                            <td class="text-right">{{ $samity['fplAmount'] }}</td>
                                            <td class="text-right">{{ $samity['loanRecoverable'] }}</td>
                                            <td class="text-right">{{ $samity['regularCollection'] }}</td>
                                            <td class="text-right">{{ $samity['dueCollection'] }}</td>
                                            <td class="text-right">{{ $samity['advanceCollection'] }}</td>
                                            <td class="text-right">{{ $samity['rebateAmount'] }}</td>
                                            <td class="text-right">{{ $samity['loanReceive'] }}</td>
                                            <td class="text-right">{{ $samity['serviceCharge'] }}</td>
                                            <td class="text-right">{{ $samity['total'] }}</td>

                                            <td class="text-right">{{ $samity['todaysDue'] }}</td>
                                            <td class="text-right">{{ $samity['insurancePremium'] }}</td>
                                            <td class="text-right">{{ $samity['todaysTotalCollection'] }}</td>
                                            <td class="text-right">{{ $samity['todaysTotalRefund'] }}</td>
                                            <td class="text-right">{{ $samity['todaysTotalNetCollection'] }}</td>
                                        </tr>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background-color: #819bc4">
                                <td colspan="5" class="text-center">Total</td>
                                <td class="text-center">{{ $totalMembersGrandTotal }}</td>
                                <td class="text-center">{{ $totalLoaneeGrandTotal }}</td>
                                <td class="text-center">{{ $totalNoOfPayerGrandTotal }}</td>
                                <td class="text-center">{{ $totalNoInstDueGrandTotal }}</td>
                                <td colspan="{{ $savingsProduct }}"></td>
                                <td class="text-right">{{ $totalsavingDepositGrandTotal }}</td>
                                <td colspan="{{ $savingsProduct }}"></td>
                                <td class="text-right">{{ $totalsavingRefundGrandTotal }}</td>
                                <td class="text-center">{{ $ldNoOfPersionGrandTotal }}</td>
                                <td class="text-right">{{ $ldAmountGrandTotal }}</td>
                                <td class="text-center">{{ $fplNoOfPersonGrandTotal }}</td>
                                <td class="text-right">{{ $fplAmountGrandTotal }}</td>
                                <td class="text-right">{{ $loanRecoverableGrandTotal }}</td>
                                <td class="text-right">{{ $regularCollectionGrandTotal }}</td>
                                <td class="text-right">{{ $dueCollectionGrandTotal }}</td>
                                <td class="text-right">{{ $advanceCollectionGrandTotal }}</td>
                                <td class="text-right">{{ $rebateAmountGrandTotal }}</td>
                                <td class="text-right">{{ $loanReceiveGrandTotal }}</td>
                                <td class="text-right">{{ $serviceChargeGrandTotal }}</td>
                                <td class="text-right">{{ $totalGrandTotal }}</td>
                                <td class="text-right">{{ $todaysDueGrandTotal }}</td>
                                <td class="text-right">{{ $insurancePremiumGrandTotal }}</td>
                                <td class="text-right">{{ $todaysTotalCollectionGrandTotal }}</td>
                                <td class="text-right">{{ $todaysTotalRefundGrandTotal }}</td>
                                <td class="text-right">{{ $todaysTotalNetCollectionGrandTotal }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>