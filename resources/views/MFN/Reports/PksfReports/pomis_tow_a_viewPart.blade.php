<div class="panel" id="loadReport">
    <div class="panel-body pdf-export">
        <div class="row text-center d-print-block">
            <div class="col-lg-12" style="color:#000;">
                <strong>{{ $groupName }}</strong><br>
                <strong>{{ $branchName }}</strong><br>
                <span>POMIS-2A ({{ $isWithServiceChanrge }})</span><br>
                <span>Funding Organization: {{ $fundingOrg }} </span><br>
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
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable text-center"  style="font-size: 9.5px">
                        <thead>
                            <tr>
                                <th rowspan="2" colspan="3">Component</th>
                                <th rowspan="2">Due At The End Of The Last Month</th>
                                <th rowspan="2">Reg.Loan Recoverable (Current Month)</th>
                                <th colspan="4">Current Month Recovered</th>
                                <th rowspan="2">New Due Amount (Current Month)</th>
                                <th colspan="2">End Of The Month</th>
                            </tr>
                            <tr>
                                <th>Regular</th>
                                <th>Due</th>
                                <th>Advance</th>
                                <th>Total</th>

                                <th>Total Due</th>
                                <th>Total Due Loanee</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $dueEndMonthGrandTotal             = 0;
                                $loanRecoverableGrandTotal         = 0;
                                $regularGrandTotal                 = 0;
                                $dueGrandTotal                     = 0;
                                $advanceGrandTotal                 = 0;
                                $totalGrandTotal                   = 0;
                                $newDueGrandTotal                  = 0;
                                $totalDueGrandTotal                = 0;
                                $totalLoaneeGrandTotal             = 0;
                            ?>

                            @foreach ($pomisTwoAData as $row)
                                <tr>
                                    <td rowspan="{{ count($row['products']) * 4  + 2 }}">{{ $row['fundingOrgName'] }}</td>

                                    <?php
                                        $dueEndMonthSubTotal             = 0;
                                        $loanRecoverableSubTotal         = 0;
                                        $regularSubTotal                 = 0;
                                        $dueSubTotal                     = 0;
                                        $advanceSubTotal                 = 0;
                                        $totalSubTotal                   = 0;
                                        $newDueSubTotal                  = 0;
                                        $totalDueSubTotal                = 0;
                                        $totalLoaneeSubTotal             = 0;
                                    ?>

                                    @foreach ($row['products'] as $products)
                                        <tr>
                                            <td rowspan="{{ count($products['genders']) + 2 }}">{{ $products['productName'] }}</td>

                                            <?php
                                                $dueEndMonthTotal             = 0;
                                                $loanRecoverableTotal         = 0;
                                                $regularTotal                 = 0;
                                                $dueTotal                     = 0;
                                                $advanceTotal                 = 0;
                                                $totalTotal                   = 0;
                                                $newDueTotal                  = 0;
                                                $totalDueTotal                = 0;
                                                $totalLoaneeTotal             = 0;
                                            ?>

                                            @foreach ($products['genders'] as $gender)
                                                <tr>
                                                    <td>{{ $gender['genderName'] }}</td>
                                                    <td class="text-right">
                                                        {{ $gender['dueEndMonth'] }}
                                                        <?php $dueEndMonthTotal += $gender['dueEndMonth'] ?>
                                                    </td>
                                                    <td class="text-right">
                                                        {{ $gender['currentMonthLoanRecoverable'] }}
                                                        <?php $loanRecoverableTotal += $gender['currentMonthLoanRecoverable'] ?>

                                                    </td>
                                                    <td class="text-right">
                                                        {{ $gender['currentMonthRegularAmount'] }}
                                                        <?php $regularTotal += $gender['currentMonthRegularAmount'] ?>

                                                    </td>
                                                    <td class="text-right">
                                                        {{ $gender['currentMonthDueAmount'] }}
                                                        <?php $dueTotal += $gender['currentMonthDueAmount'] ?>

                                                    </td>
                                                    <td class="text-right">
                                                        {{ $gender['currentMonthAdvanceAmount'] }}
                                                        <?php $advanceTotal += $gender['currentMonthAdvanceAmount'] ?>

                                                    </td>
                                                    <td class="text-right">
                                                        {{ $gender['currentMonthTotalAmount'] }}
                                                        <?php $totalTotal += $gender['currentMonthTotalAmount'] ?>

                                                    </td>
                                                    <td class="text-right">
                                                        {{ $gender['newDue'] }}
                                                        <?php $newDueTotal += $gender['newDue'] ?>

                                                    </td>
                                                    <td class="text-right">
                                                        {{ $gender['totalDue'] }}
                                                        <?php $totalDueTotal += $gender['totalDue'] ?>
                                                    </td>
                                                    <td>
                                                        {{ $gender['totalLonee'] }}
                                                        <?php $totalLoaneeTotal += $gender['totalLonee'] ?>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            <td>Total</td>
                                            <td class="text-right">
                                                {{ $dueEndMonthTotal }}
                                                <?php $dueEndMonthSubTotal += $dueEndMonthTotal ?>
                                            </td>
                                            <td class="text-right">
                                                {{ $loanRecoverableTotal }}
                                                <?php $loanRecoverableSubTotal += $loanRecoverableTotal ?>
                                            </td>
                                            <td class="text-right">
                                                {{ $regularTotal }}
                                                <?php $regularSubTotal += $regularTotal ?>
                                            </td>
                                            <td class="text-right">
                                                {{ $dueTotal }}
                                                <?php $dueSubTotal += $dueTotal ?>
                                            </td>
                                            <td class="text-right">
                                                {{ $advanceTotal }}
                                                <?php $advanceSubTotal += $advanceTotal ?>
                                            </td>
                                            <td class="text-right">
                                                {{ $totalTotal }}
                                                <?php $totalSubTotal += $totalTotal ?>
                                            </td>
                                            <td class="text-right">
                                                {{ $newDueTotal }}
                                                <?php $newDueSubTotal += $newDueTotal ?>
                                            </td>
                                            <td class="text-right">
                                                {{ $totalDueTotal }}
                                                <?php $totalDueSubTotal += $totalDueTotal ?>
                                            </td>
                                            <td>
                                                {{ $totalLoaneeTotal }}
                                                <?php $totalLoaneeSubTotal += $totalLoaneeTotal ?>
                                            </td>
                                        </tr>
                                    @endforeach
                                    <td colspan="2">Sub Total</td>
                                    <td class="text-right">
                                        {{ $dueEndMonthSubTotal }}
                                        <?php $dueEndMonthGrandTotal += $dueEndMonthSubTotal ?>
                                    </td>
                                    <td class="text-right">
                                        {{ $loanRecoverableSubTotal }}
                                        <?php $loanRecoverableGrandTotal += $loanRecoverableSubTotal ?>
                                    </td>
                                    <td class="text-right">
                                        {{ $regularSubTotal }}
                                        <?php $regularGrandTotal += $regularSubTotal ?>
                                    </td>
                                    <td class="text-right">
                                        {{ $dueSubTotal }}
                                        <?php $dueGrandTotal += $dueSubTotal ?>
                                    </td>
                                    <td class="text-right">
                                        {{ $advanceSubTotal }}
                                        <?php $advanceGrandTotal += $advanceSubTotal ?>
                                    </td>
                                    <td class="text-right">
                                        {{ $totalSubTotal }}
                                        <?php $totalGrandTotal += $totalSubTotal ?>
                                    </td>
                                    <td class="text-right">
                                        {{ $newDueSubTotal }}
                                        <?php $newDueGrandTotal += $newDueSubTotal ?>
                                    </td>
                                    <td class="text-right">
                                        {{ $totalDueSubTotal }}
                                        <?php $totalDueGrandTotal += $totalDueSubTotal ?>
                                    </td>
                                    <td>
                                        {{ $totalLoaneeSubTotal }}
                                        <?php $totalLoaneeGrandTotal += $totalLoaneeSubTotal ?>
                                    </td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="3">Grand Total</td>
                                <td class="text-right">{{ $dueEndMonthGrandTotal }}</td>
                                <td class="text-right">{{ $loanRecoverableGrandTotal }}</td>
                                <td class="text-right">{{ $regularGrandTotal }}</td>
                                <td class="text-right">{{ $dueGrandTotal }}</td>
                                <td class="text-right">{{ $advanceGrandTotal }}</td>
                                <td class="text-right">{{ $totalGrandTotal }}</td>
                                <td class="text-right">{{ $newDueGrandTotal }}</td>
                                <td class="text-right">{{ $totalDueGrandTotal }}</td>
                                <td>{{ $totalLoaneeGrandTotal }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>