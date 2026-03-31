@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\HtmlService as HTML;
?>

<form method="post" data-toggle="validator" novalidate="true">
    @csrf
    <div class="row">
        <div class="col-lg-9 offset-lg-3">
            <!-- Html View Load  -->
            {!! HTML::forCompanyFeild(null, null, true) !!}

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Amount</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" name="amount" id="amount" class="form-control round"
                            placeholder="Enter amount" required data-error="Please enter amount.">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-9">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();"
                                class="btn btn-default btn-round d-print-none">Back</a>
                            <button type="submit" class="btn btn-primary btn-round">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<!-- End Page -->
<script type="text/javascript">
    $('form').submit(function(event) {
        $(this).find(':submit').attr('disabled', 'disabled');
    });
</script>

@endsection