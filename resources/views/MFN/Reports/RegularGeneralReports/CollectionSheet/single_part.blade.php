@php
$nth = ['1st', '2nd', '3rd', '4th', '5th'];
@endphp

<div class="row d-print-none text-right" data-html2canvas-ignore="true">
    <div class="col-lg-12">
        <a href="javascript:void(0)" id="btnPrint" style="background-color:transparent;border:none;" class="btnPrint mr-2">
            <i class="fa fa-print fa-lg" style="font-size:20px;"></i>
        </a>
        <a href="javascript:void(0)" style="background-color:transparent;border:none;" onclick="getPDF();">
            <i class="fa fa-file-pdf-o fa-lg" style="font-size:20px;"></i>
        </a>
        <a href="javascript:void(0)" style="background-color:transparent;border:none;" onclick="fnDownloadXLSX();">
            <i class="fa fa-file-excel-o fa-lg" style="font-size:20px; margin-left: 5px;"></i>
        </a>
    </div>
</div>

<div class="row text-center d-print-block">
    <div class="col-lg-12" style="color:#000;">
        <strong>{{ $branch->comp_name }}</strong><br>
        <span>{{ $branch->comp_addr }}</span><br>
        <strong>Samity Wise Monthly Loan & Saving Collection Sheet</strong><br>
    </div>
</div>

<br>

