@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
?>

<form method="post" class="form-horizontal" data-toggle="validator"
    novalidate="true" id="product_ob_form_id">
    @csrf
    <div class="row">
        <div class="col-lg-8 offset-lg-3">
            <!-- Html View Load  -->
            {!! HTML::forCompanyFeild() !!}
        </div>
    </div>



    <div class="row offset-lg-1">
        <div class="col-lg-6">
            {{-- {!! HTML::forBranchFeild(true,'branch_id','branch_id',null,'','Branch') !!} --}}

            {{-- {!! HTML::forBranchFeild(true) !!} --}}
            @if(Common::getBranchId() == 1)
                <?php
                    $brachData = DB::table('gnl_branchs')
                                ->where([['is_approve', 1], ['is_delete', 0], ['is_active', 1]])
                                ->whereNotExists(function ($brachData){
                                    $brachData->select('branch_id')
                                            ->from('pos_ob_stock_m')
                                            ->whereRaw('gnl_branchs.id = pos_ob_stock_m.branch_id')
                                            ->where([['pos_ob_stock_m.is_delete', 0], ['pos_ob_stock_m.is_active', 1]]);
                                })->get();
                ?>

                <div class="form-row align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">Branch</label>
                    <div class="col-lg-5 form-group">
                        <div class="input-group">
                            <select class="form-control clsSelect2" name="branch_id"  id="branch_id" required>
                                <option value="">Select Branch</option>
                                @foreach($brachData as $data)
                                <option value="{{ $data->id }}">{{ sprintf("%04d", $data->branch_code) . " - " . $data->branch_name  }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            @else
                <input type="hidden" name="branch_id" id="branch_id" value="{{ Common::getBranchId() }}">
            @endif
        </div>

        <div class="col-lg-6">
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Opening Date</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group ghdatepicker">
                        <div class="input-group-prepend ">
                            <span class="input-group-text ">
                                <i class="icon wb-calendar round" aria-hidden="true"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control round" id="opening_date" name="opening_date"
                            placeholder="DD-MM-YYYY" required data-error="Select Date" autocomplete="off" readonly>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <table class="table w-full table-hover table-bordered table-striped ">
            <thead>
                <tr>
                    <th style="width:5%;">SL</th>
                    <th>Product</th>
                    <th>OB Quantity</th>
                    <th>Unit Cost Price</th>
                    <th>Total Amount</th>

                </tr>
            </thead>
            <tbody>
                <?php
                        $i = 0;
                        ?>
                @foreach ($ProductData as $Row)

                <tr>
                    <td scope="row" class="text-center"> {{++$i}}</td>
                    <td> <input type="hidden" name="product_arr[]" id="product_id_{{$i}}"
                            value="{{$Row->id}}">{{$Row->product_name}} {{"   (".$Row->sys_barcode.")"}}</td>

                    <td><input type="number" class="form-control round clsQuantity text-center" step="any" pattern="[0-9]"
                            name="product_qnt[]" id="product_qnt_{{$i}}" value='0'
                            onkeyup="fnCalculateTotal({{$i}});fnTotalQuantity();"></td>

                    <td>
                        <input type="number" class="form-control round text-right"
                         name="unit_cost_price[]"
                            id="unit_cost_price_{{$i}}" value='{{ $Row->cost_price }}' readonly
                            onkeyup="fnCalculateTotal({{$i}}); fnTotalAmount();">
                    </td>

                    <td><input type="number" class="form-control round clsTotal text-right" name="product_ttl[]"
                            id="product_ttl_{{$i}}" value='0' readonly></td>

                </tr>

                @endforeach


            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">
                      <h5 class="text-right">Total Quantity</h5>
                        <input type="hidden" class="text-center" name="total_product" id="total_product" value="0">
                        <input type="hidden" class="text-center" name="total_quantity" id="total_quantity" value="0" min="1">

                    </td>
                    <td id="tdTotalQuantity" class="text-center" style="font-weight: bold;"> 0</td>

                    <td class="text-right">
                        <h5>Total Amount</h5>
                        <input type="hidden" name="total_amount" id="total_amount" value="0" min="1">
                    </td>
                    <td id="tdTotalAmount" class="text-right" style="font-weight: bold;"> 0</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="row">
        <div class="col-lg-9 offset-lg-2">
            <div class="form-group d-flex justify-content-center">
                <div class="example example-buttons">
                    <a href="javascript:void(0)" onclick="goBack();"
                        class="btn btn-default btn-round d-print-none">Back</a>
                    <button type="submit" class="btn btn-primary btn-round" id="product_ob_btn_id">Save</button>
                    <!--<button type="button" class="btn btn-warning btn-round">Next</button>-->
                </div>
            </div>
        </div>
    </div>
</form>
<script>
$(document).ready(function() {

    var branchID = $('#branch_id').val();
    $.ajax({
        method: "GET",
        url: "{{url('/ajaxBranchOpendate')}}",
        dataType: "text",
        data: {
            branchID: branchID,
            moduleName: 'pos'
        },
        success: function(data) {
            if (data) {
                // $('#product_id_0').html('');
                // $('#'+firstRowFirstColId).append(data);
                // $('#'+firstRowFirstColId).trigger('change');
                // console.log (data);
                $('#opening_date').val(data);


            }
        }
    });




});

$('#branch_id').change(function() {

    var branchID = $('#branch_id').val();

    $.ajax({
        method: "GET",
        url: "{{url('/ajaxBranchOpendate')}}",
        dataType: "text",
        data: {
            branchID: branchID,
            moduleName: 'pos'
        },
        success: function(data) {
            if (data) {
                // $('#product_id_0').html('');
                // $('#'+firstRowFirstColId).append(data);
                // $('#'+firstRowFirstColId).trigger('change');
                //console.log (data);
                $('#opening_date').val(data);


            }
        }
    });


});

function fnCalculateTotal(Row) {
    var ProductQtn = $('#product_qnt_' + Row).val();
    var ProductPrice = $('#unit_cost_price_' + Row).val();
    if (Number(ProductQtn) >= 0 && Number(ProductPrice) >= 0) {
        var TotalProductPrice = (Number(ProductQtn) * Number(ProductPrice));
        $('#product_ttl_' + Row).val(TotalProductPrice);
    } else {
        $('#product_ttl_' + Row).val(0);
    }
}

function fnTotalQuantity() {

    var totalQtn = 0;
    $('.clsQuantity').each(function() {
        totalQtn = Number(totalQtn) + Number($(this).val());
    });
    $('#total_quantity').val(totalQtn);
    $('#tdTotalQuantity').html(totalQtn);
    fnTotalAmount();
}

function fnTotalAmount() {

    var totalAmt = 0;
    $('.clsTotal').each(function() {
        totalAmt = Number(totalAmt) + Number($(this).val());
    });
    $('#tdTotalAmount').html(totalAmt);
    //-------------------------- Total Amount
    $('#total_amount').val(totalAmt);
}

$("#product_ob_btn_id").click(function() {

    var b_id = $("#branch_id").val();

    if (b_id === ""){
        swal({
            icon: 'error',
            title: 'Error',
            text: 'Please select branch!!',
        });
    }

    $("#product_ob_form_id").submit();
});


$('form').submit(function(event) {
    $(this).find(':submit').attr('disabled', 'disabled');
});
</script>
@endsection
