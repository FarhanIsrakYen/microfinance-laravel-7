@extends('Layouts.erp_master')

@section('content')

<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator" novalidate="true"
    autocomplete="off">
    @csrf

    <div class="row">
        <div class="col-lg-8 offset-lg-3">

            <input type="hidden" name="id" value="{{ encrypt($savAcc->id) }}">

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title">Member</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control" value="{{ $member }}" readonly>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title">Savings Product</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control" value="{{ $savProduct }}" readonly>
                    </div>
                </div>
            </div>

            @if ($isOpening)
            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Savings Cycle</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control textNumber" name="savingsCycle" id="savingsCycle" value="{{ $savAcc->savingsCycle }}" required>
                    </div>
                </div>
            </div>
            @endif

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title">Savings Account</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control" name="accountCode" id="accountCode" value="{{ $savAcc->accountCode }}" readonly>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title">Opening Date</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        @if ($isOpening)
                        <input type="text" class="form-control" name="openingDate" id="openingDate"
                        value="{{ \Carbon\Carbon::parse($savAcc->openingDate)->format('d-m-Y') }}"    style="cursor: pointer;">
                        @else
                        <input type="text" value="{{ \Carbon\Carbon::parse($savAcc->openingDate)->format('d-m-Y') }}"
                            class="form-control" name="openingDate" id="openingDate" readonly>
                        @endif

                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title">Product Type</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control" name="productType" id="productType" readonly>
                    </div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title">Collection Frequency</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control" name="collectionFrequency" id="collectionFrequency"
                            readonly>
                    </div>
                </div>
            </div>

            {{-- regular product --}}

            <div id="regularProductDiv" style="display: none;">
                <div class="form-row form-group align-items-center">
                    <label class="col-lg-3 input-title">Interest Rate</label>
                    <div class="col-lg-5">
                        <div class="input-group">
                            <input type="text" class="form-control" name="regularInterestRate" id="regularInterestRate" value="{{ $savAcc->interestRate }}"
                                readonly>
                        </div>
                    </div>
                </div>

                <div class="form-row form-group align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">Auto Process Amount</label>
                    <div class="col-lg-5">
                        <div class="input-group">
                            <input type="text" class="form-control textNumber" name="autoProcessAmount"
                                id="autoProcessAmount" value="{{ $savAcc->autoProcessAmount }}" data-error="Please give Auto Process Amount">
                        </div>
                    </div>
                </div>
            </div>
            {{-- end regular product --}}

            {{-- one time product --}}
            <div id="onetimeProductDiv" style="display: none;">
                <div class="form-row form-group align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">Period (Month)</label>
                    <div class="col-lg-5">
                        <div class="input-group">
                            <select class="form-control clsSelect2" name="period" id="period"
                                data-error="Please Select Product" style="width: 100%;">
                                <option value="">Select</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row form-group align-items-center">
                    <label class="col-lg-3 input-title">Interest Rate</label>
                    <div class="col-lg-5">
                        <div class="input-group">
                            <input type="text" class="form-control" name="onetimeInterestRate" id="onetimeInterestRate" value="{{ $savAcc->interestRate }}"
                                readonly>
                        </div>
                    </div>
                </div>

                <div class="form-row form-group align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">Fixed Deposit Amount</label>
                    <div class="col-lg-5">
                        <div class="input-group">
                            <input type="text" class="form-control textNumber" name="onetimeDepositAmount"
                                id="onetimeDepositAmount" value="{{ $savAcc->autoProcessAmount }}" data-error="Please give Auto Deposit Amount">
                        </div>
                    </div>
                </div>

                <div class="form-row form-group align-items-center">
                    <label class="col-lg-3 input-title">Payable Amount</label>
                    <div class="col-lg-5">
                        <div class="input-group">
                            <input type="text" class="form-control" name="payableAmount" id="payableAmount" readonly>
                        </div>
                    </div>
                </div>

                @if (!$isOpening)
                <div class="form-row form-group align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">Deposit By</label>
                    <div class="col-lg-5">
                        <div class="input-group">
                            <select class="form-control" name="transactionTypeId" id="transactionTypeId" 
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
                                <input type="text" class="form-control" name="chequeNo" value="{{@$deposit->chequeNo}}">
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- end one time product --}}

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
    </div>

    </div>
    </div>
</form>


