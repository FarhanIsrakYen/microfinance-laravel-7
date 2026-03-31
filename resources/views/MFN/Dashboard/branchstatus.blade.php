@php
    $totalMembers =0;
    $totalBorrowers =0;
    $totalDue =0;
    $totalDuetoday =0;
    $totalOutstanding =0;
@endphp

<div class="table-responsive">
    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
        <thead>
            <tr>
                <th width="3%">SL</th>
                <th>Branch Name</th>
                <th>Soft Opening Date</th>
                <th>Branch Date</th>
                <th>Total Members</th>
                <th>Total Borrowers</th>
                <th>Total Due</th>
                <th>Today's Due</th>
                <th>Total Outstnading </th>
                <th width="5%">LAG</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $index => $branchData)
                <tr>
                    <td class="text-center">{{ $branchData['sl'] }}</td>
                    <td>{{ $branchData['branch_name'] }}</td>
                    <td class="text-center">{{ $branchData['soft_start_date'] }}</td>
                    <td class="text-center">{{ $branchData['branch_date'] }}</td>
                    <td class="text-center">{{ number_format($branchData['total_members']) }}</td>
                    <td class="text-center">{{ number_format($branchData['total_borrowers']) }}</td>
                    <td class="text-right">{{  number_format( $branchData['total_due'], 2) }}</td>
                    <td class="text-right">{{  number_format( $branchData['due_today'], 2) }}</td>
                    <td class="text-right">{{  number_format( $branchData['total_outstanding'], 2) }}</td>
                    <td class="text-center">{!! $branchData['LAG'] !!}</td>
                </tr>

                @php
                    $totalMembers +=  $branchData['total_members'];
                    $totalBorrowers +=  $branchData['total_borrowers'];
                    $totalDue += $branchData['total_due'];
                    $totalDuetoday += $branchData['due_today'];
                    $totalOutstanding += $branchData['total_outstanding'];
                @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align: center"><b>Total</b></td>
                <td style="text-align:center;"><b>{{ number_format($totalMembers, 2) }}</b></td>
                <td style="text-align:center;"><b>{{ number_format($totalBorrowers, 2) }}</b></td>
                <td style="text-align:right;"><b>{{ number_format($totalDue, 2) }}</b></td>
                <td style="text-align:right;"><b>{{ number_format($totalDuetoday, 2) }}</b></td>
                <td style="text-align:right;"><b>{{ number_format($totalOutstanding, 2) }}</b></td>
                <td style="text-align:center;"></td>
            </tr>
        </tfoot>
    </table>
</div>