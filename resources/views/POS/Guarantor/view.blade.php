@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\HtmlService as HTML;
?>
<div class="row">
    <div class="col-lg-9 offset-3 mb-2">
        <!-- Html View Load  -->
        {!! HTML::forCompanyFeild($GuarantorData->company_id,'disabled') !!}
    </div>
</div>
<div>
    <p class="text-center">
        <span style="color:black;"><b> Guarantor Information
            </b></span></p>
</div>
<div class="table-responsive">
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
                <td width="20%">GUARANTOR NAME</td>
                <td width="20%">{{ $GuarantorData->gr_name }}</td>
                <td width="20%">CUSTOMER NAME</td>
                <td width="20%">{{ $GuarantorData->customer['customer_name'] }}</td>
            </tr>
            <tr>
                <td width="20%">FATHER'S NAME</td>
                <td width="20%">{{ $GuarantorData->gr_father_name }}</td>
                <td width="20%">MOTHER'S NAME</td>
                <td width="20%">{{ $GuarantorData->gr_mother_name}}</td>
            </tr>

            <tr>
                <td width="20%">MOBILE</td>
                <td width="20%">
                    {{ $GuarantorData->gr_mobile }}
                </td>
                <td width="20%">NATIONAL ID</td>
                <td width="20%">{{ $GuarantorData->gr_nid }}</td>

            </tr>
            <tr>
                <td width="20%">EMAIL</td>
                <td width="20%">
                    {{ $GuarantorData->gr_email }}
                </td>
                <td width="20%">DATE OF BIRTH</td>
                <td width="20%">{{ $GuarantorData->gr_dob }}</td>

            </tr>
            <tr>
                <td width="20%">MARITAL STATUS</td>
                <td width="20%">{{ $GuarantorData->gr_marital_status }}</td>
                <td width="20%">YEARLY INCOME</td>
                <td width="20%">{{ $GuarantorData->gr_yearly_income }}</td>

            </tr>
            <tr>
                <td width="20%">RELATION</td>
                <td width="20%">{{ $GuarantorData->gr_relation_with }}</td>
                <td width="20%">DESCRIPTION</td>
                <td width="20%">{{ $GuarantorData->gr_desc }}</td>
            </tr>
        </tbody>
    </table>

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th colspan="4">
                    Present Address
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="20%">DIVISION</td>
                <td width="20%">{{ $GuarantorData->predivision['division_name'] }}</td>

                <td width="20%">DISTRICT</td>
                <td width="20%">
                    {{ $GuarantorData->predistrict['district_name'] }}
                </td>
            </tr>
            <tr>
                <td width="20%">UPAZILA</td>
                <td width="20%">{{ $GuarantorData->preupazila['upazila_name']  }}</td>
                <td width="20%">UNION</td>
                <td width="20%">{{ $GuarantorData->preunion['union_name'] }}</td>
            </tr>
            <tr>
                <td width="20%">VILLAGE</td>
                <td width="20%">{{ $GuarantorData->previllage['village_name']}}</td>
                <td width="20%">REMARKS</td>
                <td width="20%">{{ $GuarantorData->gr_pre_remarks}}</td>
            </tr>
        </tbody>
    </table>
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th colspan="4">
                    Parmanent Address
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="20%">DIVISION</td>
                <td width="20%">{{ $GuarantorData->pardivision['division_name']}}</td>

                <td width="20%">DISTRICT</td>
                <td width="20%">
                    {{ $GuarantorData->pardistrict['district_name'] }}
                </td>
            </tr>
            <tr>
                <td width="20%">UPAZILA</td>
                <td width="20%">{{ $GuarantorData->parupazila['upazila_name'] }}</td>
                <td width="20%">UNION</td>
                <td width="20%">{{ $GuarantorData->parunion['union_name'] }}</td>
            </tr>
            <tr>
                <td width="20%">VILLAGE</td>
                <td width="20%">{{ $GuarantorData->parvillage['village_name'] }}</td>
                <td width="20%">REMARKS</td>
                <td width="20%">{{ $GuarantorData->gr_par_remarks}}</td>
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
</div>

@endsection