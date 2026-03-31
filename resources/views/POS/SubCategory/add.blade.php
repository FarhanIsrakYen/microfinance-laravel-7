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
            <!-- {!! HTML::forCompanyFeild() !!} -->
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Group Name</label>
                <div class="col-lg-5">
                    <div class="form-group">

                        <div class="input-group ">
                            <select class="form-control clsSelect2" name="prod_group_id" id="prod_group_id" required
                                data-error="Please select group name." onchange="fnAjaxSelectBox(
                                                'prod_cat_id',
                                                this.value,
                                    '{{base64_encode('pos_p_categories')}}',
                                    '{{base64_encode('prod_group_id')}}',
                                    '{{base64_encode('id,cat_name')}}',
                                    '{{url('/ajaxSelectBox')}}'
                                            );">
                                <option value="">Select Group</option>

                                @foreach ($PgroupData as $Row)
                                <option value="{{$Row->id}}">{{$Row->group_name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Category Name</label>
                <div class="col-lg-5">
                    <div class="form-group">

                        <div class="input-group ">
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
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Sub Category Name</label>
                <div class="col-lg-5">
                    <div class="form-group">

                        <div class="input-group ">
                            <input type="text" class="form-control round" id="selSubCatId" name="sub_cat_name"
                                placeholder="Enter Group Name" required="required">
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