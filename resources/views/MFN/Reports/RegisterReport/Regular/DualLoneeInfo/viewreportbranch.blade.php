<?php

use App\Services\MfnService as MFN;
 
?>

<div class="row text-center  d-print-block">
    <div class="col-lg-12" style="color:#000;">
        <strong>{{$branchData->comp_name}}</strong><br>
        
        <span>{{$branchData->comp_addr}}</span><br>
        <strong>Loan Disburse Register</strong><br>
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
            <th rowspan="2">SL#</th>
            <th rowspan="2">Samity Code</th>
            <th rowspan="2">Samity Name</th>
            <th rowspan="2">Member Code</th>
            <th rowspan="2">Member Name</th>
            <th colspan="4">Loan Collection Information</th>
            <th rowspan="2">Savings Balance</th>
        </tr>
        <tr>
            {{-- loan --}}
            <th >Loan Code</th>
            <th >Disbursement Date</th>
            <th >Total Amount</th>
            <th >Outstanding Amount</th>
         
        </tr>
    </thead>
    <?php $i = 0; $temp = ''; $prodcount=0;?>
    <tbody>

        @foreach ($LoanData as $index => $Value)
        <?php  $filters['loanId']=$Value->id;  $filtersSavings['memberId']=$Value->memberId; ?>

        @if($index==0)
        <tr>
            <td rowspan="{{$LoanData->where('samityId',$Value->samityId)->count()}}">{{++$i}}</td>
            <td rowspan="{{$LoanData->where('samityId',$Value->samityId)->count()}}">{{$samityData->where('id',$Value->samityId)->first()->samityCode}}</td>
            <td rowspan="{{$LoanData->where('samityId',$Value->samityId)->count()}}">{{$samityData->where('id',$Value->samityId)->first()->name}}</td>
            <td rowspan="{{$LoanData->where('samityId',$Value->samityId)->where('memberId',$Value->memberId)->count()}}">{{$Value->m_code}}</td>
            <td rowspan="{{$LoanData->where('samityId',$Value->samityId)->where('memberId',$Value->memberId)->count()}}">{{$Value->m_name}}</td>
            <td>{{$Value->loanCode}}</td>
            <td>{{(\Carbon\Carbon::parse($Value->disbursementDate)->format('d-m-Y'))}}</td>
            <td>{{ number_format($Value->loanAmount, 2)}}</td>
            <td>{{ number_format(MFN::getLoanStatus($filters)[0]['outstanding'], 2) }}</td>
            <td rowspan="{{$LoanData->where('samityId',$Value->samityId)->where('memberId',$Value->memberId)->count()}}">{{number_format(MFN::getSavingsBalance($filtersSavings), 2)}}</td>
        @else
        <tr>
            @if ($LoanData[$index-1]->samityId == $LoanData[$index]->samityId)

                @if ($LoanData[$index-1]->memberId == $LoanData[$index]->memberId)

                <td>{{$Value->loanCode}}</td>
                <td>{{$Value->disbursementDate}}</td>
                <td>{{ number_format($Value->loanAmount, 2)}}</td>
                <td>{{ number_format(MFN::getLoanStatus($filters)[0]['outstanding'], 2) }}</td>

              
                    
                @else
                <td rowspan="{{$LoanData->where('samityId',$Value->samityId)->where('memberId',$Value->memberId)->count()}}">{{$Value->m_code}}</td>
                <td rowspan="{{$LoanData->where('samityId',$Value->samityId)->where('memberId',$Value->memberId)->count()}}">{{$Value->m_name}}</td>
                <td>{{$Value->loanCode}}</td>
                <td>{{ number_format($Value->loanAmount, 2)}}</td>
                <td>{{ number_format(MFN::getLoanStatus($filters)[0]['outstanding'], 2) }}</td>
                <td rowspan="{{$LoanData->where('samityId',$Value->samityId)->where('memberId',$Value->memberId)->count()}}">{{number_format(MFN::getSavingsBalance($filtersSavings), 2)}}</td>
                @endif
                
            @else
            <td rowspan="{{$LoanData->where('samityId',$Value->samityId)->count()}}">{{++$i}}</td>
            <td rowspan="{{$LoanData->where('samityId',$Value->samityId)->count()}}">{{$samityData->where('id',$Value->samityId)->first()->samityCode}}</td>
            <td rowspan="{{$LoanData->where('samityId',$Value->samityId)->count()}}">{{$samityData->where('id',$Value->samityId)->first()->name}}</td>
            <td rowspan="{{$LoanData->where('samityId',$Value->samityId)->where('memberId',$Value->memberId)->count()}}">{{$Value->m_code}}</td>
            <td rowspan="{{$LoanData->where('samityId',$Value->samityId)->where('memberId',$Value->memberId)->count()}}">{{$Value->m_name}}</td>
            <td>{{$Value->loanCode}}</td>
            <td>{{(\Carbon\Carbon::parse($Value->disbursementDate)->format('d-m-Y'))}}</td>
            <td>{{ number_format($Value->loanAmount, 2)}}</td>
            <td>{{ number_format(MFN::getLoanStatus($filters)[0]['outstanding'], 2) }}</td>
            <td rowspan="{{$LoanData->where('samityId',$Value->samityId)->where('memberId',$Value->memberId)->count()}}">{{number_format(MFN::getSavingsBalance($filtersSavings), 2)}}</td>
        
            @endif


    
            
        @endif
        </tr>   
        
        <?php //dd($Value);?>


       
        @endforeach


        {{-- <tr style="background-color:#b2b6b6;">
            @if ($samity_selected==null)
            <td class="text-center" colspan="4" >Sub Total</td> 
            @else
            <td class="text-center" colspan="4" >Total</td> 
            @endif
            
          
            <td class="text-right clsamount_{{$i}}">{{number_format($LoanData->where('samityId',$temp)->sum('amount'), 2)}}</td> 
       </tr> --}}


       
        
         
        
    </tbody>
</table>

                  

<script>

    var productCount = {{$i}};
    
var totalamount = 0 ;


           

    for (var i =1 ; i <=productCount; i++){
            
            
            $('.clsamount_'+i).each(function() {
                totalamount = totalamount + parseFloat(parseValue($(this).html()));
                // console.log($(this).html());     
            }); 
          

            
    }

    $('.setclsamount').html(totalamount.toFixed(2)); 

    

 function parseValue(str) {
  Value = parseFloat( str.replace(/,/g,'') ).toFixed(2);
  return +Value;
} 
</script>