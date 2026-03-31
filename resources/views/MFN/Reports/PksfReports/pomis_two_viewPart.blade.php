<div class="panel">
    <div class="panel-body pdf-export">
        <div class="row text-center d-print-block">
            <div class="col-lg-12" style="color:#000;">
                <strong>{{ $groupName }}</strong><br>
                <strong>{{ $branchName }}</strong><br>
                <span>POMIS-2 ({{ $isWithServiceChanrge }})</span><br>
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
                                <th colspan="2">At The End Of The Last Month</th>
                                <th colspan="2">Loan Disbursement Current Month</th>
                                <th rowspan="2">Total Loan Recovery Amount(Current Month)</th>
                                <th rowspan="2">Fully Paid Borrower(Current Month)</th>
                                <th colspan="2">Fully Paid Borrower(Current Month)</th>
                                <th colspan="2">Cumulative</th>
                            </tr>
                            <tr>
                                <th>Borrower No.</th>
                                <th>Loan Outstanding</th>

                                <th>Borrower No.</th>
                                <th>Amount</th>

                                <th>Borrower No.</th>
                                <th>Amount</th>

                                <th>No Of Loanee</th>
                                <th>No Of Loan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $openingBorrowerNoTotal      = 0;
                                $loanOutstandingTotal        = 0;
                                $currentMonthBorrowerNoTotal = 0;
                                $crrentMonthAmountTotal      = 0;
                                $totalRecoveryAmountTotal    = 0;
                                $fullyPaidBorrowerNoTotal    = 0;
                                $closingBorrowerNoTotal      = 0;
                                $closingAmountTotal          = 0;
                            ?>

                            @foreach ($pomisTwoDataArr as $row)
                                <tr>
                                    <td rowspan="{{ count($row['products']) * 4  + 2 }}">{{ $row['fundingOrgName'] }}</td>

                                    <?php
                                        $openingBorrowerNoSubTotal      = 0;
                                        $loanOutstandingSubTotal        = 0;
                                        $currentMonthBorrowerNoSubTotal = 0;
                                        $crrentMonthAmountSubTotal      = 0;
                                        $totalRecoveryAmountSubTotal    = 0;
                                        $fullyPaidBorrowerNoSubTotal    = 0;
                                        $closingBorrowerNoSubTotal      = 0;
                                        $closingAmountSubTotal          = 0;
                                    ?>

                                    @foreach ($row['products'] as $products)
                                        <tr>
                                            <td rowspan="{{ count($products['genders']) + 2 }}">{{ $products['productName'] }}</td>

                                            <?php
                                                $openingBorrowerNo      = 0;
                                                $loanOutstanding        = 0;
                                                $currentMonthBorrowerNo = 0;
                                                $crrentMonthAmount      = 0;
                                                $totalRecoveryAmount    = 0;
                                                $fullyPaidBorrowerNo    = 0;
                                                $closingBorrowerNo      = 0;
                                                $closingAmount          = 0;
                                            ?>

                                            @foreach ($products['genders'] as $gender)
                                                <tr>
                                                    <td>{{ $gender['genderName'] }}</td>
                                                    <td>
                                                        {{ $gender['openingBorrowerNo'] }}
                                                        <?php $openingBorrowerNo += $gender['openingBorrowerNo'] ?>
                                                    </td>
                                                    <td class="text-right">
                                                        {{ $gender['loanOutstanding'] }}
                                                        <?php $loanOutstanding += $gender['loanOutstanding'] ?>

                                                    </td>
                                                    <td>
                                                        {{ $gender['currentMonthBorrowerNo'] }}
                                                        <?php $currentMonthBorrowerNo += $gender['currentMonthBorrowerNo'] ?>

                                                    </td>
                                                    <td class="text-right">
                                                        {{ $gender['crrentMonthAmount'] }}
                                                        <?php $crrentMonthAmount += $gender['crrentMonthAmount'] ?>

                                                    </td>
                                                    <td class="text-right">
                                                        {{ $gender['totalRecoveryAmount'] }}
                                                        <?php $totalRecoveryAmount += $gender['totalRecoveryAmount'] ?>

                                                    </td>
                                                    <td>
                                                        {{ $gender['fullyPaidBorrowerNo'] }}
                                                        <?php $fullyPaidBorrowerNo += $gender['fullyPaidBorrowerNo'] ?>

                                                    </td>
                                                    <td>
                                                        {{ $gender['closingBorrowerNo'] }}
                                                        <?php $closingBorrowerNo += $gender['closingBorrowerNo'] ?>

                                                    </td>
                                                    <td class="text-right">
                                                        {{ $gender['closingAmount'] }}
                                                        <?php $closingAmount += $gender['closingAmount'] ?>

                                                    </td>
                                                </tr>
                                            @endforeach
                                            <td>Total</td>
                                            <td>
                                                {{ $openingBorrowerNo }}
                                                <?php $openingBorrowerNoSubTotal += $openingBorrowerNo ?>
                                            </td>
                                            <td class="text-right">
                                                {{ $loanOutstanding }}
                                                <?php $loanOutstandingSubTotal += $loanOutstanding ?>
                                            </td>
                                            <td>
                                                {{ $currentMonthBorrowerNo }}
                                                <?php $currentMonthBorrowerNoSubTotal += $currentMonthBorrowerNo ?>
                                            </td>
                                            <td class="text-right">
                                                {{ $crrentMonthAmount }}
                                                <?php $crrentMonthAmountSubTotal += $crrentMonthAmount ?>
                                            </td>
                                            <td class="text-right">
                                                {{ $totalRecoveryAmount }}
                                                <?php $totalRecoveryAmountSubTotal += $totalRecoveryAmount ?>
                                            </td>
                                            <td>
                                                {{ $fullyPaidBorrowerNo }}
                                                <?php $fullyPaidBorrowerNoSubTotal += $fullyPaidBorrowerNo ?>
                                            </td>
                                            <td>
                                                {{ $closingBorrowerNo }}
                                                <?php $closingBorrowerNoSubTotal += $closingBorrowerNo ?>
                                            </td>
                                            <td class="text-right">
                                                {{ $closingAmount }}
                                                <?php $closingAmountSubTotal += $closingAmount ?>
                                            </td>
                                        </tr>
                                    @endforeach
                                    <td colspan="2">Sub Total</td>
                                    <td>
                                        {{ $openingBorrowerNoSubTotal }}
                                        <?php $openingBorrowerNoTotal += $openingBorrowerNoSubTotal ?>
                                    </td>
                                    <td class="text-right">
                                        {{ $loanOutstandingSubTotal }}
                                        <?php $loanOutstandingTotal += $loanOutstandingSubTotal ?>
                                    </td>
                                    <td>
                                        {{ $currentMonthBorrowerNoSubTotal }}
                                        <?php $currentMonthBorrowerNoTotal += $currentMonthBorrowerNoSubTotal ?>
                                    </td>
                                    <td class="text-right">
                                        {{ $crrentMonthAmountSubTotal }}
                                        <?php $crrentMonthAmountTotal += $crrentMonthAmountSubTotal ?>
                                    </td>
                                    <td class="text-right">
                                        {{ $totalRecoveryAmountSubTotal }}
                                        <?php $totalRecoveryAmountTotal += $totalRecoveryAmountSubTotal ?>
                                    </td>
                                    <td>
                                        {{ $fullyPaidBorrowerNoSubTotal }}
                                        <?php $fullyPaidBorrowerNoTotal += $fullyPaidBorrowerNoSubTotal ?>
                                    </td>
                                    <td>
                                        {{ $closingBorrowerNoSubTotal }}
                                        <?php $closingBorrowerNoTotal += $closingBorrowerNoSubTotal ?>
                                    </td>
                                    <td class="text-right">
                                        {{ $closingAmountTotal }}
                                        <?php $closingAmountTotal += $closingAmountSubTotal ?>
                                    </td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="3">Grand Total</td>
                                <td>{{ $openingBorrowerNoTotal }}</td>
                                <td class="text-right">{{ $loanOutstandingTotal }}</td>
                                <td>{{ $currentMonthBorrowerNoTotal }}</td>
                                <td class="text-right">{{ $crrentMonthAmountTotal }}</td>
                                <td class="text-right">{{ $totalRecoveryAmountTotal }}</td>
                                <td>{{ $fullyPaidBorrowerNoTotal }}</td>
                                <td>{{ $closingBorrowerNoTotal }}</td>
                                <td class="text-right">{{ $closingAmountTotal }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>