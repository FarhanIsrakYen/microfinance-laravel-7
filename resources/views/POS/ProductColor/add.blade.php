@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
?>

<?php
$brandData = Common::ViewTableOrder('pos_p_brands',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'brand_name'],
    ['brand_name', 'ASC']);

?>
<!-- Page -->
<form enctype="multipart/form-data" method="POST" data-toggle="validator" novalidate="true">
    @csrf

    <div class="row">
        <div class="col-lg-9 offset-lg-3">
            {!! HTML::forBranchSelect() !!}
            {!! HTML::forCompanySelect() !!}

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Group</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group ">
                        <select class="form-control clsSelect2" name="prod_group_id" id="prod_group_id" required
                            data-error="Please select Product group" onchange="fnAjaxSelectBox(
                                                     'prod_cat_id',
                                                     this.value,
                                         '{{base64_encode('pos_p_categories')}}',
                                         '{{base64_encode('prod_group_id')}}',
                                         '{{base64_encode('id,cat_name')}}',
                                         '{{url('/ajaxSelectBox')}}'
                                                 );">
                            <option value="">Select Group</option>
                            @foreach ($ProdGroupData as $Row)
                            <option value="{{$Row->id}}">{{$Row->group_name}}</option>
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
                                         '{{base64_encode('pos_p_subcategories')}}',
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
                            data-error="Please select Product Brand.">
                            <option value="">Select Brand</option>
                            @foreach ($brandData as $Row)
                            <option value="{{$Row->id}}">{{$Row->brand_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div> -->
            
            <!-- <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Model</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="prod_model_id" id="prod_model_id" required
                            data-error="Please select Product Model." onchange="fnAjaxSelectBox(
                                                     'prod_size_id',
                                                     this.value,
                                         '{{base64_encode('pos_p_sizes')}}',
                                         '{{base64_encode('prod_model_id')}}',
                                         '{{base64_encode('id,size_name')}}',
                                         '{{url('/ajaxSelectBox')}}'
                                                 );">
                            <option value="">Select Model</option>

                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div> -->

            <!-- <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Size</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="prod_size_id" id="prod_size_id" required
                            data-error="Please select Product Size.">
                            <option value="">Select Size</option>

                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div> -->

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Color</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" placeholder="Enter color Name" name="color_name"
                            id="prod_color_id" required data-error="Please enter Product color.">
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

// $("#prod_brand_id").change(function() {

//     var brand_id = $('#prod_brand_id').val();
//     var group_id = $('#prod_group_id').val();
//     var cat_id = $('#prod_cat_id').val();
//     var sub_cat_id = $('#prod_sub_cat_id').val();


//     if (brand_id != null && group_id != null && cat_id != null && sub_cat_id != null) {

//         $.ajax({
//             method: "GET",
//             url: '{{ url("pos/color/loadModelColor") }}',
//             dataType: "text",
//             data: {
//                 group_id: group_id,
//                 cat_id: cat_id,
//                 sub_cat_id: sub_cat_id,
//                 brand_id: brand_id,
//                 sel_model_id: null
//             },
//             success: function(data) {
//                 if (data) {
//                     $('#prod_model_id')
//                         .empty()
//                         .html(data);
//                 }
//             }
//         });
//     }

// });
</script>
@endsection