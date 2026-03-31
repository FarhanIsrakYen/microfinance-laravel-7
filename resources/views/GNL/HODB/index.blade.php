@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\RoleService as Role;
?>
<!-- Page -->
    <div class="row">
        <div class="col-lg-12">
            <table class="table w-full table-hover table-bordered table-striped clsDataTable" data-plugin="dataTable">
                <thead>
                    <tr>
                        <th style="width:5%;">SL</th>
                        <th>Table Name</th>
                        <th style="width:15%;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 0;
                    ?>
                    @foreach ($HOtable as $Row)
                    <tr>
                        <td scope="row"> {{++$i}}</td>

                        <td> {{$Row->table_name}}</td>
                
                        <td class="text-center">
                            <!-- Action Calling Role Wise -->
                            {!! Role::roleWisePermission($GlobalRole, $Row->id, [], $Row->is_active) !!}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
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
        "{{url('gnl/ho_db/delete/')}}",
        "{{url('/ajaxDeleteCheck')}}",
        RowID
    );
}
</script>
@endsection
