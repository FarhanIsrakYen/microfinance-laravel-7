@php
    $totalOpening =0;
    $totalDeposit =0;
    $totalWithdraw =0;
    $totalClosing =0;
@endphp
<table class="table w-full table-hover table-bordered table-striped prTable">
    <thead class="text-center">
        <tr>
            <th width="5%">SL</th>
            <th>Product</th>
            <th>Savings Code</th>
            <th>Opening Balance</th>
            <th>Deposit</th>
            <th>Withdraw</th>
            <th>Closing Balance</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($savingProducts as $index => $sp)
            @php
                $accountForThisProducts = $accounts->where('savingsProductId',$sp->id);
                $subTotalOpening =0;
                $subTotalDeposit =0;
                $subTotalWithdraw =0;
                $subTotalClosing =0;
            @endphp
            <tr>
                <td rowspan="{{ count($accountForThisProducts)+1 }}"> {{$index + 1}} </td>
                <td rowspan="{{ count($accountForThisProducts)+1 }}"> {{$sp->name}} </td>
                @foreach ($accountForThisProducts as $account)
                    @php
                        // dd($withdraw->where('accountId',$account->id)->sum('amount'));
                        $opening = $openingDeposits->where('accountId',$account->id)->sum('amount') - $openingWithdraws->where('accountId',$account->id)->sum('amount');
                        $depositAmount = $deposit->where('accountId',$account->id)->sum('amount') ;
                        $withdrawAmount = $withdraw->where('accountId',$account->id)->sum('amount') ;
                        $closing = $opening + $depositAmount - $withdrawAmount;

                        $subTotalOpening += $opening;
                        $subTotalDeposit += $depositAmount;
                        $subTotalWithdraw += $withdrawAmount;
                        $subTotalClosing += $closing;

                        $totalOpening += $opening;
                        $totalDeposit += $depositAmount;
                        $totalWithdraw += $withdrawAmount;
                        $totalClosing += $closing;
                        
                    @endphp
                    <tr>
                    <td class="text-center">{{ $account->accountCode }}</td>
                    <td class="text-right">{{ number_format($opening, 2) }}</td>
                    <td class="text-right">{{ number_format($depositAmount, 2) }} </td>
                    <td class="text-right">{{ number_format($withdrawAmount, 2) }} </td>
                    <td class="text-right">{{ number_format($closing, 2) }} </td>
                    </tr>
                @endforeach
                <tr>
                    <td class="text-center" colspan="3"><b>SubTotal:</b></td>
                    <td class="text-right"><b class="ttl_ob">{{ number_format($subTotalOpening, 2)}}</b></td>
                    <td class="text-right"><b class="ttl_deposit">{{ number_format($subTotalDeposit, 2)}}</b></td>
                    <td class="text-right"><b class="ttl_withdraw">{{ number_format($subTotalWithdraw, 2)}}</b></td>
                    <td class="text-right"><b class="ttl_cb">{{ number_format($subTotalClosing, 2)}}</b></td>
                </tr>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td class="text-center" colspan="3"><b>Total:</b></td>
            <td class="text-right"><b class="ttl_ob">{{ number_format($totalOpening, 2)}}</b></td>
            <td class="text-right"><b class="ttl_deposit">{{ number_format($totalDeposit, 2)}}</b></td>
            <td class="text-right"><b class="ttl_withdraw">{{ number_format($withdrawAmount, 2)}}</b></td>
            <td class="text-right"><b class="ttl_cb">{{ number_format($totalClosing, 2)}}</b></td>
        </tr>
    </tfoot>
</table>