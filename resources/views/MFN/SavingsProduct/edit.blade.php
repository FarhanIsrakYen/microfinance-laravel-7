@extends('Layouts.erp_master')
@section('content')

<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator" novalidate="true"
    autocomplete="off">
    @csrf
    <div class="row">
        <div class="col-lg-12">

            <input type="hidden" name="productId" value="{{ encrypt($product->id) }}">

            <!-- Product Information -->
            <div class="panel panel-default">
                <div class="panel-heading p-2 mb-4">Product Information</div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-3 form-group">
                            <label class="input-title RequiredStar">Name</label>
                            <div class="input-group">
                                <input type="text" class="form-control round" id="name" name="name" value="{{ $product->name }}"
                                    placeholder="Enter Name" required data-error="Please enter Name">
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>
                        <div class="col-lg-3 form-group">
                            <label class="input-title RequiredStar">Short Name</label>
                            <div class="input-group">
                                <input type="text" class="form-control round" id="shortName" name="shortName" value="{{ $product->shortName }}"
                                    placeholder="Enter Short Name" required data-error="Please enter Short name.">
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>
                        <div class="col-lg-3 form-group">
                            <label class="input-title RequiredStar">Saving Product Code</label>
                            <div class="input-group">
                                <input type="text" class="form-control round" id="productCode" name="productCode"
                                    value="{{ $product->productCode }}"
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
                                    name="startDate" value="{{ \Carbon\Carbon::parse($product->effectiveDate)->format('d-m-Y') }}" autocomplete="off" placeholder="DD-MM-YYYY" readonly />
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
                            <input type="text" class="form-control round" id="productType"
                                    name="productType" value="{{ $productType }}" autocomplete="off" readonly />
                            <div class="help-block with-errors is-invalid"></div>
                        </div>

                        @if ($product->productTypeId == 2)
                        <div class="col-lg-3 form-group">
                            <label class="input-title RequiredStar">Minimum Savings Balance</label>
                            <div class="input-group">
                                <input type="text" class="form-control round textAmount" id="minSavingsBalance"
                                    name="minSavingsBalance" value="{{ $product->minimumSavingsBalance }}"
                                    data-error="Please enter Minimum Savings Balance.">
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>
                        @endif
                        
                    </div>
                </div>
            </div>

            @if ($product->productTypeId == 1)
            <!-- Collection Information -->
            <div class="panel panel-default">
                <div class="panel-heading p-2 mb-4">Regular Deposit Collection</div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-3 form-group">
                            <label class="input-title RequiredStar">Saving Collection Frequency</label>
                            <div class="input-group">
                                <input type="text" class="form-control round" id="savingsCollectionFrequency"
                                    name="savingsCollectionFrequency" value="{{ $collectionFrequency }}" autocomplete="off" readonly />
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>

                    </div>
                </div>
            </div>
            @endif            

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
                                <select class="form-control clsSelect2" name="isMandatoryOnMemberAdmission" id="isMandatoryOnMemberAdmission" placeholder="Select">
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
                                    <button type="submit" class="btn btn-primary btn-round" id="btnSubmit">Update</button>
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
    // set preset vlaues
    $("#isMultipleSavings").val("{{ $product->isMultipleSavingsAllowed }}");
    $("#isNomineeRequired").val("{{ $product->isNomineeRequired }}");
    $("#isClosingChargeApplicable").val("{{ $product->isClosingChargeApplicable }}");
    $("#closingCharge").val("{{ $product->closingCharge }}");
    $("#isPartialWithdrawAllowed").val("{{ $product->isPartialWithdrawAllowed }}");
    $("#isPartialInterestWithdrawAllowed").val("{{ $product->isPartialInterestWithdrawAllowed }}");
    $("#isDueMemberGettingInterest").val("{{ $product->isDueMemberGettingInterest }}");
    $("#isDueMemberGettingInterest").val("{{ $product->isDueMemberGettingInterest }}");
    $("#generateInterestProbation").val("{{ $product->generateInterestProbation }}");
    $("#onClosingInterestEditable").val("{{ $product->onClosingInterestEditable }}");
    $("#status").val("{{ $product->status }}");
    $("#isMandatoryOnMemberAdmission").val("{{ $product->isMandatoryOnMemberAdmission }}");

    jQuery(document).ready(function ($) {

        // After form submit actions
        $('form').submit(function (event) {
            //disable Multiple Click
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
                            window.location.href = './../';
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
        $('#isClosingChargeApplicable').trigger('change');

        $(document).on('click', '.addFDRInterest', function () {

            var html = '<div class="maturePeriodDiv element">';
            html += '<div class="row">';
            html += '<div class="col-lg-3 form-group">';
            html += '<label class="input-title">Mature Period</label>';
            html += '<div class="input-group">';
            html +=
                '<input type="text" class="maturePeriod form-control round" name="maturePeriod[]" placeholder="Enter Month">';
            html += '</div>';
            html += '</div>';
            html += '<div class="col-lg-3 form-group">';
            html += '<label class="input-title">Interest Percentage(%)</label>';
            html += '<div class="input-group">';
            html +=
                '<input type="text" class="form-control round interestRateFDR" name="interestRateFDR[]" placeholder="Enter Percentage">';
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
            html += '<div class="col-lg-3 form-group">';
            html += '<label class="input-title"></label>';
            html += '<div class="input-group">';
            html += '<a href="javascript:void(0);" class="float-right removeMatureRow blue-grey-700">';
            html += '<i class="fa fa-minus-square fa-lg" aria-hidden="true"></i>';
            html += '<span style="font-size:13px;"> Remove</span>';
            html += '</a>';
            html += '</div>';
            html += '</div>';
            html += '</div>';

            $(".maturePeriodDiv:last").after(html);
        })

        $(document).on('click', '.addFDRSubInterest', function () {

            var html = '<div class="row element ml-4">';
            html += '<div class="col-lg-3 form-group">';
            html += '<label class="input-title">Partial Period</label>';
            html += '<div class="input-group">';
            html +=
                '<input type="text" class="partialPeriod form-control round textNumber" name="partialPeriod[]" placeholder="Enter Month">';
            html += '</div>';
            html += '</div>';
            html += '<div class="col-lg-3 form-group">';
            html += '<label class="input-title">Interest Percentage(%)</label>';
            html += '<div class="input-group">';
            html +=
                '<input type="text" class="form-control round textAmount partialRateFDR" name="partialRateFDR[]" placeholder="Enter Percentage">';
            html += '</div>';
            html += '</div>';
            html += '<div class="col-lg-3 form-group">';
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

    });

</script>


@endsection
