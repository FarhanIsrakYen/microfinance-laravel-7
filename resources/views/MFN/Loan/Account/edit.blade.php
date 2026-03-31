@extends('Layouts.erp_master')
@section('content')

<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator" novalidate="true"
    autocomplete="off">
    @csrf
    <div class="nav-tabs-horizontal" id="tabs">

        <ul class="nav nav-tabs nav-tabs-reverse" role="tablist">
            <li class="nav-item mr-2" role="presentation" style="width: 49.5%">
                <a class="nav-link nav-tabs btn btn-bg-color active" id="detailsTab" data-toggle="tab"
                    href="#detailsPanel" role="tab">Loan Details
                </a>
            </li>
            <li class="nav-item mr-2" role="presentation" style="width: 49.2%">
                <a class="nav-link nav-tabs btn btn-bg-color" id="photoTab" data-toggle="tab" href="#photoPanel"
                    role="tab">Photo
                </a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content pt-20">
            <!--  tab pane 1 (Details) -->
            <div class="tab-pane active show" id="detailsPanel" role="tabpanel">

                <div class="row">
                    <div class="col-lg-12">

                        <!-- Member and Loan Details -->
                        <div class="panel panel-default">
                            <div class="panel-heading p-2 mb-4">Member and Loan Details</div>
                            <div class="panel-body">

                                <div class="row">
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title">Member</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round"
                                                id="member" value="{{ $loan->memberName }}" readonly>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title RequiredStar">Disbursement Date</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="icon wb-calendar round" aria-hidden="true"></i>
                                                </span>
                                            </div>
                                            @if ($isOpening)
                                            <input type="text" class="form-control round"
                                                id="disbursementDate" name="disbursementDate" value="{{ date('d-m-Y', strtotime($loan->disbursementDate)) }}" placeholder="DD-MM-YYYY">
                                            @else
                                            <input type="text" class="form-control round" id="disbursementDate"
                                                name="disbursementDate" placeholder="DD-MM-YYYY"
                                                value="{{ date('d-m-Y', strtotime($loan->disbursementDate)) }}"
                                                readonly>
                                            @endif

                                        </div>
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title RequiredStar">Product</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round"
                                                id="product" value="{{ $loan->productName }}" readonly>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title RequiredStar">Loan Code</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round" id="loanCode" name="loanCode"
                                            value="{{ $loan->loanCode }}"
                                                readonly required data-error="Loan Code is required"
                                                placeholder="Enter Code">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                </div>


                            </div>
                        </div>

                        <!-- Loan Configuration -->
                        <div class="panel panel-default">
                            <div class="panel-heading p-2 mb-4">Loan Configuration</div>
                            <div class="panel-body">

                                <div class="row">
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title">Loan Application No.</label>
                                        <div class="input-group">
                                            <input name="loanApplicationNo" type="text" class="form-control round textNumber" value="{{ $loan->loanApplicationNo }}"
                                            placeholder="Enter Loan Application No.">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                    @if ($loan->loanType == 'Regular')
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title RequiredStar">Payment Frequency</label>
                                        <div class="input-group">
                                            <select class="form-control clsSelect2" name="repaymentFrequencyId"
                                                id="repaymentFrequencyId" required
                                                data-error="Please Select Payment Frequency">
                                                <option value="">Select</option>
                                                @foreach ($repaymentInfos['repaymentFrequencies'] as $repaymentFrequencyId => $repaymentFrequency)
                                                <option value="{{ $repaymentFrequencyId }}" installments="{{ $repaymentInfos['eligibleNumberOfInstallments'][$repaymentFrequencyId] }}">{{ $repaymentFrequency }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title RequiredStar">Number Of Installment</label>
                                        <div class="input-group">
                                            <select class="form-control clsSelect2" name="numberOfInstallment"
                                                id="numberOfInstallment" required
                                                data-error="Please Select Number Of Installment">
                                                <option value="">Select</option>
                                            </select>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                    @elseif($loan->loanType == 'Onetime')
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title RequiredStar">Duration (In Month)</label>
                                        <div class="input-group">
                                            <select class="form-control clsSelect2" name="loanDurationInMonth"
                                                id="loanDurationInMonth" required
                                                value="{{ $loan->loanDurationInMonth }}"
                                                data-error="Please Select Loan Repay Period">
                                                <option value="">Select</option>
                                                @foreach ($repaymentInfos['eligibleMonths'] as $eligibleMonth)
                                                <option value="{{ $eligibleMonth }}">{{ $eligibleMonth }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                    @endif
                                    <div class="col-lg-3 form-group">
                                        @if ($isOpening)
                                        <label class="input-title RequiredStar">First Repay Date</label>
                                        @else
                                        <label class="input-title">First Repay Date</label>
                                        @endif
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="icon wb-calendar round" aria-hidden="true"></i>
                                                </span>
                                            </div>
                                            @if ($isOpening)
                                            <input type="text" class="form-control round"
                                                id="firstRepayDate" name="firstRepayDate" 
                                                value="{{ date('d-m-Y', strtotime($loan->firstRepayDate)) }}"placeholder="DD-MM-YYYY"
                                                autocomplete="off" required data-error="Please Select Date">
                                            @else
                                            <input type="text" class="form-control round" id="firstRepayDate" name="firstRepayDate" 
                                            value="{{ date('d-m-Y', strtotime($loan->firstRepayDate)) }}"
                                            placeholder="DD-MM-YYYY" readonly
                                            autocomplete="off">
                                            @endif

                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-3 form-group">
                                        @if ($isOpening)
                                        <label class="input-title RequiredStar">Loan Cycle</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round textNumber" name="loanCycle" value="{{ $loan->loanCycle }}"
                                                id="loanCycle" required placeholder="Emnter Loan Cycle">
                                        </div>
                                        @else
                                        <label class="input-title">Loan Cycle</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round" name="loanCycle"
                                                id="loanCycle" placeholder="Emnter Loan Cycle"
                                                value="{{ $loan->loanCycle }}">
                                        </div>
                                        @endif

                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title RequiredStar">Loan Amount</label>
                                        <span id="maxMinLoanAmount"
                                            style="display: none;color:red; font-size: 8px;"></span>
                                        <div class="input-group">
                                            <input type="text" class="form-control round textAmount" name="loanAmount" value="{{ $loan->loanAmount }}"
                                                id="loanAmount" placeholder="Enter Loan Amount" required
                                                data-error="Please Enter Loan Amount">
                                        </div>
                                        <div class="help-block with-errors is-invalid" id="loanAmountInvalid"></div>
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title">Insurance Amount</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round textAmount"
                                                name="insuranceAmount" id="insuranceAmount" readonly
                                                value="{{ $loan->insuranceAmount }}"
                                                placeholder="Insurance Amount">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title RequiredStar">Loan Purpose</label>
                                        <div class="input-group">
                                            <select class="form-control clsSelect2" name="loanPurpose" id="loanPurpose"
                                                required data-error="Please Select Repay Period">
                                                <option value="">Select</option>
                                                @foreach ($loanPurposes->where('parentId', 0) as $parent)
                                                <optgroup label="{{ $parent->title }}">
                                                    @php
                                                    $childs = $loanPurposes->where('parentId', $parent->id);
                                                    @endphp
                                                    @foreach ($childs as $child)
                                                    <option value="{{ $child->id }}">{{ $child->title }}</option>
                                                    @endforeach
                                                </optgroup>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title">Folio Number</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round textNumber" name="folioNumber" id="folioNumber" 
                                            value="{{ $loan->folioNumber }}"
                                            placeholder="Enter Folio Number">
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Interest Calculation -->
                        <div class="panel panel-default">
                            <div class="panel-heading p-2 mb-4">Interest Calculation</div>
                            <div class="panel-body">

                                <div class="row">
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title RequiredStar">Interest Calculation Method</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round" name="interestCalMethod" id="interestCalMethod"
                                            value="{{ $interestCalMethod }}"
                                            readonly required
                                            data-error="Interest Calculation Method is required"
                                            placeholder="Interest Calculation Method">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title RequiredStar">Interest rate</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round" name="interestRate" value="{{ $loan->interestRatePerYear }}"
                                                id="interestRate" readonly required
                                                data-error="Interest rate is required"
                                                placeholder="Enter Interest rate">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                </div>

                            </div>
                        </div>

                        <!-- Payments -->
                        <div class="panel panel-default">
                            <div class="panel-heading p-2 mb-4">Payments</div>
                            <div class="panel-body">

                                <div class="row">

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title RequiredStar">Additional Fee</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round" name="additionalFee" value="{{ $loan->additionalFee }}"
                                                id="additionalFee" readonly placeholder="Enter Additional Fee">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title RequiredStar">{{$loanFormFeeLabel}}</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round" name="loanFormFee"
                                            id="loanFormFee" 
                                            value="{{ $loan->loanFormFee }}"
                                            readonly placeholder="{{$loanFormFeeLabel}}">
                                        </div>
                                    </div>

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title">Payment Type</label>
                                        <div class="input-group">
                                            <select class="form-control clsSelect2" name="paymentType" id="paymentType">
                                                <option value="Cash">Cash</option>
                                                <option value="Bank">Bank</option>
                                            </select>
                                        </div>
                                    </div>


                                </div>

                                <div class="row" id="bankDiv" style="display: none;">

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title">Bank Account</label>
                                        <div class="input-group">
                                            <select class="form-control clsSelect2" name="ledgerId" id="ledgerId"
                                                style="width: 100%;">
                                                <option value="">Select</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title">Cheque No</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="chequeNo" value="{{ $loan->chequeNo }}">
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </div>

                        <!-- Extra Loan Information -->
                        <div class="panel panel-default">
                            <div class="panel-heading p-2 mb-4">Extra Loan Information</div>
                            <div class="panel-body">

                                <div class="row">
                                    @if ($loan->loanType == 'Regular')
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title RequiredStar">Total Repay Amount</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round" name="repayAmount"
                                                id="repayAmount" readonly required
                                                value="{{ $loan->repayAmount }}"
                                                data-error="Mode of Interest is required"
                                                placeholder="Enter Total Repay Amount">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title RequiredStar">Interest Amount</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round" name="ineterestAmount"
                                                id="ineterestAmount" readonly required
                                                value="{{ $loan->ineterestAmount }}"
                                                data-error="Mode of Interest is required"
                                                placeholder="Enter Interest Amount">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title RequiredStar">Installment Amount</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round" name="installmentAmount"
                                                id="installmentAmount" readonly required
                                                value="{{ $loan->installmentAmount }}"
                                                data-error="Mode of Interest is required"
                                                placeholder="Enter Installment Amount">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title RequiredStar">Extra Installment Amount
                                        </label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round" name="extraInstallmentAmount"
                                                id="extraInstallmentAmount" readonly
                                                value="{{ $loan->extraInstallmentAmount }}"
                                                placeholder="Enter Extra Installment Amount">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title RequiredStar">Actual Installment Amount</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round" name="actualInstallmentAmount"
                                                id="actualInstallmentAmount" readonly
                                                value="{{ $loan->actualInstallmentAmount }}"
                                                placeholder="Enter Actual Installment Amount">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title RequiredStar">Last Installment Amount</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round" name="lastInstallmentAmount"
                                                id="lastInstallmentAmount" readonly
                                                value="{{ $loan->lastInstallmentAmount }}">
                                        </div>
                                    </div>
                                    @endif
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title">Note</label>
                                        <div class="input-group">
                                            <textarea name="note" id="note" rows="3" class="form-control round" value="{{ $loan->note }}"
                                                placeholder="Enter Note"></textarea>
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </div>

                        @if ($isOpening)
                        <!-- Opening Collection Amount -->
                        <div class="panel panel-default">
                            <div class="panel-heading p-2 mb-4">Opening Collection</div>
                            <div class="panel-body">

                                <div class="row">

                                    @if ($loanType == 'Onetime')
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title RequiredStar">Collection Amount Principal</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round textNumber" name="openingCollectionAmountPrincipal"
                                                id="openingCollectionAmountPrincipal" placeholder="Enter Collection Amount" value="{{ @$openingCollection->principalAmount }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title RequiredStar">Collection Amount Interest</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round textNumber" name="openingCollectionAmountInterest"
                                                id="openingCollectionAmountInterest" placeholder="Enter Collection Amount" value="{{ @$openingCollection->interestAmount }}">
                                        </div>
                                    </div>
                                    @else
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title RequiredStar">Collection Amount</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round textNumber" name="openingCollectionAmount"
                                                id="openingCollectionAmount" placeholder="Enter Collection Amount" value="{{ round(@$openingCollection->amount) }}">
                                        </div>
                                    </div>
                                    @endif

                                </div>

                            </div>
                        </div>
                        @endif

                        <!-- Guarantor's Details -->
                        <div class="panel panel-default">
                            <div class="panel-heading p-2 mb-4">Guarantor's Details</div>
                            <div class="panel-body">

                                @php
                                    $firstGuarantor = $guarantors->where('guarantorNo', 1)->first();
                                    $secondGuarantor = $guarantors->where('guarantorNo', 2)->first();
                                @endphp
                                <div class="row">
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title @if(!$isOpening) RequiredStar @endif">Guarantor#1 Name</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round" name="guarantorNames[]"
                                            value="{{ @$firstGuarantor->name }}"
                                                @if(!$isOpening) required @endif placeholder="Enter Guarantor Name">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title @if(!$isOpening) RequiredStar @endif">Relation</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round" name="guarantorRelations[]"
                                            value="{{ @$firstGuarantor->relation }}"
                                                @if(!$isOpening) required @endif id="g1relation" placeholder="Enter Guarantor Relation">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title @if(!$isOpening) RequiredStar @endif">Address</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round" name="guarantorAddresses[]"
                                            value="{{ @$firstGuarantor->address }}"
                                                @if(!$isOpening) required @endif placeholder="Enter Guarantor Address">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title @if(!$isOpening) RequiredStar @endif">Contact</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    {{ $mfnGnlConfig->countryCode }}
                                                </span>
                                            </div>
                                            <input type="text" class="form-control textNumber round mobileNo" name="guarantorPhones[]"
                                            value="{{ @$firstGuarantor->phone }}"
                                                @if(!$isOpening) required @endif placeholder="Enter Guarantor Contact No.">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title">Guarantor#2 Name</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round" name="guarantorNames[]"
                                            value="{{ @$secondGuarantor->name }}"
                                                placeholder="Enter Guarantor Name">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title">Relation</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round" name="guarantorRelations[]"
                                            value="{{ @$secondGuarantor->relation }}"
                                                id="g1relation" placeholder="Enter Guarantor Relation">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title">Address</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round" name="guarantorAddresses[]"
                                            value="{{ @$secondGuarantor->address }}"
                                                placeholder="Enter Guarantor Address">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title">Contact</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    {{ $mfnGnlConfig->countryCode }}
                                                </span>
                                            </div>
                                            <input type="text" class="form-control textNumber round mobileNo" name="guarantorPhones[]"
                                            value="{{ @$secondGuarantor->phone }}"
                                                placeholder="Enter Guarantor Contact No.">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Employment Related Information -->
                        <div class="panel panel-default">
                            <div class="panel-heading p-2 mb-4">Employment Related Information</div>
                            <div class="panel-body">

                                <div class="row">
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title">Self Employment</label>
                                        <div class="input-group">
                                            <select class="form-control clsSelect2" name="selfEmployment"
                                                id="selfEmployment">
                                                <option value="1">Yes</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Family Employment -->
                                    <div class="col-lg-6">
                                        <div class="input-title form-group mt-4 border-bottom">
                                            Family Employment
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <label class="input-title">Full Time Male</label>
                                                <div class="input-group">
                                                    <input type="text" name="familyFTM" class="form-control round textNumber"
                                                    value="{{ $loan->familyFTM }}"
                                                    placeholder="Enter Full Time Male">
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <label class="input-title">Full Time Female</label>
                                                <div class="input-group">
                                                    <input type="text" name="familyFTF" class="form-control round textNumber"
                                                    value="{{ $loan->familyFTF }}"
                                                    placeholder="Enter Full Time Female">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <label class="input-title">Part Time Male</label>
                                                <div class="input-group">
                                                    <input type="text" name="familyPTM" class="form-control round textNumber"
                                                    value="{{ $loan->familyPTM }}"
                                                    placeholder="Enter Part Time Male">
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <label class="input-title">Part Time Female</label>
                                                <div class="input-group">
                                                    <input type="text" name="familyPTF" class="form-control round textNumber"
                                                    value="{{ $loan->familyPTF }}"
                                                    placeholder="Enter Part Time Female">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <label class="input-title">Full Time Male Wage</label>
                                                <div class="input-group">
                                                    <input type="text" name="fullTimeMaleWage"
                                                    value="{{ $loan->fullTimeMaleWage }}"
                                                    class="form-control textNumber round"
                                                    placeholder="Enter Full Time Male Wage">
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <label class="input-title">Full Time Female Wage</label>
                                                <div class="input-group">
                                                    <input type="text" name="fullTimeFemaleWage"
                                                    value="{{ $loan->fullTimeFemaleWage }}"
                                                    class="form-control textNumber round"
                                                    placeholder="Enter Full Time Female Wage">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <label class="input-title">Business Name</label>
                                                <div class="input-group">
                                                    <input type="text" name="businessName"
                                                    value="{{ $loan->businessName }}"
                                                    class="form-control round"
                                                    placeholder="Enter Business Name">
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <label class="input-title">Business Location</label>
                                                <div class="input-group">
                                                    {{-- <input type="textArea" name="businessLocation"
                                                    value="{{ $loan->businessLocation }}"
                                                    class="form-control round"
                                                    placeholder="Enter Business Location"> --}}
                                                    
                                                    <textarea name="businessLocation" rows="2"
                                                        class="form-control round"
                                                        placeholder="Enter Business Location">{{ $loan->businessLocation }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Outside Family Employment -->
                                    <div class="col-lg-6">
                                        <div class="input-title form-group mt-4 border-bottom">
                                            Outside Family Employment
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <label class="input-title">Full Time Male</label>
                                                <div class="input-group">
                                                    <input type="text" name="outsideFTM" class="form-control textNumber round"
                                                    value="{{ $loan->outsideFTM }}"
                                                        placeholder="Full Time Male">
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <label class="input-title">Full Time Female</label>
                                                <div class="input-group">
                                                    <input type="text" name="outsideFTF" class="form-control textNumber round"
                                                    value="{{ $loan->outsideFTF }}"
                                                        placeholder="Full Time Female">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <label class="input-title">Part Time Male</label>
                                                <div class="input-group">
                                                    <input type="text" name="outsidePTM"
                                                    value="{{ $loan->outsidePTM }}"
                                                    class="form-control textNumber round"
                                                        placeholder="Part Time Male">
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <label class="input-title">Part Time Female</label>
                                                <div class="input-group">
                                                    <input type="text" name="outsidePTF" 
                                                    value="{{ $loan->outsidePTF }}" class="form-control textNumber round"
                                                        placeholder="Part Time Female">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <label class="input-title">Part Time Male Wage</label>
                                                <div class="input-group">
                                                    <input type="text" name="partTimeMaleWage"
                                                    value="{{ $loan->partTimeMaleWage }}"
                                                        class="form-control textNumber round" placeholder="Part Time Male Wage">
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <label class="input-title">Part Time Female Wage</label>
                                                <div class="input-group">
                                                    <input type="text" name="partTimeFemaleWage"
                                                    value="{{ $loan->partTimeFemaleWage }}"
                                                        class="form-control textNumber round" placeholder="Part Time Female Wage">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

                <div class="form-row form-group d-flex justify-content-center">
                    <div class="example example-buttons">
                        <a href="javascript:void(0)" id="next" class="btn btn-primary btn-round">Next</a>
                    </div>
                </div>

            </div>
            <!--  tab pane 2 (Photo) -->
            @php
            $profileImageSizes = explode(':', $mfnMemberConfig->profileImageSize);
            $profileImageWidth = $profileImageSizes[0];
            $profileImageHeight = $profileImageSizes[1];

            $signatureImageSizes = explode(':',$mfnMemberConfig->signatureImageSize);
            $signatureImageWidth = $signatureImageSizes[0];
            $signatureImageHeight = $signatureImageSizes[1];
            @endphp
            <div class="tab-pane show" id="photoPanel" role="tabpanel">

                <div class="row">
                    <div class="col-lg-12">

                        <!-- Upload Photo -->
                        <div class="panel panel-default">
                            <div class="panel-heading p-2">Upload Photo</div>
                            <div class="panel-body">
                                <!-- Profile Picture -->
                                <div class="well border-bottom">
                                    <h4 class="panel-title text-center">Member Profile Picture</h4>
                                    <div class="row">
                                        @if ($mfnGnlConfig->useWebCam == 'yes')
                                        <div class="col-md-6">
                                            <div style="width: 290px; height: 190px;">
                                                <div></div>
                                                <video id="profileVideo" autoplay="autoplay"
                                                    style="width: 290px; height: 190px;"></video>
                                            </div>
                                            <br>
                                            <input type="button" id="profileSnap" class="btn btn-success btn-round"
                                                value="Take Picture">
                                        </div>
                                        @endif

                                        @if ($mfnGnlConfig->useWebCam == 'yes')

                                        <div class="col-md-6">
                                            @else
                                            <div class="col-md-12">
                                                @endif
                                                <div class="">
                                                    <span>Your given image will appear here... </span><br>
                                                    @if ($mfnGnlConfig->useWebCam == 'yes')
                                                    <canvas id="profileCanvas" width="{{ $profileImageWidth }}"
                                                        height="{{ $profileImageHeight }}" hidden></canvas>
                                                    @endif
                                                    <input type="text" name="profileImageText" id="profileImageText"
                                                        hidden>
                                                    <img id="profileImagePreview"
                                                    @if (file_exists('images/members/profile') . '/'. $loan->memberProfileImage))
                                                    src="{{asset('images/members/profile') . '/'. $loan->memberProfileImage}}"
                                                    @endif
                                                    
                                                        class="img-responsive img-rounded full-width mt-2"
                                                        style="height: 150PX; width: 120PX;">
                                                </div>
                                                <input class="profileImage mt-2" name="profileImage" id="profileImage"
                                                    type="file">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Signature -->
                                    <div class="well border-bottom">
                                        <h4 class="panel-title text-center">Member National ID Signature</h4>
                                        <div class="row">
                                            @if ($mfnGnlConfig->useWebCam == 'yes')
                                            <div class="col-md-6">
                                                <div style="width: 290px; height: 190px;">
                                                    <div></div>
                                                    <video id="signatureVideo" autoplay="autoplay"
                                                        style="width: 290px; height: 190px;"></video>
                                                </div>
                                                <br>
                                                <input id="signatureSnap" type="button"
                                                    class="btn btn-success btn-round" value="Take Picture">
                                            </div>
                                            @endif

                                            @if ($mfnGnlConfig->useWebCam == 'yes')
                                            <div class="col-md-6">
                                                @else
                                                <div class="col-md-12">
                                                    @endif
                                                    <div class="">
                                                        <span>Your given image will appear here... </span><br>
                                                        @if ($mfnGnlConfig->useWebCam == 'yes')
                                                        <canvas id="signatureCanvas" width="{{ $signatureImageWidth }}"
                                                            height="{{ $signatureImageHeight }}" hidden></canvas>
                                                        @endif
                                                        <input type="text" name="signatureImageText"
                                                            id="signatureImageText" hidden>
                                                        <img id="signatureImagePreview"
                                                        src="{{asset('images/members/signature') . '/'. $loan->memberSignatureImage}}"
                                                            class="img-responsive img-rounded full-width mt-2"
                                                            style="height: 100PX; width: 200PX;">
                                                    </div>
                                                    <input class="signatureImage mt-2" name="signatureImage"
                                                        id="signatureImage" type="file">
                                                </div>
                                            </div>
                                        </div>

                                        {{-- First Guarantor Image --}}
                                        <div class="well border-bottom">
                                            <h4 class="panel-title text-center">First Guarantor Image</h4>
                                            <div class="row">
                                                @if ($mfnGnlConfig->useWebCam == 'yes')
                                                <div class="col-md-6">
                                                    <div style="width: 290px; height: 190px;">
                                                        <div></div>
                                                        <video id="firstGuarantorProfileVideo" autoplay="autoplay"
                                                            style="width: 290px; height: 190px;"></video>
                                                    </div>
                                                    <br>
                                                    <input id="firstGuarantorProfileSnap" type="button"
                                                        class="btn btn-success btn-round" value="Take Picture">
                                                </div>
                                                @endif

                                                @if ($mfnGnlConfig->useWebCam == 'yes')
                                                <div class="col-md-6">
                                                    @else
                                                    <div class="col-md-12">
                                                        @endif
                                                        <div class="">
                                                            <span>Your given image will appear here... </span><br>
                                                            @if ($mfnGnlConfig->useWebCam == 'yes')
                                                            <canvas id="firstGuarantorProfileCanvas"
                                                                width="{{ $profileImageWidth }}"
                                                                height="{{ $profileImageHeight }}" hidden></canvas>
                                                            @endif
                                                            <input type="text" name="firstGuarantorProfileImageText"
                                                                id="firstGuarantorProfileImageText" hidden>
                                                            <img id="firstGuarantorProfileImagePreview"
                                                            @if (@$firstGuarantor->profileImage != '')
                                                            src="{{ asset('images/loans/guarantors').'/'.$firstGuarantor->profileImage }}"
                                                            @endif
                                                                
                                                                class="img-responsive img-rounded full-width mt-2"
                                                                style="height: 150PX; width: 120PX;">
                                                        </div>
                                                        <input class="profileImage mt-2" name="firstGuarantorProfileImage"
                                                            id="firstGuarantorProfileImage" type="file">
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- end First Guarantor Image --}}

                                            {{-- First Guarantor Signature --}}
                                            <div class="well border-bottom">
                                                <h4 class="panel-title text-center">First Guarantor Signature</h4>
                                                <div class="row">
                                                    @if ($mfnGnlConfig->useWebCam == 'yes')
                                                    <div class="col-md-6">
                                                        <div style="width: 290px; height: 190px;">
                                                            <div></div>
                                                            <video id="firstGuarantorSignatureVideo" autoplay="autoplay"
                                                                style="width: 290px; height: 190px;"></video>
                                                        </div>
                                                        <br>
                                                        <input id="firstGuarantorSignatureSnap" type="button"
                                                            class="btn btn-success btn-round" value="Take Picture">
                                                    </div>
                                                    @endif

                                                    @if ($mfnGnlConfig->useWebCam == 'yes')
                                                    <div class="col-md-6">
                                                        @else
                                                        <div class="col-md-12">
                                                            @endif
                                                            <div class="">
                                                                <span>Your given image will appear here... </span><br>
                                                                @if ($mfnGnlConfig->useWebCam == 'yes')
                                                                <canvas id="firstGuarantorSignatureCanvas"
                                                                    width="{{ $signatureImageWidth }}"
                                                                    height="{{ $signatureImageHeight }}"
                                                                    hidden></canvas>
                                                                @endif
                                                                <input type="text" name="firstGuarantorSignatureImageText"
                                                                    id="firstGuarantorSignatureImageText" hidden>
                                                                <img id="firstGuarantorSignatureImagePreview"
                                                                @if (@$firstGuarantor->signatureImage != '')
                                                                src="{{ asset('images/loans/guarantor_signatures').'/'.$firstGuarantor->signatureImage }}"
                                                                @endif
                                                                    class="img-responsive img-rounded full-width mt-2"
                                                                    style="height: 100PX; width: 200PX;">
                                                            </div>
                                                            <input class="signatureImage mt-2"
                                                                name="firstGuarantorSignatureImage"
                                                                id="firstGuarantorSignatureImage" type="file">
                                                        </div>
                                                    </div>
                                                </div>
                                                {{-- end First Guarantor Signature --}}

                                                {{-- Second Guarantor Image --}}
                                                <div class="well border-bottom">
                                                    <h4 class="panel-title text-center">Second Guarantor Image</h4>
                                                    <div class="row">
                                                        @if ($mfnGnlConfig->useWebCam == 'yes')
                                                        <div class="col-md-6">
                                                            <div style="width: 290px; height: 190px;">
                                                                <div></div>
                                                                <video id="secondGuarantorProfileVideo" autoplay="autoplay"
                                                                    style="width: 290px; height: 190px;"></video>
                                                            </div>
                                                            <br>
                                                            <input id="secondGuarantorProfileSnap" type="button"
                                                                class="btn btn-success btn-round" value="Take Picture">
                                                        </div>
                                                        @endif

                                                        @if ($mfnGnlConfig->useWebCam == 'yes')
                                                        <div class="col-md-6">
                                                            @else
                                                            <div class="col-md-12">
                                                                @endif
                                                                <div class="">
                                                                    <span>Your given image will appear here...
                                                                    </span><br>
                                                                    @if ($mfnGnlConfig->useWebCam == 'yes')
                                                                    <canvas id="secondGuarantorProfileCanvas"
                                                                        width="{{ $profileImageWidth }}"
                                                                        height="{{ $profileImageHeight }}"
                                                                        hidden></canvas>
                                                                    @endif
                                                                    <input type="text" name="secondGuarantorProfileImageText"
                                                                        id="secondGuarantorProfileImageText" hidden>
                                                                    <img id="secondGuarantorProfileImagePreview"
                                                                    @if (@$secondGuarantor->profileImage != '')
                                                                    src="{{ asset('images/loans/guarantors').'/'.$secondGuarantor->profileImage }}"
                                                                    @endif
                                                                        class="img-responsive img-rounded full-width mt-2"
                                                                        style="height: 150PX; width: 120PX;">
                                                                </div>
                                                                <input class="profileImage mt-2"
                                                                    name="secondGuarantorProfileImage"
                                                                    id="secondGuarantorProfileImage" type="file">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    {{-- end Second Guarantor Image --}}

                                                    {{-- Second Guarantor Signature --}}
                                                    <div class="well">
                                                        <h4 class="panel-title text-center">Second Guarantor Signature
                                                        </h4>
                                                        <div class="row">
                                                            @if ($mfnGnlConfig->useWebCam == 'yes')
                                                            <div class="col-md-6">
                                                                <div style="width: 290px; height: 190px;">
                                                                    <div></div>
                                                                    <video id="secondGuarantorSignatureVideo"
                                                                        autoplay="autoplay"
                                                                        style="width: 290px; height: 190px;"></video>
                                                                </div>
                                                                <br>
                                                                <input id="secondGuarantorSignatureSnap" type="button"
                                                                    class="btn btn-success btn-round"
                                                                    value="Take Picture">
                                                            </div>
                                                            @endif

                                                            @if ($mfnGnlConfig->useWebCam == 'yes')
                                                            <div class="col-md-6">
                                                                @else
                                                                <div class="col-md-12">
                                                                    @endif
                                                                    <div class="">
                                                                        <span>Your given image will appear here...
                                                                        </span><br>
                                                                        @if ($mfnGnlConfig->useWebCam == 'yes')
                                                                        <canvas id="secondGuarantorSignatureCanvas"
                                                                            width="{{ $signatureImageWidth }}"
                                                                            height="{{ $signatureImageHeight }}"
                                                                            hidden></canvas>
                                                                        @endif
                                                                        <input type="text"
                                                                            name="secondGuarantorSignatureImageText"
                                                                            id="secondGuarantorSignatureImageText" hidden>
                                                                        <img id="secondGuarantorSignatureImagePreview"
                                                                        @if(@$secondGuarantor->signatureImage != '')
                                                                        src="{{ asset('images/loans/guarantor_signatures').'/'.$secondGuarantor->signatureImage }}"
                                                                        @endif
                                                                            class="img-responsive img-rounded full-width mt-2"
                                                                            style="height: 100PX; width: 200PX;">
                                                                    </div>
                                                                    <input class="signatureImage mt-2"
                                                                        name="secondGuarantorSignatureImage"
                                                                        id="secondGuarantorSignatureImage" type="file">
                                                                </div>
                                                            </div>
                                                            {{-- end Second Guarantor Signature --}}

                                                            <div
                                                                class="form-row form-group d-flex justify-content-center">
                                                                <div class="example example-buttons">
                                                                    <a href="javascript:void(0)" id="previous"
                                                                        class="btn btn-default btn-round">Previous</a>
                                                                    <button type="submit"
                                                                        class="btn btn-primary btn-round">Update</button>
                                                                </div>
                                                            </div>

                                                        </div>


                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
