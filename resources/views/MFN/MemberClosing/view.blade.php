@extends('Layouts.erp_master')

@section('content')
<!-- Page -->
<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th colspan="4" style="color: #000;">
                    Member Closing Information
                </th>
            </tr>
        </thead>
        <tbody style="color: #000;">
            <tr>
                <td width="20%">Member Name</td>
                <td width="20%">{{$member->name}}</td>

                <td width="20%">Member Code</td>
                <td width="20%">{{$member->memberCode}}</td>
            </tr>
            <tr>
                <td width="20%">Samity</td>
                <td width="20%">{{$samity}}</td>

                <td width="20%">Branch</td>
                <td width="20%">{{$branch}}</td>
            </tr>
            <tr>
                <td width="20%">Member Closing Date</td>
                <td width="20%">{{ \Carbon\Carbon::parse($memberClosing->closingDate)->format('d-m-Y') }}</td>

                <td width="20%">Closing Balance</td>
                <td width="20%">{{$memberClosing->closingBalance}}</td>
            </tr>
        </tbody>
    </table>
    <div class="row align-items-center">
        <div class="col-lg-12">
            <div class="form-group d-flex justify-content-center">
                <div class="example example-buttons">
                    <a href="javascript:void(0)" onclick="goBack();"
                        class="btn btn-default btn-round d-print-none">Back</a>
                    <!-- <a href="javascript:void(0)" onClick="window.print();" class="btn btn-default btn-round clsPrint">Print</a> -->
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Page -->
@endsection
