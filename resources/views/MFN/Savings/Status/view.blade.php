@extends('Layouts.erp_master')
@section('content')
<div class="row">
   
    <div class="col-md-12 text-center"><h3>Savings Status</h3></div>
</div>
<div class="row">
    <div class="col-md-4">
        <table style="color: #000;">
            <tbody>
                <tr>
                    <th>Name</th>
                    <th>: </th>
                    <td>{{$memberDetails->name}}</td>
                </tr>
                <tr>
                    <th>Member Code</th>
                    <th>: </th>
                    <td>{{$memberDetails->memberCode}}</td>
                </tr>
                <tr>
                    <th>Account Code</th>
                    <th>: </th>
                    <td>{{$acc->accountCode}}</td>
                </tr>
                <tr>
                    <th>Date Range</th>
                    <th>: </th>
                    @if (count($transactionDates) > 0)
                    <td>{{$transactionDates[0]}} - {{$transactionDates[(count($transactionDates)-1)]}}</td>                        
                    @else
                    <td>- - -</td>
                    @endif
                </tr>
               
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="table-responsive">
    <table class="table w-full table-hover table-bordered table-striped clsDataTable" id="Table">
        <thead>
            <tr>
                <th style="width: 3%;" class="text-center">SL</th>
                <th class="text-center">Date</th>
                <th class="text-center">Deposite</th>
                <th class="text-center">Interest</th>
                <th class="text-center"> Withdraw </th>
                <th class="text-center"> Balance</th>
            </tr>
        </thead>
        <tbody>

            <?php $balance=0;
             $i=0; 
             $opening = 0 ;
            if(!empty($deposits->where('transactionTypeId',4)->sum('amount'))){

                $opData = $deposits->where('transactionTypeId',4)->first();
                $opening = true;
                $balance= $opData->amount;
            }
            ?>

            @if($opening== true)
            <tr>
                <td></td>
                <td>Opening balance</td>
                <td class="text-right">{{ number_format($opData->amount, 2)}}</td>
                <td class="text-right">{{number_format(0, 2)}}</td>
                <td class="text-right">{{number_format(0, 2)}}</td>
                <td class="text-right">{{number_format($balance, 2)}}</td>
             
            </tr>
            @endif

            @foreach ($transactionDates as $key => $transactionDate) 
                <?php $deposit = $deposits->where('date', $transactionDate)->first();
                $withdraw = $withdraws->where('date', $transactionDate)->first();
                // dd();
                $dip = 0 ;
                $wid = 0 ;
                $interest = 0 ;
                $type = '';
                
    
                if(!empty($deposit)){
                    $dip = $deposits->where('date', $transactionDate)->whereIn('transactionTypeId',[1, 2, 6, 7])->sum('amount');
                    $balance += $dip;
    
                    $type = $deposit->depositeType->name;
                    if(!empty($deposits->where('date', $transactionDate)->whereIn('transactionTypeId',[ 3, 5])->sum('amount'))){
                        $interest = $deposits->where('date', $transactionDate)->whereIn('transactionTypeId',[ 3, 5])->sum('amount');
                        $balance += $interest;
                    }
                   
                }
    
                if(!empty($withdraw)){
                    $wid = $withdraws->where('date', $transactionDate)->whereIn('transactionTypeId',[1, 2, 4, 6, 7])->sum('amount');
                    $balance -= $wid;
                    $type = $withdraw->depositeType->name;
                }
                
                    ?>
                {{-- $html .='<tr>';
                $html .='<td>'.($key+1).'</td>';
                $html .='<td>'.$transactionDate.'</td>';
                $html .='<td>'.$type.'</td>';
                $html .='<td>'.$dip.'</td>';
                $html .='<td>'.$wid.'</td>';
                $html .='<td>'.$balance.'</td>';
                $html .='</tr>'; --}}
                
                 <tr>
                    <td>{{++$i}}</td>
                    <td>{{$transactionDate}}</td>
                    <td class="text-right">{{ number_format($dip, 2)}}</td>
                    <td class="text-right">{{ number_format($interest, 2)}}</td>
                    <td class="text-right">{{  number_format($wid, 2)}}</td>
                    <td class="text-right">{{  number_format($balance, 2)}}</td>
                 
                  </tr>
            @endforeach
         
        </tbody>
    </table>
</div>
<div class="row align-items-center">
    <div class="col-lg-12">
        <div class="form-group d-flex justify-content-center">
            <div class="example example-buttons">
                <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round d-print-none">Back</a>
                <a href="javascript:void(0)" onClick="window.print();" class="btn btn-default btn-round d-print-none clsPrint">Print</a>
            </div>
        </div>
    </div>
</div>

<script>

</script>

@endsection
