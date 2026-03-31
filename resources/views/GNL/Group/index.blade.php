@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\RoleService as Role;
?>
<!-- Page -->
<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table w-full table-hover table-bordered table-striped clsDataTable" data-plugin="dataTable">
                <thead>
                    <tr>
                        <th style="width:5%;">SL</th>
                        <th> Name</th>
                        <th> Email</th>
                        <th class="text-center"> Phone</th>
                        <th class="text-center"> Logo</th>
                        <th style="width:15%;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 0; ?>
                    @foreach ($GroupData as $Row)
                    <tr>
                        <td scope="row"> {{++$i}}</td>
                        <td> {{$Row->group_name}}</td>
                        <td> {{$Row->group_email}}</td>
                        <td class="text-center"> {{$Row->group_phone}}</td>
                        <td class="text-center">
                            
                            @if(!empty($Row->group_logo))
                            @if(file_exists($Row->group_logo))
                          
                            <img src="{{ asset($Row->group_logo) }}" style="height: 32PX; width: 32PX;">
                            @endif
                            @else
                            <img src="{{ asset('assets/images/dummy.png') }}" style="height: 32PX; width: 32PX;">
                            @endif
                        </td>
                        <td class="text-center">
                            <!-- if want to deactive action ['edit','view', 'delete', 'destroy'] -->
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
        "{{url('gnl/group/delete/')}}",
        "{{url('/ajaxDeleteCheck')}}",
        RowID,
        "{{base64_encode('group_id')}}",
        "{{base64_encode('is_delete,0')}}",
        "{{base64_encode('gnl_companies')}}"
    );
}
</script>

@endsection