<div class="row">
     <div class="col-lg-12" style="font-size: 12px;">
        <!-- <div > -->
        <div class="reportBody">
            <span style="color: black; float: right;">
                <table class="table table-hover table-striped table-bordered w-full">
                    <thead>
                        <tr>
                            <th></th>
                            @foreach ($weekDates as $key => $weekDate)
                            <th>{{ $nth[$key] . ' Week, ' . date('d-m-Y', strtotime($weekDate)) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Savings Depositor:</td>
                            @for ($i = 0; $i < count($weekDates); $i++) <td>
                                </td>
                                @endfor
                        </tr>
                        <tr>
                            <td> No of Attendance:</td>
                            @for ($i = 0; $i < count($weekDates); $i++) <td>
                                </td>
                                @endfor
                        </tr>
                    </tbody>
                </table>

            </span>
        </div>
       
        <table style="border-collapse:separate;
        border-spacing:10px 10px;" class="table table-hover table-striped table-bordered w-full">
            <tbody>
                <tr>
                    <td>
                        <span style="color: black;" class="float-left">
                            <span style="font-weight: bold;">Branch: </span>
                            <span> {{ $branch->branch_name }}</span>
                        </span>
                    </td>
                    <td>
                        <span style="color: black;" class="float-left">
                            <span style="font-weight: bold;">Samity: </span>
                            <span> {{ $samity->name }}</span>
                        </span>
                    </td>
                    <td>

                        <span style="color: black;" class="float-left">
                            <span style="font-weight: bold;">Samity Day: </span>
                            <span> {{ $samity->samityDay }}</span>
                        </span>
                    </td>
                    <td>
                        <span style="color: black;" class="float-left">
                            <span style="font-weight: bold;">Samity Time: </span>
                            <span> {{ $samity->samityTime }}</span>
                        </span>
                    </td>
                    <td>

                        <span style="color: black;" class="float-left">
                            <span style="font-weight: bold;">Samity Opening Date: </span>
                            <span> {{ date('d-m-Y', strtotime($samity->openingDate)) }}</span>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span style="color: black;" class="float-left">
                            <span style="font-weight: bold;">Print Date: </span>
                            <span> {{ \Carbon\Carbon::now()->format('d-m-Y') }}</span>
                        </span>
                    </td>
                    <td>
                        <span style="color: black;" class="float-left">
                            <span style="font-weight: bold;">Product Category:</span>
                            <span>{{ $selectedLroductCategory }}</span>
                        </span>
                    </td>
                    <td>
                        <span style="color: black;" class="float-left">
                            <span style="font-weight: bold;">Product:</span>
                            <span>{{ $selectedLroduct }}</span>
                        </span>
                    </td>
                    <td>
                        <span style="color: black;" class="float-left">
                            <span style="font-weight: bold;">Field Officer:</span>
                            <span>{{ $fieldOfficer }}</span>
                        </span>
                    </td>
                    <td>
                        <span style="color: black;" class="float-left">
                            <span style="font-weight: bold;">Month:</span>
                            <span>{{ $month }}</span>
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>

    <!-- </div> -->
 </div>

    <div class="col-lg-12" style="font-size: 12px;">
    <div class="reportBody">
        <table class="table table-hover table-striped table-bordered w-full table-responsive">
            <thead class="text-center">
                <tr>
                    <th rowspan="3">SL</th>
                    <th colspan="{{ $showMemberCode + 3 }}">Member</th>
                    <th colspan="{{ count($weekDates) + 4 }}">Regular Savings</th>
                    <th colspan="{{ count($weekDates) + 6 }}">Loan Information</th>
                </tr>
                <tr>

                    @if ($showMemberCode)
                    <th rowspan="2">Code</th>
                    @endif
                    <th rowspan="2">Primary Product</th>
                    <th rowspan="2">Name</th>
                    <th rowspan="2">Spouse</th>
                    {{-- savings info --}}
                    <th rowspan="2">Sav. Acc.</th>
                    <th rowspan="2">Opening Balance</th>
                    <th colspan="{{ count($weekDates) }}">This Month Savings Collection</th>
                    <th colspan="2">Withdraw</th>
                    {{-- loan info --}}
                    <th rowspan="2">Disburse Date</th>
                    <th rowspan="2">Loan Amount</th>
                    <th rowspan="2">Cycle</th>
                    <th rowspan="2">Repay Week</th>
                    <th rowspan="2">Opening Outstanding</th>
                    <th rowspan="2">Opening Overdue</th>
                    <th colspan="{{ count($weekDates) }}">This Month loan Collection</th>
                </tr>
                <tr>

                    @foreach ($weekDates as $key => $weekDate)
                    <th>{{ $nth[$key] . ' Week, ' . date('d-m-Y', strtotime($weekDate)) }}</th>
                    @endforeach

                    <th>Date</th>
                    <th>Amount</th>

                    @foreach ($weekDates as $key => $weekDate)
                    <th>{{ $nth[$key] . ' Week, ' . date('d-m-Y', strtotime($weekDate)) }}</th>
                    @endforeach
                </tr>
            </thead>

            @php
            $slNo = 1;
            @endphp
            <tbody>
                @foreach ($members as $member)
                @php
                $memSavAccs = $savAccs->where('memberId', $member->id)->values();
                $memLoans = $loans->where('memberId', $member->id)->values();

                $rowSpan = max($memLoans->count(), $memSavAccs->count());
                @endphp

                @for ($i = 0; $i < $rowSpan; $i++) <tr>
                    @if ($i == 0)
                    <td rowspan="{{ $rowSpan }}">{{ $slNo++ }}</td>
                    @if ($showMemberCode)
                    <td rowspan="{{ $rowSpan }}">{{ $member->memberCode }}</td>
                    @endif
                    <td rowspan="{{ $rowSpan }}">{{ $member->primaryProduct }}</td>
                    <td rowspan="{{ $rowSpan }}">{{ $member->name }}</td>
                    <td rowspan="{{ $rowSpan }}">{{ $member->spouseName }}</td>
                    @endif
                    {{-- savings part --}}
                    @if (isset($memSavAccs[$i]))

                    <td>{{ $memSavAccs[$i]->accountCode }}</td>
                    <td class="text-right">
                        {{ number_format($savOpeningBalances->where('accountId', $memSavAccs[$i]->id)->sum('balance'), 2) }}
                    </td>
                    @foreach ($weekDates as $weekDate)
                    @php
                    $autoProcessAmount = $memSavAccs[$i]->autoProcessAmount;
                    if ($memSavAccs[$i]->collectionFrequencyId == 3) {
                    if($memSavAccs[$i]->depositSamityDay != $weekDate){
                    $autoProcessAmount = 0;
                    }
                    }
                    @endphp
                    <td class="text-right">{{ number_format($autoProcessAmount, 2) }}</td>
                    @endforeach
                    <td></td>
                    <td></td>

                    @else

                    @for ($j = 0; $j < count($weekDates) + 4; $j++) <td>
                        </td>
                        @endfor

                        @endif
                        {{-- end if of savings account exists --}}

                        {{-- Loan part --}}
                        @if (isset($memLoans[$i]))
                        <td class="text-center">{{ date('d-m-Y', strtotime($memLoans[$i]->disbursementDate)) }}</td>
                        <td class="text-right">{{ number_format($memLoans[$i]->loanAmount, 2) }}</td>
                        <td class="text-center">{{ $memLoans[$i]->loanCycle }}</td>
                        <td class="text-center">{{ $memLoans[$i]->repayWeek }}</td>
                        <td class="text-right">{{ number_format($memLoans[$i]->outstanding, 2) }}</td>
                        <td class="text-right">{{ number_format($memLoans[$i]->dueAmount, 2) }}</td>
                        @foreach ($weekDates as $weekDate)
                        @php
                        $installmentAmount = $weekDayLoanSchedules->where('loanId',
                        $memLoans[$i]->id)->where('installmentDate', $weekDate)->sum('installmentAmount');
                        @endphp
                        <td class="text-right">{{ number_format($installmentAmount, 2) }}</td>
                        @endforeach

                        @else

                        @for ($j = 0; $j < count($weekDates) + 6; $j++) <td>
                            </td>
                            @endfor

                            @endif
                            {{-- end if for loan exists --}}

                            </tr>
                            @endfor
                            @endforeach
            </tbody>
        </table>
     </div>
    </div>
    <br>

    <div class="row  d-print-block">
        <div class="col-lg-12" style="color:#000;">
            <strong>Product Wise Summary</strong><br><br>
            <span>Savings Information</span><br>
        </div>
    </div>

    <div class="col-lg-12" style="font-size: 12px;">
     <div class="reportBody">
        <table class="table table-hover table-striped table-bordered w-full">
            <thead>
                <tr>
                    <th>Savings Product</th>
                    @foreach ($weekDates as $key => $weekDate)
                    <th>{{ $nth[$key] . ' Week, ' . date('d-m-Y', strtotime($weekDate)) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php
                $savProducts = $savAccs->unique('savingsProductId')->pluck('savProductName', 'savingsProductId')->toArray();
                @endphp
                @foreach ($savProducts as $savProductId => $savProduct)
                <tr>
                    <td class="text-center">{{ $savProduct }}</td>
                    @foreach ($weekDates as $key => $weekDate)
                    @php
                    $autoProcessAmount = $savAccs->where('savingsProductId', $savProductId)->where('collectionFrequencyId',
                    '!=', 3)->sum('autoProcessAmount');
                    $autoProcessAmount += $savAccs->where('savingsProductId', $savProductId)->where('collectionFrequencyId',
                    3)->where('depositSamityDay', $weekDate)->sum('autoProcessAmount');
                    @endphp
                    <td class="text-right">{{ number_format($autoProcessAmount, 2) }}</td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    </div>

    <div class="row  d-print-block">
        <div class="col-lg-12" style="color:#000;">
            <span>Loan Information</span><br>
        </div>
    </div>

    <div class="col-lg-12" style="font-size: 12px;">
    <div class="reportBody">
        <table class="table table-hover table-striped table-bordered w-full">
            <thead>
                <tr>
                    <th class="text-center">Loan Product</th>
                    @foreach ($weekDates as $key => $weekDate)
                    <th>{{ $nth[$key] . ' Week, ' . date('d-m-Y', strtotime($weekDate)) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php
                $loanProducts = $loans->unique('productId')->pluck('loanProductName', 'productId')->toArray();
                @endphp
                @foreach ($loanProducts as $loanProductId => $loanProduct)
                <tr>
                    <td class="text-center">{{ $loanProduct }}</td>
                    @foreach ($weekDates as $key => $weekDate)
                    @php
                    $loanIds = $loans->where('productId', $loanProductId)->pluck('id')->toArray();
                    // dd($loanProductId);
                    $installmentAmount = $weekDayLoanSchedules->whereIn('loanId',
                    $loanIds)->where('installmentDate', $weekDate)->sum('installmentAmount');
                    @endphp
                    <td class="text-right">{{ number_format($installmentAmount, 2) }}</td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
</div>

<script src="{{asset('assets/js/print-js.js')}}"></script>