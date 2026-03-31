<div class="row text-center  d-print-block">
    <div class="col-lg-12" style="color:#000;">
        <strong>{{$branch->comp_name}}</strong><br>

        <span>{{$branch->comp_addr}}</span><br>
        <strong>Member Migration Balance</strong><br>
    </div>
</div>
<div class="row d-print-none text-right" data-html2canvas-ignore="true">
    <div class="col-lg-12">
        <a href="javascript:void(0)" onClick="window.print();" style="background-color:transparent;border:none;"
            class="btnPrint mr-2">
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

<div class="row">
    <div class="col-lg-12" style="font-size: 12px;">
        <span style="color: black; float: right;">
            <table style="border-collapse:separate;
            border-spacing:10px 10px;">
                <tbody>
                    <tr>
                        <td>
                            <span style="color: black;" class="float-left">
                                <span style="font-weight: bold;">Reporting Date: </span>
                                <span>
                                    {{ (\Carbon\Carbon::parse($date)->format('d-m-Y'))}}</span>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span style="color: black;" class="float-left">
                                <span style="font-weight: bold;">Print at : </span>
                                <span> {{(\Carbon\Carbon::now()->format('d-m-Y H:m:s'))}}</span>
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>

        </span>

        <table style="border-collapse:separate;
        border-spacing:10px 10px;">
            <tbody>
                <tr>
                    <td>
                        <span style="color: black;" class="float-left">
                            <span style="font-weight: bold;">Branch : </span>
                            <span> {{$branch->branch_code . ' - ' .$branch->branch_name}}</span>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span style="color: black;" class="float-left">
                            <span style="font-weight: bold;">Samity : </span>
                            <span> {{$samity->samityCode . ' - ' . $samity->name}}</span>
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<div class="table-responsive">
<table class="table table-striped table-bordered">
    <thead class="text-center">
        <tr>
            <th rowspan="2">SL#</th>
            <th rowspan="2">Member ID</th>
            <th rowspan="2">Member Name</th>
            <th rowspan="2">Father/Spouse Name</th>
            <th rowspan="2">Addmission Date</th>
            <th colspan="{{$withServiceCharge + 10}}">Loan Information</th>
            <th colspan="4">Savings Information</th>
        </tr>
        <tr>
            {{-- loan --}}
            <th>Disbursement Date</th>
            <th>First Repayment Date</th>
            <th>Loan Code</th>
            <th>Disburse Amount</th>
            <th>Loan Cycle</th>
            <th>No Of Installment</th>
            <th>Principal Recovery</th>
            @if($withServiceCharge)
            <th>Service Charge Recovery</th>
            @endif

            <th>Outstanding Balance</th>
            <th>Rebate</th>
            <th>Overdue</th>
            {{-- Savings --}}
            @foreach ($savProducts as $savProduct)
            <th>{{ $savProduct->name }}</th>
            @endforeach

        </tr>
    </thead>
    <tbody>
        @php
        $slNo = 1;
        $currentMemberId = 0;
        @endphp

        @foreach ($members as $member)

        @php

        $memberLoans = $loans->where('memberId', $member->id)->values();
        $rowSpan = count($memberLoans);
        $rowSpan = $rowSpan > 1 ? $rowSpan : 1;
        @endphp

        @for ($i = 0; $i < $rowSpan; $i++) <tr>
            @if ($i == 0)
            <td rowspan="{{ $rowSpan }}">{{ $slNo++ }}</td>
            <td rowspan="{{ $rowSpan }}">{{ $member->name }}</td>
            <td rowspan="{{ $rowSpan }}">{{ $member->memberCode }}</td>
            <td rowspan="{{ $rowSpan }}">{{ $member->spouseName }}</td>
            <td rowspan="{{ $rowSpan }}">{{ date('d-m-Y', strtotime($member->admissionDate)) }}</td>
            @endif

            {{-- loans --}}
            @if (isset($memberLoans[$i]))
            <td class="text-center">{{ date('d-m-Y', strtotime($memberLoans[$i]->disbursementDate)) }}</td>
            <td class="text-center">{{ date('d-m-Y', strtotime($memberLoans[$i]->firstRepayDate)) }}</td>
            <td class="text-center">{{ $memberLoans[$i]->loanCode }}</td>
            <td class="text-right">{{ number_format($memberLoans[$i]->loanAmount, 2) }}</td>
            <td class="text-center">{{ $memberLoans[$i]->loanCycle }}</td>
            <td class="text-center">{{ $memberLoans[$i]->numberOfInstallment }}</td>
            <td class="text-right">{{ number_format($memberLoans[$i]->principalRecovery, 2) }}</td>
            @if ($withServiceCharge)
            <td class="text-right">{{ number_format($memberLoans[$i]->serviceChargeRecovery, 2) }}</td>
            <td class="text-right">{{ number_format($memberLoans[$i]->totalOutStanding, 2) }}</td>
            @else
            <td class="text-right">{{ number_format($memberLoans[$i]->outStandingPrinciple, 2) }}</td>
            @endif
            <td class="text-right">{{ number_format($memberLoans[$i]->rebateAmount, 2) }}</td>
            @if ($withServiceCharge)
            <td class="text-right">{{ number_format($memberLoans[$i]->dueAmount, 2) }}</td>
            @else
            <td class="text-right">{{ number_format($memberLoans[$i]->dueAmountPrincipal, 2) }}</td>
            @endif
            @else
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            @if ($withServiceCharge)
            <td></td>
            @endif
            <td></td>
            <td></td>
            <td></td>
            @endif
            {{-- end loans --}}

            {{-- Savings Information --}}
            {{-- if member has multiple product loans, savings information should go with member primary product --}}
            @if (in_array($member->primaryProductId, $memberLoans->pluck('productId')->toArray()))

            @if ($member->primaryProductId == @$memberLoans[$i]->productId)
            @foreach ($savProducts as $savProduct)
            @php
            $savBalance = $deposits->where('memberId', $member->id)->where('savingsProductId',
            $savProduct->id)->sum('amount') - $withdraws->where('memberId', $member->id)->where('savingsProductId',
            $savProduct->id)->sum('amount');
            @endphp
            <td class="text-right">{{ number_format($savBalance, 2) }}</td>
            @endforeach
            @endif

            @else

            @if ($i == 0)
            @foreach ($savProducts as $savProduct)
            @php
            $savBalance = $deposits->where('memberId', $member->id)->where('savingsProductId',
            $savProduct->id)->sum('amount') - $withdraws->where('memberId', $member->id)->where('savingsProductId',
            $savProduct->id)->sum('amount');
            @endphp
            <td class="text-right">{{ number_format($savBalance, 2) }}</td>
            @endforeach
            @endif

            @endif
            {{-- end savings --}}

            </tr>
            @endfor
            @endforeach

            {{-- total --}}
            <tr style="font-weight: bold">
                <td colspan="8" class="text-center">Total</td>
                <td class="text-right">{{ number_format($loans->sum('loanAmount'), 2) }}</td>
                <td></td>
                <td></td>
                <td class="text-right">{{ number_format($loans->sum('principalRecovery'), 2) }}</td>
                @if ($withServiceCharge)
                <td class="text-right">{{ number_format($loans->sum('serviceChargeRecovery'), 2) }}</td>
                <td class="text-right">{{ number_format($loans->sum('totalOutStanding'), 2) }}</td>
                @else
                <td class="text-right">{{ number_format($loans->sum('outStandingPrinciple'), 2) }}</td>
                @endif
                <td class="text-right">{{ number_format($loans->sum('rebateAmount'), 2) }}</td>
                @if ($withServiceCharge)
                <td class="text-right">{{ number_format($loans->sum('dueAmount'), 2) }}</td>
                @else
                <td class="text-right">{{ number_format($loans->sum('dueAmountPrincipal'), 2) }}</td>
                @endif
                @foreach ($savProducts as $savProduct)
                @php
                $savBalance = $deposits->where('savingsProductId',
                $savProduct->id)->sum('amount') - $withdraws->where('savingsProductId',
                $savProduct->id)->sum('amount');
                @endphp
                <td class="text-right">{{ number_format($savBalance, 2) }}</td>
                @endforeach
            </tr>
            {{-- end total --}}
    </tbody>
</table>
</div>

