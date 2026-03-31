@extends('Layouts.erp_master')

@section('content')
<!-- Page -->

<style>
    table tbody tr td {
        width: 20%;
    }
</style>

<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th colspan="4" style="color: #000;">
                    Savings Product Interest
                </th>
            </tr>
        </thead>
        <tbody style="color: #000;">
            <tr>
                <td>Mature Period:</td>
                <td>{{ $interest->durationMonth }} Months</td>
                <td>Status:</td>
                <td>
                    @if ($interest->status == 1)
                    <span class="badge badge-primary" style="font-size: 12px;">Active</span>
                    @else
                    <span class="badge badge-danger" style="font-size: 12px;">Inactive</span>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <br>
    {{-- Partials --}}
    <div class="panel-heading p-1 mb-4">Partials</div>
    <table class="table table-striped table-bordered" style="text-align: center;">
        <thead>
            <tr>
                <th>Duration (Month)</th>
                <th>Interest Rate</th>
                <th>Effective From</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($partials as $partial)
            <tr>
                <td>{{ $partial->durationMonth }}</td>
                <td>{{ $partial->interestRate }}</td>
                <td>{{ \Carbon\Carbon::parse($partial->effectiveDate)->format('d-m-Y') }}</td>
                <td style="width: 10%;">
                    @if ($partial->status == 1)
                    <span class="badge badge-primary" style="font-size: 12px;">Active</span>
                    @else
                    <span class="badge badge-danger" style="font-size: 12px;">Inactive</span>
                    @endif
                </td>
            </tr>
            @endforeach            
        </tbody>
    </table>

    <div class="row align-items-center">
        <div class="col-lg-12">
            <div class="form-group d-flex justify-content-center">
                <div class="example example-buttons">
                    <a href="javascript:void(0)" onclick="goBack();"
                        class="btn btn-default btn-round d-print-none">Back</a>
                    <a href="javascript:void(0)" onClick="window.print();"
                        class="btn btn-default btn-round clsPrint">Print</a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
