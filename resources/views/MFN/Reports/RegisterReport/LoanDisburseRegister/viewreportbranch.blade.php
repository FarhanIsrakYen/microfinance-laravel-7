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


<table class="table table-striped table-bordered">
    <thead class="text-center">
       
        <tr>
            <th rowspan="2">SL#</th>
            <th rowspan="2">Samity Code</th>
            <th rowspan="2">Samity Name</th>
            <th colspan="2">Loan Collection Information</th>
        </tr>
        <tr>
            {{-- loan --}}
            <th >Product Name</th>
            <th >Total Amount</th>
         
           
           
        </tr>
    </thead>
    <?php $i = 0; $temp = ''; $prodcount=0;?>
    <tbody>

        @foreach ($LoanData as $index => $Value)

        <?php //dd($index,$LoanData->where('samityId',$Value->samityId)->count());?>

        @if($index == 0)
        <tr>
        <td rowspan="{{$LoanData->where('samityId',$Value->samityId)->count()}}">{{++$i}}</td>
        <td rowspan="{{$LoanData->where('samityId',$Value->samityId)->count()}}">{{$samityData->where('id',$LoanData[$index]->samityId)->first()->samityCode}}</td>
        <td rowspan="{{$LoanData->where('samityId',$Value->samityId)->count()}}">{{$samityData->where('id',$LoanData[$index]->samityId)->first()->name}}</td>
        @else
            @if (isset($LoanData[$index-1]->samityId) && $LoanData[$index]->samityId != $LoanData[$index-1]->samityId)
            <tr style="background-color:#b2b6b6;">
                @if ($samity_selected==null)
                <td class="text-center" colspan="4" >Sub total</td> 
                @else
                <td class="text-center" colspan="4" >Total</td> 
                @endif
                <td class="text-right clsamount_{{$i}}">{{number_format($LoanData->where('samityId',$temp)->sum('amount'), 2)}}</td> 
           </tr>
           <tr>
            <td rowspan="{{$LoanData->where('samityId',$Value->samityId)->count()}}">{{++$i}}</td>
            <td rowspan="{{$LoanData->where('samityId',$Value->samityId)->count()}}">{{$samityData->where('id',$LoanData[$index]->samityId)->first()->samityCode}}</td>
            <td rowspan="{{$LoanData->where('samityId',$Value->samityId)->count()}}">{{$samityData->where('id',$LoanData[$index]->samityId)->first()->name}}</td>
        
        
            @endif
        
        
        @endif

        
        
        <td>{{$loanproduct->where('id',$LoanData[$index]->productId)->first()->name}}</td>


        
        <td class="text-right">{{number_format($Value->amount, 2)}}</td>
        </tr>
        


        <?php $temp = $Value->samityId; ?>
        @endforeach
        <tr style="background-color:#b2b6b6;">
            @if ($samity_selected==null)
            <td class="text-center" colspan="4" >Sub Total</td> 
            @else
            <td class="text-center" colspan="4" >Total</td> 
            @endif
            
          
            <td class="text-right clsamount_{{$i}}">{{number_format($LoanData->where('samityId',$temp)->sum('amount'), 2)}}</td> 
       </tr>

       
    @if ($samity_selected==null)
        <tr style="background-color: #959696">
            <td class="text-center" colspan="4" >Total</td> 
            
            <td class="text-right setclsamount">0</td> 
        </tr>
    @endif
       
        
         
        
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