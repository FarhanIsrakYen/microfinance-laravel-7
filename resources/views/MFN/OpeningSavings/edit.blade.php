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
                                <option value="{{ $branch->id }}" @if($openingSavingsData->branchId == $branch->id) selected @endif>
                                    {{ sprintf("%04d", $branch->branch_code) . " - " . $branch->branch_name }}
                                </option>
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
                            @foreach($loanProductList as $product)
                                <option value="{{ $product->id }}" @if($openingSavingsData->loanProductId == $product->id) selected @endif>{{ $product->productCode . " - " . $product->shortName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Savings Product</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="savingsProductId" 
                        id="savingsProductId" required data-error="Please Select Product">
                            <option value="">Select</option>
                            @foreach($savProductList as $product)
                                <option value="{{ $product->id }}" @if($openingSavingsData->savingsProductId == $product->id) selected @endif>{{ $product->productCode . " - " . $product->shortName }}</option>
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
                            <option value="1" @if($openingSavingsData->gender == 'Male') selected @endif>Male</option>
                            <option value="2" @if($openingSavingsData->gender == 'Female') selected @endif>Female</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Deposit Amount (Cumulative):</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round textNumber" placeholder="Enter Deposit Amount (Cumulative)" 
                        name="cumulativeDeposit" id="cumulativeDeposit"
                        value="{{ $openingSavingsData->cumulativeDeposit ? $openingSavingsData->cumulativeDeposit : 0 }}">
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Interest Amount (Cumulative):</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round textNumber" placeholder="Enter Interest Amount" 
                        name="cumulativeInterest" id="cumulativeInterest"
                        value="{{ $openingSavingsData->cumulativeInterest ? $openingSavingsData->cumulativeInterest : 0 }}">
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Withdraw Amount (Cumulative):</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round textNumber" placeholder="Enter Withdraw Amount" 
                        name="cumulativeWithdraw" id="cumulativeWithdraw"
                        value="{{ $openingSavingsData->cumulativeWithdraw ? $openingSavingsData->cumulativeWithdraw : 0 }}">
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Closing Balance:</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round textNumber" placeholder="Closing Balance" 
                        name="closingBalance" id="closingBalance" readonly
                        value="{{ $openingSavingsData->closingBalance ? $openingSavingsData->closingBalance : 0 }}">
                    </div>
                </div>
            </div>
            
            <div class="form-row align-items-center">
                <div class="col-lg-2"></div>
                <div class="col-lg-7">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                            <button type="submit" class="btn btn-primary btn-round">Update</button>
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

        $('#cumulativeDeposit').keyup(function(event) { 
            var balance = 0;
            var deposit = $(this).val();
            var interest = $('#cumulativeInterest').val();
            var withdraw = $('#cumulativeWithdraw').val();
            balance = Number(deposit) + Number(interest) + Number(balance) - Number(withdraw);
            $('#closingBalance').val(Number(balance));
        });

        $('#cumulativeInterest').keyup(function(event) { 
            var balance = 0;
            var deposit = $('#cumulativeDeposit').val();
            var interest = $(this).val();
            var withdraw = $('#cumulativeWithdraw').val();
            balance = Number(deposit) + Number(interest) + Number(balance) - Number(withdraw);
            $('#closingBalance').val(Number(balance));
        });

        $('#cumulativeWithdraw').keyup(function(event) { 
            var balance = 0;
            var deposit = $('#cumulativeDeposit').val();
            var interest = $('#cumulativeInterest').val();
            var withdraw = $(this).val();
            balance = Number(deposit) + Number(interest) + Number(balance) - Number(withdraw);
            $('#closingBalance').val(Number(balance));
        });

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
                            window.location.href = "{{url('mfn/openingSavingsInfo')}}"; 
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
