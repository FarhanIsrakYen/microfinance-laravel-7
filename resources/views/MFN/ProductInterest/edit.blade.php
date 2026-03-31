@extends('Layouts.erp_master')

@section('content')


<form enctype="multipart/form-data" method="post" class="form-horizontal" 
    data-toggle="validator" novalidate="true" autocomplete="off"> 
    @csrf    
    <div class="row">
        <div class="col-lg-8 offset-lg-3">

            <input type="hidden" name="product_id" value="{{ encrypt($product->id) }}">

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title">Product</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control round" id="productId" name="productId" 
                        placeholder="Loan Product" readonly value="{{ $product->name }}">
                    </div>
                </div>
            </div>

            @if($product->productTypeId == 1)
            
                <div class="form-row align-items-center">
                    <label class="col-lg-3 input-title">Repayment Frequency</label>
                    <div class="col-lg-5">
                        <div class="form-group">
                            <div class="input-group">
                                <select class="form-control clsSelect2" name="repaymentFrequencyId" 
                                id="repaymentFrequencyId" required data-error="Please Select Repayment Frequency">
                                    <option value="">Select</option>
                                    @foreach($regularLoanRepaymentInfo as $row)
                                        <option value="{{ $row->repaymentFrequencyId }}">{{ $row->repaymentFrequencyId }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-row align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">No of Installment</label>
                    <div class="col-lg-5">
                        <div class="form-group">
                            <div class="input-group">
                                <select class="form-control clsSelect2" name="numberOfInstallment" 
                                id="numberOfInstallment" required data-error="Please Select Repayment Frequency">
                                    <option value="">Select</option>
                                </select>
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Interest Rate (%)</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control round" id="interestRatePerYear" 
                        name="interestRatePerYear" placeholder="Enter Interest Rate" 
                        required data-error="Please Interest Rate">
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
                            <input type="text" class="form-control round datepicker-custom" id="effectiveDate" 
                            name="effectiveDate" placeholder="DD-MM-YYYY" required data-error="Please Select Date">
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>

            

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Interest Rate Index</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control round" id="interestRateIndex" 
                        name="interestRateIndex" placeholder="Enter Interest Rate Index" 
                            required data-error="Please Enter Interest Rate Index">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Interest Rate Index Per Year</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control round" id="interestRateIndexPerYear" 
                        name="interestRateIndexPerYear" placeholder="Enter Interest Rate Index Per Year" 
                            required data-error="Please Enter Interest Rate Index Per Year">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            
            <div class="form-row align-items-center">
                <div class="col-lg-2"></div>
                <div class="col-lg-5">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="{{url('mfn/samityclosing')}}" class="btn btn-default btn-round">Back</a>
                            <button type="submit" class="btn btn-primary btn-round disabled">Save</button>
                            <!-- <a href="#"><button type="button" class="btn btn-warning btn-round">Next</button></a> -->
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</form>

<script type="text/javascript">

    $( document ).ready(function() {

        // Disable Multiple Click
        $('form').submit(function (event) {
            $(this).find(':submit').attr('disabled', 'disabled');
        });

        // Days before System Days are disabled
        var sysDate = "{{ \App\Services\MfnService::systemCurrentDate(Auth::user()->branch_id ) }}";
        $('.datepicker-custom').datepicker({
            dateFormat: 'dd-mm-yy',
            orientation: 'bottom',
            autoclose: true,
            todayHighlight: true,
            changeMonth: true,
            changeYear: true,
            yearRange: '1900:2050',
            minDate: new Date(sysDate),
        });

    });
</script>


@endsection
