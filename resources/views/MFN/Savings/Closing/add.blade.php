@extends('Layouts.erp_master')

@section('content')
<?php          use App\Services\MfnService as MFN;
               use App\Services\CommonService as Common;
                        $BranchID = (isset($BranchID) && !empty($BranchID)) ? $BranchID : Common::getBranchId();
                        $StartDate = (isset($StartDate) && !empty($StartDate)) ? $StartDate : MFN ::systemCurrentDate($BranchID);
                        

                        // dd($StartDate);
                    ?>

<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator" novalidate="true"
autocomplete="off">

    @csrf
    <div class="row">
        <div class="col-lg-1"></div>
        <div class="col-lg-7">

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Member</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="memberId" id="memberId" required
                            data-error="Please Select Member">
                            <option value="">Select Member</option>
                            @foreach ($memberDetails as $row)
                            <option value="{{ $row->id }}">{{ $row->name.'('.$row->memberCode.')' }}</option>
                            @endforeach
                        </select>
                           
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title">Account Code</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="accountId" id="accountId" required
                        data-error="Please Select Account">
                        <option value="">Select Account</option>
                       
                    </select>
                    </div>
                </div>
            </div>

         
            

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Closing Date</label>
                <div class="col-lg-5">
                    <div class="input-group ghdatepicker">
                        <div class="input-group-prepend ">
                            <span class="input-group-text ">
                                <i class="icon wb-calendar round" aria-hidden="true"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control round" name="closingDate" id="closing_date"
                             placeholder="DD-MM-YYYY" value="{{date('d-m-Y', strtotime($branchDate)) }}" readonly>
                    </div>
                    
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title">Method of Payment</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="transactionTypeId" id="transactionTypeId" required
                       >
                        <option value="1">Cash</option>
                        <option value="2">Bank</option>
                       
                    </select>
                    </div>
                </div>
            </div>
            <div id="bankDiv" style="display: none;">
                <div class="form-row form-group align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">Bank Account</label>
                    <div class="col-lg-5">
                        <div class="input-group">
                            <select class="form-control" name="ledgerId" id="ledgerId"
                                data-error="Please Select Bank Account">
                                <option value="">Select</option>
                            </select>
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
    
                <div class="form-row form-group align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">Cheque No</label>
                    <div class="col-lg-5">
                        <div class="input-group">
                            <input type="text" class="form-control" name="chequeNo">
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>
            <input type="hidden" class="form-control round" name="closingAmount" id="total_balance">
            
            
           

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
                    <a href="javascript:void(0)" onclick="goBack();"
                            class="btn btn-default btn-round d-print-none">Back</a>
                    <button type="submit" class="btn btn-primary btn-round">Save</button>
                </div>
            </div>
        </div>
    </div>
</form>




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

    $('form').submit(function (event) {
            event.preventDefault();
            $(this).find(':submit').attr('disabled', 'disabled');

            $.ajax({
                    url: "{{ url()->current() }}",
                    type: 'POST',
                    dataType: 'json',
                    data: $('form').serialize(),
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
                        });

                        setTimeout(function () {
                            window.location = './'
                        }, 3000);
                    }


                })
                .fail(function (e) {
                    console.log('shfghj');
                })
                .always(function () {
                    console.log("complete");
                });

        });

    $('#memberId').change(function() {

        var memberId = $('#memberId').val();
        // console.log('data');
        if (memberId != '') {

            $.ajax({
                method: "GET",
                url: "{{route('closingaccountDetails')}}",
                dataType: "text",
                data: {
                    memberId: memberId,
                },
                success: function(data) {
                    if (data) {

                        $('#accountId').html(data); 
                        // console.log(data);
                        // $('#current_stock').html(stock);
                        // $('#stock_quantity_0').val(stock);

                    }
                }
            });
        }
    
    });

    /* deposit type */
    $("#transactionTypeId").change(function (e) { 
            if ($(this).val() == 2) {
                $("#bankDiv").show('slow');
            }
            else{
                $("#bankDiv").hide('slow');
            }
        });

    $('#accountId').change(function() {

        var accountId = $('#accountId').val();
        //console.log('data');

        var total_balance = $(this).find("option:selected").attr('total_balance');
        
        // $('#total_balance').val(total_balance);
       

        getAccountInfo();
    });

    function getAccountInfo(){
        var accountId = $('#accountId').val();
            $.ajax({
                type: "POST",
                url: "./getData",
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
                    $('#total_balance').val(response.balance);
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
       

    

});



</script>


@endsection