@extends('Layouts.erp_master_full_width')
@section('content')
<script type="text/javascript" src="{{ asset('assets/js/pdf/tableExport.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.plugin.autotable.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/xlsx.core.min.js') }}"></script>
<!-- Page -->
<?php
    $startDate = Common::systemCurrentDate();
    $endDate = Common::systemCurrentDate();
    $productData = Common::ViewTableOrder('pos_products',
                            [['is_delete', 0], ['is_active', 1]],
                            ['id', 'product_name'],
                            ['product_name', 'ASC']);
    $issueData = Common::ViewTableOrder('pos_issues_d',
                            [['is_delete', 0], ['is_active', 1]],
                            ['id', 'issue_bill_no'],
                            ['issue_bill_no', 'ASC']);
    $branchData = Common::ViewTableOrder('gnl_branchs',
                            [['is_delete', 0], ['is_active', 1]],
                            ['id', 'branch_name'],
                            ['branch_name', 'ASC']);
    
    $branchId = Common::getBranchId();
    $branchInfo = Common::ViewTableFirst('gnl_branchs',
                            [['is_delete', 0], ['is_active', 1],
                            ['id', $branchId]],
                            ['id', 'branch_name']);
    $groupInfo = Common::ViewTableFirst('gnl_groups',
                            [['is_delete', 0], ['is_active', 1]],
                            ['id', 'group_name']);
?>

<div class="w-full" style="min-height: calc(100% - 44px)">
    <div class="panel">
        <div class="panel-body">
            <div class="row align-items-center pb-10 d-print-none">
                <div class="col-lg-2">
                    <label class="input-title">Start Date</label>
                    <div class="input-group">
                        <input type="text" class="form-control datepicker-custom" id="start_date" name="startDate"
                            placeholder="DD-MM-YYYY" value="{{ $startDate }}">
                    </div>
                </div>
                <div class="col-lg-2">
                    <label class="input-title">End Date</label>
                    <div class="input-group">
                        <input type="text" class="form-control datepicker-custom" id="end_date" name="endDate"
                            placeholder="DD-MM-YYYY" value="{{ $endDate }}">
                    </div>
                </div>
                <div class="col-lg-2">
                    <label class="input-title">Branch</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="branch_id" id="branch_id">
                            <option value="">Select All</option>
                            @foreach ($branchData as $row)
                            <option value="{{ $row->id }}">{{ $row->branch_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-2 pt-20 text-center">
                    <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round"
                        id="searchButton">Search</a>
                </div>
            </div>
            <div class="row d-print-none text-right">
                <div class="col-lg-12">
                    <a href="javascript:void(0)" onClick="window.print();" class="btnPrint mr-2">
                        <i class="fa fa-print fa-lg" style="font-size:20px;"></i>
                    </a>
                    <a href="javascript:void(0)" onclick="fnDownloadPDF();">
                        <i class="fa fa-file-pdf-o fa-lg" style="font-size:20px;"></i>
                    </a>
                    <a href="javascript:void(0)" onclick="fnDownloadExcel();">
                        <i class="fa fa-file-excel-o fa-lg" style="font-size:20px; margin-left: 5px;"></i>
                    </a>
                </div>
            </div>
            <div class="row text-center d-none d-print-block">
                <div class="col-lg-12" style="color:#000;">
                    <strong>{{ $groupInfo->group_name }}</strong><br>
                    <strong>{{ $branchInfo->branch_name }}</strong><br>
                    <span>Collection Report With Profit</span>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-6 col-lg-6 col-sm-6 col-md-6 col-6">
                    <span class="d-print-none"><b>Total Row:</b> <span id="totalRowDiv">0</span></span>
                </div>
                <div class="col-xl-6 col-lg-6 col-sm-6 col-md-6 col-6 text-right">
                    <span><b>Printed Date:</b> {{ date("d-m-Y") }} </span>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="table-responsive">
                        <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="10%">Collection Date</th>
                                    <th width="10%">Customer Name</th>
                                    <th width="13%">Sales Bill No</th>
                                    <th width="5">Customer Code</th>
                                    <th width="10%">Collection Amount(With PF)</th>
                                    <th width="10%">Processing Fee</th>
                                    <th width="10%">Net Collection Amount</th>
                                    <th width="10%">Collection Amount(Pr.)</th>
                                    <th width="10%">Profit</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="text-right"><b>Total:</b></td>
                                    <td class="text-center" id="pf_collection_amount"><b>0.00</b></td>
                                    <td class="text-center" id="service_charge"><b>0.00</b></td>
                                    <td class="text-center" id="collection_amount"><b>0.00</b></td>
                                    <td class="text-center" id="principal_amount"><b>0.00</b></td>
                                    <td class="text-center" id="installment_profit"><b>0.00</b></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- End Page -->
<script>
function ajaxDataLoad(start_date = null, end_date = null, branch_id = null) {
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
            "url": "{{route('collectionProfitDatatable')}}",
            "dataType": "json",
            "type": "post",
            "data": {
                _token: "{{ csrf_token() }}",
                startDate: start_date,
                endDate: end_date,
                branchId: branch_id
            }
        },
        columns: [{
                data: 'sl',
                className: 'text-center'
            },
            {
                data: 'collection_date',
                className: 'text-center'
            },
            {
                data: 'customer_name'
            },
            {
                data: 'sales_bill_no',
                className: 'text-center'
            },
            {
                data: 'customer_code',
                className: 'text-center'
            },
            {
                data: 'pf_collection_amount',
                className: 'text-right'
            },
            {
                data: 'service_charge',
                className: 'text-right'
            },
            {
                data: 'collection_amount',
                className: 'text-right'
            },
            {
                data: 'principal_amount',
                className: 'text-right'
            },
            {
                data: 'installment_profit',
                className: 'text-right'
            },
        ],
        drawCallback: function(oResult) {
            if (oResult.json) {
                $('#totalRowDiv').html(oResult.json.totalRow);
                $('#pf_collection_amount').html(oResult.json.total_pf_col_amount);
                $('#service_charge').html(oResult.json.ttl_service_charge);
                $('#collection_amount').html(oResult.json.ttl_collection_amount);
                $('#principal_amount').html(oResult.json.ttl_principal_amount);
                $('#installment_profit').html(oResult.json.ttl_installment_profit);
            }
        },
    });
}
$(document).ready(function() {
    // ajaxDataLoad();
    $('#searchButton').click(function() {
        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();
        var branch_id = $('#branch_id').val();
        // console.log(branch_id);
        ajaxDataLoad(start_date, end_date, branch_id);
    });
});

function fnDownloadPDF() {
    $('.clsDataTable').tableExport({
        type: 'pdf',
        fileName: 'All Collection report',
        jspdf: {
            orientation: 'l',
            format: 'a4',
            margins: {
                left: 10,
                right: 10,
                top: 20,
                bottom: 20
            },
            autotable: {
                styles: {
                    overflow: 'linebreak'
                },
                tableWidth: 'auto'
            }
        }
    });
}

function fnDownloadExcel() {
    $('.clsDataTable').tableExport({
        type: 'excel',
        fileName: 'All Collection report',
    });
}
</script>
@endsection