@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\RoleService as Role;
?>

    <div class="row">
        <div class="col-lg-12">
            <div class="table-responsive">
                <table class="table w-full table-hover table-bordered table-striped clsDataTable" data-plugin="dataTable">
                    <thead>
                        <tr>
                            <th style="width:5%;">SL</th>
                            <th> Name</th>
                            <th class="text-center">Code</th>
                            <th>Project</th>
                            <th>Company</th>
                            <th style="width:15%;" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 0;
                        ?>
                        @foreach ($ProjectTypeData as $Row)

                        <tr>

                            <td scope="row"> {{++$i}}</td>
                            <td> {{$Row->project_type_name}}</td>
                            <td class="text-center"> {{$Row->project_type_code}}</td>
                            <td> {{$Row->project->project_name}}</td>
                            <td> {{$Row->company->comp_name}}</td>
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
            "{{url('gnl/project_type/delete/')}}",
            "{{url('/ajaxDeleteCheck')}}",
            RowID,
            "{{base64_encode('project_type_id')}}",
            "{{base64_encode('is_delete,0')}}",
            "{{base64_encode('gnl_branchs')}}"
        );
    }
</script>
@endsection
