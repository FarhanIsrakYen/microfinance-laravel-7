@extends('Layouts.erp_master')

@section('content')

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
                        <select class="form-control" name="memberId" id="memberId" required
                            data-error="Please Select Member">
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>      

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Savings Account</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <select class="form-control" name="accountId" id="accountId" required
                            data-error="Please Select Savings Account">
                            <option value="">Select</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            
            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Deposit Date</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control" name="date" value="{{ \Carbon\Carbon::parse($sysDate)->format('d-m-Y') }}" readonly>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Amount</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control textNumber" name="amount" id="amount"
                        required data-error="Please give Amount">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            
            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Deposit By</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <select class="form-control" name="transactionTypeId" id="transactionTypeId" required
                            data-error="Please Select Deposit By">
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
                            <input type="text" class="form-control" name="chequeNo">
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
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


<link rel="stylesheet" href="{{asset('assets/css/selectize.bootstrap3.min.css')}}">
<script src="{{asset('assets/js/selectize.min.js')}}"></script>

<style>
    .selectize-control div.active {
        background-color: lightblue;
    }
    .selectize-control .lebel {
        color: #804739;
        font-weight: bold;
    }
</style>

<script type="text/javascript">
    // initialize some variables
    var authorizedDeposit = 0;
    var unauthorizedDeposit = 0;
    var totalDeposit = 0;
    var authorizedWithdraw = 0;
    var unauthorizedWithdraw = 0;
    var totalWithdraw = 0;
    var balance = 0;

    $(document).ready(function () {

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
                            window.location = './'
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


        /* member selectize */

        function selectizeMember(options){
            console.log(options);
            
            $('#memberId').selectize({
                valueField: 'id',
                labelField: 'member',
                searchField: ['name', 'memberCode'],
                sortField: [{
                    field: "memberCode",
                    direction: "asc"
                }],
                // sortDirection: 'asc',
                highlight: true,
                allowEmptyOption: true,
                maxItems: 1,
                //only using options-value in jsfiddle - real world it's using the load-function
                options: options ,
                create: false,
                render: {
                    option: function (member, escape) {
                        return '<div>' +
                            '<span class="lebel">' + member.name + ' - ' + member.memberCode + '</span> <br>' +
                            '<span>Branch: ' + member.branch + '</span> <br>' +
                            '<span>Samity: ' + member.samity + '</span> <br>' +
                            '<span>Working Area: ' + member.workingArea + '</span> ' +
                            '</div>';

                    }
                }
            });
        }

        selectizeMember(@php echo json_encode($members) @endphp);
        /* end member selectize */

        /* get savings accounts */
        $("#memberId").change(function (e) {
            $('#accountId option:gt(0)').remove();
            $('#accountId').trigger('change');

            var memberId = $(this).val();

            if(memberId == ''){
                return false;
            }

            $.ajax({
                type: "POST",
                url: "./getData",
                data: {context : 'member', memberId : memberId},
                dataType: "json",
                success: function (response) {
                    $.each(response.savAccounts, function (index, value) { 
                        $('#accountId').append("<option value="+index+">"+value+"</option>");
                    });
                },
                error: function(){
                    alert('error!');
                }
            });
        });
        /* end get saving accounts */

        /* get account information */
        $("#accountId").change(function (e) { 
            var accountId = $(this).val();

            $(".savInfo").html('');
            $('#amount').val('');

            if(accountId == ''){
                return false;
            }

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
                    $('#amount').val(response.autoProcessAmount);

                    authorizedDeposit = response.authorizedDeposit;
                    unauthorizedDeposit = response.unauthorizedDeposit;
                    totalDeposit = response.totalDeposit;
                    authorizedWithdraw = response.authorizedWithdraw;
                    unauthorizedWithdraw = response.unauthorizedWithdraw;
                    totalWithdraw = response.totalWithdraw;
                    balance = response.balance;

                    $("#amount").trigger('input');
                },
                error: function(){
                    alert('error!');
                }
            });
        });
        /* end get account information */

        /* deposit type */
        $("#transactionTypeId").change(function (e) { 
            if ($(this).val() == 2) {

                var accTypeID = 5; 
                var selected = null; 
                    $.ajax({
                        type: "POST",
                        url: "../../getBankLedgerId",
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
        /* end deposit type */

        /* calculate balance information */
        $("#amount").on('input', function () {
            var amount = 0;
            if($(this).val() != ''){
                amount = parseFloat($(this).val());
            }

            $('.balance').html(balance + amount);

            $('.unauthorizedDeposit').html(unauthorizedDeposit + amount);

            $('.totalDeposit').html(totalDeposit + amount);

        });
        /* end calculate balance information */


    });

</script>


@endsection
