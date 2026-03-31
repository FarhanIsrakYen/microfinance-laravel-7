@extends('Layouts.erp_master')
@section('content')

<!-- Page -->
<form enctype="multipart/form-data" action="" method="POST" data-toggle="validator" novalidate="true">
    @csrf

    <div class="row">
        <div class="col-lg-9 offset-lg-3">

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Group</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group ">
                        <select class="form-control clsSelect2" name="prod_group_id" id="selProdGroupId" required
                            data-error="Please select Product group" disabled>
                            <option value="{{$ProdColorData->prod_group_id}}" selected="selected">
                                {{$ProdColorData->pgroup['group_name']}}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Category</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="prod_cat_id" id="selCategoryId" required
                            data-error="Please select Category name." disabled>
                            <option value="{{$ProdColorData->prod_cat_id}}" selected="selected">
                                {{$ProdColorData->pcategory['cat_name']}}</option>
                        </select>

                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Sub Category</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" required data-error="Please select Sub Category name."
                            name="prod_sub_cat_id" id="selSubCategoryId" disabled>
                            <option value="{{$ProdColorData->prod_sub_cat_id}}" selected="selected">
                                {{$ProdColorData->psubCategoty['sub_cat_name']}}</option>

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
                            <option value="{{$ProdColorData->prod_brand_id}}" selected="selected">
                                {{$ProdColorData->pbrand['brand_name']}}</option>

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
                            <option value="{{$ProdColorData->prod_model_id}}" selected="selected">
                                {{$ProdColorData->pmodel['model_name']}}</option>
                        </select>
                    </div>
                </div>
            </div> -->

            <!-- <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Size</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="prod_size_id" id="selSizeId" required
                            data-error="Please select Product Model." disabled>
                            <option value="{{$ProdColorData->prod_size_id}}" selected="selected">
                                {{$ProdColorData->psize['size_name']}}</option>
                        </select>
                    </div>
                </div>
            </div> -->

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Color</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" placeholder="Enter color Name" name="color_name"
                            id="textcolor_name" readonly value="{{$ProdColorData->color_name}}" required
                            data-error="Please enter Product color." disabled>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-9">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();"
                                class="btn btn-default btn-round d-print-none">Back</a>
                            <!-- <button type="submit" class="btn btn-primary btn-round">Update</button> -->
                            <!-- <a href="#"><button type="button" class="btn btn-warning btn-round">Next</button></a> -->
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    </div>
    </div>
    </div>
</form>
<!-- End Page -->



@endsection