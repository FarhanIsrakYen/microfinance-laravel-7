@extends('Layouts.erp_master')

@section('content')

<!-- <style>
    table tbody tr td{
        width: 25%;
    }
</style> -->


<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th colspan="4">
                    Loan Products Information
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Product</td>
                <td>{{ $product->name }}</td>
                <td>Short Name</td>
                <td>{{ $product->shortName }}</td>
            </tr>
            <tr>
                <td>Code</td>
                <td> {{ $product->name }}</td>
                <td>Type</td>
                <td>{{ $productType->name }}</td>
            </tr>
            <tr>
                <td>Category</td>
                <td> {{ $productCat->name }}</td>
                <td>Funding Organization</td>
                <td>{{ $fundingOrg->name }}</td>
            </tr>
            <tr>
                <td>PKSF Fund</td>
                <td> {{ $pksfFund }}</td>
                <td>Maximum Loan Amount</td>
                <td>{{ $product->maxLoanAmount }}</td>
            </tr>
            <tr>
                <td>Primary Product</td>
                <td> {{ $isPrimaryProduct }}</td>
                <td>Minimum Loan Amount</td>
                <td>{{ $product->minLoanAmount }}</td>
            </tr>
            <tr>
                <td>Start Date</td>
                <td>{{ $startDate }}</td>
                <td>Average Loan Amount</td>
                <td>{{ $product->avgLoanAmount }}</td>
            </tr>
            <tr>
                <td>Eligible Write Off Years</td>
                <td>{{ $product->maxLoanAmount }}</td>
                <td>Insurance Applicable</td>
                <td>{{ $isInsuranceApplicable }}</td>
            </tr>
            @if ($product->isInsuranceApplicable == 1)
            <tr>
                <td>Insurance Calculation Method</td>
                <td>{{ $insCalcMethod->name }}</td>
                <td>Insurance Percentage</td>
                <td>{{ $product->insuranceCalculationMethodId == 1 ? $product->insurancePercentage : 'N/A'}}</td>
            </tr>
            @endif            
            <tr>
                <td>Fixed Insurance Amount</td>
                <td>{{ $product->insuranceCalculationMethodId == 2 ? $product->fixedInsuranceAmount : 'N/A'}}</td>
                <td>Mandatory Savings Percentage</td>
                <td>{{ $product->mandatorySavingsPercantage }}</td>
            </tr>
            <tr>
                <td>Multiple Loan Allowed</td>
                <td>{{ $isMultipleLoanAllowed }}</td>
                <td>Additional Fee For First Time</td>
                <td>{{ $product->additionalFreeForFirstTime }}</td>
            </tr>
            <tr>
                <td>Form Fee</td>
                <td>{{ $product->formFee }}</td>
                <td>Additional Fee</td>
                <td>{{ $product->additionalFee }}</td>
            </tr>
            <tr>
                <td>Repayment Info</td>                
                <td colspan="3">
                    @if ($product->productTypeId == 1)
            <div class="form-row form-group align-items-center" id="elgblRepayFreqRow">
                <div class="col-lg-12">
                    <div class="row">
                        <label class="col-lg-4 input-title grey-500">Repayment Frequency</label>
                        <label class="col-lg-4 input-title grey-500">Grace Period</label>
                        <label class="col-lg-4 input-title grey-500">Installments</label>
                    </div>

                    @foreach ($repaymentFrequencies as $repaymentFrequency)
                    @php
                    $isActive = in_array($repaymentFrequency->id,
                    $regularLoanRepaymentInfo->pluck('repaymentFrequencyId')->toArray()) ? true : false;

                    if (!$isActive) {
                        continue;
                    }
                    @endphp
                    <div class="row mb-2 freqDaysRow">
                        <div class="col-lg-4 input-group">
                            <div class="checkbox-custom checkbox-primary">
                                <label style="padding-left: 4px">{{ $repaymentFrequency->name }}</label>
                            </div>
                        </div>
                        <div class="col-lg-4 input-group">
                            <input type="text" class="form-control textNumber edit_freq" name="gracePeriods[]"
                                placeholder="No of days"
                                value="{{ $regularLoanRepaymentInfo->where('repaymentFrequencyId', $repaymentFrequency->id)->first()->gracePeriod }}" readonly>
                        </div>
                        <div class="col-lg-4 input-group">
                            <input type="text" class="form-control numberWithcomma edit_freq" name="installments[]"
                                placeholder="12,18,24" regex="/^\d+(,\d+)*$" /
                                value="{{ $regularLoanRepaymentInfo->where('repaymentFrequencyId', $repaymentFrequency->id)->first()->eligibleNumberOfInstallments }}" readonly>
                        </div>
                    </div>
                    @endforeach

                </div>
            </div>
            @else
            <div class="form-row form-group align-items-center" id="elgblPeriodsRow">
                <div class="col-lg-12">
                    <div class="row">
                        <label class="col-lg-4 input-title grey-500">Repayment Frequency</label>
                        <label class="col-lg-8 input-title grey-500">Periods In Month</label>
                    </div>

                    <div class="row mb-2">
                        <div class="col-lg-4 input-group">
                            <div class="checkbox-custom checkbox-primary">
                                <label style="padding-left: 4px">One Time</label>
                            </div>
                        </div>

                        <div class="col-lg-8 input-group">
                            <input type="text" class="form-control numberWithcomma" name="otElgdMonths"
                                value="{{ $otRepayMonths }}" placeholder="12,18,24" regex="/^\d+(,\d+)*$"  readonly/>
                        </div>
                    </div>

                </div>
            </div>
            @endif
            </td>                
            </tr>

        </tbody>
    </table>

    {{-- Interests Block --}}
    <div class="panel-heading p-2 mb-4">
        <a href="./../interest/add/{{$product->id}}" class="float-right blue-grey-700">
            <i class="fa fa-plus-square fa-lg" aria-hidden="true"></i>
            <span style="font-size:13px;">Add/Update</span>
        </a>
    </div>
    @if ($product->productTypeId == 1)
    <table class="table table-striped table-bordered" style="text-align: center;">
        <thead>
            <tr>
                <th>SL</th>
                <th>Repayment Frequency</th>
                <th>Number of Installment</th>
                <th>Interest Rate</th>
                <th>Interest Index (Per Year)</th>
                <th>Interest Index</th>
                <th>Effective From</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php $i=1 ?>
            @foreach($productInterestRates as $prodInt)
                <tr>
                    <td>{{ $i++ }}</td>
                    <td>{{ $repaymentFrequencies->where('id', $prodInt->repaymentFrequencyId)->first()->name }}</td>
                    <td>{{ $prodInt->numberOfInstallment }}</td>
                    <td>{{ $prodInt->interestRatePerYear }}</td>
                    <td>{{ $prodInt->interestRateIndexPerYear }}</td>
                    <td>{{ $prodInt->interestRateIndex }}</td>
                    <td>{{ \Carbon\Carbon::parse($prodInt->effectiveDate)->format('d-m-Y') }}</td>
                    <td>{{ $prodInt->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @elseif($product->productTypeId == 2)
    <table class="table table-striped table-bordered" style="text-align: center;">
        <thead>
            <tr>
                <th>SL</th>
                <th>Interest Rate</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php $i=1 ?>
            @foreach($productInterestRates as $prodInt)
                <tr>
                    <td>{{ $i++ }}</td>
                    <td>{{ $prodInt->interestRatePerYear }}</td>
                    <td>{{ $prodInt->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif
    
    {{-- End Interests Block --}}
</div>

<div class="row align-items-center">
    <div class="col-lg-12">
        <div class="form-group d-flex justify-content-center">
            <div class="example example-buttons">
                <a href="javascript:void(0)" onclick="goBack();"
                    class="btn btn-default btn-round d-print-none">Back</a>
            </div>
        </div>
    </div>
</div>

@endsection