@extends('Layouts.erp_master')
@section('content')

<form enctype="multipart/form-data" method="post" class="form-horizontal" 
    data-toggle="validator" novalidate="true" autocomplete="off">
    @csrf
    <div class="row">
        <div class="col-lg-9 offset-lg-3">

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Branch</label>
                <div class="col-lg-6">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="branchId" 
                                                id="branchId" required data-error="Please Select Branche" disabled>
                            <option value="">Select</option>
                            @foreach($branchList as $branch)
                                <option value="{{ $branch->id }}" {{ ($branch->id == $branchID )? 'selected' : '' }}>
                                    {{ $branch->branch_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title">Loan Products</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        @foreach($loanProductList as $lproduct)
                        <?php
                            $CheckText = ( in_array($lproduct->id, $loanProduct ) ) ? 'checked' : '';
                        ?>
                            <div class="checkbox-custom checkbox-primary mr-4">
                                <input type="checkbox" name="loanProductIds[]" id="loanProduct_{{$lproduct->name}}" 
                                            value="{{ $lproduct->id }}" {{ $CheckText }}>
                                <label for="loanProduct_{{$lproduct->id }}">{{ $lproduct->shortName }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title">Savings Products</label>
                <div class="col-lg-6">
                    <div class="input-group">
                        @foreach($savingsProductList as $sproduct)  
                        <?php
                            $CheckText = ( in_array($sproduct->id, $savingsProduct ) ) ? 'checked' : '';
                        ?>
                            <div class="checkbox-custom checkbox-primary mr-4">
                                <input type="checkbox" name="savingProductIds[]" id="savingsProduct_{{$sproduct->id}}" 
                                        value="{{ $sproduct->id }}" {{ $CheckText }}>
                                <label for="savingsProduct_{{$sproduct->id }}">{{ $sproduct->shortName }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <div class="col-lg-2"></div>
                <div class="col-lg-5">
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
                            window.location.href = "{{url('mfn/branchProduct')}}";
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

    });
</script>

@endsection