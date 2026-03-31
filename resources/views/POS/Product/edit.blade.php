@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\CommonService as Common;
?>

<?php 
$flagEdit = true;
$purdata = Common::ViewTableOrder('pos_purchases_d', [['product_id', $ProductData->id]], ['id'], ['id', 'ASC']);
$obdata = Common::ViewTableOrder('pos_ob_stock_d', [['product_id', $ProductData->id]], ['id'], ['id', 'ASC']);
//  dd($purdata->count());
if($purdata->count() > 0 || $obdata->count() > 0){
    $flagEdit = false;
//   dd('hh');
}
// dd('bair');


?>
<!-- Page -->
<form enctype="multipart/form-data" method="post" data-toggle="validator" novalidate="true">
    @csrf
    <input type="hidden" name="per" id="commission_percentage">

    <div class="row">
        <!--Form Left-->
        <div class="col-lg-6">
            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Supplier Name</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" required data-error="Please select Supplier Name."
                            name="supplier_id" id="supplier_id" onchange="fnGeneratebarcode();">
                            <option value="">Select Supplier</option>
                            @foreach ($SupplierData as $Row)
                            <option value="{{$Row->id}}"
                                {{ ($ProductData->supplier_id == $Row->id) ? 'selected="selected"' : '' }}>
                                {{$Row->sup_name}}</option>
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
                        <select class="form-control clsSelect2" required data-error="Please select group name."  {{($flagEdit==false)? 'disabled': ''}}
                            name="prod_group_id" id="prod_group_id" onchange="fnAjaxSelectBox(
                                                'prod_cat_id',
                                                this.value,
                                    '{{base64_encode('pos_p_categories')}}',
                                    '{{base64_encode('prod_group_id')}}',
                                    '{{base64_encode('id,cat_name')}}',
                                    '{{url('/ajaxSelectBox')}}'
                                            );">
                            <option value="">Select group</option>
                            @foreach ($PGroupData as $Row)
                            <option value="{{$Row->id}}"
                                {{ ($ProductData->prod_group_id == $Row->id) ? 'selected="selected"' : '' }}>
                                {{$Row->group_name}}</option>
                            @endforeach
                        </select>

                        <input type="hidden" name="prod_group_id" value="{{ $ProductData->prod_group_id }}">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Category</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" required data-error="Please select Category."  {{($flagEdit==false)? 'disabled': ''}}
                            name="prod_cat_id" id="prod_cat_id" onchange="fnAjaxSelectBox(
                                                'prod_sub_cat_id',
                                                this.value,
                                    '{{base64_encode('pos_p_subcategories')}}',
                                    '{{base64_encode('prod_cat_id')}}',
                                    '{{base64_encode('id,sub_cat_name')}}',
                                    '{{url('/ajaxSelectBox')}}'
                                            );">
                            <option value="">Select Category</option>
                        </select>

                        <input type="hidden" name="prod_cat_id" value="{{ $ProductData->prod_cat_id }}">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Sub Category</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" required data-error="Please select Sub Category."
                            name="prod_sub_cat_id" id="prod_sub_cat_id" onchange="fnGeneratebarcode();">
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
                            name="prod_model_id" id="prod_model_id" onchange="fnGeneratebarcode();">
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
                            name="prod_size_id" id="prod_size_id" onchange="fnGeneratebarcode();">
                            <option value="">Select Size</option>
                            <!-- @foreach ($SizeData as $Row)
                            <option value="{{$Row->id}}"
                                {{ ($ProductData->prod_size_id == $Row->id) ? 'selected="selected"' : '' }}>
                                {{$Row->size_name}}</option>
                            @endforeach -->
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Color</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" required data-error="Please select Color."
                            name="prod_color_id" id="prod_color_id" onchange="fnGeneratebarcode();">
                            <option value="">Select Color</option>
                            <!-- @foreach ($ColorData as $Row)
                            <option value="{{$Row->id}}"
                                {{ ($ProductData->prod_color_id == $Row->id) ? 'selected="selected"' : '' }}>
                                {{$Row->color_name}}</option>
                            @endforeach -->
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Brand</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" required data-error="Please select Brand."  {{($flagEdit==false)? 'disabled': ''}}
                            name="prod_brand_id" id="prod_brand_id">
                            <option value="">Select Brand</option>
                            @foreach ($BrandData as $Row)
                            <option value="{{$Row->id}}"
                                {{ ($ProductData->prod_brand_id == $Row->id) ? 'selected="selected"' : '' }}>
                                {{$Row->brand_name}}</option>
                            @endforeach
                        </select>

                        <input type="hidden" name="prod_brand_id" value="{{ $ProductData->prod_brand_id }}">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title RequiredStar">UOM</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" required data-error="Please select UOM."
                            name="prod_uom_id" id="prod_uom_id" onchange="fnGeneratebarcode();">

                            <option value="">Select UOM</option>
                            @foreach ($UOMData as $Row)
                            <option value="{{$Row->id}}"
                                {{ ($ProductData->prod_uom_id == $Row->id) ? 'selected="selected"' : '' }}>
                                {{$Row->uom_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title">Warranty</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <input type="text" name="warranty" id="warranty" class="form-control round"
                            value="{{$ProductData->warranty}}" placeholder="Enter Warranty ">
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title">Service Warranty</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" name="service_warranty" id="service_warranty"
                            value="{{$ProductData->service_warranty}}" placeholder="Enter Service Warranty. ">
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title">Compresse Warranty</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" name="compresse_warranty" id="compresse_warranty"
                            value="{{$ProductData->compresse_warranty}}" placeholder="Enter Compresse Warranty ">

                    </div>
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
                        <input type="text" class="form-control round" placeholder="Enter Product Name."
                            name="product_name" id="product_name" value="{{$ProductData->product_name}}" required
                            data-error="Please fill up Product Name." 
                            onblur="fnCheckDuplicate(
                                '{{base64_encode('pos_products')}}', 
                                this.name+'&&is_delete', 
                                this.value+'&&0',
                                '{{url('/ajaxCheckDuplicate')}}',
                                this.id,
                                'txtCodeError', 
                                'product name');
                                fnGeneratebarcode();">

                    </div>
                    <div class="help-block with-errors is-invalid" id="txtCodeError"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Cost Price</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <input type="number" class="form-control round onlyNumber" placeholder="Enter Cost Price. "
                            name="cost_price" id="cost_price" value="{{$ProductData->cost_price}}" required {{ ($flagEdit == false) ? 'readonly': '' }}
                            data-error="Please fill up cost price." onchange="fnGeneratebarcode();">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Sale Price</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <input type="number" class="form-control round onlyNumber" placeholder="Enter Sale Price."
                            name="sale_price" id="sale_price" value="{{$ProductData->sale_price}}" required
                            data-error="Please fill up sale price.">
                            <!-- onchange="fnGeneratebarcode();" -->
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title">Upload Image </label>
                <div class="col-lg-7 form-group">
                    <div class="input-group input-group-file" data-plugin="inputGroupFile">
                        <input type="text" class="form-control round" readonly="">
                        <div class="input-group-append">
                            <span class="btn btn-success btn-file">
                                <i class="icon wb-upload" aria-hidden="true"></i>
                                <input type="file" id="prod_image" name="prod_image"
                                    value="{{$ProductData->prod_image}}" 
                                    onchange="validate_fileupload(this.id);">
                            </span>
                        </div>
                    </div>
                    <span style="font-size: 14px; color: green;">(Maximum file size 1 Mb)</span>
                </div>

                <div class="col-lg-1">
                    @if(!empty($ProductData->prod_image))

                    @if(file_exists($ProductData->prod_image))
                    <img src="{{ asset($ProductData->prod_image) }}" style="width: 70px;">
                    @endif
                    @endif
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title">Minimum Stock</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <input type="number" name="min_stock" id="min_stock" class="form-control round"
                            placeholder="Enter Minimum Stock." value="{{ $ProductData->min_stock }}" >
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title">VAT </label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" placeholder="Enter VAT." name="prod_vat"
                            id="prod_vat" value="{{$ProductData->prod_vat}}">
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title">Barcode </label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" value="{{$ProductData->prod_barcode}}"
                            placeholder="Enter Barcode" name="prod_barcode" id="prod_barcode">
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title RequiredStar">System Barcode</label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <input type="text" name="sys_barcode" required id="sys_barcode" class="form-control round"
                            placeholder="System Generated Barcode" value="{{$ProductData->sys_barcode}}" readonly>

                    </div>
                    <br>
                    <input type="hidden" id="barcode_image" name="barcode_image">
                    <input type="hidden" id="barcode_image_old" name="barcode_image_old"
                        value="{{$ProductData->barcode_image}}">

                    <div id="divBarImage">
                        <img src="{{ asset($ProductData->barcode_image) }}">
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title">Description </label>
                <div class="col-lg-7 form-group">
                    <div class="input-group">
                        <textarea class="form-control round" id="prod_desc" name="prod_desc" rows="2"
                            placeholder="Enter Description">{{$ProductData->prod_desc}}</textarea>

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
                    <button type="submit" class="btn btn-primary btn-round">Update</button>
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
        '{{ $ProductData->prod_group_id }}',
        '{{base64_encode("pos_p_categories")}}',
        '{{base64_encode("prod_group_id")}}',
        '{{base64_encode("id,cat_name")}}',
        '{{url("/ajaxSelectBox")}}',
        '{{ $ProductData->prod_cat_id}}'
    );
    fnAjaxSelectBox(
        'prod_sub_cat_id',
        '{{ $ProductData->prod_cat_id }}',
        '{{base64_encode("pos_p_subcategories")}}',
        '{{base64_encode("prod_cat_id")}}',
        '{{base64_encode("id,sub_cat_name")}}',
        '{{url("/ajaxSelectBox")}}',
        '{{ $ProductData->prod_sub_cat_id}}'
    );

    // fnAjaxSelectBox(
    //     'prod_model_id',
    //     '{{ $ProductData->prod_brand_id }}',
    //     '{{base64_encode("pos_p_models")}}',
    //     '{{base64_encode("prod_brand_id")}}',
    //     '{{base64_encode("id,model_name")}}',
    //     '{{url("/ajaxSelectBox")}}',
    //     '{{ $ProductData->prod_model_id}}'
    // );

    var group_id = '{{ $ProductData->prod_group_id}}';
    var cat_id = '{{ $ProductData->prod_cat_id}}';
    var sub_cat_id = '{{ $ProductData->prod_sub_cat_id}}';
    var sel_model_id = '{{ $ProductData->prod_model_id}}';
    var sel_size_id = '{{ $ProductData->prod_size_id}}';
    var sel_color_id = '{{ $ProductData->prod_color_id}}';

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

