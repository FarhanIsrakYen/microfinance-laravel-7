@extends('Layouts.erp_master')
@section('content')
<?php 
use App\Services\RoleService as Role;
?>
<!-- Page -->

<div class="row">
    <div class="col-lg-12">
        <table class="table w-full table-hover table-bordered table-striped dataTable" data-plugin="dataTable">
            <thead>
                <tr>
                    <th style="width:3%;">SL</th>
                    <th>Name</th>
                    <th>Title Name</th>
                    <th>Short Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
        $i = 0;
        ?>
                @foreach ($vdata as $Row)

                <tr>
                    <td scope="row"> {{++$i}}</td>
                    <td> {{$Row->name}}</td>
                    <td> {{$Row->title_name}}</td>
                    <td> {{$Row->short_name}}</td>


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
     * para1 = link to delete without id
     * para 2 = ajax check link same for all
     * para 3 = id of deleting item
     * para 4 = matching column
     * para 5 = table 1
     * para 6 = table 2
     * para 7 = table 3
     */

    fnDeleteCheck(
        "{{url('acc/voucher_type/delete/')}}",
        "{{url('/ajaxDeleteCheck')}}",
        RowID,

    );
}
</script>
@endsection