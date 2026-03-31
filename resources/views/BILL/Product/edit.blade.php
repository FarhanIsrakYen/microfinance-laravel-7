@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\CommonService as Common;
?>

<?php
$flagEdit = true;
// $purdata = Common::ViewTableOrder('bill_purchases_d', [['product_id', $ProductData->id]], ['id'], ['id', 'ASC']);
// $obdata = Common::ViewTableOrder('bill_ob_stock_d', [['product_id', $ProductData->id]], ['id'], ['id', 'ASC']);
// //  dd($purdata->count());
// if($purdata->count() > 0 || $obdata->count() > 0){
//     $flagEdit = false;
// //   dd('hh');
// }
// dd('bair');


?>
<!-- Page -->
<form enctype="multipart/form-data" method="post" data-toggle="validator" novalidate="true">
    @csrf
    <input type="hidden" name="per" id="commission_percentage">

    <div class="row">
        <!--Form Left-->
        <div class="col-lg-9 offset-lg-3">
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title">Supplier Name</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="supplier_id" id="supplier_id" >
                            <option value="">Select Supplier</option>
                            @foreach ($SupplierData as $Row)
                            <option value="{{$Row->id}}" {{ ($ProductData->supplier_id == $Row->id) ? 'selected="selected"' : '' }}>
                                {{$Row->sup_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Category</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" required data-error="Please select Category." {{($flagEdit==false)? 'disabled': ''}} name="prod_cat_id" id="prod_cat_id">
                            <option value="">Select Category</option>
                            @foreach ($PCategoryData as $Row)
                            <option value="{{$Row->id}}" {{ ($ProductData->prod_cat_id == $Row->id) ? 'selected="selected"' : '' }}>
                                {{$Row->cat_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Product Name</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" 
                        placeholder="Enter Product Name." 
                        name="product_name" id="product_name" 
                        value="{{$ProductData->product_name}}" required 
                        data-error="Please fill up Product Name."
                        onblur="fnCheckDuplicate(
                                '{{base64_encode('bill_products')}}', 
                                this.name+'&&is_delete', 
                                this.value+'&&0',
                                '{{url('/ajaxCheckDuplicate')}}',
                                this.id,
                                'txtCodeError', 
                                'product name',
                                '{{$ProductData->id}}');">

                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Price</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="number" class="form-control round onlyNumber" placeholder="Enter Sale Price." name="sale_price" id="sale_price" value="{{$ProductData->sale_price}}" required data-error="Please fill up sale price."
                          >
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>


            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title">Upload Image </label>
                <div class="col-lg-5 form-group">
                    <div class="input-group input-group-file" data-plugin="inputGroupFile">
                        <input type="text" class="form-control round" readonly="">
                        <div class="input-group-append">
                            <span class="btn btn-success btn-file">
                                <i class="icon wb-upload" aria-hidden="true"></i>
                                <input type="file" id="prod_image" name="prod_image" value="{{$ProductData->prod_image}}" onchange="validate_fileupload(this.id);">
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
                <label class="col-lg-3 input-title">VAT </label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" placeholder="Enter VAT." name="prod_vat" id="prod_vat" value="{{$ProductData->prod_vat}}">
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title">Description </label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <textarea class="form-control round" id="prod_desc" name="prod_desc" rows="2" placeholder="Enter Description">{{$ProductData->prod_desc}}</textarea>

                    </div>
                </div>
            </div>
            <!--    end left -->
            <div class="row ">
                <div class="col-lg-9">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round d-print-none">Back</a>
                            <button type="submit" class="btn btn-primary btn-round">Update</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


</form>
<!-- End Page -->
<script>
    // $(document).ready(function() {
    // fnAjaxSelectBox(
    //     'prod_cat_id',
    //     '{{ $ProductData->prod_group_id }}',
    //     '{{base64_encode("bill_p_categories")}}',
    //     '{{base64_encode("prod_group_id")}}',
    //     '{{base64_encode("id,cat_name")}}',
    //     '{{url("/ajaxSelectBox")}}',
    //     '{{ $ProductData->prod_cat_id}}'
    // );
    // fnAjaxSelectBox(
    //     'prod_sub_cat_id',
    //     '{{ $ProductData->prod_cat_id }}',
    //     '{{base64_encode("bill_p_subcategories")}}',
    //     '{{base64_encode("prod_cat_id")}}',
    //     '{{base64_encode("id,sub_cat_name")}}',
    //     '{{url("/ajaxSelectBox")}}',
    //     '{{ $ProductData->prod_sub_cat_id}}'
    // );

    // fnAjaxSelectBox(
    //     'prod_model_id',
    //     '{{ $ProductData->prod_brand_id }}',
    //     '{{base64_encode("bill_p_models")}}',
    //     '{{base64_encode("prod_brand_id")}}',
    //     '{{base64_encode("id,model_name")}}',
    //     '{{url("/ajaxSelectBox")}}',
    //     '{{ $ProductData->prod_model_id}}'
    // );

    // var cat_id = '{{ $ProductData->prod_cat_id}}';
    //
    // if (cat_id != null) {

    // $.ajax({
    //     method: "GET",
    //     url: '{{ url("bill/product/loadModelForProduct") }}',
    //     dataType: "text",
    //     data: {
    //         cat_id: cat_id,
    //     },
    //     // success: function(data) {
    //     //     if (data) {
    //     //         $('#prod_model_id').empty().html(data);
    //     //     }
    //     }
    // });
    //
    // $.ajax({
    //     method: "GET",
    //     url: '{{ url("bill/product/loadSizeForProduct") }}',
    //     dataType: "text",
    //     data: {
    //         group_id: group_id,
    //         cat_id: cat_id,
    //         sub_cat_id: sub_cat_id,
    //         sel_size_id: sel_size_id
    //     },
    //     success: function(data) {
    //         if (data) {
    //             $('#prod_size_id').empty().html(data);
    //         }
    //     }
    // });

    // $.ajax({
    //     method: "GET",
    //     url: '{{ url("bill/product/loadColorForProduct") }}',
    //     dataType: "text",
    //     data: {
    //         group_id: group_id,
    //         cat_id: cat_id,
    //         sub_cat_id: sub_cat_id,
    //         sel_color_id: sel_color_id
    //     },
    //     success: function(data) {
    //         if (data) {
    //             $('#prod_color_id').empty().html(data);
    //         }
    //     }
    // });
    //     }
    //
    // });

    $(document).ready(function() {
        //
        //     $('#prod_sub_cat_id').change(function() {
        //
        //         var group_id = $('#prod_group_id').val();
        //         var cat_id = $('#prod_cat_id').val();
        //         var sub_cat_id = $('#prod_sub_cat_id').val();
        //         var sel_model_id = $('#prod_model_id').val();
        //         var sel_size_id = $('#prod_size_id').val();
        //         var sel_color_id = $('#prod_color_id').val();
        //
        //         if (group_id != null && cat_id != null && sub_cat_id != null) {
        //
        //             $.ajax({
        //                 method: "GET",
        //                 url: '{{ url("bill/product/loadModelForProduct") }}',
        //                 dataType: "text",
        //                 data: {
        //                     group_id: group_id,
        //                     cat_id: cat_id,
        //                     sub_cat_id: sub_cat_id,
        //                     sel_model_id: sel_model_id
        //                 },
        //                 success: function(data) {
        //                     if (data) {
        //                         $('#prod_model_id').empty().html(data);
        //                     }
        //                 }
        //             });
        //
        //             $.ajax({
        //                 method: "GET",
        //                 url: '{{ url("bill/product/loadSizeForProduct") }}',
        //                 dataType: "text",
        //                 data: {
        //                     group_id: group_id,
        //                     cat_id: cat_id,
        //                     sub_cat_id: sub_cat_id,
        //                     sel_size_id: sel_size_id
        //                 },
        //                 success: function(data) {
        //                     if (data) {
        //                         $('#prod_size_id').empty().html(data);
        //                     }
        //                 }
        //             });
        //
        //             $.ajax({
        //                 method: "GET",
        //                 url: '{{ url("bill/product/loadColorForProduct") }}',
        //                 dataType: "text",
        //                 data: {
        //                     group_id: group_id,
        //                     cat_id: cat_id,
        //                     sub_cat_id: sub_cat_id,
        //                     sel_color_id: sel_color_id
        //                 },
        //                 success: function(data) {
        //                     if (data) {
        //                         $('#prod_color_id').empty().html(data);
        //                     }
        //                 }
        //             });
        //         }
        //     });
        //
        //     $('#prod_group_id').change(function() {
        //         if ($('#prod_group_id').val() != "") {
        //             $('#prod_cat_id').val('');
        //             $('#prod_cat_id').selectpicker('refresh');
        //             //console.log($('#prod_cat_id').val());
        //             
        //         }
        //     });
        //
        //     $('#prod_cat_id').change(function() {
        //         if ($('#prod_cat_id').val() != "") {
        //             $('#prod_sub_cat_id').val('');
        //             $('#prod_sub_cat_id').selectpicker('refresh');
        //             
        //         }
        //     });
        //
        //     $('#prod_brand_id').change(function() {
        //         if ($('#prod_brand_id').val() != "") {
        //             // $('#prod_model_id').val('');
        //             // $('#prod_model_id').selectpicker('refresh');
        //             
        //         }
        //     });


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


    // function fnGeneratebarcode() {
    //
    //
    //     //console.log($('#prod_cat_id').val());
    //     if ($('#prod_group_id').val() != "" &&
    //         $('#prod_cat_id').val() != "" &&
    //         $('#prod_brand_id').val() != "" &&
    //         $('#product_name').val() != "" &&
    //         $('#supplier_id').val() != "" &&
    //         $('#prod_sub_cat_id').val() != "" &&
    //         $('#prod_model_id').val() != "" &&
    //         $('#prod_color_id').val() != "" &&
    //         $('#prod_uom_id').val() != "" &&
    //         $('#sale_price').val() != "") {
    //
    //         checkAllElements();
    //
    //
    //     } else {
    //         $('#sys_barcode').val('');
    //         $('#divBarImage').empty();
    //     }
    //
    // }
    //
    // function checkAllElements() {
    //     if ($('#prod_group_id').val() != "" && $('#prod_cat_id').val() != "" && $('#prod_brand_id').val() != "") {
    //         var GroupID = $('#prod_group_id').val();
    //         var CatID = $('#prod_cat_id').val();
    //         var BrandID = $('#prod_brand_id').val();
    //         $.ajax({
    //             method: "GET",
    //             url: "{{url('/ajaxBarcode')}}",
    //             dataType: "json",
    //             data: {
    //                 GroupID: GroupID,
    //                 CatID: CatID,
    //                 BrandID: BrandID
    //             },
    //             success: function(data) {
    //                 $('#sys_barcode').val(data.barcode);
    //                 $('#divBarImage').html(data.bar_image);
    //                 //console.log(data.bar_image);
    //             }
    //         });
    //     }
    // }


    $('form').submit(function(event) {
        // event.preventDefault();
        $(this).find(':submit').attr('disabled', 'disabled');
        // $(this).submit();
    });
</script>
@endsection
