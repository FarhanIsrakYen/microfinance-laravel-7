@extends('Layouts.erp_master')

@section('content')
<!-- Page -->
<form enctype="multipart/form-data" method="post" data-toggle="validator" novalidate="true">
    @csrf
    <!-- {{-- name="commission_percentage" --}} -->
    <input type="hidden" name="per" id="commission_percentage" value="">


    <div class="row">
        <!--Form Left-->
        <div class="col-lg-9 offset-lg-3">

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title">Supplier Name</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">

                        <select name="supplier_id" id="supplier_id" class="form-control clsSelect2" onchange="">

                            <option value="">Select Supplier</option>
                            @foreach ($SupplierData as $Row)
                            <option value="{{$Row->id}}">{{$Row->sup_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Category</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" required data-error="Please select Category." name="prod_cat_id" id="prod_cat_id">
                            <option value="">Select Category</option>
                            <option value="">Select group</option>
                            @foreach ($PCategoryData as $Row)
                            <option value="{{$Row->id}}"> {{$Row->cat_name}}</option>
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
                        <input type="text" name="product_name" id="product_name" class="form-control round" placeholder="Enter Product Name." required data-error="Please fill up Product Name."
                        onblur="fnCheckDuplicate(
                                '{{base64_encode('bill_products')}}', 
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
                <label class="col-lg-3 input-title RequiredStar">Price</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="number" name="sale_price" id="sale_price" class="form-control round" placeholder="Enter Sale Price." required data-error="Please fill up sale price." onblur="">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title">Product Image</label>
                <div class="col-lg-5 form-group">
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
                <label class="col-lg-3 input-title">VAT</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" placeholder="Enter VAT." name="prod_vat" id="prod_vat">
                    </div>
                </div>
            </div>
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title">Description </label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <textarea class="form-control round" id="prod_desc" name="prod_desc" rows="2" placeholder="Enter Description"></textarea>

                    </div>
                </div>
            </div>
            <!--    end left -->
            <div class="row ">
                <div class="col-lg-9">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round d-print-none">Back</a>
                            <button type="submit" class="btn btn-primary btn-round">Save</button>
                            <!-- <a href="#"><button type="button" class="btn btn-warning btn-round">Next</button></a> -->
                        </div>
                    </div>
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

                // $.ajax({
                //     method: "GET",
                //     url: '{{ url("bill/product/loadModelForProduct") }}',
                //     dataType: "text",
                //     data: {
                //         group_id: group_id,
                //         cat_id: cat_id,
                //         sub_cat_id: sub_cat_id,
                //         sel_model_id: sel_model_id
                //     },
                //     success: function(data) {
                //         if (data) {
                //             $('#prod_model_id').empty().html(data);
                //         }
                //     }
                // });

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
            }
        });


        /////////////////////////////////////////////////////////////////


        $('#prod_group_id').change(function() {
            if ($('#prod_group_id').val() != "") {
                $('#prod_cat_id').val('');
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


        $('#supplier_id').change(function() {

            if ($(this).val() != null) {
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
                            //console.log($('#commission_percentage').val());

                            calculateCost();

                        } else {

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
            $('#sale_price').val() != "") {
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
            // console.log('testfn'+BrandID);

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
                    if (data) {
                        $('#sys_barcode').val(data.barcode);
                        $('#divBarImage').html(data.bar_image);
                    }
                }
            });
        }
    }

    function calculateCost() {

        var per = Number($('#commission_percentage').val());
        var sale = Number($('#sale_price').val());

        if (per > 0) {
            var cost = sale - ((sale * per) / 100);
            $('#cost_price').val(Math.round(cost));
        }
    }
</script>

<script type="text/javascript">
    $('form').submit(function(event) {
        // event.preventDefault();
        $(this).find(':submit').attr('disabled', 'disabled');
        // $(this).submit();
    });
</script>
@endsection