<script type="text/javascript">
    $(document).ready(function () {
        
        $('#transactionTypeId').val({{ @$deposit->transactionTypeId }});
        // Disable Multiple Click
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

        /* get savings code */
        $("#savingsCycle").on('input', function () {
            getSavingsCode();
        });

        function getSavingsCode() {
            $("#accountCode").val('');
            var memberId = "{{ $savAcc->memberId }}";
            var savProductId = "{{ $savAcc->savingsProductId }}";

            var isOpening = "{{ $isOpening }}";

            if (isOpening == 1 || isOpening == true) {
                var savingsCycle = $("#savingsCycle").val();
                if (savingsCycle == '') {
                    return false;
                }
            }

            if (memberId == '' || savProductId == '') {
                return false;
            }

            if (isOpening) {
                var data = {
                    context: 'savingsCode',
                    memberId: memberId,
                    savProductId: savProductId,
                    savingsCycle: savingsCycle,
                }
            }
            else{
                var data = {
                    context: 'savingsCode',
                    memberId: memberId,
                    savProductId: savProductId
                }
            }

            $.ajax({
                type: "POST",
                url: "./../getData",
                data: data,
                dataType: "json",
                success: function (response) {
                    $("#accountCode").val(response['savCode']);
                },
                error: function () {
                    alert('error!');
                }
            });

        }
        /* end get savings code */

        /* get product information */
        function getProductInformation(){

            $('#productType').val('');
            $('#collectionFrequency').val('');
            $('#regularInterestRate').val('');
            $("#period option:gt(0)").remove();
            $('#onetimeInterestRate').val('');

            var savProductId = "{{ $savAcc->savingsProductId }}";

            if (savProductId == '' || $("#openingDate").val() == '') {
                return false;
            }

            $.ajax({
                type: "POST",
                url: "./../getData",
                async: false,
                data: {
                    context: 'product',
                    savProductId: "{{ $savAcc->savingsProductId }}",
                    openingDate: $("#openingDate").val()
                },
                dataType: "json",
                success: function (response) {
                    $("#productType").val(response['productType']);
                    $("#collectionFrequency").val(response['collectionFrequency']);

                    if (response['productTypeId'] == 1) {
                        // for regular
                        $('#regularProductDiv').show();
                        $('#onetimeProductDiv').hide();

                        if (response['regularInterestRate'] == 'shouldDefine') {
                            swal({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Interest rate is not defined.',
                            });
                        } else {
                            $("#regularInterestRate").val(response['regularInterestRate']);
                        }
                    } else if (response['productTypeId'] == 2) {
                        // for one time
                        $('#regularProductDiv').hide();
                        $('#onetimeProductDiv').show();

                        if (response['durationInterests'] == 'shouldDefine') {
                            swal({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Interest rate is not defined.',
                            });
                        } else {
                            $.each(response['durationInterests'], function (duration,
                                interestRate) {
                                $("#period").append("<option value=" + duration +
                                    " interestRate=" + interestRate + ">" +
                                    duration + "</option>");
                            });
                        }
                    }

                },
                error: function () {
                    alert('error!');
                }
            });

        }
        getProductInformation();
        /* end get product information */

        $("#openingDate").change(function (e) {
            getProductInformation();
        });

        /* one time period change */
        $('#period').change(function (e) {
            $('#onetimeInterestRate').val($("#period option:selected").attr('interestrate'));
            $('#onetimeDepositAmount').trigger('input');
        });
        $('#period').val("{{ $savAcc->periodMonth }}").trigger('change');
        /* end one time period change */

        /* calculate payable amount */
        $('#onetimeDepositAmount').on('input', function () {
            var onetimeDepositAmount = 0;
            var interestRate = 0;
            var month = 0;

            if ($(this).val() == '' || $('#period').val() == '') {
                $('#payableAmount').val('');
                return false;
            }

            if ($(this).val() != '') {
                onetimeDepositAmount = parseFloat($(this).val());
            }
            if ($('#period').val() != '') {
                month = parseInt($("#period").val());
            }

            interestRate = parseFloat($('#onetimeInterestRate').val());

            month = month / 12;

            var payableAmount = onetimeDepositAmount + Math.round(month * (interestRate / 100) *
                onetimeDepositAmount);

            $('#payableAmount').val(payableAmount);
        });
        $('#onetimeDepositAmount').trigger('input');
        /* end calculate payable amount */

        /* deposit type */
        $("#transactionTypeId").change(function (e) {
            if ($(this).val() == 2) {
                var accTypeID = 5; 
                var selected =  "{{@$deposit->ledgerId == null ? '' : $deposit->ledgerId}}" ;
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
            } else {
                $("#bankDiv").hide('slow');
            }
        });
        $("#paymentType").trigger('change');
        /* end deposit type */

        var isOpening = "{{ $isOpening }}";

        if (isOpening) {
            var systemDate = new Date("{{ $sysDate }}");
            $('#openingDate').datepicker({
                dateFormat: 'dd-mm-yy',
                orientation: 'bottom',
                autoclose: true,
                todayHighlight: true,
                changeMonth: true,
                changeYear: true,
                yearRange: '1900:+10',
                maxDate: systemDate,
                onSelect: function() {
                    $("#onetimeInterestRate").val('');
                    $("#payableAmount").val('');
                    getProductInformation();
                }
            }).keydown(false);
        }


    });

</script>


@endsection
