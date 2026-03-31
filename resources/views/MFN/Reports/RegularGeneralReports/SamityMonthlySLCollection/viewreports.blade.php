<?php

use App\Services\MfnService as MFN;
 
?>

<div class="row text-center  d-print-block">
    <div class="col-lg-12" style="color:#000;">
        <strong>{{$branchData->comp_name}}</strong><br>
        
        <span>{{$branchData->comp_addr}}</span><br>
        <strong>Samity Wise Monthly Loan & Saving Collection Sheet</strong><br>
    </div>
</div>
<div class="row d-print-none text-right" data-html2canvas-ignore="true">
    <div class="col-lg-12">
        <a href="javascript:void(0)" onClick="window.print();"
            style="background-color:transparent;border:none;" class="btnPrint mr-2">
            <i class="fa fa-print fa-lg" style="font-size:20px;"></i>
        </a>
        <a href="javascript:void(0)" style="background-color:transparent;border:none;"
            onclick="getPDF();">
            <i class="fa fa-file-pdf-o fa-lg" style="font-size:20px;"></i>
        </a>
        <a href="javascript:void(0)" style="background-color:transparent;border:none;"
            onclick="fnDownloadXLSX();">
            <i class="fa fa-file-excel-o fa-lg" style="font-size:20px; margin-left: 5px;"></i>
            {{-- <i class="fa fa-file-pdf-o fa-lg" style="font-size:20px;"></i> --}}
        </a>
    </div>
</div>

<div class="row">       

    <div class="col-lg-12" style="font-size: 12px;">
        <span style="color: black; float: right;">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th></th>
                        @foreach ($weekdays as $key => $value)
                        <?php $week ='';

                        if($key+1==1){
                            $week =($key+1).'st week';
                        }else if ($key+1 == 2 ) {
                            $week =($key+1).'nd week';
                        }else if ($key+1 == 3) {
                            $week =($key+1).'rd week';
                        }else {
                            $week =($key+1).'th week';
                        }


                        $value= \Carbon\Carbon::parse($value)->format('d-m-Y');

                        ?>

                        <th>{{$week .' '.$value}}</th>
                        @endforeach

                    </tr>
                </thead>
                <tbody>
                    <tr>
                        
                        <td>Savings Depositor:</td>
                        @foreach ($weekdays as $key => $value)
                        <td></td>
                        @endforeach

                    </tr>
                    <tr>
                        
                        <td> No of Attendance:</td>
                        @foreach ($weekdays as $key => $value)
                        <td></td>
                        @endforeach

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
                                <span style="font-weight: bold;">Branch Name: </span>
                                <span> {{$branchData->branch_name}}</span>
                            </span>
                    </td>
                    <td>
                        
                            <span style="color: black;" class="float-left">
                                <span style="font-weight: bold;">Samity Name: </span>
                                <span> {{$samityData->name}}</span>
                            </span>
                    </td>
                    <td>
                        
                        <span style="color: black;" class="float-left">
                            <span style="font-weight: bold;">Samity Day: </span>
                            <span> {{$samityData->samityDay}}</span>
                        </span>
                    </td>
                    <td>
                        
                        <span style="color: black;" class="float-left">
                            <span style="font-weight: bold;">Samity Time: </span>
                            <span> {{$samityData->samityTime}}</span>
                        </span>
                    </td>
                    <td>
                        
                        <span style="color: black;" class="float-left">
                            <span style="font-weight: bold;">Samity Opening Date: </span>
                            <span> {{$samityData->openingDate}}</span>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>
                            <span style="color: black;" class="float-left">
                                <span style="font-weight: bold;">Print Date: </span>
                                <span> {{\Carbon\Carbon::parse($sysDate)->format('d-m-Y')}}</span>
                            </span>
                    </td>
                    <td>
                        
                            <span style="color: black;" class="float-left">
                                <span style="font-weight: bold;">Product Category:</span>
                                <span>{{(!empty($product_cat))? $product_cat->name: 'All'}}</span>
                            </span>
                    </td>
                    <td>
                        
                        <span style="color: black;" class="float-left">
                            <span style="font-weight: bold;">Product:</span>
                            <span>{{(!empty($product))? $product->name: 'All'}}</span>
                        </span>
                    </td>
                    <td>
                        
                        <span style="color: black;" class="float-left">
                            <span style="font-weight: bold;">Field Worker Name:</span>
                            <span>{{$samityData->hrEmployee->emp_name}}</span>
                        </span>
                    </td>
                    <td>
                        
                        <span style="color: black;" class="float-left">
                            <span style="font-weight: bold;">Month:</span>
                            <span>{{$month}}</span>
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>

    </div>
</div>

