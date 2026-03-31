@extends('Layouts.erp_master')
@section('content')
<!-- Page -->
<form enctype="multipart/form-data" method="post" data-toggle="validator" novalidate="true">
    @csrf

    <!-- {{-- name="commission_percentage" --}} -->
    <input type="hidden" name="per" id="commission_percentage" value="">

    <div class="row">
        <!--Form Left-->
        <div class="col-lg-6">

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Supplier Name</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">

                        <select name="supplier_id" id="supplier_id" class="form-control clsSelect2" required
                            data-error="Please select Supplier Name." >

                            <option value="">Select Supplier</option>
                            @foreach ($SupplierData as $Row)
                            <option value="{{$Row->id}}">{{$Row->sup_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Group</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" required data-error="Please select group name." 
                            name="prod_group_id" id="prod_group_id" onchange="fnAjaxSelectBox(
                                                'prod_cat_id',
                                                this.value,
                                    '{{base64_encode('inv_p_categories')}}',
                                    '{{base64_encode('prod_group_id')}}',
                                    '{{base64_encode('id,cat_name')}}',
                                    '{{url('/ajaxSelectBox')}}'
                                            );">
                            <option value="">Select group</option>
                            @foreach ($PGroupData as $Row)
                            <option value="{{$Row->id}}">{{$Row->group_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Category</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" required data-error="Please select Category."
                            name="prod_cat_id" id="prod_cat_id" onchange="fnAjaxSelectBox(
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
                <label class="col-lg-4 input-title RequiredStar">Sub Category</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" required data-error="Please select Sub Category."
                            name="prod_sub_cat_id" id="prod_sub_cat_id" >
                            <option value="">Select Sub Category</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Model</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" required data-error="Please select Model."
                            name="prod_model_id" id="prod_model_id" >
                            <option value="">Select Model</option>

                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Size</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" required data-error="Please select Size."
                            name="prod_size_id" id="prod_size_id" >
                            <option value="">Select Size</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Color</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" required 
                            data-error="Please select Color."
                            name="prod_color_id" id="prod_color_id" >
                            <option value="">Select Color</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Brand</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" required 
                            data-error="Please select Brand."
                            name="prod_brand_id" id="prod_brand_id">
                            <option value="">Select Brand</option>
                            @foreach ($BrandData as $Row)
                            <option value="{{$Row->id}}">{{$Row->brand_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title RequiredStar">UOM</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" required 
                            data-error="Please select UOM."
                            name="prod_uom_id" id="prod_uom_id" >

                            <option value="">Select UOM</option>
                            @foreach ($UOMData as $Row)
                            <option value="{{$Row->id}}">{{$Row->uom_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <!--    end left -->
        </div>

        <!--Form Right-->
        <div class="col-lg-6">

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Product Name</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <input type="text" name="product_name" id="product_name" class="form-control round"
                            placeholder="Enter Product Name." required data-error="Please fill up Product Name."
                            onblur="fnCheckDuplicate(
                                '{{base64_encode('inv_products')}}', 
                                this.name+'&&is_delete', 
                                this.value+'&&0',
                                '{{url('/ajaxCheckDuplicate')}}',
                                this.id,
                                'txtCodeError', 
                                'product name');">
                    </div>
                    <div class="help-block with-errors is-invalid" id="txtCodeError"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Cost Price</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <input type="number" name="cost_price" id="cost_price" class="form-control round"
                            placeholder="Enter Cost Price." required data-error="Please fill up cost price"
                            onblur="">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title">Product Image</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group input-group-file" data-plugin="inputGroupFile">
                        <input type="text" class="form-control round" readonly="">
                        <div class="input-group-append">
                            <span class="btn btn-success btn-file">
                                <i class="icon wb-upload" aria-hidden="true"></i>
                                <input type="file" id="prod_image" name="prod_image" onchange="validate_fileupload(this.id);">
                            </span>
                        </div>
                    </div>
                    <span style="font-size: 14px; color: green;">(Maximum file size 1 Mb)</span>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title">Minimum Stock</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <input type="number" name="min_stock" id="min_stock" class="form-control round"
                            placeholder="Enter Minimum Stock.">
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title">Description </label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <textarea class="form-control round" id="prod_desc" name="prod_desc" rows="2"
                            placeholder="Enter Description"></textarea>

                    </div>
                </div>
            </div>
        </div>
        <!--  end Right    -->
    </div>

    <div class="row ">
        <div class="col-lg-12">
            <div class="form-group d-flex justify-content-center">
                <div class="example example-buttons">
                    <a href="javascript:void(0)" onclick="goBack();"
                        class="btn btn-default btn-round d-print-none">Back</a>
                    <button type="submit" class="btn btn-primary btn-round">Save</button>
                    <!-- <a href="#"><button type="button" class="btn btn-warning btn-round">Next</button></a> -->
                </div>
            </div>
        </div>
    </div>
</form>
<!-- End Page -->
<script type="text/javascript">
$(document).ready(function() {

    // Data Load

    $('#prod_sub_cat_id').change(function() {

        var group_id = $('#prod_group_id').val();
        var cat_id = $('#prod_cat_id').val();
        var sub_cat_id = $('#prod_sub_cat_id').val();
        var sel_model_id = $('#prod_model_id').val();
        var sel_size_id = $('#prod_size_id').val();
        var sel_color_id = $('#prod_color_id').val();

        if (group_id != null && cat_id != null && sub_cat_id != null) {

            $.ajax({
                method: "GET",
                url: '{{ url("pos/product/loadModelForProduct") }}',
                dataType: "text",
                data: {
                    group_id: group_id,
                    cat_id: cat_id,
                    sub_cat_id: sub_cat_id,
                    sel_model_id: sel_model_id
                },
                success: function(data) {
                    if (data) {
                        $('#prod_model_id').empty().html(data);
                    }
                }
            });

            $.ajax({
                method: "GET",
                url: '{{ url("pos/product/loadSizeForProduct") }}',
                dataType: "text",
                data: {
                    group_id: group_id,
                    cat_id: cat_id,
                    sub_cat_id: sub_cat_id,
                    sel_size_id: sel_size_id
                },
                success: function(data) {
                    if (data) {
                        $('#prod_size_id').empty().html(data);
                    }
                }
            });

            $.ajax({
                method: "GET",
                url: '{{ url("pos/product/loadColorForProduct") }}',
                dataType: "text",
                data: {
                    group_id: group_id,
                    cat_id: cat_id,
                    sub_cat_id: sub_cat_id,
                    sel_color_id: sel_color_id
                },
                success: function(data) {
                    if (data) {
                        $('#prod_color_id').empty().html(data);
                    }
                }
            });
        }
    });

    /////////////////////////////////////////////////////////////////
    $('#prod_group_id').change(function() {
        if ($('#prod_group_id').val() != "") {
            $('#prod_cat_id').val('');
            // $('#prod_cat_id').selectpicker('refresh');

            $('#prod_cat_id').empty().html('');

            
        }
    });

    $('#prod_cat_id').change(function() {
        if ($('#prod_cat_id').val() != "") {
            $('#prod_sub_cat_id').val('');
            $('#prod_sub_cat_id').empty().html('');
            
        }
    });

    $('#prod_brand_id').change(function() {
        if ($('#prod_brand_id').val() != "") {
            // $('#prod_model_id').val('');
            // $('#prod_model_id').selectpicker('refresh');
            
        }
    });


    // $('#supplier_id').change(function() {

    //     if ($(this).val() != null) {
    //         var ID = $('#supplier_id').val();

    //         $.ajax({
    //             method: "GET",
    //             url: "{{url('/ajaxSupplierInfo')}}",
    //             dataType: "json",
    //             data: {
    //                 ID: ID
    //             },
    //             success: function(data) {
    //                 if (data.supplier_type == "2") {

    //                     $('#cost_price').val('0');
    //                     $('#cost_price').prop('readonly', true);
    //                     $('#commission_percentage').val(data.comission_percent);

    //                     calculateCost();

    //                 } else {
                        
    //                     $("#cost_price").prop('readonly', false);
    //                     $('#commission_percentage').val('0');
    //                 }
    //             }
    //         });
    //     }
    // });

    // $('#sale_price').keyup(function() {
    //     if ($(this).val() != '') {
    //         calculateCost();
    //     }
    // });

});


// function calculateCost() {

//     var per = Number($('#commission_percentage').val());
//     var sale = Number($('#sale_price').val());

//     if (per > 0) {
//         var cost = sale - ((sale * per) / 100);
//         $('#cost_price').val(Math.round(cost));
//     }
// }


    $('form').submit(function(event) {
        $(this).find(':submit').attr('disabled', 'disabled');
    });

    function validate_fileupload(id) {
        var myFile = $('#' + id).prop('files');
        var filetype = myFile[0].type;
        var filesize = myFile[0].size / (1024 * 1024);  // in mb

        var errorFlag = false;

        if(filesize > 1){
            errorFlag = true;
        }

        if(filetype == 'image/jpeg' 
            || filetype == 'image/jpg' 
            || filetype == 'image/png' 
            || filetype == 'image/bmp' 
            || filetype == 'image/gif')
        {
            errorFlag = false;
        }
        else{
            errorFlag = true;
        }
        
        if(errorFlag === true){
            $('#' + id).val('');
            swal({
                icon: 'error',
                title: 'Error',
                text: 'File size must be equal or less than 1 mb & file type is image. !!',
            });
        }
    }
</script>
@endsection