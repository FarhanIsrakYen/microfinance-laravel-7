@extends('Layouts.erp_master')

@section('content')
<!-- Page -->

<style>
    .table > tbody td p {
        margin-bottom:0px;
    }

</style>
<div class="page">
    <div class="page-header">
        <h4 class="">Branch List</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{url('gnl')}}">Home</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0)">Company Setting</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0)">Branch</a></li>
            <li class="breadcrumb-item active">List</li>
        </ol>
        <div class="page-header-actions">
            <a class="btn btn-sm btn-primary btn-outline btn-round" href="{{url('gnl/branch/new')}}">
                <i class="icon wb-link" aria-hidden="true"></i>
                <span class="hidden-sm-down">New Entry</span>
            </a>
        </div>
    </div>

    <div class="page-content">
        <div class="panel">
            <div class="panel-body">
                <div class="row">
                    <div class="col-lg-12 table-responsive">
                        <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                            <thead>
                                <tr>
                                    <th style="width: 3%;">SL#</th>
                                    <th>Name</th>
                                    <th>Code</th>
                                    <th>Contact Info</th>
                                    <th>Opening Date</th>

                                    {{-- <th>Branch Opening Date</th>
                                    <th>Software Opening Date</th> --}}
                                    <th>Company</th>
                                    <th>Approve</th>
                                    <th style="width: 15%;">Action</th>
                                </tr>
                            </thead>
                            <!-- <tbody>
                                <?php
                                $i = 0;
                                ?>
                                @foreach ($BranchData as $Row)

                                <tr>
                                    <td scope="row"> {{++$i}}</td>
                                    <td> {{$Row->branch_name}}</td>
                                    <td> {{$Row->branch_code}}</td>
                                    <td class="text-left">
                                        <p><b>Person:</b>{{$Row->contact_person}}</p>
                                        <p><b>Mobile:</b>{{$Row->branch_phone}}</p>
                                    </td>
                                    <td class="text-left">
                                        <p><b>Branch:</b>{{date('d-M-y', strtotime($Row->branch_opening_date))}}</p>
                                        <p><b>Software:</b>{{date('d-M-y', strtotime($Row->soft_start_date))}}</p>
                                    </td>
                                    <td> {{$Row->company['comp_name']}}</td>
                                    @if($Row->is_approve === 1)
                                    <td class="text-primary">Approved</td>
                                    @else
                                    <td class="text-danger">Pending</td>
                                    @endif

                                    <td>

                                        @if($Row->is_approve == 0)
                                        <a href="{{url('gnl/branch/approve/'.$Row->id)}}" title="Pending" class="btnPending">
                                            <i class="icon fa fa-check-square fa-2x mr-2 blue-grey-600" style="font-size: 18px;"></i>
                                        </a>
                                        @endif

                                        <a href="{{URL::to('gnl/branch/edit/'.$Row->id)}}" title="Edit">
                                            <i class="icon wb-edit mr-2 blue-grey-600"></i> </a>
                                        <a href="{{URL::to('gnl/branch/view/'.$Row->id)}}" title="View">
                                            <i class="icon wb-eye mr-2 blue-grey-600"></i></a>

                                        @if($Row->id != 0)
                                            @if($Row->is_active==1)
                                            <a href="{{URL::to('gnl/branch/publish/'.$Row->id)}}" title="Unpublish" class="btnUnpublish">
                                                <i class="icon fa-check-square-o mr-2 blue-grey-600"></i> </a>
                                            @else
                                            <a href="{{URL::to('gnl/branch/publish/'.$Row->id)}}" title="Publish" class="btnPublish">
                                                <i class="icon fa-square-o mr-2 blue-grey-600"></i> </a>

                                            @endif
                                        @endif

                                        @if($Row->id != 0)
                                        <a href="#" title="Delete"
                                           onclick="fnDeleteCheck(
                                           '{{url('gnl/branch/delete/')}}',
                                           '{{url('/ajaxDeleteCheck')}}',
                                           '{{$Row->id}}',
                                           '{{base64_encode('branch_id')}}', '',
                                           '{{base64_encode('gnl_map_area_branch')}}'
                                                   );">
                                            <i class="icon wb-trash mr-2 blue-grey-600"></i>
                                        </a>
                                        @endif
                                    </td>

                                </tr>

                                @endforeach
                            </tbody> -->

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $("#btnApprove").attr("disabled", true);
</script>
<!-- End Page -->
<script>
function ajaxDataLoad(){

    $('.clsDataTable').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        stateSave: true,
        stateDuration: 1800,
        // ordering: false,
        // lengthMenu: [[10, 20, 30, 50], [10, 20, 30, 50]],
        "ajax":{
                 "url": "{{route('branchDatatable')}}",
                 "dataType": "json",
                 "type": "post",
                 "data":{ _token: "{{csrf_token()}}"}
               },
        columns: [

              { data: 'id', name: 'id' },
              { data: 'branch_name', name: 'branch_name' },
              { data: 'branch_code', name: 'branch_code' },
              { data: 'Contact Info', name: 'Contact Info' },
               { data: 'opening Date', name: 'opening Date' },
              { data: 'comp_name', name: 'comp_name' },
              { data: 'approved', name: 'approved' },
              {data: 'action', name: 'action', orderable: false},

        ],
        'fnRowCallback': function(nRow, aData, Index) {
            var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData.action.action_link);
            $('td:last', nRow).html(actionHTML);
        }

    });
}

$(document).ready( function () {
    ajaxDataLoad();
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
        "{{url('gnl/branch/delete/')}}",
        "{{url('/ajaxDeleteCheck')}}",
        RowID,
        "{{base64_encode('branch_id')}}",
        "",
        "{{base64_encode('gnl_map_area_branch')}}"
    );
}


 </script>
<!-- End Page -->
@endsection
