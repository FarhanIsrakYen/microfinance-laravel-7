<div class="panel" id="loadReport">
    <div class="panel-body pdf-export">
        <div class="row text-center d-print-block">
            <div class="col-lg-12" style="color:#000;">
                <strong>{{ $groupName }}</strong><br>
                <strong>{{ $branchName }}</strong><br>
                <span>POMIS-3 ({{ $isWithServiceChanrge }})</span><br>
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
                                <th rowspan="2" colspan="2">Component</th>
                                <th colspan="1">Standard Loan</th>
                                <th colspan="2">Watchful (1-30 Days)</th>
                                <th colspan="2">Substandard(31-180 Days)</th>
                                <th colspan="2">Doubtful(181-365)</th>
                                <th colspan="1">Bad Loan(365+)</th>
                                <th rowspan="2">Total Outstanding</th>
                                <th rowspan="2">Total Overdue</th>
                                <th rowspan="2">Overdue Loan Outstanding</th>
                                <th rowspan="2">Outstanding With More Than 2 Due Installments</th>
                                <th rowspan="2">Saving Balance Of Overdue Loanee</th>
                            </tr>
                            <tr>
                                <th>Loan Outstanding</th>
                                <th>Overdue</th>
                                <th>Loan Outstanding</th>
                                <th>Overdue</th>
                                <th>Loan Outstanding</th>
                                <th>Overdue</th>
                                <th>Loan Outstanding</th>
                                <th>Overdue/Loan Outstanding</th>
                            </tr>
                            <tr>
                                <th colspan="2">1</th>
                                <th>2</th>
                                <th>3</th>
                                <th>4</th>
                                <th>5</th>
                                <th>6</th>
                                <th>7</th>
                                <th>8</th>
                                <th>9</th>
                                <th>10=2+4+6+8+9</th>
                                <th>11=3+5+7+9</th>
                                <th>12=4+6+8+9</th>
                                <th>13</th>
                                <th>14</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $standardOutstandingGrandTotal                          = 0;
                                $watchfulDueGrandTotal                                  = 0;
                                $watchfulOutstandingGrandTotal                          = 0;
                                $substandardDueGrandTotal                               = 0;
                                $substandardOutstandingGrandTotal                       = 0;
                                $doubtfulDueGrandTotal                                  = 0;
                                $doubtfulOutstandingGrandTotal                          = 0;
                                $badOutstandingGrandTotal                               = 0;
                                $totalOutstandingGrandTotal                             = 0;
                                $totalOverDueGrandTotal                                 = 0;
                                $overDueLoanOutstandingGrandTotal                       = 0;
                                $outstandingWithMoreThanTwoDueInstallmentsGrandTotal    = 0;
                                $savingBalanceOfOverdueLoaneeGrandTotal                 = 0;
                            ?>
                            @foreach ($pomisThreeDataArr as $row)
                                <tr>
                                    <td rowspan="{{ count($row['products']) + 2 }}">{{ $row['fundingOrgName'] }}</td>

                                    <?php
                                        $standardOutstandingSubTotal                          = 0;
                                        $watchfulDueSubTotal                                  = 0;
                                        $watchfulOutstandingSubTotal                          = 0;
                                        $substandardDueSubTotal                               = 0;
                                        $substandardOutstandingSubTotal                       = 0;
                                        $doubtfulDueSubTotal                                  = 0;
                                        $doubtfulOutstandingSubTotal                          = 0;
                                        $badOutstandingSubTotal                               = 0;
                                        $totalOutstandingSubTotal                             = 0;
                                        $totalOverDueSubTotal                                 = 0;
                                        $overDueLoanOutstandingSubTotal                       = 0;
                                        $outstandingWithMoreThanTwoDueInstallmentsSubTotal    = 0;
                                        $savingBalanceOfOverdueLoaneeSubTotal                 = 0;
                                    ?>

                                    @foreach ($row['products'] as $product)
                                        <tr>
                                            <?php
                                                $totalOutstanding = $product['standardOutstanding'] + $product['watchfulOutstanding'] + $product['substandardOutstanding'] + $product['doubtfulOutstanding'] + $product['badOutstanding'];

                                                $totalOverDue = $product['watchfulDue'] + $product['substandardDue'] + $product['doubtfulDue'] + $product['badOutstanding'];

                                                $overDueLoanOutstanding = $product['watchfulOutstanding'] + $product['substandardOutstanding'] + $product['doubtfulOutstanding'] + $product['badOutstanding'];

                                                $standardOutstandingSubTotal      += $product['standardOutstanding'];
                                                $watchfulDueSubTotal              += $product['watchfulDue'];
                                                $watchfulOutstandingSubTotal      += $product['watchfulOutstanding'];
                                                $substandardDueSubTotal           += $product['substandardDue'];
                                                $substandardOutstandingSubTotal   += $product['substandardOutstanding'];
                                                $doubtfulDueSubTotal              += $product['doubtfulDue'];
                                                $doubtfulOutstandingSubTotal      += $product['doubtfulOutstanding'];
                                                $badOutstandingSubTotal           += $product['badOutstanding'];
                                                $totalOutstandingSubTotal         += $totalOutstanding;
                                                $totalOverDueSubTotal             += $totalOverDue;
                                                $overDueLoanOutstandingSubTotal   += $overDueLoanOutstanding;
                                                $outstandingWithMoreThanTwoDueInstallmentsSubTotal   += $product['outstandingWithMoreThanTwoDueInstallments'];
                                                $savingBalanceOfOverdueLoaneeSubTotal   += $product['savingBalanceOfOverdueLoanee'];

                                            ?>
                                            <td>{{ $product['productName'] }}</td>
                                            <td>{{ $product['standardOutstanding'] }}</td>
                                            <td>{{ $product['watchfulDue'] }}</td>
                                            <td>{{ $product['watchfulOutstanding'] }}</td>
                                            <td>{{ $product['substandardDue'] }}</td>
                                            <td>{{ $product['substandardOutstanding'] }}</td>
                                            <td>{{ $product['doubtfulDue'] }}</td>
                                            <td>{{ $product['doubtfulOutstanding'] }}</td>
                                            <td>{{ $product['badOutstanding'] }}</td>
                                            <td>{{ $totalOutstanding }}</td>
                                            <td>{{ $totalOverDue }}</td>
                                            <td>{{ $overDueLoanOutstanding }}</td>
                                            <td>
                                            {{ $product['outstandingWithMoreThanTwoDueInstallments'] }}
                                            </td>
                                            <td>{{ $product['savingBalanceOfOverdueLoanee'] }}</td>
                                        </tr>
                                    @endforeach
                                    <tr style="background-color: #cad6eb;">
                                        <?php 
                                            $standardOutstandingGrandTotal    += $standardOutstandingSubTotal;
                                            $watchfulDueGrandTotal            += $watchfulDueSubTotal;
                                            $watchfulOutstandingGrandTotal    += $watchfulOutstandingSubTotal;
                                            $substandardDueGrandTotal         += $substandardDueSubTotal;
                                            $substandardOutstandingGrandTotal += $substandardOutstandingSubTotal;
                                            $doubtfulDueGrandTotal            += $doubtfulDueSubTotal;
                                            $doubtfulOutstandingGrandTotal    += $doubtfulOutstandingSubTotal;
                                            $badOutstandingGrandTotal         += $badOutstandingSubTotal;
                                            $totalOutstandingGrandTotal       += $totalOutstandingSubTotal;
                                            $totalOverDueGrandTotal           += $totalOverDueSubTotal;
                                            $overDueLoanOutstandingGrandTotal   += $overDueLoanOutstandingSubTotal;
                                            $outstandingWithMoreThanTwoDueInstallmentsGrandTotal    += $outstandingWithMoreThanTwoDueInstallmentsSubTotal;
                                            $savingBalanceOfOverdueLoaneeGrandTotal                 += $savingBalanceOfOverdueLoaneeSubTotal;
                                        ?>
                                        <td>Sub Total</td>
                                        <td>{{ $standardOutstandingSubTotal }}</td>
                                        <td>{{ $watchfulDueSubTotal }}</td>
                                        <td>{{ $watchfulOutstandingSubTotal }}</td>
                                        <td>{{ $substandardDueSubTotal }}</td>
                                        <td>{{ $substandardOutstandingSubTotal }}</td>
                                        <td>{{ $doubtfulDueSubTotal }}</td>
                                        <td>{{ $doubtfulOutstandingSubTotal }}</td>
                                        <td>{{ $badOutstandingSubTotal }}</td>
                                        <td>{{ $totalOutstandingSubTotal }}</td>
                                        <td>{{ $totalOverDueSubTotal }}</td>
                                        <td>{{ $overDueLoanOutstandingSubTotal }}</td>
                                        <td>
                                        {{ $outstandingWithMoreThanTwoDueInstallmentsSubTotal }}</td>
                                        <td>{{ $savingBalanceOfOverdueLoaneeSubTotal }}</td>
                                    </tr>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background-color: #819bc4">
                                <td colspan="2">Grand Total</td>
                                <td>{{ $standardOutstandingGrandTotal }}</td>
                                <td>{{ $watchfulDueGrandTotal }}</td>
                                <td>{{ $watchfulOutstandingGrandTotal }}</td>
                                <td>{{ $substandardDueGrandTotal }}</td>
                                <td>{{ $substandardOutstandingGrandTotal }}</td>
                                <td>{{ $doubtfulDueGrandTotal }}</td>
                                <td>{{ $doubtfulOutstandingGrandTotal }}</td>
                                <td>{{ $badOutstandingGrandTotal }}</td>
                                <td>{{ $totalOutstandingGrandTotal }}</td>
                                <td>{{ $totalOverDueGrandTotal }}</td>
                                <td>{{ $overDueLoanOutstandingGrandTotal }}</td>
                                <td>{{ $outstandingWithMoreThanTwoDueInstallmentsGrandTotal }}</td>
                                <td>{{ $savingBalanceOfOverdueLoaneeGrandTotal }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>