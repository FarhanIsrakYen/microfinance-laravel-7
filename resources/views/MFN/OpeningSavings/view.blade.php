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
                    Opening Savings Information
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Branch</td>
                <td>{{ $openingSavings->branch_name }}</td>
                <td>Loan Product</td>
                <td>{{ $openingSavings->loan_product }}</td>
            </tr>
            <tr>
                <td>Savings Product</td>
                <td>{{ $openingSavings->savings_product }}</td>
                <td>Gender</td>
                <td> {{ $openingSavings->gender }}</td>
            </tr>
            <tr>
                <td>Deposit Amount (Cumulative)</td>
                <td>{{ $openingSavings->cumulativeDeposit }}</td>
                <td>Interest Amount (Cumulative)</td>
                <td>{{ $openingSavings->cumulativeInterest }}</td>
            </tr>
            <tr>
                <td>Withdraw Amount (Cumulative)</td>
                <td> {{ $openingSavings->cumulativeWithdraw }}</td>
                <td>Closing Balance:</td>
                <td>{{ $openingSavings->closingBalance }}</td>
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