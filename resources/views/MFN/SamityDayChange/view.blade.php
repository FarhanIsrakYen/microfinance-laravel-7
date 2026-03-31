@extends('Layouts.erp_master')

@section('content')


<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th colspan="4">
                    Samity Day Change Information
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="25%">Branch</td>
                <td width="25%">{{ $branch->branch_name }}</td>
                <td width="25%">Branch Code</td>
                <td width="25%">{{ $branch->branch_code }}</td>
            </tr>
            <tr>
                <td width="25%">Samity</td>
                <td width="25%">{{ $samity }}</td>
                <td width="25%">Effective Date</td>
                <td width="25%">{{ $effectiveDate }}</td>
            </tr>
            <tr>
                <td width="25%">Old Day</td>
                <td width="25%">{{ $samityDayChange->oldSamityDay }}</td>
                <td width="25%">New Day</td>
                <td width="25%">{{ $samityDayChange->newSamityDay }}</td>
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
            </div>
        </div>
    </div>
</div>

@endsection