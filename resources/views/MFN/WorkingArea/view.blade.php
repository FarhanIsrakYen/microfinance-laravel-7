@extends('Layouts.erp_master')

@section('content')

<style>
    table tbody tr td{
        width: 25%;
    }
</style>
<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th colspan="4">
                    Working Area Information
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Area Name</td>
                <td>{{ $warea->name }}</td>
                <td>Branch Name</td>
                <td>{{$branch->branch_name}}</td>
            </tr>
            <tr>
                <td>Village</td>
                <td> {{$village->village_name}}</td>
                <td>Branch Code</td>
                <td>{{$branch->branch_code}}</td>
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