$(document).ready(function() {

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

    $('#prod_group_id').change(function() {
        if ($('#prod_group_id').val() != "") {
            $('#prod_cat_id').val('');
            $('#prod_cat_id').empty().html('');
            //console.log($('#prod_cat_id').val());
            fnGeneratebarcode();
        }
    });

    $('#prod_cat_id').change(function() {
        if ($('#prod_cat_id').val() != "") {
            $('#prod_sub_cat_id').val('');
            $('#prod_sub_cat_id').empty().html('');
            fnGeneratebarcode();
        }
    });

    $('#prod_brand_id').change(function() {
        if ($('#prod_brand_id').val() != "") {
            // $('#prod_model_id').val('');
            // $('#prod_model_id').selectpicker('refresh');
            fnGeneratebarcode();
        }
    });


    $('#supplier_id').change(function() {

        if ($(this).val() != '') {
            var ID = $('#supplier_id').val();
            $.ajax({
                method: "GET",
                url: "{{url('/ajaxSupplierInfo')}}",
                dataType: "json",
                data: {
                    ID: ID
                },
                success: function(data) {
                    if (data.supplier_type == "2") {

                        $('#cost_price').val('0');
                        $('#cost_price').prop('readonly', true);
                        $('#commission_percentage').val(data.comission_percent);

                        calculateCost();
                    } else {
                        $('#cost_price').val('0');
                        $("#cost_price").prop('readonly', false);
                        $('#commission_percentage').val('0');
                    }
                }
            });
        }
    });
    $('#sale_price').keyup(function() {
        if ($(this).val() != '') {
            calculateCost();
        }
    });
});

