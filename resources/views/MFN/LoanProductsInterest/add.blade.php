@extends('Layouts.erp_master')
@section('content')

<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator" novalidate="true"
    autocomplete="off">
    @csrf
    <div class="row">
        <div class="col-lg-12">

            <input type="hidden" name="productId" value="{{ encrypt($product->id) }}">

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Product</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control round"
                            value="{{ $product->productCode.' - '.$product->name }}" readonly>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>


            @if ($product->productTypeId == 1) {{-- if it is regular product --}}
            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Reapyment Frequency</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="reapymentFrequencyId" id="reapymentFrequencyId">
                            <option value="">Select</option>
                            @foreach ($eligibleRepaymentFrequencies as $eligRepayFre)
                            <option value="{{ $eligRepayFre->id }}">{{ $eligRepayFre->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Nummber of Installment</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="installmentNumber" id="installmentNumber">
                            <option value="">Select</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>            
            @endif
            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Interest Rate</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" name="interestRate" class="form-control round textAmount" required
                        data-error="Please Select Interest Rate">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            @if ($product->productTypeId == 1) {{-- if it is regular product --}}
            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Interest Rate Index (Per Year)</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" name="interestRateIndexPerYear" class="form-control round textAmount" required
                        data-error="Please Select Interest Rate Index Per Year">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Interest Rate Index</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" name="interestRateIndex" class="form-control round textAmount" required
                        data-error="Please Select Interest Rate Index">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            @endif
            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Interest Calculation Method</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="interestCalculationMethodId" id="interestCalculationMethodId">
                            <option value="">Select</option>
                            @foreach ($interestCalculationMethods as $interestCalculationMethod)
                            <option value="{{ $interestCalculationMethod->id }}">{{ $interestCalculationMethod->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Effective Date</label>
                <div class="col-lg-5">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="icon wb-calendar round" aria-hidden="true"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control round" id="effectiveDate"
                                name="effectiveDate" placeholder="DD-MM-YYYY" required
                                data-error="Please Select Date">
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <div class="col-lg-12">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                            <button type="submit" class="btn btn-primary btn-round">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script type="text/javascript">
    jQuery(document).ready(function ($) {

        $('form').submit(function (event) {
            //disable Multiple Click
            event.preventDefault();
            // $(this).find(':submit').attr('disabled', 'disabled');

            $.ajax({
                    url: "./../add",
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
                            window.location.href = "./../../view/" + "{{ $product->id }}";
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

        // limit decimal point to 5 digits
        $(document).on('input', '.textAmount',function () {
            this.value = this.value.slice(0, (this.value.indexOf("."))+6);
        });

        // get number of installment of a particular Repayment Frequency of the product
        $("#reapymentFrequencyId").change(function () {
            $("#installmentNumber option:gt(0)").remove();

            if($(this).val() == ''){
                return false;
            }

            $.ajax({
                type: "post",
                url: "./../getProductFrequencyWiseInstallments",
                data: {productId : "{{ $product->id }}", 'reapymentFrequencyId': $("#reapymentFrequencyId").val()},
                dataType: "json",
                success: function (installments) {
                    $.each(installments, function (index, value) { 
                        $("#installmentNumber").append("<option value="+value+">"+value+"</option>");
                    });
                },
                error: function (response) {
                alert('Error');
                }
            });
        });

        $('#effectiveDate').datepicker({
            dateFormat: 'dd-mm-yy',
            orientation: 'bottom',
            autoclose: true,
            todayHighlight: true,
            changeMonth: true,
            changeYear: true,
            yearRange: '1900:+10',
            minDate: new Date("{{ $minEffectiveDate }}"),
        });        

    });

</script>

@endsection
