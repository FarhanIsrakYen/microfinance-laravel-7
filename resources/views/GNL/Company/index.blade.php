@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\RoleService as Role;
use App\Services\CommonService as Common;
?>
<!-- Page -->
    <div class="row">
        <div class="col-lg-12">
            <div class="table-responsive">
                <table class="table w-full table-hover table-bordered table-striped clsDataTable" data-plugin="dataTable">
                    <thead>
                        <tr>
                            <th style="width:5%;">SL</th>
                            <th>Name</th>
                            <th class="text-center">Code</th>
                            <th>Email</th>
                            <th class="text-center">Logo</th>
                            <th>Group</th>
                            @if(Common::isSuperUser() == true)
                            <th>Modules</th>
                            @endif
                            <th style="width:15%;" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 0;
                        ?>
                        @foreach ($CompanyData as $Row)
                        <tr>
                            <td scope="row"> {{++$i}}</td>

                            <td> {{$Row->comp_name}}</td>
                            <td class="text-center"> {{$Row->comp_code}}</td>
                            <td> {{$Row->comp_email}}</td>

                            <td class="text-center">
                                @if(!empty($Row->comp_logo))
                                @if(file_exists($Row->comp_logo))
                                <img src="{{ asset($Row->comp_logo) }}" style="height: 32PX; width: 32PX;">
                                @endif
                                @else
                                <img src="{{ asset('assets/images/dummy.png') }}" style="height: 32PX; width: 32PX;">
                                @endif
                            </td>
                            <td> {{$Row->group['group_name'] }}</td>

                            @if(Common::isSuperUser() == true)
                            <td>
                                <?php
                                if(!empty($Row->module_arr)){

                                    $sysModules = DB::table('gnl_sys_modules')
                                        ->where('is_delete', 0)
                                        ->whereIn('id', explode(',',$Row->module_arr))
                                        ->pluck('module_name');

                                    if($sysModules){
                                        echo implode(',<br>', $sysModules->toArray());
                                    }
                                }
                                ?>
                            </td>
                            @endif

                            <td class="text-center">
                                <!-- Action Calling Role Wise -->
                                {!! Role::roleWisePermission($GlobalRole, $Row->id) !!}
                            </td>
                        </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>
<!-- End Page -->
<script>
    function fnDelete(RowID) {
        /**
         * para1 = link to delete without id
         * para 2 = ajax check link same for all
         * para 3 = id of deleting item
         * para 4 = matching column
         * para 5 = table 1
         * para 6 = table 2
         * para 7 = table 3
         */

        fnDeleteCheck(
            "{{url('gnl/company/delete/')}}",
            "{{url('/ajaxDeleteCheck')}}",
            RowID,
            "{{base64_encode('company_id')}}",
            "{{base64_encode('is_delete,0')}}",
            "{{base64_encode('gnl_projects')}}",
            "{{base64_encode('gnl_project_types')}}",
            "{{base64_encode('gnl_branchs')}}"
        );
    }
</script>
@endsection
