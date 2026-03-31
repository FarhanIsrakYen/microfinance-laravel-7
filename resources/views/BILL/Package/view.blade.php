<?php
    use App\Services\HtmlService as HTML;

?>


@extends('Layouts.erp_master')
@section('content')

<div class="row d-print-none">
    <div class="col-lg-9 offset-3 mb-2">
        <!-- Html View Load  -->
        {!! HTML::forCompanyFeild($PackageData->company_id,'disabled') !!}
    </div>
</div>

<div class="table-responsive d-print-none">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th colspan="4">
                    Packages Information
                </th>
            </tr>
        </thead>
        <tbody>

            <tr>
                <td width="25%">Package Name</td>
                <td width="25%">{{$PackageData->package_name}}</td>

                <td width="30%">Package Products</td>
                <td>{{ implode(", ", $product_arr) }}</td>

            </tr>
            <tr>
                <td>Package Price</td>
                <td>{{ $PackageData->package_price }}</td>
                <td></td>
                <td></td>
            </tr>

            </tr>
        </tbody>
    </table>
</div>
<div class="row align-items-center">
    <div class="col-lg-12">
        <div class="form-group d-flex justify-content-center">
            <div class="example example-buttons">
                <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round d-print-none">Back</a>
                <a href="javascript:void(0)" onClick="window.print();" class="btn btn-default d-print-none btn-round clsPrint">Print</a>
            </div>
        </div>
    </div>
</div>
@endsection
