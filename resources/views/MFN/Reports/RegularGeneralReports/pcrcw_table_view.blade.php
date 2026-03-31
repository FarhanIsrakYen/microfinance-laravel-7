<?php
    $ttlAdditionalFee = 0;
    $ttlDisbursementAmount = 0;
    $ttlRegularRecoAmt = 0;
    $ttlRegular = 0;
    $ttlDue = 0;
    $ttlAdvance = 0;
    $ttlRebate = 0;
    $ttlLrPrincipal = 0;
    $ttlLrServiceCharge = 0;
    $ttlLrTotal = 0;
?>
<div id="reportTableDiv">
    <table class="table w-full table-hover table-bordered table-striped"  style="font-size: 9.5px">
        <thead class="text-center">
            <tr>
                <th colspan="2">Field Worker</th>
                <th colspan="2">Samity</th>
                <th rowspan="3">Component</th>
                <th colspan="{{ $savingProduct }}">Savings Collection</th>
                <th colspan="{{ $savingProduct }}">Interest On Savings</th>
                <th colspan="{{ $savingProduct }}">Savings Refund</th>
                <th rowspan="3">Additional Fee Collection</th>
                <th rowspan="3">Disbursement Amount</th>
                <th rowspan="3">Regular Recoverable</th>
                <th colspan="7">Loan Collection</th>
            </tr>
            <tr>
                <th rowspan="2">ID</th>
                <th rowspan="2">Name</th>

                <th rowspan="2">ID</th>
                <th rowspan="2">Name</th>  

                {!! $th !!}
                {!! $th !!}
                {!! $th !!}
                
                <th rowspan="2">Regular</th>
                <th rowspan="2">Due</th>
                <th rowspan="2">Advance</th>
                <th rowspan="2">Rebate</th>

                <th colspan="3">Total Collection</th>
            </tr>
            <tr>
                <th rowspan="1">Loan Receive (Principal)</th>
                <th rowspan="1">Loan Receive (Service Charge)</th>
                <th rowspan="1">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pcrData as $row)
                <tr>
                    <td rowspan="{{ count($row['samity']) + 1 }}">{{ $row['emp_code'] }}</td>
                    <td rowspan="{{ count($row['samity']) + 1 }}">{{ $row['emp_name'] }}</td>
                    @foreach ($row['samity'] as $samity)
                        <tr>
                            <td>{{ $samity['samityCode'] }}</td>
                            <td>{{ $samity['samityName'] }}</td>
                            <td>{{ $samity['component'] }}</td>
                            @foreach ($samity['savingProduct'] as $sprod)
                                <td>{{ $sprod }}</td>
                            @endforeach
                            @foreach ($samity['savingInterestAmount'] as $sInterest)
                                <td>{{ $sInterest }}</td>
                            @endforeach
                            @foreach ($samity['savingRefund'] as $sreFund)
                                <td>{{ $sreFund }}</td>
                            @endforeach
                            <td>
                                {{ $samity['additionalFee'] }}
                                <?php $ttlAdditionalFee += $samity['additionalFee'] ?>
                            </td>
                            <td>
                                {{ $samity['disbursementAmount'] }}
                                <?php $ttlDisbursementAmount += $samity['disbursementAmount'] ?>
                            </td>
                            <td>
                                {{ $samity['regularRecoAmt'] }}
                                <?php $ttlRegularRecoAmt += $samity['regularRecoAmt'] ?>
                            </td>
                            <td>
                                {{ $samity['regular'] }}
                                <?php $ttlRegular += $samity['regular'] ?>
                            </td>
                            <td>
                                {{ $samity['due'] }}
                                <?php $ttlDue += $samity['due'] ?>
                            </td>
                            <td>
                                {{ $samity['advance'] }}
                                <?php $ttlAdvance += $samity['advance'] ?>
                            </td>
                            <td>
                                {{ $samity['rebate'] }}
                                <?php $ttlRebate += $samity['rebate'] ?>
                            </td>
                            <td>
                                {{ $samity['lrPrincipal'] }}
                                <?php $ttlLrPrincipal += floatval($samity['lrPrincipal']) ?>
                            </td>
                            <td>
                                {{ $samity['lrServiceCharge'] }}
                                <?php $ttlLrServiceCharge += floatval($samity['lrServiceCharge']) ?>
                            </td>
                            <td>
                                {{ $samity['lrTotal'] }}
                                <?php $ttlLrTotal += floatval($samity['lrTotal']) ?>
                            </td>
                        </tr>
                    @endforeach
                </tr>            
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="{{ 5 + $savingProduct * 3 }}" class="text-right"><b>TOTAL: </b></td>
                <td class="text-right"><b>{{ $ttlAdditionalFee }}</b></td>
                <td class="text-right"><b>{{ $ttlDisbursementAmount }}</b></td>
                <td class="text-right"><b>{{ $ttlRegularRecoAmt }}</b></td>
                <td class="text-right"><b>{{ $ttlRegular }}</b></td>
                <td class="text-right"><b>{{ $ttlDue }}</b></td>
                <td class="text-right"><b>{{ $ttlAdvance }}</b></td>
                <td class="text-right"><b>{{ $ttlRebate }}</b></td>
                <td class="text-right"><b>{{ $ttlLrPrincipal }}</b></td>
                <td class="text-right"><b>{{ $ttlLrServiceCharge }}</b></td>
                <td class="text-right"><b>{{ $ttlLrTotal }}</b></td>
            </tr>
        </tfoot>
    </table>
</div>