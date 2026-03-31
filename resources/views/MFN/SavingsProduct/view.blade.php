@extends('Layouts.erp_master')

@section('content')
<!-- Page -->

<style>
    table tbody tr td {
        width: 20%;
    }

</style>

<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th colspan="4" style="color: #000;">
                    Savings Product
                </th>
            </tr>
        </thead>
        <tbody style="color: #000;">
            <tr>
                <td>Name</td>
                <td>{{ $product->name }}</td>
                <td>Short Name</td>
                <td>{{ $product->shortName }}</td>
            </tr>

            <tr>
                <td>Product Code</td>
                <td>{{ $product->productCode }}</td>
                <td>Product type</td>
                <td>{{ $productType }}</td>
            </tr>

            <tr>
                <td>Effective Date</td>
                <td>{{ \Carbon\Carbon::parse($product->effectiveDate)->format('d-m-Y') }}</td>
                <td>Minimum Savings Balance</td>
                <td>{{ $product->productTypeId == 2 ? $product->minimumSavingsBalance : 'N\A' }}</td>
            </tr>

            <tr>
                <td>Collection Frequency</td>
                <td>{{ $collectionFrequency }}</td>
                <td>Generate Interest Probation?</td>
                <td>{{ $product->generateInterestProbation }}</td>
            </tr>
            <tr>
                <td>Multiple Savings Allowed?</td>
                <td>{{ $product->isMultipleSavingsAllowed }}</td>
                <td>Nominee Required?</td>
                <td>{{ $product->isNomineeRequired }}</td>
            </tr>
            <tr>
                <td>Closing Charge Applicable?</td>
                <td>{{ $product->isClosingChargeApplicable }}</td>
                <td>Closing Charge</td>
                <td>{{ $product->isClosingChargeApplicable == 'Yes' ? $product->closingCharge : 'N/A' }}</td>
            </tr>
            <tr>
                <td>Partial Withdraw Allowed?</td>
                <td>{{ $product->isPartialWithdrawAllowed == 'YesIfNoLoan' ? 'Yes, If No Loan' : $product->isPartialWithdrawAllowed }}
                </td>
                <td>Due Member Getting Interest?</td>
                <td>{{ $product->isDueMemberGettingInterest }}</td>
            </tr>
            <tr>
                <td>On Closing Interest Editable?</td>
                <td>{{ $product->onClosingInterestEditable }}</td>
                <td>Status</td>
                <td>{{ $product->status == 1 ? 'Active' : 'Inactive' }}</td>
            </tr>
            @if ($product->productTypeId == 1)
            <tr>
                <td>Interest Calculation Method</td>
                <td>{{ $interestCalculationMethod }}</td>
                @if ($product->interestAvgMethodPeriodId == 2)
                <td>Interest Avg MethodPeriod</td>
                <td>{{ $interestAvgMethodPeriod }}</td>
                @else
                <td></td>
                <td></td>
                @endif
            </tr>
            @endif

        </tbody>
    </table>

    <br>

    {{-- Interests Block --}}
    <div class="panel-heading p-2 mb-4">
        <a href="./../interest/add/{{$product->id}}" class="float-right addFDRInterest blue-grey-700">
            <i class="fa fa-plus-square fa-lg" aria-hidden="true"></i>
            <span style="font-size:13px;">Add/Update</span>
        </a>
    </div>
    @if ($product->productTypeId == 1)
    <table class="table table-striped table-bordered" style="text-align: center;">
        <thead>
            <tr>
                <th>SL</th>
                <th>InterestRate</th>
                <th>Effective Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @php
            $sl = 1;
            @endphp
            @foreach ($interests as $interest)
            <tr>
                <td style="width: 10%;">{{ $sl++ }}</td>
                <td>{{ $interest->interestRate }}</td>
                <td>{{ \Carbon\Carbon::parse($interest->effectiveDate)->format('d-m-Y') }}</td>
                {{-- <td>{{ $interest->status == 1 ? 'Active' : 'Inactive' }}</td> --}}
                <td style="width: 10%;">
                    @if ($interest->status == 1)
                    <span class="badge badge-primary" style="font-size: 12px;">Active</span>
                    @else
                    <span class="badge badge-danger" style="font-size: 12px;">Inactive</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @elseif($product->productTypeId == 2)
    <table class="table table-striped table-bordered" style="text-align: center;">
        <thead>
            <tr>
                <th>SL</th>
                <th>Duration (Month)</th>
                <th>Parent Period (Month)</th>
                <th>InterestRate</th>
                <th>Effective Date</th>
                <th>Status</th>
                <th>Partials</th>
            </tr>
        </thead>
        <tbody>
            @php
            $sl = 1;
            $maturePeriodInterests = $interests->where('parentId', 0);
            // dd($interests, $maturePeriodInterests);
            @endphp
            @foreach ($maturePeriodInterests as $maturePeriodInterest)
            <tr>
                <td style="width: 10%;">{{ $sl++ }}</td>
                <td>{{ $maturePeriodInterest->durationMonth }}</td>
                <td>N\A</td>
                <td>{{ $maturePeriodInterest->interestRate }}</td>
                <td>{{ \Carbon\Carbon::parse($maturePeriodInterest->effectiveDate)->format('d-m-Y') }}</td>
                <td style="width: 10%;">
                    @if ($maturePeriodInterest->status == 1)
                    <span class="badge badge-primary" style="font-size: 12px;">Active</span>
                    @else
                    <span class="badge badge-danger" style="font-size: 12px;">Inactive</span>
                    @endif
                </td>
                @if (count($interests->where('parentId', $maturePeriodInterest->id)) > 0)
                <td>
                    <a href="./../interest/view/{{ $maturePeriodInterest->id }}" title="View" class="btnView">
                        <i class="icon wb-eye mr-2 blue-grey-600"></i>
                    </a>
                </td>
                @else
                <td>N/A</td>
                @endif

            </tr>
            {{-- @php
                $partialInterests = $interests->where('parentId', $maturePeriodInterest->id);
            @endphp
            @foreach ($partialInterests as $partialInterest)
            <tr>
                <td style="width: 10%;">{{ $sl++ }}</td>
            <td>{{ $partialInterest->durationMonth }}</td>
            <td>{{ $maturePeriodInterest->durationMonth }}</td>
            <td>{{ $partialInterest->interestRate }}</td>
            <td>{{ \Carbon\Carbon::parse($partialInterest->effectiveDate)->format('d-m-Y') }}</td>
            <td style="width: 10%;">
                @if ($partialInterest->status == 1)
                <span class="badge badge-primary" style="font-size: 12px;">Active</span>
                @else
                <span class="badge badge-danger" style="font-size: 12px;">Inactive</span>
                @endif
            </td>
            </tr>
            @endforeach --}}
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- End Interests Block --}}

    <div class="row align-items-center d-print-none">
        <div class="col-lg-12 ">
            <div class="form-group d-flex justify-content-center ">
                <div class="example example-buttons">
                    <a href="javascript:void(0)" onclick="goBack();"
                        class="btn btn-default btn-round d-print-none">Back</a>
                    <a href="javascript:void(0)" onClick="window.print();"
                        class="btn btn-default btn-round clsPrint">Print</a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Page -->

@endsection
