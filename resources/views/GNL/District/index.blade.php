@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\CommonService as Common;

$divisionData = Common::ViewTableOrder('gnl_divisions',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'division_name'],
    ['division_name', 'ASC']);
?>
<!-- Page -->

    <!-- Search Option Start -->
    <div class="row align-items-center pb-10 d-print-none">
        <div class="col-lg-2">
            <label class="input-title">Division</label>
            <div class="input-group">
                <select class="form-control clsSelect2" name="division_id" id="division_id">
                    <option value="">Select All</option>
                    @foreach ($divisionData as $row)
                    <option value="{{ $row->id }}">{{ $row->division_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-lg-2 pt-20 text-center">
            <a href="javascript:void(0)" id="searchButton" name="searchButton" class="btn btn-primary btn-round">Search</a>
        </div>
    </div>
    <!-- Search Option End -->

    <div class="row">
        <div class="col-lg-12">
            <table class="table w-full table-hover table-bordered table-striped clsDataTable" >
                <thead>
                    <tr>
                        <th style="width:5%;">SL</th>
                        <th>Name</th>
                        <th>Division</th>
                        <th style="width:15%;" class="text-center">Action</th>
                    </tr>
                </thead>
                <!-- <tbody>
                    <?php
                    $i = 0;
                    ?>
                    @foreach ($DisData as $Row)
                    <tr>
                        <td scope="row">{{++$i}}</td>
                        <td>{{$Row->district_name}}</td>
                        <td>{{$Row->division['division_name'] }}</td>

                        <td>
                            <a href="{{URL::to('gnl/district/edit/'.$Row->id)}}" title="Edit">
                                <i class="icon wb-edit mr-2 blue-grey-600"></i> </a>
                            <a href="{{URL::to('gnl/district/view/'.$Row->id)}}" title="View">
                                <i class="icon wb-eye mr-2 blue-grey-600"></i></a>

                            @if($Row->is_active==1)
                            <a href="{{URL::to('gnl/district/publish/'.$Row->id)}}" title="Unpublish" class="btnUnpublish">
                                <i class="icon fa-check-square-o mr-2 blue-grey-600"></i> </a>
                            @else
                            <a href="{{URL::to('gnl/district/publish/'.$Row->id)}}" title="Publish" class="btnPublish">
                                <i class="icon fa-square-o mr-2 blue-grey-600"></i> </a>

                            @endif
                            <a href="#" title="Delete"
                                onclick="fnDeleteCheck(
                                    '{{url('gnl/district/delete/')}}',
                                    '{{url('/ajaxDeleteCheck')}}',
                                    '{{$Row->id}}',
                                    '{{base64_encode('district_id')}}',
                                    '{{base64_encode('is_delete,0')}}',
                                    '{{base64_encode('gnl_upazilas')}}'
                                    );">
                                <i class="icon wb-trash mr-2 blue-grey-600"></i></a>
                            <a href="{{URL::to('gnl/district/delete/'.$Row->id)}}" title="Delete" class="btnDelete">
                                <i class="icon wb-scissor mr-2 blue-grey-600"></i></a>
                        </td>
                    </tr>

                    @endforeach

                </tbody> -->
            </table>
        </div>
    </div>
<!-- End Page -->
<script>
    function ajaxDataLoad(divisionID = null){

        $('.clsDataTable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            stateSave: true,
            stateDuration: 1800,
            // ordering: false,
            // lengthMenu: [[10, 20, 30, 50], [10, 20, 30, 50]],
            "ajax":{
                     "url": "{{route('DistrictDatatable')}}",
                     "dataType": "json",
                     "type": "post",
                     "data":{
                         _token: "{{csrf_token()}}",
                         divisionID: divisionID,
                     }
                   },
            columns: [

                  { data: 'id', name: 'id', className: 'text-center',targets:[1], width: '5%' },
                  { data: 'district_name', name: 'district_name'},
                  { data: 'division_name', name: 'division_name', orderable: false },
                  {data: 'action', name: 'action', orderable: false, className: 'text-center', width: '15%'},

            ],
            'fnRowCallback': function(nRow, aData, Index) {
                var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData.action.action_link);
                $('td:last', nRow).html(actionHTML);
            }

        });
    }

    $(document).ready( function () {
        ajaxDataLoad();
        $('#searchButton').click(function() {
            var divisionID = $('#division_id').val();

            ajaxDataLoad(divisionID);
        });
    });

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
            "{{url('gnl/district/delete/')}}",
            "{{url('/ajaxDeleteCheck')}}",
            RowID,
            "{{base64_encode('district_id')}}",
            "",
            "{{base64_encode('gnl_upazilas')}}"
        );
    }
 </script>
@endsection
