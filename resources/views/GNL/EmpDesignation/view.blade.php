@extends('Layouts.erp_master')

@section('content')


<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th colspan="4">
                    Loan Product Category Information
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="25%">Name</td>
                <td width="25%">{{ $loanPCategory->name }}</td>
                <td width="25%">Short Name</td>
                <td width="25%">{{$loanPCategory->shortName}}</td>
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