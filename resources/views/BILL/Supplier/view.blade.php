@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\HtmlService as HTML;
?>
<!-- Page -->
<div class="row">
    <div class="col-lg-9 offset-3 mb-2">
        <!-- Html View Load  -->
        {!! HTML::forCompanyFeild($SupplierData->company_id,'disabled') !!}
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th colspan="4">
                    Supplier Information

                </th>
            </tr>
        </thead>
        <tbody style="color: #000;">
            <tr>
                <td width="20%">SUPPLIER NAME</td>
                <td width="20%">{{$SupplierData->sup_name}}</td>

                <td width="20%">SUPPLIER TYPE</td>
                <td width="20%">@if($SupplierData->supplier_type==1) PURCHASE @else
                    COMMISSION
                    @endif</td>
            </tr>
            <tr>
                <td width="20%">SUPPLIER'S COMPANY </td>
                <td width="20%">{{$SupplierData->comission_percent}}
                </td>

                <td width="20%">EMAIL </td>
                <td width="20%"> {{$SupplierData->sup_email}}</td>
            </tr>
            <tr>
            <tr>
                <td width="20%"> MOBILE</td>
                <td width="20%">{{$SupplierData->sup_phone}}</td>

                <td width="20%">ADDRESS</td>
                <td width="20%">{{$SupplierData->sup_addr}}</td>
            </tr>
            <tr>
                <td width="20%">WEBSITE</td>
                <td width="20%">{{$SupplierData->sup_web_add}}</td>
                <td width="20%">DESCRIPTION </td>
                <td width="20%">{{$SupplierData->sup_desc}}</td>
            </tr>
            <tr>
                <td width="20%">REFERENCE NO</td>
                <td width="20%">{{$SupplierData->sup_ref_no}}</td>
                <td width="20%">ATTENTIONS</td>
                <td width="20%">{{$SupplierData->sup_attentionA}}</td>
            </tr>
        </tbody>
    </table>

    <div class="row align-items-center">
        <div class="col-lg-12">
            <div class="form-group d-flex justify-content-center">
                <div class="example example-buttons">
                    <a href="javascript:void(0)" onclick="goBack();"
                        class="btn btn-default btn-round d-print-none">Back</a>
                    <a href="javascript:void(0)" onClick="window.print();" class="btn btn-default btn-round clsPrint">Print</a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Page -->

@endsection
