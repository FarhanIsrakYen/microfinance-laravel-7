@extends('Layouts.erp_master')

@section('content')

<style>
    table tbody tr td{
        width: 25%;
    }
</style>
<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th colspan="4">
                    Opening Loan Information
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Branch</td>
                <td>{{ $openingLoan->branch_name }}</td>
                <td>Loan Product</td>
                <td>{{ $openingLoan->product_name }}</td>
            </tr>
            <tr>
                <td>Gender</td>
                <td> {{ $openingLoan->gender }}</td>
                <td>Loan Disbursement (Cumulative):</td>
                <td>{{ $openingLoan->cumulativeDisbursement }}</td>
            </tr>
            <tr>
                <td>Total Loan Repay - with Service Charge (Against Cumulative Loan Disburse)</td>
                <td>{{ $openingLoan->cumulativeRepay }}</td>
                <td>Loan Recovery -Principle (Cumulative)</td>
                <td>{{ $openingLoan->cumulativeCollectionPrincipal }}</td>
            </tr>
            <tr>
                <td>Loan Recovery -with Service Charge (Cumulative)</td>
                <td> {{ $openingLoan->cumulativeCollection }}</td>
                <td>Write Off Amount -with Service Charge (Cumulative):</td>
                <td>{{ $openingLoan->cumulativeWriteOff }}</td>
            </tr>
            <tr>
                <td>Write Off Number (Cumulative)</td>
                <td>{{ $openingLoan->cumulativeWriteOffNumber }}</td>
                <td>Write Off Amount - Principle (Cumulative)</td>
                <td>{{ $openingLoan->cumulativeWriteOffPrincipal }}</td>
            </tr>
            <tr>
                <td>Loan Waiver Amount (Cumulative)</td>
                <td> {{ $openingLoan->cumulativeWaiver }}</td>
                <td>Loan Waiver Amount - Principle (Cumulative):</td>
                <td>{{ $openingLoan->cumulativeWaiverPrincipal }}</td>
            </tr>
            <tr>
                <td>Loan Rebate Amount (Cumulative)</td>
                <td>{{ $openingLoan->cumulativeRebate }}</td>
                <td>Fully Paid Borrower No. (Cumulative)</td>
                <td>{{ $openingLoan->cumulativeFullyPaidBorrowerNo }}</td>
            </tr>
            <tr>
                <td>Borrower No. (Cumulative)</td>
                <td> {{ $openingLoan->cumulativeBorrowerNo }}</td>
                <td>Loan No. (Cumulative):</td>
                <td>{{ $openingLoan->cumulativeLoanNo }}</td>
            </tr>

        </tbody>
    </table>
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