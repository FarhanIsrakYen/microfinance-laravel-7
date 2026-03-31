@extends('Layouts.erp_master')

@section('content')


<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th colspan="4">
                    Branch Product Information
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="25%">Branch</td>
                <td width="25%">{{ $branchName }}</td>
                <td width="25%">Branch Code</td>
                <td width="25%">{{ $branchCode }}</td>
            </tr>
            <tr>
                <td width="25%">Loan Products</td>
                <td width="25%">{{ $loanProduct }}</td>
                <td width="25%">Savings Products</td>
                <td width="25%">{{ $savingsProduct }}</td>
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