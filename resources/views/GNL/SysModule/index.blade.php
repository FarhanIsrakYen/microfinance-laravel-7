@extends('Layouts.erp_master')
@section('content')
<?php
use App\Services\RoleService as Role;
?>
<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                <thead>
                    <tr>
                        <th width="5%">SL</th>
                        <th>Module Name</th>
                        <th>Module Short Name</th>
                        <th>Route Link</th>
                        <th>Module Icon</th>
                        <th style="width:15%;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php $i = 0 @endphp
                    @foreach($module  as $row)
                    <tr>
                        <td>{{ ++$i }}</td>
                        <td>{{ $row->module_name }}</td>
                        <td>{{ $row->module_short_name }}</td>
                        <td>{{ $row->route_link }}</td>
                        <td class="text-center">
                            @if($row->module_icon != null)
                            <i class="fa {{ $row->module_icon }}" style="font-size:25px;" aria-hidden="true"></i>
                            @endif
                        </td>
                        <td class="text-center">
                            <!-- Action Calling Role Wise -->
                            {!! Role::roleWisePermission($GlobalRole, $row->id, [], $row->is_active) !!}
                        </td>
                        <!-- <td>
                            <a href="{{ url('gnl/sys_module/edit/'.$row->id) }}" title="Edit">
                                <i class="icon wb-edit mr-2 blue-grey-600"></i>
                            </a>

                            @if($row->is_active == 1)
                            <a href="{{ url('gnl/sys_module/publish/'.$row->id) }}" title="Unpublish" class ="btnUnpublish">
                                <i class="icon fa-check-square-o mr-2 blue-grey-600"></i>
                            </a>
                            @else
                            <a href="{{ url('gnl/sys_module/publish/'.$row->id) }}" title="Publish" class ="btnPublish">
                                <i class="icon fa-square-o mr-2 blue-grey-600"></i>
                            </a>
                            @endif

                            <a href="{{ url('gnl/sys_module/delete/'.$row->id) }}" class="btnDelete" title="Delete">
                                <i class="icon wb-trash mr-2 blue-grey-600"></i>
                            </a>

                            <a href="{{ url('gnl/sys_module/destroy/'.$row->id)}}" title="Parmanent Delete" class="btnDelete">
                                <i class="icon wb-scissor mr-2 blue-grey-600"></i>
                            </a>

                        </td> -->
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $('.clsDataTable').DataTable({
        ordering: false,
    });
</script>

@endsection
