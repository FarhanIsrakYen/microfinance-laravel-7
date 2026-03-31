@extends('Layouts.erp_master')

@section('content')
<!-- Page -->
<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th colspan="4" style="color: #000;">
                    Savings Acconut Information
                </th>
            </tr>
        </thead>
        <tbody style="color: #000;">
            <tr>
                <td width="20%">Member</td>
                <td width="20%">{{$member->member}}</td>

                <td width="20%">Savings Product
                </td>
                <td width="20%">{{$savProduct->savProduct}}</td>
            </tr>
            <tr>
                <input type="hidden" name="id" value="{{ encrypt($savingsAcc->id) }}">
                <td width="20%">Savings Cycle</td>
                <td width="20%">{{$savingsAcc->savingsCycle}}</td>
                <td width="20%">Savings Account</td>
                <td width="20%">{{$savingsAcc->accountCode}}</td>
            </tr>
            
            <tr>
                <td width="20%">Opening Date
                </td>
                <td width="20%">{{ \Carbon\Carbon::parse($savingsAcc->openingDate)->format('d-m-Y')}}</td>

                <td width="20%">Product Type
                </td>
                <td width="20%">{{$prodType}}</td>
            </tr>
            <tr>
                <td width="20%">Collection Frequency
                </td>
                <td width="20%">{{$collFreq}}</td>

                <td width="20%">Period (MONTH)
                </td>
                <td width="20%"> {{$savingsAcc->periodMonth}}</td>
            </tr>
            <tr>
                <td width="20%">Interest Rate
                </td>
                <td width="20%">{{$savingsAcc->interestRate}}</td>
                <td width="20%">Fixed Deposit Amount
                </td>
                <td width="20%">{{$savingsAcc->autoProcessAmount}}</td>
            </tr>
            <tr>

                <td width="20%">Payable Amount
                </td>
                <td width="20%">{{$payableAmt}}</td>

                <td width="20%">Balance</td>
                <td width="20%">{{$Balance}}</td>
            </tr>
        </tbody>
    </table>
    <div class="row align-items-center  d-print-none">
        <div class="col-lg-12">
            <div class="form-group d-flex justify-content-center">
                <div class="example example-buttons">
                    <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round d-print-none">Back</a>
                    <a href="javascript:void(0)" onClick="window.print();" class="btn btn-default btn-round clsPrint d-print-none">Print</a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Page -->


@endsection
