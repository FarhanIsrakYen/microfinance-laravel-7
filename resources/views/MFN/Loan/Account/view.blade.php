@extends('Layouts.erp_master')

@section('content')
<!-- Page -->
<div class="table-responsive">
    <div class="panel-body">
        <div class="row">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th colspan="6">
                                <strong>Pictures</strong>
                            </th>
                        </tr>
                    </thead>
                </table>
        </div>
        <div class="row">
            <div class="col-md-6">
                <table class="table table-striped table-bordered">
                    <tbody>
                        <tr>
                            <td style="font-size: 14px;">Member Picture :</td>
                            <td>

                              <img id="profileImagePreview"
                              @if ($memberDetails->profileImage != '')
                              src="{{asset('images/members/profile') . '/'. $memberDetails->profileImage}}"
                              @endif
                              class="img-responsive img-rounded full-width mt-2"
                              style="width: 250px; height: 150px;">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="col-md-6">
                <table class="table table-striped table-bordered">
                    <tbody>
                        <tr>
                            <td style="font-size: 14px;">Guarantor Picture : </td>
                            <td>
                              <img id="profileImagePreview"
                              @if ($guarantors->where('guarantorNo', 1)->max('profileImage') != '')
                              src="{{asset('images\loans\guarantors') . '/'. $guarantors->where('guarantorNo', 1)->max('profileImage')}}"
                              @endif
                              class="img-responsive img-rounded full-width mt-2"
                              style="width: 250px; height: 150px;">

                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th colspan="6">
                                <strong>Signature</strong>
                            </th>
                        </tr>
                    </thead>
                </table>
        </div>

        <div class="row">
            <div class="col-md-6">
                <table class="table table-striped table-bordered">
                    <tbody>
                        <tr>
                            <td style="font-size: 14px;">Member NID Signature :</td>
                            <td>
                              <img id="profileImagePreview"
                              @if ($memberDetails->signatureImage != '')
                              src="{{asset('images\members\signature') . '/'. $memberDetails->signatureImage}}"
                              @endif
                              class="img-responsive img-rounded full-width mt-2"
                              style="width: 250px; height: 150px;">

                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="col-md-6">

                <table class="table table-striped table-bordered">
                    <tbody>
                        <tr>
                            <td style="font-size: 14px;">Guarantor NID Signature : </td>
                            <td>
                              <img id="profileImagePreview"
                              @if ($guarantors->where('guarantorNo', 1)->max('signatureImage') != '')
                              src="{{asset('images\loans\guarantor_signatures') . '/'. $guarantors->where('guarantorNo', 1)->max('signatureImage')}}"
                              @endif
                              class="img-responsive img-rounded full-width mt-2"
                              style="width: 250px; height: 150px;">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th colspan="6">
                                <strong>Loan Information</strong>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Loan ID:</td>
                            <td>{{$loan->loanCode}}</td>
                            <td>Product:</td>
                            <td>{{ $loanProductName }}</td>
                        </tr>
                        <tr>
                            <td>Member Name:</td>
                            <td>{{$member->name}}&nbsp;{{$member->memberCode}}</td>
                            <td>Loan Cycle:</td>
                            <td>{{$loan->loanCycle}}</td>
                        </tr>
                        <tr>
                            <td>Father's/Spouse Name:</td>
                            <td> @if ($memberDetails->fatherName || $memberDetails->spouseName)
                                   {{$memberDetails->fatherName}} &nbsp;{{$memberDetails->spouseName}}
                                @endif</td>
                            <td>Mode of payment:</td>
                            <td>{{$loanDetails->paymentType}}</td>
                        </tr>
                        <tr>
                            <td>Age:</td>
                            <td>{{$memberAge}}&nbsp;Years</td>
                            <td>Mobile No:</td>
                            <td>{{$memberDetails->mobileNo}}</td>
                        </tr>
                        <tr>
                            <td>Samity:</td>
                            <td>{{$samity->name}}&nbsp;{{$samity->samityCode}}<br>{{$samity->samityDay}}&nbsp;(Samity Day)</td>
                            <td>Transfer In Date:</td>
                            <td>--</td>
                        </tr>
                        <tr>
                            <td>Disbursement Date:</td>
                            <td>{{date('d-m-Y', strtotime($loan->disbursementDate))}}</td>
                            <td>Due Amount:</td>
                            <td>--</td>
                        </tr>
                        <tr>
                            <td>First Repay Date:</td>
                            <td>{{date('d-m-Y', strtotime($loan->firstRepayDate))}}</td>
                            <td>Advance Amount:</td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>Interest Rate:</td>
                            <td>{{$intRate->interestRatePerYear}}&nbsp;% &nbsp; {{$loan->interestRateIndex}} &nbsp;({{$intCalculateMethod->name}})</td>
                            <td>Recovery Amount:</td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>Extra Installment Amount:</td>
                            <td>{{$loan->extraInstallmentAmount}}</td>
                            <td>Opening Loan Outstanding:</td>
                            <td>--</td>
                        </tr>
                        <tr>
                            <td>Current Status:</td>
                            <td>{{ $loanCurrentStatus }}</td>
                            <td>Rebate:</td>
                            <td>-</td>
                        </tr>
                        
                        @if ($loan->loanType == 'Regular')
                        <tr>
                            <td>Repayment Frequency:</td>
                            <td>{{$repayment->name}}</td>
                            <td>Loan Outstanding:</td>
                            <td>{{ number_format($loan->repayAmount - $loanCollection->amount) }}</td>
                        </tr>                       
                        @elseif ($loan->loanType == 'Onetime')
                        <tr>
                            <td>Repayment Frequency:</td>
                            <td>Onetime</td>
                            <td>Loan Outstanding:</td>
                            <td>{{ number_format($loan->loanAmount - $loanCollection->principalAmount) }}</td>
                        </tr> 
                        @endif
                        
                        <tr>
                            <td>Mode of interest:</td>
                            <td>--</td>
                            <td>Loan Purpose:</td>
                            <td>{{$loanPurposes->title}}</td>
                        </tr>
                        <tr>
                            <td>Loan Amount:</td>
                            <td>{{$loan->loanAmount}}</td>
                            <td>Loan Sub Purpose:</td>
                            <td>--</td>
                        </tr>
                        <tr>
                            <td>Interest Amount:</td>
                            <td>{{$loan->ineterestAmount}}</td>
                            <td>Guarantor's Name #1:</td>
                            <td>{{$guarantors->where('guarantorNo', 1)->max('name')}}</td>
                        </tr>
                        <tr>
                            <td>Total Repay Amount: </td>
                            <td>{{$loan->repayAmount}}</td>
                            <td>Guarantor's Relationship #1:</td>
                            <td>{{$guarantors->where('guarantorNo', 1)->max('relation')}}</td>
                        </tr>
                        <tr>
                            <td>Number of Installment:</td>
                            <td>{{$loan->numberOfInstallment}}</td>
                            <td>Guarantor's Address #1:</td>
                            <td>{{$guarantors->where('guarantorNo', 1)->max('address')}}</td>
                        </tr>
                        <tr>
                            <td>Loan Period in Month:</td>

                            <td></td>
                            <td>Guarantor's Name #2:</td>
                            <td>{{ $guarantors->where('guarantorNo', 2)->max('name') }}</td>
                        </tr>
                        <tr>
                            <td>Loan Application No:</td>
                            <td>{{$loan->numberOfInstallment}}</td>
                            <td>Guarantor's Relationship #2:</td>
                            <td>{{ $guarantors->where('guarantorNo', 2)->max('relation') }}</td>
                        </tr>
                        <tr>
                            <td>Insurance/Guarantor's Amount:</td>
                            <td>{{$loan->insuranceAmount}}</td>
                            <td>Guarantor's Address #2:</td>
                            <td>{{ $guarantors->where('guarantorNo', 2)->max('address') }}</td>
                        </tr>
                        <tr>
                            <td>Loan Closing Date:</td>
                            @if ((bool)strtotime($loan->loanCompleteDate))
                            <td>{{ date('d-m-Y', strtotime($loan->loanCompleteDate)) }}</td>                                
                            @else
                            <td>-</td>
                            @endif
                            <td>Transfer Out Date:</td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>Installment Amount:</td>
                            <td>{{$loan->installmentAmount}}Taka<br>{{$loan->actualInstallmentAmount}}(actual) + {{$loan->extraInstallmentAmount}}(extra) * {{$loan->numberOfInstallment}} (no. of installment)<br> + {{$loan->lastInstallmentAmount}} (last installment amount)</td>
                            <td>Folio Number:</td>
                            <td>{{$loanDetails->folioNumber}}</td>
                        </tr>
                        <tr>
                            <td>Additional Fee:</td>
                            <td>{{$loanDetails->additionalFee}}</td>
                            <td>{{$loanFormFeeLabel}}:</td>
                            <td>{{$loanDetails->loanFormFee}}</td>
                        </tr>
                        <tr>
                            <td>Payment:</td>
                            <td colspan="3">{{$loanDetails->paymentType}}</td>
                        </tr>
                        <tr>
                            <td>Employment:</td>
                            <td colspan="3">
                                <table width="100%">
                                    <tbody>
                                        <tr>
                                            <td>Self: 0</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <table width="100%" id="regularLoanEmploymentData">
                                                    <thead>
                                                        <tr>
                                                            <th width="25%"></th>
                                                            <th>Family</th>
                                                            <th>Wages Based</th>
                                                            <th>Wage (Per Month)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <th>Full Time Male</th>
                                                            <td>{{$loanDetails->familyFTM}}</td>
                                                            <td>{{$loanDetails->fullTimeMaleWage}}</td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Full Time Female</th>
                                                            <td>{{$loanDetails->familyFTF}}</td>
                                                            <td>{{$loanDetails->fullTimeFemaleWage}}</td>
                                                            <td>0</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Part Time Male</th>
                                                            <td>{{$loanDetails->familyPTM}}</td>
                                                            <td>{{$loanDetails->partTimeMaleWage}}</td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Part Time Female</th>
                                                            <td>{{$loanDetails->familyPTF}}</td>
                                                            <td>{{$loanDetails->partTimeFemaleWage}}</td>
                                                            <td>0</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div style="height:20px"></div>
                <table class="table table-striped table-bordered view" id="interestRateList">
                    <thead>
                        <tr>
                            <th colspan="10" style="text-align:left">
                                <strong>Loan Schedule</strong>
                            </th>
                        </tr>
                        <tr>
                            <th rowspan="2">#</th>
                            <th rowspan="2">Date</th>
                            <th rowspan="2">Samity Day</th>
                            <th colspan="3">Amount</th>
                            <th width="100" rowspan="2">Principal Amount (P)</th>
                            <th width="100" rowspan="2">Interest Amount (I)</th>
                            <th rowspan="2">Status</th>
                            <th rowspan="2">Reschedule</th>
                        </tr>
                        <tr>
                            <th width="120">Actual Amount</th>
                            <th width="120">Extra Amount</th>
                            <th width="120">Installment Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            if ($loan->loanType == 'Regular'){
                                $collectionAmount = $loanCollection->amount;
                            }
                            if ($loan->loanType == 'Onetime'){
                                $collectionAmount = $loanCollection->principalAmount;
                            }
                        @endphp
                        @foreach ($loanSchedules as $loanSchedule)
                            <tr>
                                <td>{{ $loanSchedule['installmentNo'] }}</td>
                                <td>{{ date('d-m-Y', strtotime($loanSchedule['installmentDate'])) }}</td>
                                <td>{{ $loanSchedule['weekDay'] }}</td>
                                <td>{{ round($loanSchedule['actualInastallmentAmount'], 2) }}</td>
                                <td>{{ round($loanSchedule['extraInstallmentAmount'], 2) }}</td>
                                <td>{{ $loanSchedule['installmentAmount'] }}</td>
                                <td>{{ round($loanSchedule['installmentAmountPrincipal'], 2) }}</td>
                                <td>{{ round($loanSchedule['installmentAmountInterest'], 2) }}</td>
                                @if ($collectionAmount >= $loanSchedule['installmentAmount'])
                                <td>Paid</td>
                                @elseif($collectionAmount > 0)
                                <td>Partially Paid</td>
                                @else
                                <td>Unpaid</td>
                                @endif

                                @if ($loanSchedule['installmentDate'] >= $sysDate && $collectionAmount < $loanSchedule['installmentAmount'])
                                <td style="text-align: center; font-size: 20px;">
                                    <a href="javascript:void(0)" onclick="reschedule({{$loanSchedule['installmentNo']}});">
                                        <i class="fa fa-clock-o" aria-hidden="true"></i>
                                    </a>
                                </td>
                                @else
                                <td>-</td>
                                @endif
                            </tr>
                            @php
                                $collectionAmount -= $loanSchedule['installmentAmount'];
                            @endphp
                        @endforeach
                    </tbody>
                </table>
        </div>
    </div>

    <div class="row align-items-center">
        <div class="col-lg-12">
            <div class="form-group d-flex justify-content-center">
                <div class="example example-buttons">
                    <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round d-print-none">Back</a>
                    <!-- <a href="javascript:void(0)" onClick="window.print();" class="btn btn-default btn-round clsPrint">Print</a> -->
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Page -->

<script>
    function reschedule(installmentNumber) {
            var loanId = "{{ encrypt($loan->id) }}";

            if (loanId != '' && installmentNumber != '') {
                window.location = './../../loanReschedule/add?loanId=' + loanId + '&installmentNumber=' + installmentNumber;                
            }
        }
    $(document).ready(function () {

        
    });
</script>
@endsection
