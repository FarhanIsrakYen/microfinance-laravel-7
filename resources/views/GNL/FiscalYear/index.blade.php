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

                            <th>Name</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Company</th>
                            <th style="width:15%;" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 0;
                        ?>
                        @foreach ($FiscalYear as $Row)
                        <tr>
                            <td scope="row">{{++$i}}</td>

                            <td>{{$Row->fy_name}}</td>
                            <td>{{date('d-m-y', strtotime($Row->fy_start_date))}}</td>
                            <td>{{date('d-m-y', strtotime($Row->fy_end_date))}}</td>
                            <td>{{$Row->company['comp_name'] }}</td>
                            <!-- <td>
                                <a href="{{URL::to('gnl/fiscal_year/edit/'.$Row->id)}}" title="Edit">
                                    <i class="icon wb-edit mr-2 blue-grey-600"></i> </a>
                                <a href="{{URL::to('gnl/fiscal_year/view/'.$Row->id)}}" title="View">
                                    <i class="icon wb-eye mr-2 blue-grey-600"></i></a>

                                @if($Row->is_active==1)
                                <a href="{{URL::to('gnl/fiscal_year/publish/'.$Row->id)}}" title="Unpublish" class="btnUnpublish">
                                    <i class="icon fa-check-square-o mr-2 blue-grey-600"></i> </a>
                                @else
                                <a href="{{URL::to('gnl/fiscal_year/publish/'.$Row->id)}}" title="Publish" class="btnPublish">
                                    <i class="icon fa-square-o mr-2 blue-grey-600"></i> </a>

                                @endif
                                <a href="{{URL::to('gnl/fiscal_year/delete/'.$Row->id)}}" title="Delete" class="btnDelete">
                                    <i class="icon wb-trash mr-2 blue-grey-600"></i></a>
                                <a href="{{URL::to('gnl/fiscal_year/delete/'.$Row->id)}}" title="Delete" class="btnDelete">
                                    <i class="icon wb-scissor mr-2 blue-grey-600"></i></a>
                            </td> -->
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
            "{{url('gnl/fiscal_year/delete/')}}",
            "{{url('/ajaxDeleteCheck')}}",
            RowID
        );
    }
</script>



@endsection
