@extends('Layouts.erp_master')
@section('content')

<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator" novalidate="true"
    autocomplete="off">
    @csrf
    <div class="row">
        <div class="col-lg-12">

            <!-- Product Information -->
            <div class="panel panel-default">
                <div class="panel-heading p-2 mb-4">Product Information</div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-3 form-group">
                            <label class="input-title RequiredStar">Name</label>
                            <div class="input-group">
                                <input type="text" class="form-control round" id="name" name="name"
                                    placeholder="Enter Name" required data-error="Please enter Name">
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>
                        <div class="col-lg-3 form-group">
                            <label class="input-title RequiredStar">Short Name</label>
                            <div class="input-group">
                                <input type="text" class="form-control round" id="shortName" name="shortName"
                                    placeholder="Enter Short Name" required data-error="Please enter Short name.">
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>
                        <div class="col-lg-3 form-group">
                            <label class="input-title RequiredStar">Saving Product Code</label>
                            <div class="input-group">
                                <input type="text" class="form-control round" id="productCode" name="productCode"
                                    placeholder="Enter Saving Product Code" required
                                    data-error="Please Enter Saving Product Code">
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-3 form-group">
                            <label class="input-title">Start Date</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="icon wb-calendar round" aria-hidden="true"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control round" id="startDate"
                                    name="startDate" autocomplete="off" placeholder="DD-MM-YYYY" required
                                    data-error="Please Select start Date">
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Deposit Information -->
            <div class="panel panel-default">
                <div class="panel-heading p-2 mb-4">Deposit Information</div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-3 form-group">
                            <label class="input-title RequiredStar">Type Of Product</label>
                            <div class="input-group">
                                <select class="form-control clsSelect2" name="productType" id="productType"
                                    placeholder="Select" required data-error="Please Select Type Of Deposit">
                                    <option value="">Select</option>
                                    @foreach ($productTypes as $productType)
                                    <option value="{{ $productType->id }}">{{ $productType->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>

                        <div class="col-lg-3 form-group" id="minSavingsBalanceDiv" style="display: none;">
                            <label class="input-title RequiredStar">Minimum Savings Balance</label>
                            <div class="input-group">
                                <input type="text" class="form-control round textAmount" id="minSavingsBalance"
                                    name="minSavingsBalance" placeholder="Enter Minimum Savings Balance"
                                    data-error="Please enter Minimum Savings Balance.">
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Collection Information -->
            <div class="panel panel-default" style="display: none;" id="collectionFrequencyDiv">
                <div class="panel-heading p-2 mb-4">Regular Deposit Collection</div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-3 form-group">
                            <label class="input-title RequiredStar">Saving Collection Frequency</label>
                            <div class="input-group">
                                <select class="form-control clsSelect2" name="savingsCollectionFrequency"
                                    id="savingsCollectionFrequency" placeholder="Select Saving Collection Frequency"
                                    style="width: 100%;"
                                    data-error="Please Select Saving Collection Frequency">
                                    <option value="">Select</option>
                                    @foreach ($savingsCollectionFrequencies as $savingsCollectionFrequency)
                                    <option value="{{ $savingsCollectionFrequency->id }}">
                                        {{ $savingsCollectionFrequency->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- FDR Setting -->
            <div class="panel panel-default" id="frdRow" style="display: none">
                <div class="panel-heading p-2 mb-4">
                    FDR Interest
                    <a href="javascript:void(0);" class="float-right addFDRInterest blue-grey-700">
                        <i class="fa fa-plus-square fa-lg" aria-hidden="true"></i>
                        <span style="font-size:13px;">Add</span>
                    </a>
                </div>
                <div class="panel-body">
                    <div class="card maturePeriodDiv" style="border: 1px solid rgba(0,0,0,.125)">
                        <div class="card-body">
                            <div class="row">
                            <div class="col-lg-2 form-group">
                                <label class="input-title RequiredStar">Mature Period</label>
                                <div class="input-group">
                                    <input type="text" class="maturePeriod form-control round textNumber"
                                        name="maturePeriod[]" placeholder="Enter Month">
                                </div>
                            </div>
                            <div class="col-lg-2 form-group">
                                <label class="input-title RequiredStar">Interest Rate(%)</label>
                                <div class="input-group">
                                    <input type="text" class="form-control round textAmount interestRateFDR"
                                        name="interestRateFDR[]" placeholder="Enter Percentage">
                                </div>
                            </div>
                            <div class="col-lg-2 form-group">
                                <label class="input-title">FDR Amount</label>
                                <div class="input-group">
                                    <input type="text" class="form-control round textNumber"
                                        name="matureFdrAmount[]" placeholder="FDR Amount" readonly value="100000">
                                </div>
                            </div>
                            <div class="col-lg-2 form-group">
                                <label class="input-title">Total Repay Amount</label>
                                <div class="input-group">
                                    <input type="text" class="form-control round textAmount matureRepayAmount"
                                        name="matureRepayAmount[]" placeholder="Enter Total Repay">
                                </div>
                            </div>
                            <div class="col-lg-4 form-group">
                                <label class="input-title"></label>
                                <div class="input-group">
                                    <a href="javascript:void(0);" class="float-right addFDRSubInterest blue-grey-700">
                                        <i class="fa fa-plus-square fa-lg" aria-hidden="true"></i>
                                        <span style="font-size:13px;">Add Partial Period</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Regular Interest -->
            <div class="panel panel-default" id="regularInterestRow" style="display: none">
                <div class="panel-heading p-2 mb-4">Regular Interest</div>
                <div class="panel-body">
                    <div class="row">

                        <div class="col-lg-3 form-group">
                            <label class="input-title RequiredStar">Interest Rate</label>
                            <div class="input-group">
                                <input type="text" class="form-control round textAmount" id="interestRateRegular"
                                    name="interestRateRegular" placeholder="Enter interest Rate">
                            </div>
                        </div>

                        <div class="col-lg-3 form-group">
                            <label class="input-title RequiredStar">Interest Calculation Method</label>
                            <div class="input-group">
                                <select class="form-control clsSelect2" name="interestCalculationMethod"
                                    id="interestCalculationMethod" placeholder="Select Interest Calculation Method"
                                    data-error="Please Select Interest Calculation Method" style="width: 100%">
                                    <option value="">Select</option>
                                    @foreach ($interestCalMethods as $interestCalMethod)
                                    <option value="{{ $interestCalMethod->id }}">{{ $interestCalMethod->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-3 form-group" style="display: none;" id="interestAvgMethodPeriodDiv">
                            <label class="input-title">Consider Avg. By</label>
                            <div class="input-group">
                                <select class="form-control clsSelect2" name="interestAvgMethodPeriod"
                                    id="interestAvgMethodPeriod" placeholder="Select"
                                    data-error="Please Select Yes or No" style="width: 100%">
                                    <option value="">Select</option>
                                    @foreach ($interestAvgMethosPeriods as $interestAvgMethosPeriod)
                                    <option value="{{ $interestAvgMethosPeriod->id }}">
                                        {{ $interestAvgMethosPeriod->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Others -->
            <div class="panel panel-default">
                <div class="panel-heading p-2 mb-4">Others</div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-3 form-group">
                            <label class="input-title RequiredStar">Is Multiple Savings Allowed?</label>
                            <div class="input-group">
                                <select class="form-control clsSelect2" name="isMultipleSavings" id="isMultipleSavings"
                                    placeholder="Select" required data-error="Please Select Yes or No">
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>
                        <div class="col-lg-3 form-group">
                            <label class="input-title RequiredStar">Is Nominee Required?</label>
                            <div class="input-group">
                                <select class="form-control clsSelect2" name="isNomineeRequired" id="isNomineeRequired"
                                    placeholder="Select" required data-error="Please Select Yes or No">
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>
                        <div class="col-lg-3 form-group">
                            <label class="input-title">Is Closing Charge Applicable?</label>
                            <div class="input-group">
                                <select class="form-control clsSelect2" name="isClosingChargeApplicable"
                                    id="isClosingChargeApplicable" placeholder="Select" required
                                    data-error="Please Select Yes or No">
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>
                        <div class="col-lg-3 form-group" id="closingChargeRow" style="display: none;">
                            <label class="input-title">Closing Charge</label>
                            <div class="input-group">
                                <input type="text" class="form-control round textAmount" name="closingCharge" id="closingCharge"
                                    placeholder="Enter Closing Charge"
                                    data-error="Please Enter Closing Charge">
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>
                    </div>

                    <div class="row">

                        <div class="col-lg-3 form-group">
                            <label class="input-title RequiredStar">Is Partial Withdraw Allowed?</label>
                            <div class="input-group">
                                <select class="form-control clsSelect2" name="isPartialWithdrawAllowed"
                                    id="isPartialWithdrawAllowed" placeholder="Select" required
                                    data-error="Please Select Yes or No">
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="YesIfNoLoan">Yes, If Member has no Loan</option>
                                </select>
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>

                        <div class="col-lg-3 form-group">
                            <label class="input-title RequiredStar">Is Partial Interest Withdraw Allowed?</label>
                            <div class="input-group">
                                <select class="form-control clsSelect2" name="isPartialInterestWithdrawAllowed"
                                    id="isPartialInterestWithdrawAllowed" placeholder="Select" required
                                    data-error="Please Select Yes or No">
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="YesIfNoLoan">Yes, If Member has no Loan</option>
                                </select>
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>

                        <div class="col-lg-3 form-group">
                            <label class="input-title RequiredStar">Is Due Member Getting Interest?</label>
                            <div class="input-group">
                                <select class="form-control clsSelect2" name="isDueMemberGettingInterest"
                                    id="isDueMemberGettingInterest" placeholder="Select" required
                                    data-error="Please Select Yes or No">
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>

                        <div class="col-lg-3 form-group">
                            <label class="input-title">Generate Interest Probation?</label>
                            <div class="input-group">
                                <select class="form-control clsSelect2" name="generateInterestProbation"
                                    id="generateInterestProbation" placeholder="Select" required
                                    data-error="Please Select Yes or No" style="width: 100%">
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-lg-3 form-group">
                            <label class="input-title RequiredStar">On Closing Interest Editable</label>
                            <div class="input-group">
                                <select class="form-control clsSelect2" name="onClosingInterestEditable"
                                    id="onClosingInterestEditable" placeholder="Select" required
                                    data-error="Please Select Yes or No">
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>

                        <div class="col-lg-3 form-group">
                            <label class="input-title">Status</label>
                            <div class="input-group">
                                <select class="form-control clsSelect2" name="status" id="status" placeholder="Select">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>    
                        
                        <div class="col-lg-3 form-group">
                            <label class="input-title">Is Mandatory On Member Admission?</label>
                            <div class="input-group">
                                <select class="form-control clsSelect2" name="isMandatoryOnMemberAdmission" placeholder="Select">
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row align-items-center">
                        <div class="col-lg-12">
                            <div class="form-group d-flex justify-content-center">
                                <div class="example example-buttons">
                                    <a href="javascript:void(0)" onclick="goBack();"
                                        class="btn btn-default btn-round">Back</a>
                                    <button type="submit" class="btn btn-primary btn-round" id="btnSubmit">Save</button>
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
    jQuery(document).ready(function ($) {

        function makeFdrArray() {

            var maturePeriodsArray = new Array();
            $.each($("[name^=maturePeriod"), function (index, el) {

                var maturePeriod = new Object();
                maturePeriod.month = $(el).val();
                maturePeriod.interestRate = $(el).parents().eq(2).find('.interestRateFDR').val();

                var partialArray = new Array();
                var partialPeriods = $(el).closest('.maturePeriodDiv').find(".partialPeriod");
                $.each(partialPeriods, function (pIndex, pel) {
                    var partial = new Object();
                    partial.month = $(pel).val();
                    partial.interestRate = $(pel).parents().eq(2).find('.partialRateFDR').val();
                    partialArray.push(partial);
                });

                maturePeriod.partials = partialArray;
                maturePeriodsArray.push(maturePeriod);
            });

            maturePeriodsArray = JSON.stringify(maturePeriodsArray);

            return maturePeriodsArray;

            // console.log(JSON.stringify(maturePeriodsArray));
        }

        // After form submit actions
        $('form').submit(function (event) {
            //disable Multiple Click
            event.preventDefault();

            var maturePeriodsArray = makeFdrArray();

            $(this).find(':submit').attr('disabled', 'disabled');            

            $.ajax({
                    url: "{{ url()->current() }}",
                    type: 'POST',
                    dataType: 'json',
                    data: $('form').serialize() + "&maturePeriods=" + maturePeriodsArray,
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
                            window.location.href = './';
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

        $("#productType").change(function (e) {
            if ($(this).val() == 1) {
                $("#minSavingsBalanceDiv").hide('slow');
                $("#collectionFrequencyDiv").show('slow');
                $("#regularInterestRow").show('slow');
                $("#frdRow").hide('slow');
            } else if ($(this).val() == 2) {
                $("#minSavingsBalanceDiv").show('slow');
                $("#collectionFrequencyDiv").hide('slow');
                $("#regularInterestRow").hide('slow');
                $("#frdRow").show('slow');
            } else {
                $("#minSavingsBalanceDiv").hide('slow');
                $("#collectionFrequencyDiv").hide('slow');
                $("#regularInterestRow").hide('slow');
                $("#frdRow").hide('slow');
            }
        });

        // Show interest Rate if Calculation Method is selected as FLAT,otherwise hide
        $('#interestCalculationMethod').change(function () {
            if ($(this).val() == 2) {
                $('#interestAvgMethodPeriodDiv').show('fast');
            } else {
                $('#interestAvgMethodPeriodDiv').hide('fast');
            }
        });

        // Show Closing Charge if it is applicable,otherwise hide
        $('#isClosingChargeApplicable').change(function () {
            if ($('#isClosingChargeApplicable').val() == 'Yes') {
                $('#closingChargeRow').show('slow');
            } else {
                $('#closingChargeRow').hide('slow');
            }
        });

        // Days before System Days are disabled
        var sysDate = "{{ \App\Services\MfnService::systemCurrentDate(Auth::user()->branch_id ) }}";
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

        $(document).on('click', '.addFDRInterest', function () {

            var html = '<div class="card maturePeriodDiv" style="border: 1px solid rgba(0,0,0,.125)">';
            html += '<div class="card-body">';
            html += '<div class="row">';
            html += '<div class="col-lg-2 form-group">';
            html += '<label class="input-title">Mature Period <span style="color: red">*</span></label>';
            html += '<div class="input-group">';
            html +=
                '<input type="text" class="maturePeriod form-control round" name="maturePeriod[]" placeholder="Enter Month">';
            html += '</div>';
            html += '</div>';
            html += '<div class="col-lg-2 form-group">';
            html += '<label class="input-title">Interest Rate(%) <span style="color: red">*</span></label>';
            html += '<div class="input-group">';
            html +=
                '<input type="text" class="form-control round interestRateFDR" name="interestRateFDR[]" placeholder="Enter Percentage">';
            html += '</div>';
            html += '</div>';

            html += '<div class="col-lg-2 form-group">';
            html += '<label class="input-title">FDR Amount</label>';
            html += '<div class="input-group">';
            html +=
                '<input type="text" class="form-control round textNumber" name="matureFdrAmount[]" placeholder="FDR Amount" readonly value="100000">';
            html += '</div>';
            html += '</div>';

            html += '<div class="col-lg-2 form-group">';
            html += '<label class="input-title">Total Repay Amount</label>';
            html += '<div class="input-group">';
            html +=
                '<input type="text" class="form-control round textAmount matureRepayAmount" name="matureRepayAmount[]" placeholder="Enter Total Repay">';
            html += '</div>';
            html += '</div>';

            html += '<div class="col-lg-2 form-group">';
            html += '<label class="input-title"></label>';
            html += '<div class="input-group">';
            html +=
                '<a href="javascript:void(0);" class="float-right addFDRSubInterest blue-grey-700">';
            html += '<i class="fa fa-plus-square fa-lg" aria-hidden="true"></i>';
            html += '<span style="font-size:13px;"> Add Partial Period</span>';
            html += '</a>';
            html += '</div>';
            html += '</div>';

            html += '<div class="col-lg-2 form-group">';
            html += '<label class="input-title"></label>';
            html += '<div class="input-group">';
            html += '<a href="javascript:void(0);" class="float-right removeMatureRow blue-grey-700">';
            html += '<i class="fa fa-minus-square fa-lg" aria-hidden="true"></i>';
            html += '<span style="font-size:13px;"> Remove</span>';
            html += '</a>';
            html += '</div>';
            html += '</div>';

            html += '</div>';
            html += '</div>';

            $(".maturePeriodDiv:last").after(html);
        })

        $(document).on('click', '.addFDRSubInterest', function () {

            var html = '<div class="row partialPeriodDiv" style="margin-left:150px">';
            html += '<div class="col-lg-2 form-group">';
            html += '<label class="input-title">Partial Period <span style="color: red">*</span></label>';
            html += '<div class="input-group">';
            html +=
                '<input type="text" class="partialPeriod form-control round textNumber" name="partialPeriod[]" placeholder="Enter Month">';
            html += '</div>';
            html += '</div>';
            html += '<div class="col-lg-3 form-group">';
            html += '<label class="input-title">Interest Rate(%) <span style="color: red">*</span></label>';
            html += '<div class="input-group">';
            html +=
                '<input type="text" class="form-control round textAmount partialRateFDR" name="partialRateFDR[]" placeholder="Enter Percentage">';
            html += '</div>';
            html += '</div>';

            html += '<div class="col-lg-2 form-group">';
            html += '<label class="input-title"></label>';
            html += '<div class="input-group">';
            html += '<a href="javascript:void(0);" class="float-right removePartialRow blue-grey-700">';
            html += '<i class="fa fa-minus-square fa-lg" aria-hidden="true"></i>';
            html += '<span style="font-size:13px;"> Remove</span>';
            html += '</a>';
            html += '</div>';
            html += '</div>';
            html += '</div>';

            $(this).parents().eq(3).append(html);
        })

        $(document).on('click', '.removePartialRow', function () {
            $(this).parents().eq(2).remove();
        })

        $(document).on('click', '.removeMatureRow', function () {

            $(this).closest('.maturePeriodDiv').remove();

        })

        function fnCalcMatureRepay(el) {
            var month = $(el).closest('.maturePeriodDiv').find('.maturePeriod').val();
            if(month == ''){
                $(el).closest('.maturePeriodDiv').find('.matureRepayAmount').val('');
                return false;
            }
            var year = month/12;

            var baseAmount = 100000;
            var repayAmount = '';
            if (el.val() != '') {
                var repayAmount = Math.round(baseAmount + (el.val() * baseAmount / 100 * year));
            }
            $(el).closest('.maturePeriodDiv').find('.matureRepayAmount').val(repayAmount);
        }

        function fnCalcMatureRate(el) {
            var month = $(el).closest('.maturePeriodDiv').find('.maturePeriod').val();
            if(month == ''){
                $(el).closest('.maturePeriodDiv').find('.interestRateFDR').val('');
                return false;
            }
            var year = month/12;

            var baseAmount = 100000;
            var interestRate = '';
            if (el.val() != '') {
                var interestRate = (el.val()-baseAmount)*100/baseAmount / year;
            }
            $(el).closest('.maturePeriodDiv').find('.interestRateFDR').val(interestRate);
        }

        // function fnCalcPartialRepay(el) {
        //     var baseAmount = 100000;
        //     var repayAmount = '';
        //     if (el.val() != '') {
        //         var repayAmount = baseAmount + (el.val() * baseAmount / 100);
        //     }
        //     $(el).closest('.partialPeriodDiv').find('.partialRepayAmount').val(repayAmount);
        // }

        // function fnCalcPartialRate(el) {
        //     var baseAmount = 100000;
        //     var interestRate = '';
        //     if (el.val() != '') {
        //         var interestRate = (el.val()-baseAmount)*100/baseAmount;
        //     }
        //     $(el).closest('.partialPeriodDiv').find('.partialRateFDR').val(interestRate);
        // }


        $(document).on('keyup', '.interestRateFDR', function () {
            fnCalcMatureRepay($(this));
        });
        $(document).on('keyup', '.maturePeriod', function () {
            var element = $(this).closest('.maturePeriodDiv').find('.interestRateFDR');
            fnCalcMatureRepay(element);
        });

        $(document).on('keyup', '.matureRepayAmount', function () {
            fnCalcMatureRate($(this));
        });

        // $(document).on('keyup', '.partialRateFDR', function () {
        //     fnCalcPartialRepay($(this));
        // });

        // $(document).on('keyup', '.partialRepayAmount', function () {
        //     fnCalcPartialRate($(this));
        // });

    });

</script>


@endsection
