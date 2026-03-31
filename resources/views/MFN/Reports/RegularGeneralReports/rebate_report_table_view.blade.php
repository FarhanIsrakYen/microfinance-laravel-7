<?php

use App\Services\MfnService as MFN;
 
?>

<div class="row text-center  d-print-block">
    <div class="col-lg-12" style="color:#000;">
        <strong>{{$branchData->comp_name}}</strong><br>
        
        <span>{{$branchData->comp_addr}}</span><br>
        <strong>Rebate Report</strong><br>
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
                                <span style="font-weight: bold;">Reporting Date: </span>
                                <span> {{ (\Carbon\Carbon::parse($FromDate)->format('d-m-Y')).' to '.(\Carbon\Carbon::parse($toDate)->format('d-m-Y'))}}</span>
                            </span>
                        </td>
                       
                        
                    </tr>
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
                <tr>
                    <td>
                        
                        <span style="color: black;" class="float-left">
                            <span style="font-weight: bold;">Samity : </span>
                            <span> {{($samity_selected==null)? 'All': $samityData->where('id',$samity_selected)->first()->name}}</span>
                        </span>
                     </td>
                </tr>
                
            </tbody>
        </table>

    </div>
</div>


<table class="table w-full table-hover table-bordered table-striped">
    <thead class="text-center">
        <tr>
            <th rowspan="2" style="width: 3%;">SL</th>
            <th colspan="2" class="text-center">Member</th>
            <th rowspan="2">Loan Code</th>
            <th rowspan="2">Loan Product</th>
            <th rowspan="2">Samity Code</th>
            <th rowspan="2">Samity Name</th>
            <th rowspan="2">Rebate Date</th>
            <th rowspan="2" class="text-center">Loan Amount</th>
            <th rowspan="2" class="text-center">Rebate Amount</th>
        </tr>
        <tr>
            <th class="text-center">Name</th>
            <th class="text-center">Code</th>
        </tr>
    </thead>
    <?php $i = 0; $totalLoanAmount = 0; $totalRebateAmount = 0;
    ?>
    <tbody>
        @foreach ($rebates as $item)
            {{-- @php
                dd($item->transactionTypeId);
            @endphp --}}
            @php 
                $totalLoanAmount += $item->loanAmount;
                $totalRebateAmount += $item->rebateAmount;
            @endphp
            <tr>
                <td class="text-center">{{++$i}}</td>
                <td > {{ $item->memberName}}</td>
                <td class="text-center">{{ $item->memberCode}}</td>
                <td class="text-center">{{$item->loanCode}}</td>
                <td class="text-center">{{$item->loanProduct}}</td>
                <td class="text-center">{{$item->samityCode}}</td>
                <td>{{$item->samityName}}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($item->rebateDate)->format('d-m-Y')}}</td>
                <td class="text-right">{{number_format($item->loanAmount,2)}}</td>
                <td class="text-right">{{number_format($item->rebateAmount,2)}}</td>
                
            </tr>
        @endforeach
        
    </tbody>
    <tfoot>
        <tr>
            <td colspan="8" class="text-right"><b>TOTAL : </b></td>
            <td class="text-right"><b>{{number_format($totalLoanAmount,2)}}</b></td>
            <td class="text-right"><b>{{number_format($totalRebateAmount,2)}}</b></td>
        </tr>
    </tfoot>
</table>

                  

<script>


</script>