@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\HtmlService as HTML;
?>


<!-- Page -->
<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator" novalidate="true">
    @csrf
    
    <div class="row">
        <div class="col-lg-9 offset-lg-3">
            <!-- Html View Load  -->
            <!-- {!! HTML::forCompanyFeild($PModelData->company_id) !!} -->

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Group</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group ">
                        <select class="form-control clsSelect2" name="prod_group_id" id="prod_group_id" required
                            data-error="Please select Product group" onchange="fnAjaxSelectBox(
                                             'prod_cat_id',
                                             this.value,
                                 '{{base64_encode('inv_p_categories')}}',
                                 '{{base64_encode('prod_group_id')}}',
                                 '{{base64_encode('id,cat_name')}}',
                                 '{{url('/ajaxSelectBox')}}'
                                         );">

                            @foreach($PgroupData as $Row)
                            <option value="{{ $Row->id }}" @if($Row->id==$PModelData->prod_group_id)
                                selected='selected' @endif >{{ $Row->group_name }}</option>
                            @endforeach

                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Category</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="prod_cat_id" id="prod_cat_id" required
                            data-error="Please select Category name." onchange="fnAjaxSelectBox(
                                             'prod_sub_cat_id',
                                             this.value,
                                 '{{base64_encode('inv_p_subcategories')}}',
                                 '{{base64_encode('prod_cat_id')}}',
                                 '{{base64_encode('id,sub_cat_name')}}',
                                 '{{url('/ajaxSelectBox')}}'
                                         );">
                            <option value="">Select Category</option>
                        </select>

                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Sub Category</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" required data-error="Please select Sub Category name."
                            name="prod_sub_cat_id" id="prod_sub_cat_id">
                            <option value="">Select Sub category</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <!-- <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Brand</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="prod_brand_id" id="prod_brand_id" required
                            data-error="Please select Product Brand." onchange="fnAjaxSelectBox(
                                'prod_model_id',
                                this.value,
                    '{{base64_encode('inv_p_models')}}',
                    '{{base64_encode('prod_brand_id')}}',
                    '{{base64_encode('id,model_name')}}',
                    '{{url('/ajaxSelectBox')}}'
                            );">
                            <option value="">Select Brand</option>
                            @foreach ($BrandData as $Row)
                            <option value="{{$Row->id}}"
                                {{ ($PModelData->prod_brand_id == $Row->id) ? 'selected="selected"' : '' }}>
                                {{$Row->brand_name}}</option>
                            @endforeach

                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div> -->

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Model Name</label>
                <div class="col-lg-5">
                    <div class="form-group">

                        <div class="input-group ">
                            <input type="text" value="{{$PModelData->model_name}}" class="form-control round"
                                id="model_name" name="model_name" placeholder="Enter Brand Name" required="required">
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
    fnAjaxSelectBox(
        'prod_cat_id',
        '{{ $PModelData->prod_group_id }}',
        '{{base64_encode("inv_p_categories")}}',
        '{{base64_encode("prod_group_id")}}',
        '{{base64_encode("id,cat_name")}}',
        '{{url("/ajaxSelectBox")}}',
        '{{ $PModelData->prod_cat_id}}'
    );

    fnAjaxSelectBox(
        'prod_sub_cat_id',
        '{{ $PModelData->prod_cat_id }}',
        '{{base64_encode("inv_p_subcategories")}}',
        '{{base64_encode("prod_cat_id")}}',
        '{{base64_encode("id,sub_cat_name")}}',
        '{{url("/ajaxSelectBox")}}',
        '{{ $PModelData->prod_sub_cat_id}}'
    );

    $('form').submit(function(event) {
        // event.preventDefault();
        $(this).find(':submit').attr('disabled', 'disabled');
        // $(this).submit();
    });
});
</script>
@endsection