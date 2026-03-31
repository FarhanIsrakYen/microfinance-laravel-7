@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\HtmlService as HTML;
?>

<!-- Page -->
<div class="row">
    <div class="col-lg-9 offset-3 mb-2">
        <!-- Html View Load  -->
        {!! HTML::forCompanyFeild($ProductData->company_id,'disabled') !!}
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th colspan="4">
                    Product Information
                </th>
            </tr>
        </thead>
        <tbody style="color: #000;">
            <tr>
                <td width="20%">SUPPLIER NAME</td>
                <td width="20%">{{$ProductData->supplier->sup_name}}</td>

                <td width="20%">CATEGORY</td>
                <td width="20%">{{$ProductData->category->cat_name}}</td>

            <tr>


            <tr>
                <td width="20%">PRODUCT</td>
                <td width="20%">{{$ProductData->product_name}}</td>

                <td width="20%">SALE PRICE</td>
                <td width="20%" class="text-right">{{$ProductData->sale_price}}</td>
              </tr>
              <tr>
                <td width="20%">OPENING STOCK</td>
                <td width="20%">{{$ProductData->o_stock}}</td>

                <td width="20%">OPENING STOCK AMOUNT</td>
                <td width="20%">{{$ProductData->o_stock_amount}}</td>
              </tr>
              <tr>
                <td width="20%">MINIMUM STOCK </td>
                <td width="20%">{{$ProductData->min_stock}}</td>

                <td width="20%">VAT</td>
                <td width="20%">{{$ProductData->prod_vat}}</td>
              </tr>
              <tr>
                <td width="20%">UPLOAD IMAGE </td>
                <td width="20%">
                    @if(!empty($ProductData->prod_image))

                    @if(file_exists($ProductData->prod_image))
                    <img src="{{ asset($ProductData->prod_image) }}" style="height: 32PX; width: 32PX;">
                    @endif
                    @else
                    <img src="{{ asset('assets/images/dummy.png') }}" style="height: 32PX; width: 32PX;">
                    @endif

                  </td>
                <td width="20%">DESCRIPTION</td>
                <td width="20%">{{$ProductData->prod_desc}}</td>

            </tr>
        </tbody>
    </table>
</div>


<div class="row align-items-center">
    <div class="col-lg-12">
        <div class="form-group d-flex justify-content-center">
            <div class="example example-buttons">
                <a href="javascript:void(0)" onclick="goBack();"
                    class="btn btn-default btn-round d-print-none">Back</a>
                <a href="javascript:void(0)" onClick="window.print();" class="btn btn-default d-print-none btn-round clsPrint">Print</a>
            </div>
        </div>
    </div>
</div>
<!-- End Page -->


@endsection
