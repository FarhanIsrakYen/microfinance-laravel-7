@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\HtmlService as HTML;
?>

<!-- Page -->
    <div class="row">
        <div class="col-lg-9 offset-3 mb-2">
            <!-- Html View Load  -->
            {!! HTML::forCompanyFeild($BranchData->company_id,'disabled') !!}
        </div>
    </div>
    <!-- <div>
    <p class="text-center">
        <span style="color:black;"><b> Branch Information</b></span></p>
    </div> -->
    {{-- <div class="table-responsive">
        
    </div> --}}
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th colspan="4"  style="color: #000;">
                    Branch Information

                </th>
            </tr>
        </thead>
        <tbody  style="color: #000;">
            <tr>
                <td width="20%">GROUP</td>
                <td width="20%">{{$BranchData->group['group_name']}}</td>

                <td width="20%">PROJECT</td>
                <td width="20%">{{$BranchData->project['project_name']}}
                </td>
            </tr>
            <tr>
                <td width="20%">PROJECT TYPE</td>
                <td width="20%">{{ $BranchData->projectType['project_type_name'] }}</td>

                <td width="20%">BRANCH NAME </td>
                <td width="20%">
                    {{ $BranchData->branch_name }}
                </td>
            </tr>
            <tr>
            <tr>
                <td width="20%"> BRANCH CODE</td>
                <td width="20%">{{$BranchData->branch_code}}</td>

                <td width="20%">CONTACT PERSON</td>
                <td width="20%"> {{$BranchData->contact_person}}</td>
            </tr>
            <tr>

                <td width="20%">EMAIL</td>
                <td width="20%">{{$BranchData->branch_email}}</td>

                <td width="20%">MOBILE </td>
                <td width="20%">{{$BranchData->branch_phone}}</td>

            </tr>
            <tr>
                <td width="20%">BRANCH ADDRESS</td>
                <td width="20%">{{$BranchData->branch_addr}}</td>
                <td width="20%">BRANCH OPENING DATE</td>
                <td width="20%">{{date('m-d-y', strtotime($BranchData->branch_opening_date))}}</td>
            </tr>
            <tr>
                <td width="20%">SOFTWARE OPENING DATE</td>
                <td width="20%">{{date('m-d-y', strtotime($BranchData->soft_start_date))}}</td>
                <td width="20%">&nbsp</td>
                <td width="20%">&nbsp</td>
            </tr>


        </tbody>
    </table>
    <div class="row align-items-center">
        <div class="col-lg-12">
            <div class="form-group d-flex justify-content-center">
                <div class="example example-buttons">
                    <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round d-print-none">Back</a>
                    <a href="javascript:void(0)" onclick="window.print();" class="btn btn-default btn-round clsPrint d-print-none">Print</a>
                </div>
            </div>
        </div>
    </div>
<!-- End Page -->

@endsection
