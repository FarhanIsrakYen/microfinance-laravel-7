@extends('Layouts.erp_master')

@section('content')
<!-- Page -->
<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th colspan="4" style="color: #000;">
                    Savings Withdraw Information
                </th>
            </tr>
        </thead>
        <tbody style="color: #000;">
            <tr>
              <input type="hidden" name="id" value="{{ encrypt($savingsWithdraw->id) }}">
                <td width="20%">Member</td>
                <td width="20%">{{$member->member}}</td>

                <td width="20%">Savings Account
                </td>
                <td width="20%">{{$savAccount->accountCode}}</td>
            </tr>
            <tr>

                <td width="20%">Withdraw  Date</td>
                <td width="20%">{{\Carbon\Carbon::parse($savingsWithdraw->date)->format('d-m-Y')}}</td>
                <td width="20%">Amount</td>
                <td width="20%">{{$savingsWithdraw->amount}}</td>
            </tr>

            @if($savingsWithdraw->transactionTypeId == 2 )
              <tr>
                  <td width="20%">Bank Account</td>
                  <td width="20%">{{$Ledger->name}}</td>

                  <td width="20%">Cheque No</td>
                  <td width="20%">{{$savingsWithdraw->chequeNo}}</td>

              </tr>
            @endif

            <tr>
              <td width="20%">Withdraw By</td>
              <td width="20%">{{$withdrawType}}</td>

              <td width="20%">Entry By</td>
              <td width="20%">{{$entryBy}}</td>
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