<table class="table table-striped table-bordered">
    <thead class="text-center">
       
        <tr>
            <th rowspan="3">SL#</th>
            <th colspan="{{($member_code_show+3)}}">Member</th>
            <th colspan="{{$weekdays->count()+3}}">Regular Savings</th>
            <th colspan="{{$weekdays->count()+6}}">Loan Information</th>
        </tr>
        <tr>
           
            @if($member_code_show)
            <th rowspan="2">Code</th>
            @endif
            <th rowspan="2">Primary Product</th>
            <th rowspan="2">Name</th>
            <th rowspan="2">Spouse</th>
            {{-- savings info  --}}
            <th rowspan="2">Opening Balance</th>
            <th colspan="{{count($weekdays)}}">This Month Savings Collection</th>
            <th colspan="2">Withdraw</th>
            {{-- loan info  --}}
            <th rowspan="2">Disburse Date</th>
            <th rowspan="2">Loan Amount</th>
            <th rowspan="2">Cycle</th>
            <th rowspan="2">Repay Week</th>
            <th rowspan="2">Opening Outstanding</th>
            <th rowspan="2">Opening Overdue</th>
            <th colspan="{{count($weekdays)}}">This Month loan Collection</th>
        </tr>
        <tr>

            @foreach ($weekdays as $key => $value)
         
            <?php $week ='';

            if($key+1==1){
                $week =($key+1).'st week';
            }else if ($key+1 == 2 ) {
                $week =($key+1).'nd week';
            }else if ($key+1 == 3) {
                $week =($key+1).'rd week';
            }else {
                $week =($key+1).'th week';
            }

            $value= \Carbon\Carbon::parse($value)->format('d-m-Y');


            ?>

            <th>{{$week .' '.$value}}</th>
            @endforeach
        
            <th>Date</th>
            <th>Amount</th>

            @foreach ($weekdays as $key => $value)
         

            <?php 
            $week ='';

            if($key+1==1){
                $week =($key+1).'st week';
            }else if ($key+1 == 2 ) {
                $week =($key+1).'nd week';
            }else if ($key+1 == 3) {
                $week =($key+1).'rd week';
            }else {
                $week =($key+1).'th week';
            }


            $value= \Carbon\Carbon::parse($value)->format('d-m-Y');

            ?>

            <th>{{$week .' '.$value}}</th>
            @endforeach
        </tr>
    </thead>
    <?php $i = 0 ?>
    <tbody>
      
        @foreach ($memberDetails as $key => $value)

            <?php $rowspan=0;
            //dd($memberDetails);
            $rowspan = $value->max; 
            ?>
           <tr>
            <td rowspan="{{$rowspan}}" >{{++$i}}</td>
             @if($member_code_show)
             <td rowspan="{{$rowspan}}">{{$value->memberCode}}</td>
             @endif
            
            <td rowspan="{{$rowspan}}">{{$value->Product_Name}}</td>
            <td rowspan="{{$rowspan}}">{{$value->name}}</td>
            <td rowspan="{{$rowspan}}">{{$value->spouseName}}</td>
            
            
            @for ($j = 0; $j < $value->max; $j++)
                {{-- savings info --}}
                @if(!empty($value->SavingsInfo[$j]->accountCode))    
                
                <?php
                // $i = 0;
                $filters['accountId'] = $value->SavingsInfo[$j]->id;
                $filters['dateTo'] = $previousDayDate;
                // dd($filters);
                ?>
                
                <th class="text-right">{{number_format((MFN::getSavingsBalance($filters)), 2)}}</th>
                @foreach ($weekdays as $key => $weekday)
                {{-- <th class="text-right">{{number_format($value->SavingsInfo[$j]->autoProcessAmount, 2)}}</th> --}}
                <th>{{(MFN::getCloseWeekDate($weekday,$value->SavingsInfo[$j]->openingDate,$value->SavingsInfo[$j]->freequency))? number_format($value->SavingsInfo[$j]->autoProcessAmount, 2) : '0.00' }}</th>
                @endforeach
                <th class="text-center">-</th>
                <th class="text-right">-</th>
                @else
                <th class="text-center">-</th>
                @foreach ($weekdays as $key => $weekday)
                <th class="text-right">-</th>
                @endforeach
                <th class="text-center">-</th>
                <th class="text-right">-</th>
                @endif
               

               {{-- loan info --}}
               @if(!empty($value->LoanInfo[$j]->loanCode)) 

               <th class="text-center">{{$value->LoanInfo[$j]->disbursementDate}}</th>
               <th class="text-right">{{number_format($value->LoanInfo[$j]->loanAmount, 2)}}</th>
               <th class="text-center">{{$value->LoanInfo[$j]->loanCycle}}</th>
               <th class="text-center">Repayweek</th>
               <th class="text-right">{{number_format($value->LoanInfo[$j]->loanStatus[0]['outstanding'], 2)}}</th>
               <th class="text-right">{{number_format($value->LoanInfo[$j]->loanStatus[0]['dueAmount'], 2)}}</th>
               {{-- input type veriables --}}
               
               @foreach ($weekdays as $key => $weekday)

               <th class="text-right">{{($value->LoanInfo[$j]->calInstalment->where('loanId',$value->LoanInfo[$j]->id)->where('installmentDate',$weekday)->max('installmentAmount'))? number_format($value->LoanInfo[$j]->calInstalment->where('loanId',$value->LoanInfo[$j]->id)->where('installmentDate',$weekday)->max('installmentAmount'), 2) : '-' }}</th>
               @endforeach

            
               @else
               <th class="text-center">-</th>
               <th class="text-right">-</th>
               <th class="text-center">-</th>
               <th class="text-center">-</th>
               <th class="text-right">-</th>
               <th class="text-right">-</th>
               {{-- input type veriables --}}
               
               @foreach ($weekdays as $key => $weekday)
               <th class="text-right">-</th>
               @endforeach
               @endif
               
               
           </tr>
           @endfor
        
        @endforeach

        
    </tbody>
</table>