</form>

<link rel="stylesheet" href="{{asset('assets/css/selectize.bootstrap3.min.css')}}">
<script src="{{asset('assets/js/selectize.min.js')}}"></script>

<style>
    .selectize-control div.active {
        background-color: lightblue;
    }

    .selectize-control .lebel {
        color: #804739;
        font-weight: bold;
    }

</style>

<script type="text/javascript">
    // initialize variables
    var maxLoanAmount = "{{ $product->maxLoanAmount }}";
    var minLoanAmount = "{{ $product->minLoanAmount }}";
    var isInsuranceApplicable = "{{ $product->isInsuranceApplicable }}";
    var insuranceCalculationMethodId = "{{ $product->insuranceCalculationMethodId }}";
    var insurancePercentage = "{{ $product->insurancePercentage }}";
    var fixedInsuranceAmount = "{{ $product->fixedInsuranceAmount }}";
    var loanType = "{{ $loanType }}";
    var isOpening = "{{ $isOpening }}";

    $(document).ready(function () {

        // set values
        if (loanType == 'Regular') {
            $("#repaymentFrequencyId").val("{{ $loan->repaymentFrequencyId }}");
            setNumberOfInstallments();
            $("#numberOfInstallment").val("{{ $loan->numberOfInstallment }}");
        }
        if (loanType == 'Onetime') {
            $("#loanDurationInMonth").val("{{ $loan->loanDurationInMonth }}");
        }
        $("#loanPurpose").val("{{ $loan->loanPurposeId }}");
        $("#paymentType").val("{{ $loan->paymentType }}");
        $("#ledgerId").val("{{ $loan->ledgerId }}");
        

        $('form').submit(function (event) {
            event.preventDefault();
            // $(this).find(':submit').attr('disabled', 'disabled');

            $.ajax({
                    url: "{{ url()->current() }}",
                    type: 'POST',
                    dataType: 'json',
                    contentType: false,
                    data: new FormData(this),
                    processData: false,
                })
                .done(function (response) {
                    if (response['alert-type'] == 'error') {
                        swal({
                            icon: 'error',
                            title: 'Oops...',
                            text: response['message'],
                        });
                        $('form').find(':submit').prop('disabled', false);
                    } else {
                        $('form').trigger("reset");
                        swal({
                            icon: 'success',
                            title: 'Success...',
                            text: response['message'],
                            timer: 2000,
                            showConfirmButton: false
                        }).then(function () {
                            window.location.href = "./../";
                        });
                    }

                })
                .fail(function () {
                    console.log("error");
                });
        });


        if (isOpening) {
            var systemDate = new Date("{{ $sysDate }}");
            $('#disbursementDate').datepicker({
                dateFormat: 'dd-mm-yy',
                orientation: 'bottom',
                autoclose: true,
                todayHighlight: true,
                changeMonth: true,
                changeYear: true,
                yearRange: '1900:+10',
                maxDate: systemDate
            }).keydown(false);

            $('#firstRepayDate').datepicker({
                dateFormat: 'dd-mm-yy',
                orientation: 'bottom',
                autoclose: true,
                todayHighlight: true,
                changeMonth: true,
                changeYear: true,
                yearRange: '1900:+10',
            }).keydown(false);
        }


        /* on input loan cycle get cloan code */
        $("#loanCycle").on('input', function (e) {
            $("#loanCode").val('');

            if ($(this).val() == '') {
                return false;
            }

            var data = {
                context: 'loanCycle',
                memberId: "{{ $loan->memberId }}",
                productId: "{{ $loan->productId }}",
            }

            if (isOpening) {
                var loanCycle = 0;
                if ($('#loanCycle').val() != '') {
                    loanCycle = parseInt($('#loanCycle').val());
                }
                data.loanCycle = loanCycle;
            }

            $.ajax({
                type: "POST",
                url: "./../getData",
                data: data,
                dataType: "json",
                success: function (response) {
                    $("#loanCode").val(response['loanCode']);
                    $("#additionalFee").val(response['additionalFee']);
                },
                error: function () {
                    alert('error!');
                }
            });
        });
        /* end on input loan cycle get cloan code */

        function setNumberOfInstallments(){
            $('#numberOfInstallment option:gt(0)').remove();

            var installments = $("#repaymentFrequencyId option:selected").attr('installments');
            installments = installments.split(',');

            $.each(installments, function (index, value) {
                $('#numberOfInstallment').append("<option value=" + value + ">" + value +
                    "</option>");
            });
        }

        /* on selecting repaymentFrequencyId get the number of repaymmnets/ number of installments */
        $("#repaymentFrequencyId").change(function (e) {
            $('#numberOfInstallment option:gt(0)').remove();
            $('#firstRepayDate').val('');

            if ($(this).val() == '') {
                return false;
            }

            setNumberOfInstallments();

            // if (isOpening) {
            //     return false;
            // }

            // get first repay date
            var repaymentFrequencyId = $("#repaymentFrequencyId").val();

            if (repaymentFrequencyId == '') {
                return false;
            }

            $.ajax({
                type: "POST",
                url: "./../getData",
                data: {
                    context: 'repaymentFrequency',
                    memberId: "{{ $loan->memberId }}",
                    productId: "{{ $loan->productId }}",
                    disbursementDate: "{{ $loan->disbursementDate }}",
                    repaymentFrequencyId: repaymentFrequencyId
                },
                dataType: "json",
                success: function (response) {
                    $("#firstRepayDate").val(response['firstRepayDate']);
                },
                error: function () {
                    alert('error!');
                }
            });

        });
        /* end on selecting repaymentFrequencyId get the number of repaymmnets/ number of installments */

        $("#numberOfInstallment").change(function (e) {
            getInterestNInstallmentInformation();
        });

        /* for one time loan on selecting loanDurationInMonth get data */
        $("#loanDurationInMonth").change(function (e) {
            $('#firstRepayDate').val('');

            var memberId = $('#memberId').val();
            var productId = $('#productId').val();
            var disbursementDate = $('#disbursementDate').val();

            if ($(this).val() == '' || memberId == '' || productId == '') {
                return false;
            }

            $.ajax({
                type: "POST",
                url: "./../getData",
                data: {
                    context: 'onetimeLoanDuration',
                    memberId: "{{ $loan->memberId }}",
                    productId: "{{ $loan->productId }}",
                    disbursementDate: "{{ $loan->disbursementDate }}",
                    loanDurationInMonth: $('#loanDurationInMonth').val(),
                },
                dataType: "json",
                success: function (response) {
                    $("#firstRepayDate").val(response['firstRepayDate']);
                },
                error: function () {
                    alert('error!');
                }
            });
        });
        /* end for one time loan on selecting loanDurationInMonth get data */

        /* on input loan amount set values */
        $("#loanAmount").on('input', function () {

            $("#repayAmount, #ineterestAmount, #installmentAmount, #extraInstallmentAmount, #actualInstallmentAmount, #lastInstallmentAmount").val('');

            var loanAmount = 0;

            if ($("#loanAmount").val() != '') {
                loanAmount = parseFloat($("#loanAmount").val());
            }
            if (loanAmount < minLoanAmount || loanAmount > maxLoanAmount) {
                $("#maxMinLoanAmount").text('Enter Amount Between ' + minLoanAmount + ' to ' +
                    maxLoanAmount);
                $("#maxMinLoanAmount").show();
            } else {
                $("#maxMinLoanAmount").hide();
                if (loanType == 'Regular') {
                    getInterestNInstallmentInformation();
                }                
            }

            calculateInsurenceAmount();            
        });
        /* end on input loan amount set values */

        function calculateInsurenceAmount() {
            var insuranceAmount = 0;

            if (isInsuranceApplicable == 0) {
                $("#insuranceAmount").val(insuranceAmount);
                return true;
            }

            var loanAmount = 0;

            if ($("#loanAmount").val() != '') {
                loanAmount = parseFloat($("#loanAmount").val());
            }

            if (insuranceCalculationMethodId == 1) {
                insuranceAmount = Math.round(loanAmount * insurancePercentage / 100);
            }

            if (insuranceCalculationMethodId == 2) {
                insuranceAmount = fixedInsuranceAmount;
            }

            $("#insuranceAmount").val(insuranceAmount);
        }

        /* get loan schedule information */
        function getInterestNInstallmentInformation() {
            $("#interestCalMethod, #interestRate, #repayAmount, #ineterestAmount, #installmentAmount, #extraInstallmentAmount, #actualInstallmentAmount, #lastInstallmentAmount")
                .val('');

            var repaymentFrequencyId = $('#repaymentFrequencyId').val();
            var numberOfInstallment = $('#numberOfInstallment').val();

            var loanAmount = 0;

            if ($('#loanAmount').val() != '') {
                loanAmount = parseFloat($('#loanAmount').val());
            }

            if (repaymentFrequencyId == '' || numberOfInstallment == '') {
                return false;
            }

            $.ajax({
                type: "POST",
                url: "./../getData",
                data: {
                    context: 'InterestNInstallment',
                    productId: "{{ $loan->productId }}",
                    repaymentFrequencyId: repaymentFrequencyId,
                    numberOfInstallment: numberOfInstallment,
                    loanAmount: loanAmount,
                },
                dataType: "json",
                success: function (response) {
                    if (response['interestRate'] == null) {
                        alert('Interest Rate is not defined');
                        return false;
                    } else {
                        $("#interestCalMethod").val(response['interestCalMethod']);
                        $("#interestRate").val(response['interestRate'].interestRatePerYear);
                        $("#repayAmount").val(response['repayAmount']);
                        $("#ineterestAmount").val(response['ineterestAmount']);
                        $("#installmentAmount").val(response['installmentAmount']);
                        $("#extraInstallmentAmount").val(response['extraInstallmentAmount']);
                        $("#actualInstallmentAmount").val(response['actualInastallmentAmount']);
                        $("#lastInstallmentAmount").val(response['lastInstallmentAmount']);
                    }
                },
                error: function () {
                    alert('error!');
                }
            });
        }
        /* end get loan schedule information */

        // Next button in Savings Tab
        $("#next").click(function (e) {
            e.preventDefault();
            $("#photoTab").trigger('click');
        });
        $("#previous").click(function (e) {
            e.preventDefault();
            $("#detailsTab").trigger('click');
        });

        $("#paymentType").change(function (e) {
            if ($(this).val() == 'Bank') {
               
                var accTypeID = 5; 
                var selected = {{ $loan->ledgerId }}; 
                    $.ajax({
                        type: "POST",
                        url: "../../getBankLedgerId",
                        data: { 
                            accTypeID : accTypeID,
                            selected:selected,
                        },
                        dataType: "text",
                        success: function (data) {
                            $("#ledgerId").html(data);
                            
                    

                        },
                        error: function(){
                            alert('error!');
                        }
                    });

                $("#bankDiv").show('fast');
            } else {
                $("#bankDiv").hide('fast');
            }
        });

        /* mobile number length */
        $(document).on('input', '.mobileNo', function () {
            if ($(this).val().length != "{{ $mfnGnlConfig->mobileNoLength }}") {
                $(this).parents().eq(1).find('.help-block').html("Length should be " +
                    "{{ $mfnGnlConfig->mobileNoLength }}").show();
                $(this).focus();
            } else {
                $(this).parents().eq(1).find('.help-block').fadeOut('slow');
            }
        });
        /* end mobile number length */

        /* set image when choose file */
        $('#profileImage').change(function (event) {
            $('#profileImageText').val('');
            $('#profileImagePreview').attr('src', URL.createObjectURL(event.target.files[0]));
        });
        $('#signatureImage').change(function (event) {
            $('#signatureImageText').val('');
            $('#signatureImagePreview').attr('src', URL.createObjectURL(event.target.files[0]));
        });
        /* end set image when choose file */

        /* set first guarantor image when choose file */
        $('#firstGuarantorProfileImage').change(function (event) {
            $('#firstGuarantorProfileImageText').val('');
            $('#firstGuarantorProfileImagePreview').attr('src', URL.createObjectURL(event.target.files[0]));
        });
        $('#firstGuarantorSignatureImage').change(function (event) {
            $('#firstGuarantorSignatureImageText').val('');
            $('#firstGuarantorSignatureImagePreview').attr('src', URL.createObjectURL(event.target.files[0]));
        });
        /* end set first guarantor image when choose file */

        /* set second guarantor image when choose file */
        $('#secondGuarantorProfileImage').change(function (event) {
            $('#secondGuarantorProfileImageText').val('');
            $('#secondGuarantorProfileImagePreview').attr('src', URL.createObjectURL(event.target.files[0]));
        });
        $('#secondGuarantorSignatureImage').change(function (event) {
            $('#secondGuarantorSignatureImageText').val('');
            $('#secondGuarantorSignatureImagePreview').attr('src', URL.createObjectURL(event.target.files[0]));
        });
        /* end set second guarantor image when choose file */

        if ("{{$mfnGnlConfig->useWebCam}}" == 'yes') {
            /* web cam */
            var profileVideo = document.getElementById('profileVideo');
            var signatureVideo = document.getElementById('signatureVideo');
            var firstGuarantorProfileVideo = document.getElementById('firstGuarantorProfileVideo');
            var firstGuarantorSignatureVideo = document.getElementById('firstGuarantorSignatureVideo');
            var secondGuarantorProfileVideo = document.getElementById('secondGuarantorProfileVideo');
            var secondGuarantorSignatureVideo = document.getElementById('secondGuarantorSignatureVideo');

            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({
                    video: true
                }).then(function (stream) {
                    profileVideo.srcObject = stream;
                    profileVideo.play();

                    signatureVideo.srcObject = stream;
                    signatureVideo.play();

                    firstGuarantorProfileVideo.srcObject = stream;
                    firstGuarantorProfileVideo.play();

                    firstGuarantorSignatureVideo.srcObject = stream;
                    firstGuarantorSignatureVideo.play();

                    secondGuarantorProfileVideo.srcObject = stream;
                    secondGuarantorProfileVideo.play();

                    secondGuarantorSignatureVideo.srcObject = stream;
                    secondGuarantorSignatureVideo.play();
                });
            }

            // take profile image
            var profileCanvas = document.getElementById('profileCanvas');
            var profileContext = profileCanvas.getContext('2d');

            document.getElementById('profileSnap').addEventListener('click', function () {
                profileContext.drawImage(profileVideo, 0, 0, 340, 280);
                var image = profileCanvas.toDataURL("image/png");
                document.getElementById("profileImagePreview").src = image;
                document.getElementById("profileImageText").value = image;
            });

            // take Signature image
            var signatureCanvas = document.getElementById('signatureCanvas');
            var signatureContext = signatureCanvas.getContext('2d');

            document.getElementById('signatureSnap').addEventListener('click', function () {
                signatureContext.drawImage(signatureVideo, 0, 0, 340, 280);
                var image = signatureCanvas.toDataURL("image/png");
                document.getElementById("signatureImagePreview").src = image;
                document.getElementById("signatureImageText").value = image;
            });

            // take first Guarantor Profile image
            var firstGuarantorProfileCanvas = document.getElementById('firstGuarantorProfileCanvas');
            var firstGuarantorProfileContext = firstGuarantorProfileCanvas.getContext('2d');

            document.getElementById('firstGuarantorProfileSnap').addEventListener('click', function () {
                firstGuarantorProfileContext.drawImage(firstGuarantorProfileVideo, 0, 0, 340, 280);
                var image = firstGuarantorProfileCanvas.toDataURL("image/png");
                document.getElementById("firstGuarantorProfileImagePreview").src = image;
                document.getElementById("firstGuarantorProfileImageText").value = image;
            });

            // take first Guarantor Signature image
            var firstGuarantorSignatureCanvas = document.getElementById('firstGuarantorSignatureCanvas');
            var firstGuarantorSignatureContext = firstGuarantorSignatureCanvas.getContext('2d');

            document.getElementById('firstGuarantorSignatureSnap').addEventListener('click', function () {
                firstGuarantorSignatureContext.drawImage(firstGuarantorSignatureVideo, 0, 0, 340, 280);
                var image = firstGuarantorSignatureCanvas.toDataURL("image/png");
                document.getElementById("firstGuarantorSignatureImagePreview").src = image;
                document.getElementById("firstGuarantorSignatureImageText").value = image;
            });

            // take second Guarantor Profile image
            var secondGuarantorProfileCanvas = document.getElementById('secondGuarantorProfileCanvas');
            var secondGuarantorProfileContext = secondGuarantorProfileCanvas.getContext('2d');

            document.getElementById('secondGuarantorProfileSnap').addEventListener('click', function () {
                secondGuarantorProfileContext.drawImage(secondGuarantorProfileVideo, 0, 0, 340, 280);
                var image = secondGuarantorProfileCanvas.toDataURL("image/png");
                document.getElementById("secondGuarantorProfileImagePreview").src = image;
                document.getElementById("secondGuarantorProfileImageText").value = image;
            });

            // take second Guarantor Signature image
            var secondGuarantorSignatureCanvas = document.getElementById('secondGuarantorSignatureCanvas');
            var secondGuarantorSignatureContext = secondGuarantorSignatureCanvas.getContext('2d');

            document.getElementById('secondGuarantorSignatureSnap').addEventListener('click', function () {
                secondGuarantorSignatureContext.drawImage(secondGuarantorSignatureVideo, 0, 0, 340, 280);
                var image = secondGuarantorSignatureCanvas.toDataURL("image/png");
                document.getElementById("secondGuarantorSignatureImagePreview").src = image;
                document.getElementById("secondGuarantorSignatureImageText").value = image;
            });

            /* end web cam */
        }



    });

</script>


@endsection
