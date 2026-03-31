@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\HtmlService as HTML;
?>

<div class="row">
    <div class="col-lg-9 offset-3 mb-2">
        <!-- Html View Load  -->
        {!! HTML::forCompanyFeild($CustomerData->company_id,'disabled') !!}
    </div>
</div>
<div>
    <p class="text-center">
        <span style="color:black;">
            <b>Customer Information</b>
        </span>
    </p>
</div>

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th colspan="4">
                    Basic Information

                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="20%">BRANCH NAME</td>
                <td width="20%">{{ $CustomerData->branch['branch_name'] }}</td>

                <td width="20%">COMPANY NAME</td>
                <td width="20%">{{ $CustomerData->company_name }}</td>
            </tr>
            <tr>
                <td width="20%">BILLING NAME</td>
                <td width="20%">{{ $CustomerData->billing_name }}</td>

                <td width="20%">CUSTOMER NAME</td>
                <td width="20%">{{ $CustomerData->customer_name }}</td>
            </tr>
            <tr>
                <td width="20%">CUSTOMER CODE</td>
                <td width="20%">{{ $CustomerData->customer_code }}</td>

                <td width="20%">MOBILE </td>
                <td width="20%">
                    {{ $CustomerData->customer_mobile }}
                </td>
            </tr>
            <tr>
            <tr>

                <td width="20%">NATIONAL ID</td>
                <td width="20%">{{ $CustomerData->customer_nid }}</td>

                <td width="20%">GENDER</td>
                <td width="20%">{{ $CustomerData->cus_gender }}</td>

            </tr>
            <tr>
                <td width="20%">PRESENT ADDRESS</td>
                <td width="20%">{{ $CustomerData->present_addr }}</td>

                <td width="20%">PERMANENT ADDRESS</td>
                <td width="20%">
                    {{ $CustomerData->permanent_addr }}
                </td>
            </tr>
            <tr>
                <td width="20%">MARITAL STATUS</td>
                <td width="20%">{{ $CustomerData->marital_status }}</td>
                <td width="20%">EMAIL</td>
                <td width="20%">{{ $CustomerData->customer_email }}</td>
            </tr>
            <tr>
                <td width="20%">DATE OF BIRTH</td>
                <td width="20%"> {{ $CustomerData->customer_dob }}</td>
                <td width="20%">CUSTOMER PICTURE</td>
                <td width="20%">
                    @if(!empty($CustomerData->customer_image))

                    @if(file_exists($CustomerData->customer_image))
                    <img src="{{ asset($CustomerData->customer_image) }}" style="height: 32PX; width: 32PX;">
                    @endif
                    @else
                    <img src="{{ asset('assets/images/dummy.png') }}" style="height: 32PX; width: 32PX;">
                    @endif
                </td>
            </tr>
            <tr>
                <td width="20%">DESCRIPTION</td>
                <td width="20%">{{ $CustomerData->customer_desc }}</td>
            </tr>

        </tbody>
    </table>
    <div class="row align-items-center">
        <div class="col-lg-12">
            <div class="form-group d-flex justify-content-center">
                <div class="example example-buttons">
                    <a href="javascript:void(0)" onclick="goBack();"
                        class="btn btn-default btn-round d-print-none">Back</a>
                        <a href="javascript:void(0)" onClick="window.print();"
                        class="btn btn-default btn-round clsPrint d-print-none">Print</a>
                </div>
            </div>
        </div>
    </div>

@endsection
