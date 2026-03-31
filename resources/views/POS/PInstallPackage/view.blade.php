@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\HtmlService as HTML;
?>

<form enctype="multipart/form-data" action="" method="POST" data-toggle="validator" novalidate="true">
    @csrf

    <div class="row">
        <div class="col-lg-9 offset-lg-3">
            <!-- Html View Load  -->
            <!-- {!! HTML::forCompanyFeild($ProdInstPackData->company_id,'disabled') !!} -->

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Month</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" placeholder="Enter uom Name" name="uom_name"
                            id="textuom_name" readonly value="{{$ProdInstPackData->prod_inst_month}}" required
                            data-error="Please enter Month.">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Profit</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" placeholder="Enter uom Name" name="uom_name"
                            id="textuom_name" readonly value="{{$ProdInstPackData->prod_inst_profit}}" required
                            data-error="Please enter Product uom.">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();"
                                class="btn btn-default btn-round d-print-none">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection