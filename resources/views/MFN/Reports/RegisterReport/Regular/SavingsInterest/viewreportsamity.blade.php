<?php

use App\Services\MfnService ;
 
?>

<div class="row text-center  d-print-block">
    <div class="col-lg-12" style="color:#000;">
        <strong>{{$branchData->comp_name}}</strong><br>
        
        <span>{{$branchData->comp_addr}}</span><br>
        <strong>Advance Register Report</strong><br>
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
                                <span style="font-weight: bold;">Branch	:  </span>
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



<table class="table table-striped table-bordered">
    <thead class="text-center">
       
        <tr>
            <th >SL#</th>
            <th >Member Code</th>
            <th >Member Name</th>
            <th >Savings Code</th>
            <th> Savings Opening Date</th>
            <th >Interest Amount</th>
            <th >balance</th>
        </tr>
       
    </thead>
    <?php $i = 0; $SamitySavings = $savings->groupBy('memberId'); ?>
    <tbody>
        @foreach ($SamitySavings as $index => $Value)
        <?php   $filters['memberId']=$Value->first()->memberId;
        if (!empty($toDate)) {
            $filters['dateTo']=$toDate;
         
        }
?>
        <tr>
            

       
            <td>{{++$i}}</td>
            <?php //dd($samityData->where('id',$Value->samityId)->first()->name);?>
            <td>{{ $MemberData->where('id',$Value->first()->memberId)->first()->name}}</td>
            <td>{{ $MemberData->where('id',$Value->first()->memberId)->first()->memberCode}}</td>
            <td>{{ $Value->first()->accountCode}}</td>
            <td>{{ $Value->first()->openingDate}}</td>
           
            <td class="text-right">{{number_format($Value->sum('amount'), 2)}}</td>
            <td class="text-right">{{ number_format((MfnService::getSavingsBalance($filters)), 2)}}</td>
        </tr>

     


       
        @endforeach

        
    </tbody>
</table>

<br><br>

<h6>Product Wise</h6>
    <table class="table table-striped table-bordered">
        <thead class="text-center">
        
            <tr>
                <th >SL#</th>
                <th >Product Name</th>
                <th >Interest Amount</th>
            </tr>
        
        </thead>
        <?php $i=0; $savings = $savings->groupBy('savingsProductId');?>

        @foreach ($savings as $index => $Value)
        <?php //dd($Value);?>
        <tr>
            <td>{{++$i}}</td>
            <td>{{$Value->first()->product}}</td>
            <td class="text-right">{{number_format($Value->sum('amount'), 2)}}</td>
        </tr>
        @endforeach
    </table>
       

<script>

</script>