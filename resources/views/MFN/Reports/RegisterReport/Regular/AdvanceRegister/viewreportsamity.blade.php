<?php

use App\Services\MfnService as MFN;
 
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
                                    <span style="font-weight: bold;">Reporting Date to: </span>
                                    <span> {{ (\Carbon\Carbon::parse($FromDate)->format('d-m-Y'))}}</span>
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


<table class="table table-striped table-bordered">
    <thead class="text-center">
       
        <tr>
            <th >SL#</th>
            <th >Member Code</th>
            <th >Member Name</th>
            <th >Component Name</th>
            <th >Loan Code</th>
            <th >Loan Disburse Date</th>
            <th >Disburse Amount</th>
            <th >Present Loan Amount</th>
            <th >Advance Amount</th>
        </tr>
       
    </thead>
    <?php $i = 0; $temp = ''; $prodcount=0;?>
    <tbody>

        @foreach ($loans->where('samityId',$samity_selected) as $index => $Value)
       
        @if (!isset($loans[$index-1]->memberId) )
        <tr>
            <td rowspan="{{$loans->where('memberId',$loans[$index]->memberId)->count()}}">{{++$i}}</td>
            <td rowspan="{{$loans->where('memberId',$loans[$index]->memberId)->count()}}">{{$Value->memberCode}}</td>
            <td rowspan="{{$loans->where('memberId',$loans[$index]->memberId)->count()}}">{{$Value->member}}</td>
        @else
            @if ($loans[$index-1]->memberId != $loans[$index]->memberId) 
            <tr>
                <td rowspan="{{$loans->where('memberId',$loans[$index]->memberId)->count()}}">{{++$i}}</td>
                <td rowspan="{{$loans->where('memberId',$loans[$index]->memberId)->count()}}">{{$Value->memberCode}}</td>
                <td rowspan="{{$loans->where('memberId',$loans[$index]->memberId)->count()}}">{{$Value->member}}</td>
        
            @endif

        @endif

        
            
            {{-- <td>{{$Value->memberCode}}</td>
            <td>{{$Value->member}}</td> --}}
            <td>{{$Value->component}}</td>
            <td>{{$Value->loanCode}}</td>
            <td>{{$Value->disbursementDate}}</td>
            <td>{{$Value->disburseAmount}}</td>
            <td>{{$Value->loanAmount}}</td>
            <td>{{$Value->adAmount}}</td>
        </tr>

     


       
        @endforeach

        
    </tbody>
</table>

<br><br>

@if($prodCatWise == 1)

<h6>Product Wise</h6>

    <table class="table table-striped table-bordered">
        <thead class="text-center">
        
            <tr>
                <th >SL#</th>
                <th >Component Name</th>
                <th >Disburse Amount</th>
                <th >Present Loan Amount</th>
                <th >Advance Amount</th>
            </tr>
        
        </thead>
        <?php $i=0; $loans = $loans->where('samityId',$samity_selected)->groupBy('component');?>

        @foreach ($loans as $index => $Value)
        <?php //dd($Value);?>
        <tr>
            <td>{{++$i}}</td>
            <td>{{$Value->first()->component}}</td>
            <td>{{$Value->sum('disburseAmount')}}</td>
            <td>{{$Value->sum('loanAmount')}}</td>
            <td>{{$Value->sum('adAmount')}}</td>
        </tr>
        @endforeach
    </table>
@else
<h6>Category Wise</h6>
    <table class="table table-striped table-bordered">
        <thead class="text-center">
        
            <tr>
                <th >SL#</th>
                <th >Category Name</th>
                <th >Disburse Amount</th>
                <th >Present Loan Amount</th>
                <th >Advance Amount</th>
            </tr>
        
        </thead>
        <?php $i=0; $loans = $loans->where('samityId',$samity_selected)->groupBy('category');?>

        @foreach ($loans as $index => $Value)
        <?php //dd($Value);?>
        <tr>
            <td>{{++$i}}</td>
            <td>{{$Value->first()->category}}</td>
            <td>{{$Value->sum('disburseAmount')}}</td>
            <td>{{$Value->sum('loanAmount')}}</td>
            <td>{{$Value->sum('adAmount')}}</td>
        </tr>
        @endforeach
    </table>
@endif
                  

<script>

</script>