function calculateCost() {

    var per = Number($('#commission_percentage').val());
    var sale = Number($('#sale_price').val());
    if (per > 0) {
        var cost = sale - ((sale * per) / 100);
        $('#cost_price').val(Math.round(cost));
    }
}


function fnGeneratebarcode() {


    //console.log($('#prod_cat_id').val());
    if ($('#prod_group_id').val() != "" &&
        $('#prod_cat_id').val() != "" &&
        $('#prod_brand_id').val() != "" &&
        $('#product_name').val() != "" &&
        $('#supplier_id').val() != "" &&
        $('#prod_sub_cat_id').val() != "" &&
        $('#prod_model_id').val() != "" &&
        $('#prod_color_id').val() != "" &&
        $('#prod_uom_id').val() != "" && 
        $('#cost_price').val() != "") {

            // &&
        // $('#sale_price').val() != ""

        checkAllElements();


    } else {
        $('#sys_barcode').val('');
        $('#divBarImage').empty();
    }

}

function checkAllElements() {
    if ($('#prod_group_id').val() != "" && $('#prod_cat_id').val() != "" && $('#prod_brand_id').val() != "") {
        var GroupID = $('#prod_group_id').val();
        var CatID = $('#prod_cat_id').val();
        var BrandID = $('#prod_brand_id').val();
        $.ajax({
            method: "GET",
            url: "{{url('/ajaxBarcode')}}",
            dataType: "json",
            data: {
                GroupID: GroupID,
                CatID: CatID,
                BrandID: BrandID
            },
            success: function(data) {
                $('#sys_barcode').val(data.barcode);
                $('#divBarImage').html(data.bar_image);
                //console.log(data.bar_image);
            }
        });
    }
}


$('form').submit(function(event) {
    // event.preventDefault();
    $(this).find(':submit').attr('disabled', 'disabled');
    // $(this).submit();
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