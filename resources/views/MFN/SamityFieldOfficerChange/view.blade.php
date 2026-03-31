@extends('Layouts.erp_master')

@section('content')
<!-- Page -->


<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th colspan="4" style="color: #000;">
                    Field Officer Change Information
                </th>
            </tr>
        </thead>
        <tbody style="color: #000;">
            <tr>
                <td width="20%">Name</td>
                <td width="20%">{{$samity->name}}</td>

                <td width="20%">Samity Code</td>
                <td width="20%">{{$samity->samityCode}}</td>
            </tr>
            <tr>
                <td width="20%">Branch Name</td>
                <td width="20%">{{$branch->branch_name}}</td>
                <td width="20%">Branch Code</td>
                <td width="20%">{{$branch->branch_code}}</td>
            </tr>
            <tr>
            <tr>
                <td width="20%">Working Area</td>
                <td width="20%">{{$workingArea->name}}</td>

                <td width="20%">Union/ Word</td>
                <td width="20%">{{$union->union_name}}</td>
            </tr>
            <tr>
                <td width="20%">Thana</td>
                <td width="20%">{{$upazila->upazila_name}}</td>

                <td width="20%">Village</td>
                <td width="20%"> {{$village->village_name}}</td>
            </tr>
            <tr>
                <td width="20%">District</td>
                <td width="20%">{{$district->district_name}}</td>
                <td width="20%">Division</td>
                <td width="20%">{{$division->division_name}}</td>
            </tr>
            <tr>
                <td width="20%">New Field Officer</td>
                <td width="20%">{{$newFieldOfficerName}}</td>
                <td width="20%">Previos Field Officer</td>
                <td width="20%">{{$previousFieldOfficerName}}</td>
            </tr>

            <tr>
                <td width="20%">Effective From</td>
                <td width="20%">{{ \Carbon\Carbon::parse($samityFOC->effectiveDate)->format('d-m-Y') }}</td>
                <td width="20%"></td>
                <td width="20%"></td>
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