@extends('Layouts.erp_master')
@section('content')
<?php 
use App\Services\RoleService as Role;
?>

<div class="row">
    <div class="col-lg-12">
    <!-- data-plugin="dataTable" -->
        <div class="table-responsive">
            <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                <thead>
                    <tr>
                        <th width="5%">SL</th>
                        <th width="20%">Menu Name</th>
                        <th width="15%">Parent Menu</th>
                        <th width="15%">Route Link</th>
                        <!--<th width="15%">Controller</th>
                        <th width="8%">Method</th>-->
                        <th width="5%">Icon</th>
                        <th width="10%">Order By</th>
                        <th width="15%">Module</th>
                        <th width="15%">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 0;
                    foreach ($sysMenus as $menus) {
                        
                        ?>
                        <tr>
                            <td>{{ ++$i }}</td>

                            <td>{{ $menus->menu_name }}</td>
                            <td>
                                <?php
                                if ($menus->parent_menu_id == 0) {
                                    echo 'Root';
                                } else {
                                    foreach ($sysMenus as $menuName) {
                                        if ($menus->parent_menu_id == $menuName->id) {
                                            echo $menuName->menu_name . " <br>(<small style='color:blue;'>" . $menuName->route_link . "</small>)";
                                        }
                                    }
                                }
                                ?>

                            </td>
                            <td>{{ $menus->route_link }}</td>
                            <!--<td>{{ $menus->controller }}</td>
                            <td>{{ $menus->action }}</td>-->
                            <td>{{ $menus->menu_icon }}</td>
                            <td>{{ $menus->order_by }}</td>
                            <td>{{ $menus->SysModule->module_name }}</td>

                            <td class="text-center">
                                <!-- Action Calling Role Wise -->
                                {!! Role::roleWisePermission($GlobalRole, $menus->id, [], $menus->is_active) !!}
                            </td>

                            <!-- <td>
                                <a href="{{ url('gnl/sys_menu/edit/'.$menus->id) }}" title="Edit">
                                    <i class="icon wb-edit mr-2 blue-grey-600"></i>
                                </a>

                                @if($menus->is_active == 1)
                                <a href="{{ url('gnl/sys_menu/publish/'.$menus->id) }}" title="Unpublish" class ="btnUnpublish">
                                    <i class="icon fa-check-square-o mr-2 blue-grey-600"></i>
                                </a>
                                @else
                                <a href="{{ url('gnl/sys_menu/publish/'.$menus->id) }}" title="Publish" class ="btnPublish">
                                    <i class="icon fa-square-o mr-2 blue-grey-600"></i>
                                </a>
                                @endif

                                <a href="{{ url('gnl/sys_permission/'.$menus->id) }}" title="Permission create">
                                    <i class="icon icon wb-folder mr-2 blue-grey-600"></i>
                                </a>

                                <a href="{{ url('gnl/sys_menu/delete/'.$menus->id) }}" title="Delete" class ="btnDelete">
                                    <i class="icon wb-trash mr-2 blue-grey-600"></i>
                                </a>

                                <a href="{{ url('gnl/sys_menu/destroy/'.$menus->id)}}" title="Parmanent Delete" class="btnDelete">
                                    <i class="icon wb-scissor mr-2 blue-grey-600"></i>
                                </a>

                            </td> -->
                        </tr>
                        <?php
                    }
                    ?>
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