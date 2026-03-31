@extends('Layouts.erp_master')
@section('content')

<form enctype="multipart/form-data" method="post" class="form-horizontal" 
    data-toggle="validator" novalidate="true" autocomplete="off"> 
    @csrf                          
    <div class="row">
        <div class="col-lg-8 offset-lg-3">
            
            @if(Auth::user()->branch_id == 1)
            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Branch</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="branchId" 
                        id="branchId" required data-error="Please Select Branch">
                            <option value="">Select</option>
                            @foreach($branchList as $branch)
                                <option value="{{ $branch->id }}">{{ sprintf("%04d", $branch->branch_code) . " - " . $branch->branch_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            @endif

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Loan Product</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="loanProductId" 
                        id="loanProductId" required data-error="Please Select Product">
                            <option value="">Select</option>
                            @foreach($productList as $product)
                                <option value="{{ $product->id }}">{{ $product->productCode . " - " . $product->shortName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Gender</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="gender" 
                        id="gender" required data-error="Please Select Gender">
                            <option value="">Select</option>
                            <option value="1">Male</option>
                            <option value="2">Female</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Loan Disbursement (Cumulative):</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round textNumber" placeholder="Enter Loan Disbursement(Cumulative):" 
                        name="cumulativeDisbursement" id="cumulativeDisbursement" value="0">
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Total Loan Repay - with Service Charge (Against Cumulative Loan Disburse):</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round textNumber" placeholder="Enter Total Loan Repay" 
                        name="cumulativeRepay" id="cumulativeRepay" value="0">
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Loan Recovery -Principle (Cumulative):</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round textNumber" placeholder="Enter Loan Recovery -Principle (Cumulative)" 
                        name="cumulativeCollectionPrincipal" id="cumulativeCollectionPrincipal" value="0">
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Loan Recovery -with Service Charge (Cumulative):</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round textNumber" placeholder="Enter Loan Recovery -with Service Charge (Cumulative)" name="cumulativeCollection" id="cumulativeCollection" value="0">
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Write Off Amount -with Service Charge (Cumulative):</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round textNumber" placeholder="Enter Write Off Amount -with Service Charge (Cumulative)" 
                        name="cumulativeWriteOff" id="cumulativeWriteOff" value="0">
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Write Off Number (Cumulative):</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round textNumber" placeholder="Enter Write Off Number (Cumulative)" 
                        name="cumulativeWriteOffNumber" id="cumulativeWriteOffNumber" value="0">
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Write Off Amount - Principle (Cumulative):</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round textNumber" placeholder="Enter Write Off Amount - Principle (Cumulative)" 
                        name="cumulativeWriteOffPrincipal" id="cumulativeWriteOffPrincipal" value="0">
                    </div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Loan Waiver Amount (Cumulative):</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round textNumber" placeholder="Enter Loan Waiver Amount - Principle (Cumulative)" 
                        name="cumulativeWaiver" id="cumulativeWaiver" value="0">
                    </div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Loan Waiver Amount - Principle (Cumulative):</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round textNumber" placeholder="Enter Loan Waiver Amount - Principle (Cumulative)" 
                        name="cumulativeWaiverPrincipal" id="cumulativeWaiverPrincipal" value="0">
                    </div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Loan Rebate Amount (Cumulative):</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round textNumber" placeholder="Enter Loan Rebate Amount (Cumulative)" 
                        name="cumulativeRebate" id="cumulativeRebate" value="0">
                    </div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Fully Paid Borrower No. (Cumulative):</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round textNumber" placeholder="Enter Fully Paid Borrower No. (Cumulative)" 
                        name="cumulativeFullyPaidBorrowerNo" id="cumulativeFullyPaidBorrowerNo" value="0">
                    </div>
                </div>
            </div>


            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Borrower No. (Cumulative):</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round textNumber" placeholder="Enter Borrower No. (Cumulative)" 
                        name="cumulativeBorrowerNo" id="cumulativeBorrowerNo" value="0">
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Loan No. (Cumulative):</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round textNumber" placeholder="Enter Loan No. (Cumulative)" 
                        name="cumulativeLoanNo" id="cumulativeLoanNo" value="0">
                    </div>
                </div>
            </div>
            
            <div class="form-row align-items-center">
                <div class="col-lg-2"></div>
                <div class="col-lg-7">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                            <button type="submit" class="btn btn-primary btn-round">Save</button>
                            <!-- <a href="#"><button type="button" class="btn btn-warning btn-round">Next</button></a> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
    
<script type="text/javascript">
    jQuery(document).ready(function($) {

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
            .done(function(response) {
                if (response['alert-type']=='error') {
                    swal({
                        icon: 'error',
                        title: 'Oops...',
                        text: response['message'],
                    });
                    $('form').find(':submit').prop('disabled', false);
                }
                else{
                    $('form').trigger("reset");
                    swal({
                        icon: 'success',
                        title: 'Success...',
                        text: response['message'],
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function () {
                            window.location.href = "{{url('mfn/openingLoanInfo')}}"; 
                        });
                    }
                
                })
            .fail(function() {
                console.log("error");
            })
            .always(function() {
                console.log("complete");
            });
            
        });

    });
    
</script>

@endsection
