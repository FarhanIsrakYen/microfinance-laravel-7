@extends('Layouts.erp_master')
@section('content')
<?php
use App\Services\HtmlService as HTML;
?>

<!--  Page -->
<form method="post" class="form-horizontal" data-toggle="validator" novalidate="true">
    @csrf
    <div class="row">
        <div class="col-lg-9 offset-lg-3">
            <!-- Html View Load  -->
            {!! HTML::forCompanyFeild() !!}
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Package Name</label>
                <div class="col-lg-5">
                    <div class="form-group">

                        <div class="input-group ">
                          <input type="text" class="form-control round" id="packageId" name="package_name"
                              placeholder="Enter Package Name" required="required">
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Package Products</label>
                <div class="col-lg-5">
                    <div class="form-group">
                        <div class="input-group ">
                            <select class="form-control clsSelect2" multiple="multiple" 
                                name="package_products[]" id="package_products" required
                                data-error="Please select Package Products.">
                               <option value="">Select Package Products</option>

                               @foreach ($ProductData as $Row)
                               @if($Row->prod_cat_id == 5)
                               <option value="{{$Row->id}}">{{$Row->product_name}}</option>
                               @endif
                               @endforeach
                              {{-- @foreach($PCategoryData as $PCategory)
                               @if($PCategory->cat_name == "Ready Product")
                               @foreach ($ProductData as $Row)
                               <option value="{{$Row->id}}">{{$Row->product_name}}</option>
                               @endforeach
                               @endif
                              @endforeach --}}
                            </select>
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Package Price</label>
                <div class="col-lg-5">
                    <div class="form-group">

                        <div class="input-group ">
                            <input type="text" class="form-control round" id="package_price" name="package_price"
                                placeholder="Enter Package Price" required="required">
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-9">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();"
                                class="btn btn-default btn-round d-print-none">Back</a>
                            <button type="submit" class="btn btn-primary btn-round" id="validateButton2">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<!-- End Page -->
<script>
$(document).ready(function() {
    $('form').submit(function(event) {
        // event.preventDefault();
        $(this).find(':submit').attr('disabled', 'disabled');
        // $(this).submit();
    });

});
</script>
@endsection
