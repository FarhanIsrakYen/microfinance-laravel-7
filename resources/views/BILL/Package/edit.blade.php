@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\HtmlService as HTML;
?>

<!-- End Page -->
<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator" novalidate="true">
    @csrf
    <div class="row ">
        <div class="col-lg-9 offset-lg-3">
            <!-- Html View Load  -->
            {!! HTML::forCompanyFeild($PackageData->company_id) !!}
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Package Name</label>
                <div class="col-lg-5">
                    <div class="form-group">

                        <div class="input-group ">
                          <input type="text" value="{{$PackageData->package_name}}" class="form-control round"
                              id="package_name" name="package_name" placeholder="Enter Package Name" required
                              data-error="Please select Package Name.">
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
                          <select class="form-control clsSelect2" name="package_products[]" id="package_products" required
                              data-error="Please select  Package Products" multiple="multiple">
                              @foreach ($ProductData as $Row)
                              @if($Row->prod_cat_id == 5)
                              <option value="{{ $Row->id }}" @if(in_array($Row->id, explode(',',$PackageData->package_products)))
                                    {{ 'selected' }}
                                    @endif>{{ $Row->product_name }}</option>
                              @endif
                              @endforeach
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
                            <input type="text" value="{{$PackageData->package_price}}" class="form-control round"
                                id="package_price" name="package_price" placeholder="Enter Package Price" required">
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
                            <button type="submit" class="btn btn-primary btn-round" id="validateButton2">Update</button>
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
    // fnAjaxSelectBox(
    //     'prod_cat_id',
    //     '{{ $PackageData->prod_group_id }}',
    //     '{{base64_encode("pos_p_categories")}}',
    //     '{{base64_encode("prod_group_id")}}',
    //     '{{base64_encode("id,cat_name")}}',
    //     '{{url("/ajaxSelectBox")}}',
    //     '{{ $PackageData->prod_cat_id}}'
    // );

    $('form').submit(function(event) {
        // event.preventDefault();
        $(this).find(':submit').attr('disabled', 'disabled');
        // $(this).submit();
    });

});
</script>
@endsection
