@extends('Layouts.erp_master')
@section('content')
<?php 
use App\Services\HtmlService as HTML;
?>
<!-- Page -->
<div class="row">
    <div class="col-lg-9 offset-3 mb-2">
        <!-- Html View Load  -->
        {!! HTML::forCompanyFeild($EmployeeData->company_id,'disabled') !!}
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th colspan="4" >
                    Employee Information
                </th>
            </tr>
        </thead>
        <tbody style="color: #000;">
            <tr>
                <td width="20%">BRANCH NAME</td>
                <td width="20%">{{$EmployeeData->branch['branch_name']}}</td>

                <td width="20%">EMPLOYEE NAME</td>
                <td width="20%">{{$EmployeeData->emp_name}}</td>
            </tr>
            <tr>
                <td width="20%">EMPLOYEE CODE</td>
                <td width="20%">{{sprintf('%04d',$EmployeeData->emp_code)}}</td>

                <td width="20%">FATHER'S NAME </td>
                <td width="20%"> {{$EmployeeData->emp_father_name}}</td>
            </tr>
            <tr>
            <tr>
                <td width="20%"> MOTHER'S NAME</td>
                <td width="20%">{{$EmployeeData->emp_mother_name}}</td>

                <td width="20%">DATE OF BIRTH</td>
                <td width="20%">{{date('m-d-y', strtotime($EmployeeData->emp_dob))}}</td>
            </tr>
            <tr>

                <td width="20%">EMAIL</td>
                <td width="20%">{{$EmployeeData->emp_email}}</td>

                <td width="20%">MOBILE </td>
                <td width="20%">{{$EmployeeData->emp_phone}}</td>

            </tr>
            <tr>
                <td width="20%">NATIONAL ID</td>
                <td width="20%">{{$EmployeeData->emp_national_id}}</td>
                <td width="20%">GENDER</td>
                <td width="20%">{{$EmployeeData->emp_gender}}</td>
            </tr>
            <tr>
                <td width="20%">DESIGNATION</td>
                <td width="20%">{{$EmployeeData->designation['name']}}</td>
                <td width="20%">PRESENT ADDRESS</td>
                <td width="20%">{{$EmployeeData->emp_present_addr}}</td>
            </tr>
            <tr>
                <td width="20%">DEPARTMENT</td>
                <td width="20%">{{$EmployeeData->department['dept_name']}}</td>
                <td width="20%">PARMANENT ADDRESS</td>
                <td width="20%">{{$EmployeeData->emp_parmanent_addr}}</td>
            </tr>
            <tr>
                <td width="20%">DESCRIPTION</td>
                <td width="20%">{{$EmployeeData->emp_description}}</td>
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
                <a href="javascript:void(0)" onClick="window.print();"
                    class="btn btn-default btn-round clsPrint d-print-none">Print</a>
            </div>
        </div>
    </div>
</div>
<!-- End Page -->

@endsection
