@extends('Layouts.erp_master')
@section('content')

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
                <td width="20%"><b>Employee Name</b></td>
                <td width="20%">{{ $employeeName }}</td>

                <td width="20%"><b>Employee Code</b></td>
                <td width="20%">{{ $empCode }}</td>
            </tr>
            <tr>
                <td width="20%"><b>Branch From</b></td>
                <td width="20%">{{ $branchFrom }}</td>

                <td width="20%"><b>Branch To</b></td>
                <td width="20%"> {{ $branchTo }}</td>
            </tr>
            <tr>
                <td width="20%"><b>Transfer Date</b></td>
                <td width="20%">{{ $transferDate }}</td>

                <td width="20%"><b>Is Approved?</b></td>
                <td width="20%"> {{ $isApproved }}</td>
            </tr>
            <tr>
                <td width="20%"><b>Approved By</b></td>
                <td width="20%">{{ $approvedBy }}</td>

                <td width="20%"><b>Entry By</b></td>
                <td width="20%"> {{ $createdBy }}</td>
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
