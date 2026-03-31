@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\RoleService as Role;
use App\Services\CommonService as Common;
?>
    <div class="row">
        <div class="col-lg-12">
            <div class="table-responsive">
                <table class="table w-full table-hover table-bordered table-striped clsDataTable" data-plugin="dataTable">
                    <thead>
                        <tr>
                            <th style="width:5%;">SL</th>
                            <th>Name</th>
                            <th class="text-center">Code</th>
                            <th>Zone</th>
                            <th style="width:15%;" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 0;
                        ?>
                        @foreach ($RegionData as $Row)
                        <tr>
                            <td scope="row" class="text-center">{{++$i}}</td>
                            <td>{{$Row->region_name}}</td>
                            <td class="text-center">{{$Row->region_code}}</td>
                            <td>
                                <?php

                                $zoneWiseAreahName = DB::table('gnl_zones')
                                    ->where([['is_delete', 0], ['is_active', 1]])
                                    ->whereIn('id', explode(',', $Row->zone_arr))
                                    ->select(DB::raw('GROUP_CONCAT(zone_name, "(", zone_code, ")") as zone_name'))
                                    ->first();
                                
                                echo ($zoneWiseAreahName) ? $zoneWiseAreahName->zone_name : $Row->zone_arr;
                                ?>
                            </td>
                            
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
            "{{url('gnl/region/delete/')}}",
            "{{url('/ajaxDeleteCheck')}}",
            RowID,
            "{{base64_encode('region_id')}}",
            "{{base64_encode('is_delete,0')}}",
            // "{{base64_encode('gnl_companies')}}"
        );
    }
</script>


@endsection
