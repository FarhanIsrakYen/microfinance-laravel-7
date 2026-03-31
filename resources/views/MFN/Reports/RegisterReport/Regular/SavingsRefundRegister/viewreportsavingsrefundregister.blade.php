<?php

use App\Services\MfnService as MFN;
 
?>

<div class="row text-center  d-print-block">
    <div class="col-lg-12" style="color:#000;">
        <strong>{{$branchData->comp_name}}</strong><br>
        
        <span>{{$branchData->comp_addr}}</span><br>
        <strong>Savings Refund Register</strong><br>
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
                                <span> {{$branchData->branch_code . " - " .$branchData->branch_name }}</span>
                            </span>
                    </td>
                    
                        <td>
                            <span style="color: black;" class="float-left">
                                <span style="font-weight: bold;">Primary Product Category :  </span>
                                <span> {{($selected_category==null)? 'All': $loanProdCategoryData->where('id',$selected_category)->first()->name}}</span>
                            </span>
                        </td>
                    
                        
                    
                    <td>
                        <span style="color: black;" class="float-left">
                            <span style="font-weight: bold;">Savings Product :  </span>
                            <span> {{($selected_primary_product==null)? 'All': $loanProductData->where('id',$selected_primary_product)->first()->name}}</span>
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
                     
                     <td>
                        <span style="color: black;" class="float-left">
                            <span style="font-weight: bold;">Primary Product :  </span>
                            <span> {{($selected_savings_product==null)? 'All': $savingsProductData->where('id',$selected_savings_product)->first()->name}}</span>
                        </span>
                    </td>
                    
                </tr>
                
            </tbody>
        </table>

    </div>
</div>


<table class="table w-full table-hover table-bordered table-striped">
    <thead>
        <tr>
            <th rowspan="2">SL</th>
            <th rowspan="2">Date</th>
            <th colspan="2">Members</th>
            <th colspan="2">Samity</th>
            <th rowspan="2">Savings <br> Code</th>
            <th rowspan="2">Savings <br> Balance <br>Before <br> Refund</th>
            <th colspan="5">Savings Refund / Withdrawal</th>
            <th rowspan="2">Signature <br> of <br> Member</th>
            <th rowspan="2">Signature <br>of Field<br> Officer</th>
            
        </tr>
        <tr>
            <th>Code</th>
            <th>Name</th>
            <th>Samity <br>Code</th>
            <th>Samity <br>Name</th>
            <th>Full <br>Refund</th>
            <th>Partial <br>Refund</th>
            <th>Loan <br>Adjust</th>
            <th>Total <br>Savings <br>Refund / <br>Withdrawal</th>
            <th>Current <br>Savings <br>Balance</th>
        </tr>
    </thead>
    <?php $i = 0; $total = 0;
    ?>
    <tbody>
        @foreach ($savingsRefundRegister as $item)
            {{-- @php
                dd($item->transactionTypeId);
            @endphp --}}
            
            <tr>
                <td class="text-center">{{++$i}}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($item->date)->format('d-m-Y')}}</td>
                <td class="text-center"> {{ $item->memberCode}}</td>
                <td >{{ $item->memberName}}</td>
                <td class="text-center">{{$item->samity}}</td>
                <td >{{$item->samityName}}</td>
                <td class="text-center">{{$item->accountCode}}</td>
                <td class="text-right">{{number_format($item->savBalBeforeRefund,2)}}</td>
                <td class="text-center">
                    @if ($item->transactionTypeId == 7)
                        Yes
                    @else
                        No
                    @endif
                </td>
                <td class="text-center">
                    @if ($item->transactionTypeId != 7)
                        Yes
                    @else
                        No
                    @endif
                </td>
                <td></td>
                <td class="text-right">{{number_format($item->totalRefund,2)}}</td>
                <td class="text-right">{{number_format($item->currentSavBalance,2)}}</td>
                <td ></td>
                <td ></td>
                
            </tr>
        @endforeach
        
    </tbody>
</table>

                  

<script>


</script>