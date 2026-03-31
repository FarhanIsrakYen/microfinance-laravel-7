@extends('Layouts.erp_master')
@section('content')

<!-- Page -->
<div class="row">
    <div class="col-lg-9 offset-lg-3">

        <div class="form-row align-items-center">
            <label class="col-lg-3 input-title RequiredStar">Group</label>
            <div class="col-lg-5 form-group">
                <div class="input-group ">
                    <select class="form-control clsSelect2" name="prod_group_id" id="selProdGroupId" disabled>
                        <option value="{{$ProdSizeData->prod_group_id}}" selected="selected">
                            {{$ProdSizeData->pgroup['group_name']}}</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-row align-items-center">
            <label class="col-lg-3 input-title RequiredStar">Category</label>
            <div class="col-lg-5 form-group">
                <div class="input-group">
                    <select class="form-control clsSelect2" name="prod_cat_id" id="selCategoryId" disabled>
                        <option value="{{$ProdSizeData->prod_cat_id}}" selected="selected">
                            {{$ProdSizeData->pcategory['cat_name']}}</option>
                    </select>

                </div>
            </div>
        </div>

        <div class="form-row align-items-center">
            <label class="col-lg-3 input-title RequiredStar">Sub Category</label>
            <div class="col-lg-5 form-group">
                <div class="input-group">
                    <select class="form-control clsSelect2" name="prod_sub_cat_id" id="selSubCategoryId" disabled>
                        <option value="{{$ProdSizeData->prod_sub_cat_id}}" selected="selected">
                            {{$ProdSizeData->psubCategoty['sub_cat_name']}}</option>

                    </select>
                </div>
            </div>
        </div>

        <!-- <div class="form-row align-items-center">
            <label class="col-lg-3 input-title RequiredStar">Brand</label>
            <div class="col-lg-5 form-group">
                <div class="input-group">
                    <select class="form-control clsSelect2" name="prod_brand_id" id="selBrandId" required
                        data-error="Please select Product Brand." disabled>
                        <option value="{{$ProdSizeData->prod_brand_id}}" selected="selected">
                            {{$ProdSizeData->pbrand['brand_name']}}</option>

                    </select>
                </div>
            </div>
        </div> -->

        <!-- <div class="form-row align-items-center">
            <label class="col-lg-3 input-title RequiredStar">Model</label>
            <div class="col-lg-5 form-group">
                <div class="input-group">
                    <select class="form-control clsSelect2" name="prod_model_id" id="selModelId" required
                        data-error="Please select Product Model." disabled>
                        <option value="{{$ProdSizeData->prod_model_id}}" selected="selected">
                            {{$ProdSizeData->pmodel['model_name']}}</option>
                    </select>
                </div>
            </div>
        </div> -->

        <div class="form-row align-items-center">
            <label class="col-lg-3 input-title RequiredStar">Size</label>
            <div class="col-lg-5 form-group">
                <div class="input-group">
                    <input type="text" class="form-control round" placeholder="Enter Size Name" name="size_name"
                        id="textsize_name" value="{{$ProdSizeData->size_name}}" readonly required
                        data-error="Please enter Product Size.">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-9">
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
<!-- End Page -->



@endsection