@extends('Layouts.erp_master')
@section('content')
<?php 
use App\Services\RoleService as Role;
?>
    <div class="row">
        <div class="col-lg-12">
            <table class="table w-full table-hover table-bordered table-striped clsDataTable" data-plugin="dataTable">
                <thead>
                    <tr>
                        <th width="4%">SL</th>
                        <th width="5%">Image</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Role Name</th>
                        <th>Contact No</th>
                        <th>Company</th>
                        <th>Branch</th>
                        <th style="width:15%;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 0; ?>
                    @foreach($user as $row)
                    <tr>
                        <td>{{ ++$i }}</td>
                        <td>
                            @if(!empty($row->user_image))
                            @if(file_exists($row->user_image))
                            <img src="{{ asset($row->user_image) }}" style="height: 32PX; width: 32PX;">
                            @endif
                            @else
                            <img src="{{ asset('assets/images/dummy.png') }}" style="height: 32PX; width: 32PX;">
                            @endif
                        </td>
                        <td>{{ $row->full_name }}</td>
                        <td>{{ $row->username }}</td>
                        <td>{{ $row->role_name }}</td>
                        <td>{{ $row->contact_no }}</td>
                        <td>{{ $row->comp_name }}</td>
                        <td>{{ $row->branch_name }}</td>
                        <td class="text-center">
                            <!-- Action Calling Role Wise -->
                            {!! Role::roleWisePermission($GlobalRole, $row->id, [], $row->is_active) !!}
                        </td>
                        <!-- <td class="text-center">
                            <a href="{{ url('gnl/sys_user/edit/'.$row->id) }}" title="Edit">
                                <i class="icon wb-edit mr-2 blue-grey-600"></i>
                            </a>

                            @if($row->is_active == 1)
                            <a href="{{ url('gnl/sys_user/publish/'.$row->id) }}" title="Unpublish" class ="btnUnpublish">
                                <i class="icon fa-check-square-o mr-2 blue-grey-600"></i>
                            </a>
                            @else
                            <a href="{{ url('gnl/sys_user/publish/'.$row->id) }}" title="Publish" class ="btnPublish">
                                <i class="icon fa-square-o mr-2 blue-grey-600"></i>
                            </a>
                            @endif

                            <a href="{{ url('gnl/sys_user/view/'.$row->id) }}" title="View">
                                <i class="icon wb-eye mr-2 blue-grey-600"></i>
                            </a>

                            <a href="{{ url('gnl/sys_user/change_pass/'.$row->id) }}" title="Change Password">
                                <i class="icon fa-exchange mr-2 blue-grey-600"></i>
                            </a>

                            <a href="{{ url('gnl/sys_user/delete/'.$row->id) }}" title="Delete" class="btnDelete">
                                <i class="icon wb-trash mr-2 blue-grey-600"></i>
                            </a>

                            <a href="{{ url('gnl/sys_user/destroy/'.$row->id)}}" title="Parmanent Delete" class="btnDelete">
                                <i class="icon wb-scissor mr-2 blue-grey-600"></i>
                            </a>
                        </td> -->
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection