@php

use App\Services\MfnService as MFN;
 
@endphp

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
            <th >SL#</th>
            <th >Member</th>
            <th >Account</th>
            <th >Date</th>
            <th >Closing Amount</th>
            
        </tr>
    </thead>
    @php $i = 0; $total = 0;@endphp
    <tbody>

        @foreach ($SavingsClosing  as $index => $item)
        
        @php $total += $item->closingAmount;@endphp

        @if(!isset($SavingsClosing[$index-1]->memberId) )
        <tr>
            <td  rowspan="{{$SavingsClosing->where('memberId',$SavingsClosing[$index]->memberId)->count()}}" class="text-center">{{++$i}}</td>
            <td  rowspan="{{$SavingsClosing->where('memberId',$SavingsClosing[$index]->memberId)->count()}}">{{$item->Mname}}</td>
            <td class="text-center">{{$item->accountCode}}</td>
            <td class="text-center">{{$item->closingDate}}</td>
            <td class="text-right">{{number_format($item->closingAmount, 2)}}</td>

        @else
            @if ($SavingsClosing[$index-1]->memberId != $SavingsClosing[$index]->memberId) 
            <tr>
                <td rowspan="{{$SavingsClosing->where('memberId',$SavingsClosing[$index]->memberId)->count()}}" class="text-center">{{++$i}}</td>
                <td rowspan="{{$SavingsClosing->where('memberId',$SavingsClosing[$index]->memberId)->count()}}">{{$item->Mname}}</td>
                <td class="text-center">{{$item->accountCode}}</td>
                <td class="text-center">{{$item->closingDate}}</td>
                <td class="text-right">{{number_format($item->closingAmount, 2)}}</td>
            @else
                <td class="text-center">{{$item->accountCode}}</td>
                <td class="text-center">{{$item->closingDate}}</td>
                <td class="text-right">{{number_format($item->closingAmount, 2)}}</td>
            @endif

        @endif
            </tr>
        @endforeach

        <tr>
            <td colspan="4" class="text-center font-weight-bold">Grand Total</td>
            <td class="text-right font-weight-bold">{{number_format($total, 2)}}</td>
        </tr>

    </tbody>
</table>

                  

<script>


</script>