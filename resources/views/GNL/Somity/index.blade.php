@extends('Layouts.erp_master')

@section('content')
<!-- Page -->
<!-- <div class="page">
    <div class="page-header">
        <h4 class="">Union List</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0)">Address</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0)">Union/Zone</a></li>
            <li class="breadcrumb-item active">List</li>
        </ol>
        <div class="page-header-actions">
            <a class="btn btn-sm btn-primary btn-outline btn-round" href="{{ url('gnl/union/new') }}">
                <i class="icon wb-link" aria-hidden="true"></i>
                <span class="hidden-sm-down">New Entry</span>
            </a>
        </div>
    </div>

    <div class="page-content">
        <div class="panel">
            <div class="panel-body"> -->
                <div class="row">
                    <div class="col-lg-12">
                        <table class="table w-full table-hover table-bordered table-striped dataTable" data-plugin="dataTable">
                            <thead>
                                <tr>
                                    <th style="width:5%;">SL#</th>
                                    <th>Division </th>
                                    <th>District </th>
                                    <th>Upazila </th>
                                    <th>Union </th>
                                    <th style="width:15%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>Ambala Group</td>
                                    <td>info@ambalagroup.org</td>
                                    <td>info@ambalagroup.org</td>
                                    <td>171</td>
                                    <td>
                                        <a href="{{URL::to('gnl/group/edit/')}}"><i class="icon wb-edit mr-2 blue-grey-600"></i></a>
                                        <a href="{{URL::to('gnl/group/delete/')}}"><i class="icon wb-trash blue-grey-600"></i></a>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Page -->

@endsection
