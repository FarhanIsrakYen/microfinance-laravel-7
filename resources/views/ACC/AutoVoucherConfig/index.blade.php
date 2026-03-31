@extends('Layouts.erp_master')
@section('content')
<?php 
use App\Services\RoleService as Role;
?>

<!-- Page -->

<div class="panel-body">
    <div class="row">
    <div class="col-lg-12">
    <table class="table w-full table-hover table-bordered table-striped dataTable" data-plugin="dataTable">
        <thead>
            <tr>
                <th style="width:3%;">SL</th>
                <th>Business Type</th>
                <th>Voucher Type</th>
                <th>Local Narration</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            
            <?php
            $i = 0;
            ?>
            @foreach ($data as $Row)

            <tr>
                <td scope="row"> {{++$i}}</td>
                <td> {{$Row->salestype['name']}}</td>
                <td> {{$Row->vouchertype['name']}}</td>
                <td> {{$Row->local_narration}}</td>
               

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
<Script>
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
            "{{url('acc/auto_v_config/delete/')}}",
            "{{url('/ajaxDeleteCheck')}}",
            RowID,
            
        );
    }
</Script>
@endsection
