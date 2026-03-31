@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\HtmlService as HTML;
use App\Services\CommonService as Common;

$branchId =  Common::getBranchId();

?>

<!-- Page -->

    <!-- Search Option Start -->
    <div class="row align-items-center d-flex justify-content-center">

        <!-- Html View Load For Branch Search -->
        {!! HTML::forBranchFeildSearch('all') !!}

        @if($branchId==1)
        <div class="col-lg-2 pt-20 text-center">
            <a href="javascript:void(0)" id="searchButton" name="searchButton" class="btn btn-primary btn-round">Search</a>
        </div>
        @endif

    </div>
    <!-- Search Option End -->

    <div class="row">
        <div class="col-lg-12">
            <div class="mt-4 table-responsive">
                <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                    <thead>
                        <tr>
                            <th width="4%">SL</th>
                            <th>Name</th>
                            <th class="text-center">Code</th>
                            <!-- <th>Customer Type</th> -->
                            <th>Mobile</th>
                            <th>Email</th>
                            <th class="text-left">Branch</th>
                            <!-- <th class="text-left">Company</th> -->
                            <th>Action</th>
                        </tr>
                    </thead>

                </table>
            </div>
        </div>
    </div>
<!-- End Page -->

<script>

    function ajaxDataLoad(BranchID = null){

        $('.clsDataTable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            stateSave: true,
            stateDuration: 1800,
            // ordering: false,
            // lengthMenu: [[10, 20, 30, 50], [10, 20, 30, 50]],
            "ajax":{
               "url": "{{route('customerBillDatatable')}}",
               "dataType": "json",
               "type": "post",
               "data":{ _token: "{{csrf_token()}}",
                        BranchID:BranchID,
                    }
             },
            columns: [
                { data: 'id',className: 'text-center'},
                { data: 'customer_name',className: 'text-left'},
                { data: 'customer_code',className: 'text-center'},
                // { data: 'customer_type',className: 'text-center'},
                { data: 'customer_mobile',className: 'text-center'},
                { data: 'customer_email',className: 'text-center'},
                { data: 'branch_name'},
                // { data: 'comp_name'},
                { data: 'action' , orderable: false, className: 'text-center'},

            ],
            'fnRowCallback': function(nRow, aData, Index) {
                var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData.action.action_link);
                $('td:last', nRow).html(actionHTML);
            }

        });
    }

    $(document).ready( function () {

        ajaxDataLoad();

        $('#searchButton').click(function(){
            var BranchID = $('#branch_id').val();
            ajaxDataLoad(BranchID);
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
            "{{url('bill/customer/delete/')}}",
            "{{url('/ajaxDeleteCheck')}}",
            RowID,
            "{{base64_encode('customer_id')}}",
            "{{base64_encode('is_delete,0')}}",
            "{{base64_encode('bill_guarantors')}}",
            "{{base64_encode('bill_sales_m')}}"
        );
    }
</script>
@endsection
