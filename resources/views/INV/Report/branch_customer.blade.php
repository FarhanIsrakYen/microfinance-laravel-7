@extends('Layouts.erp_master_full_width')
@section('content')

<?php
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use App\Services\HtmlService as HTML;

$startDate = Common::systemCurrentDate();
$endDate = Common::systemCurrentDate();
$branchId = Common::getBranchId();

$branchInfo = Common::ViewTableFirst('gnl_branchs',
    [['is_delete', 0], ['is_active', 1], ['id', $branchId]],
    ['id', 'branch_name']);

$groupInfo = Common::ViewTableFirst('gnl_groups',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'group_name']);

?>
<div class="w-full d-print-none">
    <div class="panel">
        <div class="panel-body smallDivWidth">
            <div class="row align-items-center d-flex justify-content-center">

                {!! HTML::forBranchFeildSearch_new('all') !!}

                @if($branchId == 1)
                <div class="col-lg-2 pt-30 text-center ml-4">
                    <a href="javascript:void(0)" name="searchButton" id="searchButton" class="btn btn-primary btn-round">Search</a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="w-full">
    <div class="panel">
        <div class="panel-body smallDivWidth">

            <div class="row text-dark ExportHeading">
                <div class="col-xl-12 col-lg-12 col-sm-12 col-md-12 col-12" style="text-align:center;">
                    <br>
                    <strong>{{ $groupInfo->group_name }}</strong><br>
                    <strong>{{ $branchInfo->branch_name }}</strong><br>
                    <span>Branch Customer Report</span><br>
                    (<span id="start_date_txt">{{ (new Datetime($startDate))->format('d-m-Y') }}</span>
                    To
                    <span id="end_date_txt">{{ (new Datetime($endDate))->format('d-m-Y') }}</span>)
                    <br><br>
                </div>
            </div>

            <div class="row d-print-none text-right">
                <div class="col-lg-12">
                    <a href="javascript:void(0)" onClick="window.print();"
                        style="background-color:transparent;border:none;" class="btnPrint mr-2">
                        <i class="fa fa-print fa-lg" style="font-size:20px;"></i>
                    </a>

                    <a href="javascript:void(0)" style="background-color:transparent;border:none;"
                        onclick="getDownloadPDF();">
                        <i class="fa fa-file-pdf-o fa-lg" style="font-size:20px; margin-left: 5px;"></i>
                    </a>

                    <a href="javascript:void(0)" title="Excel" style="background-color:transparent;border:none;"
                        onclick="fnDownloadExcel('ExportHeading,ExportDiv', 'Branch_Customer_Report_{{ (new Datetime())->format('d-m-Y') }}');">
                        <i class="fa fa-file-excel-o fa-lg" style="font-size:20px; margin-left: 5px;"></i>
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-6 col-lg-6 col-sm-6 col-md-6 col-6">
                    <!-- <span class="d-print-none"><b>Total Row:</b> <span id="totalRowDiv">0</span></span> -->
                </div>
                <div class="col-xl-6 col-lg-6 col-sm-6 col-md-6 col-6 text-right">
                    <span><b>Printed Date:</b> {{ date("d-m-Y") }} </span>
                </div>
            </div>

            <div class="row ExportDiv">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-striped clsDataTable">
                        <thead>
                            <tr>
                                <th width="3%">SL</th>
                                <th>Branch Name</th>
                                <th>Customer</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <td colspan="2" style="text-align: right!important;"><b>Total</b></td>
                                <td class="text-center" id="nCustomer"><b>0</b></td>
                            </tr>
                        </tfoot>
                    </table>
                    @include('../elements.signature.signatureSet')
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {

        $('#searchButton').click(function() {
            var branch_id = $('#branch_id').val();

            ajaxDataLoad( branch_id);
        });

        $('#branch_id').width('100%');
    });

    function ajaxDataLoad( branch_id = null) {

        $('.clsDataTable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            // lengthMenu: [[10, 20, 30, 50, -1], [10, 20, 30, 50, "All"]],
            paging: false,
            ordering: false,
            info: false,
            searching: false,
            "ajax": {
                "url": "{{ route('branchcustomerDatatable') }}",
                "dataType": "json",
                "type": "post",
                "data": {
                    _token: "{{ csrf_token() }}",
                    branchId: branch_id,
                }
            },
            columns: [{
                    data: 'sl',
                    className: 'text-center'
                },
                {
                    data: 'branch_name'
                },
                {
                    data: 'customer_count',
                    className: 'text-center'
                },

            ],
            drawCallback: function(oResult) {

                if (oResult.json) {
                    $('#totalRowDiv').html(oResult.json.totalRow);
                     $('#nCustomer').html(oResult.json.tcustomer);
                }
            },
        });
    }

</script>
@endsection
