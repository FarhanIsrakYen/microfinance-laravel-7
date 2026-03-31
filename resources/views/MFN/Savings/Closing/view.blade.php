@extends('Layouts.erp_master')

@section('content')

<div class="row">
    <div class="col-lg-1"></div>
    <div class="col-lg-7">

        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th colspan="4" style="color: #000;">
                            Savings Account Closed Information
                        </th>
                    </tr>
                </thead>
                <tbody style="color: #000;">
                    <tr>
                        <td width="20%" colspan="2" >Savings Account No.
                        </td>
                        <td width="20%" colspan="2" >{{$savAccount->accountCode}}</td>
                    </tr>
                <input type="hidden" id="accid" value="{{$accClose->accountId}}" >
                    <tr>
                      <input type="hidden" name="id" value="{{ encrypt($savingsWithdraw->id) }}">
                        <td width="20%">Member</td>
                        <td width="20%">{{$member->name}}</td>
                        <td width="20%">Member Code</td>
                        <td width="20%">{{$member->memberCode}}</td>
                        
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
                      <td width="20%">Method</td>
                      <td width="20%">{{$withdrawType}}</td>
        
                      <td width="20%">Closed By</td>
                      <td width="20%">{{$entryBy}}</td>
                    </tr>
                </tbody>
            </table>
         
        </div>
       

    </div>
    <div class="col-lg-3 align-items-left">
        <table class="table table-bordered table-striped">
            <tr>
                <td>Authorized Deposit</td>
                <td class="savInfo authorizedDeposit"></td>
            </tr>
            <tr>
                <td>Unauthorized Deposit</td>
                <td class="savInfo unauthorizedDeposit"></td>
            </tr>
            <tr>
                <td>Total Deposit</td>
                <td class="savInfo totalDeposit"></td>
            </tr>
            <tr>
                <td>Authorized Withdraw</td>
                <td class="savInfo authorizedWithdraw"></td>
            </tr>
            <tr>
                <td>Unauthorized Withdraw</td>
                <td class="savInfo unauthorizedWithdraw"></td>
            </tr>                
            <tr>
                <td>Total Withdraw</td>
                <td class="savInfo totalWithdraw"></td>
            </tr>                
            <tr>
                <td>Balance</td>
                <td class="savInfo balance"></td>
            </tr>
            
        </table>
    </div>
    <div class="col-lg-1"></div>
</div>
<div class="row align-items-center">
    <div class="col-lg-12">
        <div class="form-group d-flex justify-content-center">
            <div class="example example-buttons">
                <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round d-print-none">Back</a>
                    <a href="javascript:void(0)" onClick="window.print();" class="btn btn-default btn-round clsPrint d-print-none">Print</a>
            </div>
        </div>
    </div>
</div>
<!-- Page -->

<!-- End Page -->

<script>

    // initialize some variables
    var initialAmount = 0;
    var isAuthorized = 0;
    var authorizedDeposit = 0;
    var unauthorizedDeposit = 0;
    var totalDeposit = 0;
    var authorizedWithdraw = 0;
    var unauthorizedWithdraw = 0;
    var totalWithdraw = 0;
    var balance = 0;
$(document).ready(function () {
  
    function getAccountInfo(){
        var accountId = $('#accid').val();
            $.ajax({
                type: "POST",
                url: "./../getData",
                data: {context : 'account', accountId : accountId},
                dataType: "json",
                success: function (response) {
                    $('.authorizedDeposit').html(response.authorizedDeposit);
                    $('.unauthorizedDeposit').html(response.unauthorizedDeposit);
                    $('.totalDeposit').html(response.totalDeposit);
                    $('.authorizedWithdraw').html(response.authorizedWithdraw);
                    $('.unauthorizedWithdraw').html(response.unauthorizedWithdraw);
                    $('.totalWithdraw').html(response.totalWithdraw);
                    $('.balance').html(response.balance);

                    authorizedDeposit = response.authorizedDeposit;
                    unauthorizedDeposit = response.unauthorizedDeposit;
                    totalDeposit = response.totalDeposit;
                    authorizedWithdraw = response.authorizedWithdraw;
                    unauthorizedWithdraw = response.unauthorizedWithdraw;
                    totalWithdraw = response.totalWithdraw;
                    balance = response.balance;
                },
                error: function(){
                    alert('error!');
                }
            });
        }
        getAccountInfo();
       

    

});



</script>

@endsection
