@extends('Layouts.erp_master')
@section('content')
<?php

use App\Services\MfnService as MFN;
 $gater = (count($data[0])>=count($data[1])? count($data[0]) : count($data[1]) );

?>

<div class="nav-tabs-horizontal f-inline-flex" id="tabs">

    <ul class="nav nav-tabs nav-tabs-reverse  text-center" role="tablist">
        <li class="nav-item mr-2 flex-fill" role="presentation" id="initialList">
            <a class="nav-link nav-tabs btn btn-bg-color active" id="initialTab" data-toggle="tab" href="#details" role="tab">Details
            </a>
        </li>
        <li class="nav-item mr-2 flex-fill" role="presentation" id="savingList">
            <a class="nav-link nav-tabs btn btn-bg-color" id="savingTab" data-toggle="tab" href="#saving" role="tab">Saving Details
            </a>
        </li>
        <li class="nav-item mr-2 flex-fill" role="presentation" id="loanList">
            <a class="nav-link nav-tabs btn btn-bg-color" id="loanTab" data-toggle="tab" href="#loan" role="tab">Loan Details
            </a>
        </li>
    </ul>
    <div class="row mt-4">
        <div class="col-md-5">
            <div class="row">
                <div class="col-md-12">
                    <div>
                        <img id="image" src="{{$memberDetails->profileImage}}" alt="Profile Image" height="150" width="150">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="row">
                <div class="col-md-12">
                    <table style="width:100%" id="memberGist" class="table table-striped table-bordered right">
                        <tbody>
                            <tr>
                                <th colspan="4">Loan</th>
                                <th colspan="2">Saving</th>
                            </tr>
                           
                            <tr>
                                <td>Product</td>
                                <td>Loan Amount</td>
                                <td>Outstanding</td>
                                <td>Adv/Due</td>
                                <td>Product</td>
                                <td>Balance</td>
                            </tr>
                            @for ($x = 0; $x < $gater ; $x++)
                            <tr>
                                
                                <td>{{( empty($data[1][$x]['LPName']))? '': $data[1][$x]['LPName']}}</td>
                                <td class="cls_l_am">{{( empty($data[1][$x]['loan_Amount']))? '': $data[1][$x]['loan_Amount']}}</td>
                                <td class="cls_l_out">{{( empty($data[1][$x]['Outstanding']))? '': $data[1][$x]['Outstanding']}}</td>
                                <td>--</td>
                                <td>{{empty($data[0][$x]['SPName'])? '': $data[0][$x]['SPName']}}</td>
                                <td class="cls_s_bal">{{empty($data[0][$x]['balance'])? '': $data[0][$x]['balance']}}</td>
                            </tr>
                            @endfor
                            <tr>
                                <td class="text-left">Total</td>
                                <td id="l_am">0</td>
                                <td id="l_out">0</td>
                                <td id="l_avg">0</td>
                                <td class="text-left">Total</td>
                                <td id="s_bal">0</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <table style="color: #000;">
                <tbody>
                    <tr>
                        <th>Name</th>
                        <th>:</th>
                        <td>{{$member->name}}{{$member->id}}</td>
                    </tr>
                    <tr>
                        <th>Spouse Name</th>
                        <th>:</th>
                        <td>{{$memberDetails->spouseName}}</td>
                    </tr>
                    {{-- <tr>
                        <th>Surname</th>
                        <th>:</th>
                        <td>{{$memberDetails->surName}}</td>
                    </tr> --}}
                    <tr>
                        <th>Code</th>
                        <th>:</th>
                        <td>{{$member->memberCode}}</td>
                    </tr>
                    <tr>
                        <th>Samity</th>
                        <th>:</th>
                        <td>
                        {{$member->samity->name}}
                        </td>
                    </tr>
                    <tr>
                        <th>FO Name</th>
                        <th>:</th>
                        <td>{{$member->samity->hrEmployee->emp_name}}</td>
                    </tr>
                    <tr>
                        <th>National ID Signature</th>
                        <th>:</th>
                        <td>
                            <img id="image1" src="{{$memberDetails->signatureImage}}" alt="National ID Picture" height="100" width="150">
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Tab Content -->
    <div class="tab-content pt-20">
        <!-- tab pane 1 (Details) -->
        <div class="tab-pane active show" id="details" role="tabpanel">

            <div class="table-responsive mt-4">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th colspan="6" style="color: #000;">
                                Members Information
                            </th>
                        </tr>
                    </thead>
                    <tbody style="color: #000;">
                        <tr>
                            <td width="16.66%">Primary Product:</td>
                            <td width="16.66%">{{$member->loanProduct->name}}</td>

                            <td width="16.66%">Member Code:</td>
                            <td width="16.66%">{{$member->memberCode}}</td>

                            <td width="16.66%">Admission Date:</td>
                            <td width="16.66%">{{\Carbon\Carbon::parse($member->admissionDate)->format('d-m-Y')}}</td>
                        </tr>
                       <tr> 
                            <td width="16.66%">Surname:</td>
                            <td width="16.66%">{{$memberDetails->surName}}</td>

                            <td width="16.66%">Father's Name:</td>
                            <td width="16.66%">{{$memberDetails->fatherName}}</td>
                            
                            <td width="16.66%">Mother's Name:</td>
                            <td width="16.66%">{{$memberDetails->motherName}}</td>
                        </tr>
                        <tr>
                            <td width="16.66%">Son's Name:</td>
                            <td width="16.66%">{{$memberDetails->sonName}}</td>                            

                            <td width="16.66%">Date of Birth:</td>
                            <td width="16.66%" colspan="3">{{\Carbon\Carbon::parse($memberDetails->dateOfBirth)->format('d-m-Y')}}</td> 
                        </tr>
                        <tr>
                            <td width="16.66%">Present Address:</td>
                            @if ($memberDetails->preVillage)
                            <td width="16.66%">{{$memberDetails->preVillage->village_name}}&nbsp;
                                {{$memberDetails->preUnion->union_name}}&nbsp;
                                {{$memberDetails->preUpazila->upazila_name}}&nbsp;
                                {{$memberDetails->preDistrict->district_name}}&nbsp;
                                {{$memberDetails->preDivision->division_name}}&nbsp;
                            </td>    
                            @else
                            <td width="16.66%"> </td>
                            @endif
                            

                            <td width="16.66%">Permanent Address: </td>
                            @if ($memberDetails->perVillage)
                            <td width="16.66%">
                                {{$memberDetails->perVillage->village_name}}&nbsp;
                                {{$memberDetails->perUnion->union_name}}&nbsp;
                                {{$memberDetails->perUpazila->upazila_name}}&nbsp;
                                {{$memberDetails->perDistrict->district_name}}&nbsp;
                                {{$memberDetails->perDivision->division_name}}&nbsp;
                
                            </td>  
                            @else
                            <td width="16.66%"> </td>
                            @endif
                            
                            

                            <td width="16.66%">Gender: </td>
                            <td width="16.66%">{{$member->gender}}</td>
                        </tr>
                        <tr>
                            <td width="16.66%">National ID:</td>
                            <td width="16.66%">{{$memberDetails->firstEvidence}}</td>

                            <td width="16.66%">Form Application No:</td>
                            <td width="16.66%">{{$memberDetails->formApplicationNo}}</td>

                            <td width="16.66%">Educational Qualification:</td>
                            <td width="16.66%">{{$memberDetails->memberEducation->name}}</td>
                        </tr>
                        
                        <tr>
                            <td width="16.66%">Admission Fee:</td>
                            <td width="16.66%">{{$memberDetails->admissionFee}}</td>

                            <td width="16.66%">Mobile Number: </td>
                            <td width="16.66%">{{$memberDetails->mobileNo}}</td>

                            <td width="16.66%">Nationality:</td>
                            <td width="16.66%">{{$nationality}}</td>
                        </tr>
                        <tr>
                            <td width="16.66%">Fixed Asset Description:</td>
                            <td colspan="5">{{$memberDetails->fixedAssetDescription}}</td>
                        </tr>
                        <tr>
                            <td width="16.66%">Nominee Information:</td>
                            @if ($nomineeinfo)
                                <td colspan="5">Nominee name:{{$nomineeinfo->name}}, &nbsp; &nbsp; Relation:{{$relationWithMember}}, &nbsp;  &nbsp;Mobile: {{$nomineeinfo->mobileNo}}, &nbsp;  &nbsp;Share: {{$nomineeinfo->share}}</td>    
                            @else
                                <td colspan="5"> </td>
                            @endif
                            
                        </tr>
                        <tr>
                            <td width="16.66%">Reference Information:</td>
                            @if ($relationWithReference)
                                <td colspan="5">Reference name:{{$relationWithReference->name}}, &nbsp; &nbsp; Relation:{{$relationWithReference->relationship}},&nbsp;  &nbsp;Mobile: {{$relationWithReference->mobileNo}}, &nbsp;  &nbsp;Organization: {{$relationWithReference->organization}}, &nbsp;  &nbsp;Designation: {{$relationWithReference->designation}}</td>
                            @else
                                <td colspan="5"> </td>
                            @endif
                        </tr>

                        <tr>

                            <td width="16.66%">Yearly Income:</td>
                            <td width="16.66%" colspan="2">{{$memberDetails->yearlyIncome}}</td>

                            <td width="16.66%">Land Area:</td>
                            <td width="16.66%" colspan="2"> {{$memberDetails->landArea}}  </td>

                        </tr>

                    </tbody>
                </table>


                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th colspan="8" style="color: #000;">
                                Mandatory Savings INFO
                            </th>
                        </tr>
                    </thead>
                    <tbody style="color: #000;">
                        <?php
                    // $i = 0;
                    // $filter= array();
                    // dd($savingsaccinfoMendetory);
                    ?>
                    @foreach ($savingsaccinfoMendetory as $Row)
                    {{-- <tr>
                        
                    </tr> --}}

                    <?php
                    // $i = 0;
                    $filters['accountId'] = $Row->id;
                    // dd($filters);
                    ?>
                   
                   
                    

                    
                    <tr>
                        
                        <td width="10%">Account Code:</td>
                        <td width="20%">{{$Row->accountCode}}</td>
                        <td>Savings Type :</td>
                        <td>Mendatory</td>

                        <td>Interest Rate: </td>
                        <td>{{$Row->interestRate}}</td>
                    </tr>
                    <tr>
                        <td>Opening Date: </td>
                        <td width="20%">{{\Carbon\Carbon::parse($Row->openingDate)->format('d-m-Y')}}</td>

                        <td>Total Balance:</td>
                        <td>{!! MFN::getSavingsBalance($filters) !!}</td>
                        
                        <td>Savings Cycle: </td>
                        <td>{{$Row->savingsCycle}}</td>
                    </tr>
                    <tr>
                        <td colspan="6"></td>
                        
                    </tr>

                    @endforeach


                      

                    </tbody>
                </table>

                
                <div class="row align-items-center">
                    <div class="col-lg-12">
                        <div class="form-group d-flex justify-content-center">
                            <div class="example example-buttons">
                                <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round d-print-none">Back</a>
                                <a href="javascript:void(0)" onClick="window.print();" class="btn btn-default btn-round clsPrint">Print</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- tab pane 2 (Savings)-->
        <div class="tab-pane show" id="saving" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th colspan="8" style="color: #000;">
                              Saving Details
                            </th>
                        </tr>
                    </thead>
                    <tbody >

                                  
                                @foreach ($savingsaccinfoAll as $Row)
                                <?php
                                    // $i = 0;
                                    $filters['accountId'] = $Row->id;
                                    // dd($filters);
                                    ?>
                                        <tr>
                                            {{-- <td rowspan="3">{{$Row->savingsProduct->name}}</td> --}}
                                            <td>Account Code:</td>
                                            <td colspan="2">{{$Row->accountCode}}&nbsp;[cyc -  {{$Row->savingsCycle}}]</td>
                                            <td>Product:</td>
                                            <td colspan="2"><strong>{{$Row->savingsProduct->shortName}}</strong>&nbsp;-%{{$Row->interestRate}}</td>
                                            
                                        </tr>
                                        <tr>
                                            <td>Total Balance:</td>
                                            <td>{!! MFN::getSavingsBalance($filters) !!}</td>
                                            <td>Opening Date:</td>
                                            <td>{{\Carbon\Carbon::parse($Row->openingDate)->format('d-m-Y')}}</td>
                                            <td>Auto Process Amount:</td>
                                            <td>{{$Row->autoProcessAmount}}</td>
                                        </tr>
                                        <tr>
                                            
                                            <td colspan="6" class="text-right">
                                            <button class="btn btn-primary active" onclick="fnshowsavingsdetails({{$Row->id}});"  >View	Transaction</button>
                                            </td>
                                        </tr>
                                @endforeach
										
					</tbody>
                </table>
            </div>
            <div class="row align-items-center">
                <div class="col-lg-12">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round d-print-none">Back</a>
                            <a href="javascript:void(0)" onClick="window.print();" class="btn btn-default btn-round clsPrint">Print</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- tab pane 3 (Loan)-->
        <div class="tab-pane show" id="loan" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th colspan="11" style="color: #000;">
                                Loan Details
                            </th>
                        </tr>
                    </thead>
                    <tbody style="color: #000;">
                        
                                @foreach ($loanaccinfoAll as $Row)
                                <?php  $filters['loanId'] = $Row->id;  ?>
                                    <tr>
                                        <td>Loan ID:</td>
                                        <td colspan="3">
                                            {{$Row->loanCode}} [cyc-{{$Row->loanCycle}}]
                                        </td>
                                        
                                        <td>Loan Amount:</td>
                                        <td>{{$Row->loanAmount}}</td>
                                        <td>Interest Amount:</td>
                                        <td>{{$Row->ineterestAmount}}</td>
                                        <td>Total Repay Amount:</td>
                                        <td>{{$Row->repayAmount}}</td>
                                    </tr>
                                    <tr>
                                        <td>Product:</td>
                                        <td>{{$Row->loanProduct->name}}</td>
                                        <td>Interest Rate:</td>
                                        <td> {{$Row->interestRate->interestRatePerYear}}
                                        </td>
                                        <td>Loan Outstanding:</td>
                                        <td>{{$Row->loanAmount - (MFN::getLoanCollection($filters) )  }}</td>
                                        <td>Recovery Amount:</td>
                                        <td>{!! MFN::getLoanCollection($filters) !!}</td>
                                        <td>Advance/Due Amount:</td>
                                        <td>-- / --</td>
                                    </tr>
                                    <tr>
                                        <td>Disbursement:</td>
                                        <td>{{\Carbon\Carbon::parse($Row->disbursementDate)->format('d-m-Y')}}</td>
                                        <td>First Repay:</td>
                                        <td>{{\Carbon\Carbon::parse($Row->firstRepayDate)->format('d-m-Y')}}</td>
                                        
                                        <td>Insurance Amount:</td>
                                        <td>{{$Row->insuranceAmount}}</td>
                                        <td>Number of Installment:</td>
                                        <td>{{$Row->numberOfInstallment}}</td>
                                        <td>Installment Amount:</td>
                                        <td>{{$Row->installmentAmount}}</td>
                                    </tr>
                                    <tr>
                                        
                                        <td colspan="10" class="text-right">
                                            <button class="btn btn-primary active viewTransaction" onclick="fnshowloanDetails({{$Row->id}});" >View
                                                Transaction</button>
                                        </td>
                                    </tr>
                                
                                @endforeach
										
					</tbody>
                </table>
            </div>
            <div class="row align-items-center">
                <div class="col-lg-12">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round d-print-none">Back</a>
                            <a href="javascript:void(0)" onClick="window.print();" class="btn btn-default btn-round d-print-none clsPrint">Print</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Button trigger modal -->

  
  <!-- Modal savings -->
  <div class="modal fade" id="savingsModal" tabindex="-1" role="dialog" aria-labelledby="savingsModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="savingsLongTitle">Savings Account Details</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">

            <table class="table table-striped table-bordered" id="savingsTableDetails">
                <thead>
                    <tr>
                        <th>SL</th>
                        <th>Transaction Date</th>
                        <th>Payment Mode</th>
                        <th>Diposite Amount</th>
                        <th>Withdraw Amount</th>
                        <th>Balance</th>
                    </tr>

                </thead>
                <tbody>

                </tbody>
            </table>
       


        </div>
        <div class="modal-footer">
          {{-- <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary">Save changes</button> --}}
        </div>
      </div>
    </div>
  </div>
     <!-- Modal savings -->
  <div class="modal fade" id="loanModal" tabindex="-1" role="dialog" aria-labelledby="loanModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="loanLongTitle">Loan Collection Details</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">

            <table class="table table-striped table-bordered" id="loanTableDetails">
                <thead>
                    <tr>
                        <th>SL</th>
                        <th>Transaction Date</th>
                        <th>Payment Mode</th>
                        <th>Collection Amount</th>
                        
                    </tr>

                </thead>
                <tbody>

                </tbody>
            </table>
       


        </div>
        <div class="modal-footer">
          {{-- <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary">Save changes</button> --}}
        </div>
      </div>
    </div>
  </div>

  <script>

$(document).ready(function() {

    
 
     var t_am = 0;
     var t_out = 0;
     var t_bal = 0;
    $('.cls_l_am').each(function() {
        t_am = Number(t_am) + Number($(this).html());
       ;
    });
    $('#l_am').html(t_am);

    $('.cls_l_out').each(function() {
        t_out = Number(t_out) + Number($(this).html());
       ;
    });
    $('#l_out').html(t_out);

    $('.cls_s_bal').each(function() {
        t_bal = Number(t_bal) + Number($(this).html());
       ;
    });
    $('#s_bal').html(t_bal);







});
//   
   
    function fnshowsavingsdetails(RowID) {

        $.ajax({
        method: "GET",
        url: "{{route('saveingsDetails')}}",
        dataType: "text",
        data: {
            RowID: RowID,
        },
        success: function(data) {
            if (data) {
                // $('#savingsTableDetails tbody').empty();
                $('#savingsTableDetails tbody').html(data);
                // console.log(data)
                $("#savingsModal").modal();
                
            }else{
                $('#savingsTableDetails tbody').html('');
                $("#savingsModal").modal();
            }
        }
     });
       
    }
    function fnshowloanDetails(RowID) {

            $.ajax({
                method: "GET",
                url: "{{route('loanDetails')}}",
                dataType: "text",
                data: {
                    RowID: RowID,
                },
                success: function(data) {
                    if (data) {
                        $('#loanTableDetails tbody').html(data);
                // console.log(data)
                $("#loanModal").modal();
                        
                    }else{
                        $('#loanTableDetails tbody').html('');
                        $("#loanModal").modal();

                    }
                }
            });

}

</script>
@endsection
