@extends('Layouts.erp_master')

@section('content')
<!-- Page -->
<div class="table-responsive" style="overflow-x: hidden">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th colspan="4" style="color: #000;">
                    Member Samity Transfer Information
                </th>
            </tr>
        </thead>
        <tbody style="color: #000;">
            <tr>
                <td width="20%">Member Name</td>
                <td width="20%">{{$member->name}}</td>

                <td width="20%">Transfer Date </td>
                <td width="20%">{{ \Carbon\Carbon::parse($memberSamityTransfer->date)->format('d-m-Y') }}</td>
            </tr>
            <tr>
                <td width="20%">New Samity</td>
                <td width="20%">{{$newSamityName}}</td>
                <td width="20%">Old Samity</td>
                <td width="20%">{{$oldSamityName}}</td>
            </tr>
            <tr>
                <td width="20%">New Member Code</td>
                <td width="20%">{{$memberSamityTransfer->newMemberCode}}</td>
                <td width="20%">Old Member Code</td>
                <td width="20%">{{$memberSamityTransfer->oldMemberCode}}</td>
            </tr>
            <tr>
                <td width="20%">New MRA Code</td>
                <td width="20%">{{$memberSamityTransfer->newMraCode}}</td>
                <td width="20%">Old MRA Code</td>
                <td width="20%">{{$memberSamityTransfer->oldMraCode}}</td>
            </tr>
            <tr>
                
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
