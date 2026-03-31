@extends('Layouts.erp_master')
@section('content')
<?php 
use App\Services\RoleService as Role;
?>
<style>
.table>tbody td p {
    margin-bottom: 0px;
}
</style>

<!-- Page -->
<div class="row smallDivWidth">
    <div class="col-lg-12">
        <table class="table w-full table-hover table-bordered table-striped dataTable" data-plugin="dataTable">
            <thead>
                <tr>
                    <th style="width:5%;">SL</th>
                    <th> Name</th>
                    <th style="width:15%;" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 0;?>
                @foreach ($PGroupData as $Row)
                <tr>
                    <td scope="row"> {{++$i}}</td>
                    <td> {{$Row->group_name}}</td>
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

<!-- End Page -->

<script>
function fnDelete(RowID) {
    /**
     * para 1 = link to delete without id
     * para 2 = ajax check link same for all
     * para 3 = id of deleting item
     * para 4 = matching column
     * para 5 = table 1
     * para 6 = table 2
     * para 7 = table 3
     */

    fnDeleteCheck(
        "{{url('inv/group/delete/')}}",
        "{{url('/ajaxDeleteCheck')}}",
        RowID,
        "{{base64_encode('prod_group_id')}}",
        "{{base64_encode('is_delete,0')}}",
        "{{base64_encode('inv_p_categories')}}"
    );
}
</script>
@endsection