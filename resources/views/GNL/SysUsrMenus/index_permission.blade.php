@extends('Layouts.erp_master')
@section('content')

<!-- <div class="page min-height">
    <div class="page-header">
        <h4 class="">User Menu Permission</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0)">User Management</a></li>
            <li class="breadcrumb-item"><a href="{{ url('gnl/sys_menu') }}">System User Menus</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0)">System User Menu Permission</a></li>
            <li class="breadcrumb-item active">List</li>
        </ol>
        <div class="page-header-actions">
            <a class="btn btn-sm btn-primary btn-outline btn-round" href="{{ url('gnl/sys_permission/'.$mid.'/add') }}">
                <i class="icon wb-link" aria-hidden="true"></i>
                <span class="hidden-sm-down">New Entry</span>
            </a>
        </div>
    </div>

    <div class="page-content">
        <div class="panel">
            <div class="panel-body"> -->

<div class="row">
    <div class="col-lg-12 text-right">
        <a class="btn btn-sm btn-primary btn-outline btn-round" href="{{ url('gnl/sys_permission/'.$mid.'/add') }}">
            <i class="icon wb-link" aria-hidden="true"></i>
            <span class="hidden-sm-down">New Entry</span>
        </a>
    </div>
</div>
<br>

<div class="row">
    <div class="col-lg-12">
        <table class="table w-full table-hover table-bordered table-striped clsDataTable" data-plugin="dataTable">
            <thead>
                <tr>
                    <th width="5%">SL#</th>
                    <th>Permission Name</th>
                    <th>Route Link</th>
                    <th>Page Title</th>
                    <!--<th>Method Name</th>-->
                    <th>Parent Menu</th>
                    <th>Set Status</th>
                    <th>Order By</th>
                    <th style="width:15%;">Action</th>
                </tr>
            </thead>
            <tbody>
                @php $i = 0; @endphp
                @foreach($upermission as $permission)
                <tr>
                    <td>{{ ++$i }}</td>
                    <td>{{ $permission->name }}</td>
                    <td>{{ $permission->route_link }}</td>
                    <td>{{ $permission->page_title }}</td>
                    <td>{{ $permission->SysMenu['menu_name'] }}</td>
                    <td class="text-center">{{ $permission->set_status }}</td>
                    <td class="text-center">{{ $permission->order_by }}</td>
                    <td class="text-center">
                        <a href="{{ url('gnl/sys_permission/'.$mid.'/edit/'.$permission->id) }}" title="Edit">
                            <i class="icon wb-edit mr-2 blue-grey-600"></i>
                        </a>

                        @if($permission->is_active == 1)
                        <a href="{{ url('gnl/sys_permission/'.$mid.'/publish/'.$permission->id) }}" title="Unpublish"
                            class="btnUnpublish">
                            <i class="icon fa-check-square-o mr-2 blue-grey-600"></i>
                        </a>
                        @else
                        <a href="{{ url('gnl/sys_permission/'.$mid.'/publish/'.$permission->id) }}" title="Publish"
                            class="btnPublish">
                            <i class="icon fa-square-o mr-2 blue-grey-600"></i>
                        </a>
                        @endif

                        <a href="{{ url('gnl/sys_permission/'.$mid.'/delete/'.$permission->id) }}" title="Delete"
                            class="btnDelete">
                            <i class="icon wb-trash mr-2 blue-grey-600"></i>
                        </a>

                        <a href="{{ url('gnl/sys_permission/'.$mid.'/destroy/'.$permission->id)}}"
                            title="Parmanent Delete" class="btnDelete">
                            <i class="icon wb-scissor mr-2 blue-grey-600"></i>
                        </a>

                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection