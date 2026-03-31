<?php

use App\Services\MfnService as MFN;
 
?>
<div class="row text-center  d-print-block">
    <div class="col-lg-12" style="color:#000;">
        <strong>{{$branchData->comp_name}}</strong><br>
        
        <span>{{$branchData->comp_addr}}</span><br>
        <strong>PKSF-POMIS -1 Report</strong><br>
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
            <table style="border-collapse:separate;
            border-spacing:10px 10px;">
                
                <tbody>
                    
                    <tr>
                        <td>
                            
                            <span style="color: black;" class="float-left">
                                <span style="font-weight: bold;">Print Date : </span>
                                <span> {{(\Carbon\Carbon::parse($sysDate)->format('d-m-Y'))}}</span>
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
                                <span style="font-weight: bold;">Branch :  </span>
                                <span> {{$branchData->branch_name .' & '.$branchData->branch_code}}</span>
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
            <th colspan="3" rowspan="3">Component</th>
            <th  colspan="2">Opening Balance</th>
            <th  colspan="2">Current Month Savings Collection</th>
            <th  colspan="2">Current Month Savings Withdraw</th>
            <th  colspan="3">Closing Balance</th>
        </tr>
        <tr>
           
            <th>Male</th>
            <th>Female</th>
            <th>Male</th>
            <th>Female</th>
            <th>Male</th>
            <th>Female</th>
            <th>Male</th>
            <th>Female</th>
            <th>Total</th>
        </tr>
        
    </thead>
    <?php //$multiply = 1 ; ?>
    <tbody>
      
        @foreach ($pksfIDs as $key => $value)
        <tr>
        <td  rowspan="{{$queryData->where('fundingOrgId',$value)->count('branchId')/2 + $queryData->where('fundingOrgId',$value)->unique('loanProductId')->count('loanProductId')}}" >{{$queryData->where('fundingOrgId',$value)->first()->PksfName}}</td>
      
        <?php $loanProducts = $queryData->where('fundingOrgId',$value)->unique('loanProductId');?>
           @foreach ($loanProducts as $key2 => $item)
           
            
           
           @if (!isset($loanProducts[$key2-1]->loanProductId) )
            <td rowspan="{{$queryData->where('fundingOrgId',$value)->where('loanProductId', $item->loanProductId)->count('loanProductId')/2 +1}}"> {{$queryData->where('fundingOrgId',$value)->where('loanProductId', $item->loanProductId)->first()->Lname}}</td>
           
           @elseif( isset($loanProducts[$key2-1]->loanProductId) && $loanProducts[$key2]->loanProductId != $loanProducts[$key2-1]->loanProductId)
           <tr>
           <td rowspan="{{$queryData->where('fundingOrgId',$value)->where('loanProductId', $item->loanProductId)->count('loanProductId')/2 +1}}"> {{$queryData->where('fundingOrgId',$value)->where('loanProductId', $item->loanProductId)->first()->Lname}}</td>
         
            @endif
            
            <?php $savingsProducts = $queryData->where('fundingOrgId',$value)->where('loanProductId', $item->loanProductId)->unique('savingsProductId');?>
            
            @foreach ($savingsProducts as $key3 => $sav)
                @if (!isset($savingsProducts[$key3-1]->savingsProductId))
                <?php //dd($queryData->where('fundingOrgId',$value)->where('loanProductId', $item->loanProductId)->where('savingsProductId', $sav->savingsProductId)->unique('savingsProductId')->count('savingsProductId')); ?>
                <td rowspan="{{$queryData->where('fundingOrgId',$value)->where('loanProductId', $item->loanProductId)->where('savingsProductId', $sav->savingsProductId)->unique('savingsProductId')->count('savingsProductId')}}">{{$queryData->where('fundingOrgId',$value)->where('loanProductId', $item->loanProductId)->where('savingsProductId',$sav->savingsProductId)->first()->SPname}}</td>   
        
                @elseif( isset($savingsProducts[$key3-1]->savingsProductId) && $savingsProducts[$key3]->savingsProductId != $savingsProducts[$key3-1]->savingsProductId)
               <tr>
               <td rowspan="{{$queryData->where('fundingOrgId',$value)->where('loanProductId', $item->loanProductId)->where('savingsProductId', $sav->savingsProductId)->unique('savingsProductId')->count('savingsProductId')}}"> {{$queryData->where('fundingOrgId',$value)->where('loanProductId', $item->loanProductId)->where('savingsProductId',$sav->savingsProductId)->first()->SPname}}</td> 
                @endif
                
                <td class="text-right"> {{ number_format($queryData->where('fundingOrgId',$value)->where('loanProductId', $item->loanProductId)->where('savingsProductId',$sav->savingsProductId)->where('gender','Male')->sum('openingBalance'), 2) }}</td>
                <td class="text-right"> {{ number_format($queryData->where('fundingOrgId',$value)->where('loanProductId', $item->loanProductId)->where('savingsProductId',$sav->savingsProductId)->where('gender','Female')->sum('openingBalance'), 2)}}</td>

                <td class="text-right"> {{ number_format($queryData->where('fundingOrgId',$value)->where('loanProductId', $item->loanProductId)->where('savingsProductId',$sav->savingsProductId)->where('gender','Male')->sum('deposit'), 2) }}</td>
                <td class="text-right"> {{ number_format($queryData->where('fundingOrgId',$value)->where('loanProductId', $item->loanProductId)->where('savingsProductId',$sav->savingsProductId)->where('gender','Female')->sum('deposit'), 2) }}</td>
                
                <td class="text-right"> {{ number_format($queryData->where('fundingOrgId',$value)->where('loanProductId', $item->loanProductId)->where('savingsProductId',$sav->savingsProductId)->where('gender','Male')->sum('withdraw'), 2) }}</td>
                <td class="text-right"> {{ number_format($queryData->where('fundingOrgId',$value)->where('loanProductId', $item->loanProductId)->where('savingsProductId',$sav->savingsProductId)->where('gender','Female')->sum('withdraw'), 2) }}</td>


                <td class="text-right"> {{ number_format($queryData->where('fundingOrgId',$value)->where('loanProductId', $item->loanProductId)->where('savingsProductId',$sav->savingsProductId)->where('gender','Male')->sum('closingBalance'), 2)}}</td>
                <td class="text-right"> {{ number_format($queryData->where('fundingOrgId',$value)->where('loanProductId', $item->loanProductId)->where('savingsProductId',$sav->savingsProductId)->where('gender','Female')->sum('closingBalance'), 2)}}</td>

                <td class="text-right"> {{ number_format($queryData->where('fundingOrgId',$value)->where('loanProductId', $item->loanProductId)->where('savingsProductId',$sav->savingsProductId)->where('gender','Male')->sum('closingBalance') + $queryData->where('fundingOrgId',$value)->where('loanProductId', $item->loanProductId)->where('savingsProductId',$sav->savingsProductId)->where('gender','Female')->sum('closingBalance'), 2)}}</td>
            </tr>
            @endforeach

           <tr style="background-color:#b2b6b6;">
               <td class="text-center">total</td>
            <td class="text-right"> {{ number_format($queryData->where('fundingOrgId',$value)->where('loanProductId', $item->loanProductId)->where('gender','Male')->sum('openingBalance'), 2)}}</td>
            <td class="text-right"> {{ number_format($queryData->where('fundingOrgId',$value)->where('loanProductId', $item->loanProductId)->where('gender','Female')->sum('openingBalance'), 2)}}</td>

            <td class="text-right"> {{ number_format($queryData->where('fundingOrgId',$value)->where('loanProductId', $item->loanProductId)->where('gender','Male')->sum('deposit'), 2)}}</td>
            <td class="text-right"> {{ number_format($queryData->where('fundingOrgId',$value)->where('loanProductId', $item->loanProductId)->where('gender','Female')->sum('deposit'), 2)}}</td>
            
            <td class="text-right"> {{ number_format($queryData->where('fundingOrgId',$value)->where('loanProductId', $item->loanProductId)->where('gender','Male')->sum('withdraw'), 2)}}</td>
            <td class="text-right"> {{ number_format($queryData->where('fundingOrgId',$value)->where('loanProductId', $item->loanProductId)->where('gender','Female')->sum('withdraw'), 2)}}</td>


            <td class="text-right"> {{ number_format($queryData->where('fundingOrgId',$value)->where('loanProductId', $item->loanProductId)->where('gender','Male')->sum('closingBalance'), 2)}}</td>
            <td class="text-right">  {{ number_format($queryData->where('fundingOrgId',$value)->where('loanProductId', $item->loanProductId)->where('gender','Female')->sum('closingBalance'), 2)}}</td>

            <td class="text-right"> {{ number_format($queryData->where('fundingOrgId',$value)->where('loanProductId', $item->loanProductId)->where('gender','Male')->sum('closingBalance') + $queryData->where('fundingOrgId',$value)->where('loanProductId', $item->loanProductId)->where('gender','Female')->sum('closingBalance'), 2)}}</td>
           </tr>
           

            
           
           
           @endforeach
           
        </tr>
        <tr>
            

            
        
        @endforeach

        
    </tbody>
</table>