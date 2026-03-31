@extends('Layouts.erp_master')
@section('content')

<?php
    $collectionType = strstr(url()->current(), '/mfn/oneTimeLoanTransaction');
?>

<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator" novalidate="true" autocomplete="off">
    @csrf

    <input type="hidden" name="samityId" id="samityId" value="">
    <input type="hidden" name="branchId" id="branchId" value="">
    <input type="hidden" name="loanCode" id="loanCode" value="">

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
                <label class="col-lg-3 input-title RequiredStar">Loan Id</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <select class="form-control" name="loanId" id="loanId" required
                            data-error="Please Select Loan Account">
                            <option value="">Select</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            
            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Transaction Date</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control" name="collectionDate" value="{{ Carbon\Carbon::parse($sysDate)->format('d-m-Y') }}" readonly>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Payment Type</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <select class="form-control" name="paymentType" id="paymentType" required
                            data-error="Please Select Deposit By">
                            <option value="Cash">Cash</option>
                            <option value="Bank">Bank</option>
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

            @if($collectionType)
                <div id="collectionTypeDiv">
                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-3 input-title RequiredStar">Principal Amount</label>
                        <div class="col-lg-5">
                            <div class="input-group">
                                <input type="text" class="form-control textNumber" name="principalAmount" id="principalAmount"
                                required data-error="Please give Amount">
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>
                    </div>
        
                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-3 input-title RequiredStar">Interest Amount</label>
                        <div class="col-lg-5">
                            <div class="input-group">
                                <input type="text" class="form-control textNumber" name="interestAmount" id="interestAmount"
                                required data-error="Please give Amount">
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Amount</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control textNumber" name="amount" id="amount"
                        required data-error="Please give Amount" @if($collectionType) {{ 'readonly' }} @endif>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 align-items-left">
            <table class="table table-bordered table-striped">
                <tr>
                    @if($collectionType)
                        <td>Principal Collection</td>
                    @else
                        <td>Total Collection</td>
                    @endif
                    <td class="savInfo collection"></td>
                </tr>
                <tr>
                    <td>Due Amount</td>
                    <td class="savInfo dueAmount"></td>
                </tr>
                <tr>
                    <td>Advance Amount</td>
                    <td class="savInfo advanceAmount"></td>
                </tr>
                <tr>
                    <td>Out Standing</td>
                    <td class="savInfo outStanding"></td>
                </tr>
                <tr>
                    <td>Installment No</td>
                    <td class="savInfo installmentNo"></td>
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

    $(document).ready(function () {

        /* member selectize */
        selectizeMember(<?= json_encode($members) ?>);
        /* end member selectize */
    });

    function selectizeMember(options){

        $('#memberId').selectize({
            valueField: 'id',
            labelField: 'member',
            searchField: ['name', 'memberCode'],
            sortField: [{
                field: "memberCode",
                direction: "asc"
            }],
            // sortDirection: 'asc',
            placeholder: 'Select Member',
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

    /* get loan accounts */
    $("#memberId").change(function (e) {
        $('#loanId option:gt(0)').remove();

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
                $.each(response['loans'], function (index, value) { 
                    $('#loanId').append("<option value="+index+">"+value+"</option>");
                });
                $(".savInfo").html('');
            },
            error: function(){
                alert('error!');
            }
        });
    });
    /* end get loan accounts */

    /* get loan information */
    $("#loanId").change(function (e) { 

        var loanId = $(this).val();

        $(".savInfo").html('');

        if(loanId == ''){
            return false;
        }

        $.ajax({
            type: "POST",
            url: "./getData",
            data: {context : 'loanData', loanId : loanId},
            dataType: "json",
            success: function (response) {
                
                $('.collection').html(response.collection);
                $('.dueAmount').html(response.dueAmount);
                $('.advanceAmount').html(response.advanceAmount);
                $('.outStanding').html(response.outStanding);
                $('.installmentNo').html(response.installmentNo);

                $('#samityId').val(response.samityId);
                $('#branchId').val(response.branchId);

                $('#loanCode').val($("#loanId").find('option:selected').html());
            },
            error: function(){
                alert('error!');
            }
        });
    });
    /* end get loan information */

    /* deposit type */
    $("#paymentType").change(function (e) { 
        if ($(this).val() == 'Bank') {

                var accTypeID = 5; 
                var selected = null; 
                    $.ajax({
                        type: "POST",
                        url: "../getBankLedgerId",
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

    /* calculate transaction amount information */
    $("#principalAmount").keyup(function () {

        var amount = 0;

        if($(this).val() != ''){

            var principle_amt = parseFloat($(this).val());

            if ($('#interestAmount').val() == '') {
                amount = principle_amt;
            }
            else{
                amount = principle_amt + parseFloat($("#interestAmount").val());
            }

            $('#amount').val(amount);
        }
    });

    $("#interestAmount").keyup(function () {

        var amount;

        if($(this).val() != ''){

            var interest_amt = parseFloat($(this).val());

            if ($("#principalAmount").val() == '') {
                amount = interest_amt;
            }
            else{
                amount = interest_amt + parseFloat($("#principalAmount").val())
            }

            $('#amount').val(amount);
        }
    });
    /* end calculate transaction amount information */

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

</script>


@endsection
