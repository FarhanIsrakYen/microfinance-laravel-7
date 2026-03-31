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
            <th colspan="2">Member</th>
            <th rowspan="2">Spouse</th>
            <th rowspan="2">Village Name</th>
            <th rowspan="2">Union Name</th>
            <th rowspan="2">Upazila Name</th>
            <th rowspan="2">Age</th>
            <th rowspan="2">Profession</th>

            <th rowspan="2">Education</th>
            <th rowspan="2">Application No.</th>
            <th rowspan="2">Field Officer</th>

            <th rowspan="2">Admission Date</th>

            <th rowspan="2">Status</th>


        </tr>
        <tr>
            {{-- loan --}}
            <th >Name</th>
            <th >Code</th>
         
           
           
        </tr>
    </thead>
    <?php $i = 0; ?>
    <tbody>

        @foreach ($DataQuerry as $item)
        <?php //dd($item); ?>
        <tr>
        <td>{{++$i}}</td>
        <td>{{$item->samityCode}}</td>
        <td>{{$item->samity}}</td>
        <td>{{$item->name}}</td>
        <td>{{$item->memberCode}}</td>
        <td>{{$item->spouseName}}</td>
        <td>{{$item->preVillage->village_name}}</td>
        <td>{{$item->preUnion->union_name}}</td>
        <td>{{$item->preUpazila->upazila_name}}</td>
        <td>{{ ((\Carbon\Carbon::parse($sysDate)->format('Y'))-(\Carbon\Carbon::parse($item->dateOfBirth)->format('Y'))) }}</td>
        <td>{{$item->memberProfession->name}}</td>
        <td>{{$item->memberEducation->name}}</td>
        <td>{{$item->formApplicationNo}}</td>
        <td>{{$item->emp_name}}</td>

        <td>{{\Carbon\Carbon::parse($item->admissionDate)->format('d-m-Y')}}</td>
        <td>{{($item->closingDate=="0000-00-00")? 'Active': 'Close'}}</td>

        </tr>
            
        @endforeach

      
  
         
        
    </tbody>
</table>

                  

<script>


</script>