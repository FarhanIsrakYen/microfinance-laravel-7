@extends('Layouts.erp_master')
@section('content')
<?php 
use App\Services\RoleService as Role;
?>

<div class="row">
    <div class="col-lg-12">
        <table class="table w-full table-hover table-bordered table-striped dataTable" data-plugin="dataTable">
            <thead>
                <tr>
                    <th width="3%" class="text-center">SL</th>
                    <th>Role Name</th>
                    <th>Order By</th>
                    <th style="width:15%;">Action</th>
                </tr>
            </thead>
            <tbody>
                @php $i = 0 @endphp
                @foreach($user_role as $urole)
                    <tr>
                        <td class="text-center">{{ ++$i }}</td>
                        <td>{{ $urole->role_name }}</td>
                        <td>{{ $urole->order_by }}</td>
                        <td class="text-center">
                            <!-- Action Calling Role Wise -->
                            {!! Role::roleWisePermission($GlobalRole, $urole->id, [], $urole->is_active) !!}
                        </td>
                        <!-- <td>
                            <a href="{{ url('gnl/sys_role/edit/'.$urole->id) }}" title="Edit">
                                <i class="icon wb-edit mr-2 blue-grey-600"></i>
                            </a>

                            @if($urole->is_active == 1)
                                <a href="{{ url('gnl/sys_role/publish/'.$urole->id) }}" title="Unpublish" class ="btnUnpublish">
                                    <i class="icon fa-check-square-o mr-2 blue-grey-600"></i>
                                </a>
                            @else
                                <a href="{{ url('gnl/sys_role/publish/'.$urole->id) }}" title="Publish" class ="btnPublish">
                                    <i class="icon fa-square-o mr-2 blue-grey-600"></i>
                                </a>
                            @endif
                            
                            <a href="{{ asset('gnl/sys_role/passign/'.$urole->id) }}" title="Permission Assign">
                                <i class="icon wb-grid-4 mr-2 blue-grey-600"></i>
                            </a>
                            
                            <a href="{{ url('gnl/sys_role/delete/'.$urole->id) }}" class="btnDelete" title="Delete">
                                <i class="icon wb-trash mr-2 blue-grey-600"></i>
                            </a>
                            
                            <a href="{{ url('gnl/sys_role/destroy/'.$urole->id)}}" title="Parmanent Delete" class="btnDelete">
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