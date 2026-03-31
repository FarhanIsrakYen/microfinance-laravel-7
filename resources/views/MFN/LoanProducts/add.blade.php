@extends('Layouts.erp_master')
@section('content')

<?php
    use App\Services\MfnService;
?>

<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator" novalidate="true"
    autocomplete="off">
    @csrf
    <div class="row">
        <div class="col-lg-6">

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Name</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round" id="name" name="name" placeholder="Enter Name"
                            required data-error="Please enter Name">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Short Name</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round" id="shortName" name="shortName"
                            placeholder="Enter Short Name" required data-error="Please enter Short name.">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Loan Product Code</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round" id="code" name="code"
                            placeholder="Enter Loan Product Code" required data-error="Please Enter Loan Product Code">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Loan Product Category</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2" id="productCategoryId" name="productCategoryId"
                            placeholder="Select Loan Product Category">
                            <option>Select</option>
                            @foreach($loanCategories as $loanCategory)
                            <option value="{{ $loanCategory->id }}">{{$loanCategory->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Funding Organization</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="fundingOrganizationId" id="fundingOrganizationId"
                            placeholder="Select Funding Organization" required
                            data-error="Please Select Funding Organization">
                            <option value="">Select</option>
                            @foreach($funOrgs as $funOrg)
                            <option value="{{ $funOrg->id }}">{{ $funOrg->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center" id="pksfFundRow" style="display: none">
                <label class="col-lg-4 input-title">PKSF Fund</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="pksfFundId" id="pksfFundId"
                            placeholder="Select PKSF Fund" style="width: 100%">
                            <option value="">Select</option>
                            @foreach ($pksfFunds as $pksfFund)
                            <option value="{{ $pksfFund->id }}"> {{ $pksfFund->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Start Date</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="icon wb-calendar round" aria-hidden="true"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control round" id="startDate" name="startDate"
                            autocomplete="off" placeholder="DD-MM-YYYY">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Is Primary Product</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="isPrimaryProduct" id="isPrimaryProduct"
                            placeholder="Select" required data-error="Please Select Yes or No">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center" id="multipleLoanRow" style="display: none;">
                <label class="col-lg-4 input-title RequiredStar">Is Multiple Loan Allowed</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="isMultipleLoanAllowed" id="isMultipleLoanAllowed"
                            style="width: 100%">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center" id="loanProdTypeRow" style="display: none;">
                <label class="col-lg-4 input-title RequiredStar">Loan Product Type</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="productTypeId" id="productTypeId"
                            style="width: 100%">
                            @foreach ($productTypes as $productType)
                            <option value="{{ $productType->id }}">{{ $productType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Minimum Loan Amount</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round textAmount" id="minLoanAmount" name="minLoanAmount"
                            placeholder="Enter Minimum Loan Amount" required
                            data-error="Please Enter Minimum Loan Amount">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Average Loan Amount</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round textAmount" id="avgLoanAmount" name="avgLoanAmount"
                            placeholder="Enter Average Loan Amount" required
                            data-error="Please Enter Average Loan Amount">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Maximum Loan Amount</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round textAmount" id="maxLoanAmount" name="maxLoanAmount"
                            placeholder="Enter Maximum Loan Amount" required
                            data-error="Please Enter Maximum Loan Amount">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Years to Eligible Write-Off</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round" id="yearsEligibleWriteOff"
                            name="yearsEligibleWriteOff" placeholder="Enter Years" required
                            data-error="Please Enter Years">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

        </div>

        <div class="col-lg-6">

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Is Insurance Applicable</label>
                <div class="col-lg-8">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="isInsuranceApplicable" id="isInsuranceApplicable"
                            placeholder="Select" required data-error="Please Select Yes or No">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center" id="insuranceCalcRow">
                <label class="col-lg-4 input-title RequiredStar">Insurance Calculation Method</label>
                <div class="col-lg-8">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="insuranceCalculationMethodId"
                            id="insuranceCalculationMethodId" placeholder="Select Insurance Calculation Method" required
                            data-error="Please Select Insurance Calculation Method">
                            @foreach ($insuranceCalMethods as $insuranceCalMethod)
                            <option value="{{ $insuranceCalMethod->id }}">{{ $insuranceCalMethod->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center" id="insAmountPercentRow">
                <label class="col-lg-4 input-title RequiredStar">Insurance Amount Percentage(%) Against Principle Loan
                    Amount</label>
                <div class="col-lg-8">
                    <div class="input-group">
                        <input type="text" class="form-control round textAmount" id="insurancePercentage"
                            name="insurancePercentage" placeholder="Enter Insurance Percentage">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center" id="insAmountFixedRow" style="display: none">
                <label class="col-lg-4 input-title RequiredStar">Fixed Insurance Amount:
                    Amount</label>
                <div class="col-lg-8">
                    <div class="input-group">
                        <input type="text" class="form-control round textAmount" id="fixedinsurance"
                            name="fixedinsurance" placeholder="Enter Insurance Percentage">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center" id="elgblRepayFreqRow">
                <label class="col-lg-4 input-title RequiredStar">Eligible Repayment &nbsp &nbsp Frequency</label>
                <div class="col-lg-8">
                    <div class="row">
                        <label class="col-lg-4 input-title grey-500">Repayment Frequency</label>
                        <label class="col-lg-4 input-title grey-500">Grace Period</label>
                        <label class="col-lg-4 input-title grey-500">Installments</label>
                    </div>

                    @foreach ($repaymentFrequencies as $repaymentFrequency)
                    <div class="row mb-2 freqDaysRow">
                        <div class="col-lg-4 input-group">
                            <div class="checkbox-custom checkbox-primary">
                                <input type="checkbox" name="repaymentFrequencys[]" value="{{ $repaymentFrequency->id }}" class="freqDaysCls">
                                    <label style="padding-left: 4px">{{ $repaymentFrequency->name }}</label>
                            </div>
                        </div>
                        <div class="col-lg-4 input-group">
                            <input type="text" class="form-control textNumber edit_freq" name="gracePeriods[]"
                                placeholder="No of days" readonly>
                        </div>
                        <div class="col-lg-4 input-group">
                            <input type="text" class="form-control numberWithcomma edit_freq" name="installments[]"
                                placeholder="12,18,24" readonly regex="/^\d+(,\d+)*$" />
                        </div>
                    </div>
                    @endforeach

                </div>
            </div>

            <div class="form-row form-group align-items-center" id="elgblPeriodsRow" style="display: none">
                <label class="col-lg-4 input-title RequiredStar">Eligible Periods</label>
                <div class="col-lg-8">
                    <div class="row">
                        <label class="col-lg-4 input-title grey-500">Repayment Frequency</label>
                        <label class="col-lg-8 input-title grey-500">Periods In Month</label>
                    </div>

                    <div class="row mb-2">
                        <div class="col-lg-4 input-group">
                            <div class="checkbox-custom checkbox-primary">
                                <input type="checkbox" checked>
                                <label style="padding-left: 4px">One Time</label>
                            </div>
                        </div>
                        
                        <div class="col-lg-8 input-group">
                            <input type="text" class="form-control numberWithcomma" name="otElgdMonths"
                                placeholder="12,18,24" regex="/^\d+(,\d+)*$" />
                        </div>
                    </div>

                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Form Fee</label>
                <div class="col-lg-8">
                    <div class="input-group">
                        <input type="text" class="form-control round textAmount" id="formFee" name="formFee"
                            placeholder="Enter Form Fee" required data-error="Please Enter Form Fee">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Additional Fee</label>
                <div class="col-lg-8">
                    <div class="input-group">
                        <input type="text" class="form-control round textAmount" id="additionalFee" name="additionalFee"
                            placeholder="Enter Additional Fee" required data-error="Please Enter Additional Fee">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Additional Fee For First Time</label>
                <div class="col-lg-8">
                    <div class="input-group">
                        <input type="text" class="form-control round textAmount" id="additionalFreeForFirstTime"
                            name="additionalFreeForFirstTime" placeholder="Enter Additional Fee For First Time" required
                            data-error="Please Enter Additional Fee For First Time">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

        </div>
    </div>
    <div class="row align-items-center">
        <div class="col-lg-12">
            <div class="form-group d-flex justify-content-center">
                <div class="example example-buttons">
                    <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                    <button type="submit" class="btn btn-primary btn-round" id="btnSubmit">Save</button>
                </div>
            </div>
        </div>
    </div>
</form>

<script type="text/javascript">
    jQuery(document).ready(function ($) {
        // After form submit actions
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
                            timer: 2000,
                            showConfirmButton: false
                        }).then(function () {
                            window.location.href = "{{ url('mfn/loanProducts') }}";
                        });
                    }

                })
                .fail(function () {
                    console.log("error");
                })
                .always(function () {
                    console.log("complete");
                });
        });


        // Show PKSF Fund if pksf is selected,otherwise hide
        $('#fundingOrganizationId').change(function () {
            if ($('#fundingOrganizationId').val() == 1) {
                $('#pksfFundRow').show('slow');
            } else {
                $('#pksfFundRow').hide('slow');
            }
        });

        // Show Calculation Method if insurance is applicable,otherwise hide
        $('#isPrimaryProduct').change(function () {
            if ($('#isPrimaryProduct').val() == 0) {
                $('#multipleLoanRow,#loanProdTypeRow').show('slow');
            } else {
                $('#multipleLoanRow,#loanProdTypeRow').hide('slow');
            }
        });

        // Show Eligible Periods if Loan Product Type is One Time,otherwise hide
        $('#productTypeId').change(function () {
            if ($('#productTypeId').val() == 2) {
                $('#elgblPeriodsRow').show('slow');
                $('#elgblRepayFreqRow').hide('slow');
            } else {
                $('#elgblPeriodsRow').hide('slow');
                $('#elgblRepayFreqRow').show('slow');
            }
        });

        // Show Multiple Loan if Primary Product is selected Yes,otherwise hide
        $('#isInsuranceApplicable').change(function () {
            if ($('#isInsuranceApplicable').val() == 1) {
                $('#insuranceCalcRow,#insAmountPercentRow').show('slow');
            } else {
                $('#insuranceCalcRow,#insAmountPercentRow').hide('slow');
            }
        });

        $('#insuranceCalculationMethodId').change(function () {
            if ($('#insuranceCalculationMethodId').val() == 2) {
                $('#insAmountPercentRow').hide('slow');
                $('#insAmountFixedRow').show('slow');
            } else {
                $('#insAmountFixedRow').hide('slow');
                $('#insAmountPercentRow').show('slow');
            }
        });

        // Grace Period and installment editable if corresponding checkbox is checked
        $(".freqDaysCls").click(function () {
            $(".freqDaysCls").each(function () {
                if ($(this).is(':checked')) {
                    $(this).closest('.freqDaysRow').find('.edit_freq').prop('readonly', false);
                } else if ($('.freqDaysCls').is(':not(:checked)')) {
                    $(this).closest('.freqDaysRow').find('.edit_freq').val('').prop('readonly',
                        true);
                }
            });
        });
        // End

        // Days after System Days are disabled
        var sysDate = "{{ MfnService::systemCurrentDate(Auth::user()->branch_id ) }}";
        $('#startDate').datepicker({
            dateFormat: 'dd-mm-yy',
            orientation: 'bottom',
            autoclose: true,
            todayHighlight: true,
            changeMonth: true,
            changeYear: true,
            yearRange: '-30:+1',
            maxDate: new Date(sysDate),
        });

        //if the letter is not Decimal,don't type anything
        $(".numberWithcomma").on('input', function (event) {
            var text = $(this);
            text.val(text.val().replace(/[^0-9,]/g, ''));
            text.val(text.val().replace(/,,+/g, ','));
        });


    });

</script>

@endsection
