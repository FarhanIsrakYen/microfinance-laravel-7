@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\HtmlService as HTML;
?>

<div class="row">
    <div class="col-lg-8 offset-lg-3">
        <!-- Html View Load  -->
        {!! HTML::forCompanyFeild($POBDataM->company_id, 'disabled') !!}
    </div>
</div>

<div class="row">
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead>
                <th colspan="4">
                    Opening Balance Stock Information
                </th>
            </thead>
            <tbody style="color: #000;">
                <tr>
                    <th width="20%">Branch</th>
                    <td width="20%">{{$POBDataM->branch['branch_name']}}</td>

                    <th width="20%">Opening Date </th>
                    <td width="20%">{{date('d-m-Y', strtotime($POBDataM->opening_date))}}</td>
                </tr>
            </tbody>

        </table>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th width="20%">Product Name</th>
                    <th width="20%">OB Quantity</th>
                    <th width="20%">Unit Cost Price</th>
                    <th width="20%">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($POBDataD as $data)
                <tr>
                    <td> {{$data->product['product_name']}}{{"   (".$data->product['sys_barcode'].")"}} </td>
                    <td class="text-center"><input type="hidden" class="clsQuantity"
                            value="{{ $data->product_quantity}}">{{ $data->product_quantity}}</td>
                    <td class="text-right">{{ $data->unit_cost_price}}</td>
                    <td class="text-right"><input type="hidden" class="clsTotal"
                            value="{{ $data->total_cost_amount}}">{{ $data->total_cost_amount}}</td>

                </tr>

                @endforeach

                <tr>
                    <th width="20%" class="text-right">Total Quantity</th>
                    <td width="20%" class="text-center">{{ $POBDataM->total_quantity}}</td>

                    <th width="20%" class="text-right">Total Amount </th>
                    <td width="20%" class="text-right">{{ $POBDataM->total_amount}}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="row">
    <div class="col-lg-9 offset-lg-2">
        <div class="form-group d-flex justify-content-center">
            <div class="example example-buttons">
                <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                <a href="javascript:void(0)" onClick="window.print();"
                    class="btn btn-default btn-round clsPrint d-print-none">Print</a>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    fnTotalQuantity();
});

function fnCalculateTotal(Row) {
    var ProductQtn = $('#product_qnt_' + Row).val();
    var ProductPrice = $('#unit_cost_price_' + Row).val();
    if (Number(ProductQtn) > 0 && Number(ProductPrice) > 0) {
        var TotalProductPrice = (Number(ProductQtn) * Number(ProductPrice));
        $('#product_ttl_' + Row).val(TotalProductPrice);
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
</script>
@endsection
