@extends('Layouts.erp_master')
@section('content')
<?php 
use App\Services\HtmlService as HTML;
?>
<form enctype="multipart/form-data" method="POST" data-toggle="validator" novalidate="true">
    @csrf

    <div class="row">
        <div class="col-lg-9 offset-lg-3">

            <!-- Html View Load  -->
            <!-- {!! HTML::forCompanyFeild() !!} -->

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Month</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" placeholder="Enter Month" name="prod_inst_month"
                            id="textprod_inst_month" required data-error="Please enter Product Istallment Package.">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Profit</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" placeholder="Enter Profit" name="prod_inst_profit"
                            id="textprod_inst_profit" required data-error="Please enter Product Istallment Profit.">
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

<script type="text/javascript">
$('form').submit(function(event) {
    // event.preventDefault();
    $(this).find(':submit').attr('disabled', 'disabled');
    // $(this).submit();
});
</script>
@endsection
