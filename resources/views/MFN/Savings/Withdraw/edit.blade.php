@extends('Layouts.erp_master')

@section('content')

<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator" novalidate="true"
autocomplete="off">

    @csrf

    <div class="row">
        <div class="col-lg-1"></div>
        <div class="col-lg-7">

            <input type="hidden" name="id" value="{{ encrypt($withdraw->id) }}">

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title">Member</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control" value="{{ $member }}" readonly>
                    </div>
                </div>
            </div>   
            
            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title">Savings Account</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control" value="{{ $savAccount }}" readonly>
                    </div>
                </div>
            </div>
            
            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title">Withdraw Date</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control" name="date" value="{{ \Carbon\Carbon::parse($withdraw->date)->format('d-m-Y') }}" readonly>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Amount</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control textNumber" 
                        name="amount" id="amount" value="{{ $withdraw->amount }}"
                        required data-error="Please give Amount">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            
            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Withdraw By</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <select class="form-control" name="transactionTypeId" id="transactionTypeId" required
                            data-error="Please Select Withdraw By">
                            <option value="1">Cash</option>
                            <option value="2">Bank</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
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
                            <input type="text" class="form-control" name="chequeNo" value="{{ $withdraw->chequeNo }}">
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>            

            <div class="row align-items-center">
                <div class="col-lg-12">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();"
                                    class="btn btn-default btn-round d-print-none">Back</a>
                            <button type="submit" class="btn btn-primary btn-round">Update</button>
                        </div>
                    </div>
                </div>
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
    </div>

    </div>
    </div>
</form>


<script type="text/javascript">
    // initialize some variables
    var initialAmount = "{{ $withdraw->amount }}";
    var isAuthorized = "{{ $withdraw->isAuthorized }}";
    var authorizedDeposit = 0;
    var unauthorizedDeposit = 0;
    var totalDeposit = 0;
    var authorizedWithdraw = 0;
    var unauthorizedWithdraw = 0;
    var totalWithdraw = 0;
    var balance = 0;

    $(document).ready(function () {        

        // set default values
        $('#transactionTypeId').val({{ $withdraw->transactionTypeId }});
        $('#ledgerId').val({{ $withdraw->ledgerId }});

        // Disable Multiple Click
        $('form').submit(function (event) {
            event.preventDefault();
            // $(this).find(':submit').attr('disabled', 'disabled');

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
                            window.location = './../'
                        }, 3000);
                    }

                })
                .fail(function () {
                    console.log("error");
                })
                .always(function () {
                    console.log("complete");
                });

        });


        /* transaction type */
        $("#transactionTypeId").change(function (e) { 
            if ($(this).val() == 2) {
                var accTypeID = 5; 
                var selected = {{ $withdraw->ledgerId }}; 
                    $.ajax({
                        type: "POST",
                        url: "../../../getBankLedgerId",
                        data: { 
                            accTypeID : accTypeID,
                            selected:selected,
                        },
                        dataType: "text",
                        success: function (data) {
                            $("#ledgerId").html(data);
                            
                        //   console.log(data);


                        },
                        error: function(){
                            alert('error!');
                        }
                    });

                $("#bankDiv").show('slow');
            }
            else{
                $("#bankDiv").hide('slow');
            }
        });
        $("#transactionTypeId").trigger('change');
        /* end transaction type */

        /* get account information */
        
        function getAccountInfo(){
            $.ajax({
                type: "POST",
                url: "./../getData",
                data: {context : 'account', accountId : "{{ $withdraw->accountId }}"},
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
        /* end getting account information */

        /* calculate balance information */
        $("#amount").on('input', function () {
            var amount = 0;
            if($(this).val() != ''){
                amount = parseFloat($(this).val());
            }

            $('.balance').html(balance + (initialAmount - amount));

            if(isAuthorized == 1){
                $('.authorizedWithdraw').html(authorizedWithdraw + (amount - initialAmount));
            }
            else{
                $('.unauthorizedWithdraw').html(unauthorizedWithdraw + (amount - initialAmount));
            }

            $('.totalWithdraw').html(totalWithdraw + (amount - initialAmount));
        });
        /* end calculate balance information */


    });

</script>


@endsection
