@extends('Layouts.erp_master_full_width')
@section('content')

<?php
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use App\Services\HtmlService as HTML;

$endDate = Common::systemCurrentDate();

$areaData = Common::ViewTableOrder('gnl_areas',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'area_name'],
    ['id', 'ASC']);

$zoneData = Common::ViewTableOrder('gnl_zones',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'zone_name'],
    ['id', 'ASC']);

$branchId = Common::getBranchId();

$branchInfo = Common::ViewTableFirst('gnl_branchs',
    [['is_delete', 0], ['is_active', 1],['id', $branchId]],
    ['id', 'branch_name']);

$groupInfo = Common::ViewTableFirst('gnl_groups',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'group_name']);




?>
<div class="w-full d-print-none">
    <div class="panel">
        <div class="panel-body">
            <div class="row align-items-center pb-10 d-print-none">

                <div class="col-lg-2">
                    <label class="input-title">Zone</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="zone_id" id="zone_id" onchange="fnAjaxGetArea();">
                            <option value="">Select Option</option>
                            @foreach($zoneData as $row)
                            <option value="{{ $row->id}}">{{ $row->zone_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-2">
                    <label class="input-title">Area</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="area_id" id="area_id"
                            onchange="fnAjaxGetBranch();">

                            <option value="">Select Option</option>
                            @foreach($areaData as $row)
                            <option value="{{ $row->id}}">{{ $row->area_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {!! HTML::forBranchFeildSearch_new('all') !!}
                <div class="col-lg-2">
                    <label class="input-title">End Date</label>
                    <div class="input-group">
                        <input type="text" class="form-control datepicker-custom" id="end_date" name="EndDate"
                            placeholder="DD-MM-YYYY" value="{{ $endDate }}">
                    </div>
                </div>

                <div class="col-lg-2 pt-20 text-center">
                    <a href="javascript:void(0)" name="searchButton" id="searchButton"
                        class="btn btn-primary btn-round">Search</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="w-full">
    <div class="panel">
        <div class="panel-body">

            <div class="row text-dark ExportHeading">
                <div class="col-xl-12 col-lg-12 col-sm-12 col-md-12 col-12" style="text-align:center;">
                    <br>
                    <strong>{{ $groupInfo->group_name }}</strong><br>
                    <strong><span id="reportBranch">{{ $branchInfo->branch_name }}</span></strong><br>
                    <span>Due Register Report(Current + Over Due)</span><br>
                    (<span id="end_date_txt">{{ (new Datetime($endDate))->format('d-m-Y') }}</span>)
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
                        onclick="fnDownloadExcel('ExportHeading,ExportDiv', 'Current_Over_Due_Report_{{ (new Datetime())->format('d-m-Y') }}');">
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
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead>
                            <tr>
                                <th width="3%">SL</th>
                                <!-- <th>Customer Code</th> -->
                                <th>Customer Name</th>
                                <th>Bill No.</th>
                                <th width="10%">Sales Date</th>
                                <th>Sales Amount</th>
                                <th>Installment</th>
                                <th>Installment Amount</th>
                                <th>Paid Amount</th>
                                <th>Current Due</th>
                                <th>Over Due</th>
                                <th>Total Due</th>
                                <th>Total Balance</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-align:right!important;"><b>Total:</b></td>
                                <td class="text-right"><b id="sAmt">0.00</b></td>
                                <td></td>
                                <td class="text-right"><b id="instAmt">0.00</b></td>
                                <td class="text-right"><b id="pAmt">0.00</b></td>
                                <td class="text-right"><b id="cDue">0.00</b></td>
                                <td class="text-right"><b id="oDue">0.00</b></td>
                                <td class="text-right"><b id="tDue">0.00</b></td>
                                <td class="text-right"><b id="tBalance">0.00</b></td>
                                <td></td>
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

        var end_date = $('#end_date').val();
        var zone_id = $('#zone_id').val();
        var area_id = $('#area_id').val();
        var branch_id = $('#branch_id').val();

        ajaxDataLoad(end_date, zone_id, area_id, branch_id);

        $('#reportBranch').html($('#branch_id').find("option:selected").text());
        $('#end_date_txt').html($('#end_date').val());
    });

    
    
});

function ajaxDataLoad(end_date = null, zone_id = null, area_id = null, branch_id = null) {

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
            "url": "{{ url('pos/report/current_n_over_due') }}",
            "dataType": "json",
            "type": "post",
            "data": {
                endDate: end_date,
                zoneId: zone_id,
                areaId: area_id,
                branchId: branch_id,
            }
        },
        columns: [{
                data: 'sl',
                className: 'text-center'
            },
            // {
            //     data: 'customer_no'
            // },
            {
                data: 'customer_name'
            },
            {
                data: 'sales_bill_no'
            },
            {
                data: 'sales_date'
            },
            {
                data: 'sales_amount',
                className: 'text-right'
            },
            {
                data: 'installment',
                className: 'text-center'
            },
            {
                data: 'installment_amount',
                className: 'text-right'
            },
            {
                data: 'paid_amount',
                className: 'text-right'
            },
            {
                data: 'current_due',
                className: 'text-right'
            },
            {
                data: 'over_due',
                className: 'text-right'
            },
            {
                data: 'total_due',
                className: 'text-right'
            },
            {
                data: 'total_balance',
                className: 'text-right'
            },
            {
                data: 'status',
                className: 'text-center'
            }
        ],
        drawCallback: function(oResult) {

            if (oResult.json) {
                $('#totalRowDiv').html(oResult.json.totalRow);
                $('#sAmt').html(oResult.json.ttl_sales_amount);
                $('#instAmt').html(oResult.json.ttl_inst_amount);
                $('#pAmt').html(oResult.json.ttl_paid_amount);
                $('#cDue').html(oResult.json.ttl_current_due);
                $('#oDue').html(oResult.json.ttl_over_due);
                $('#tDue').html(oResult.json.ttl_due);
                $('#tBalance').html(oResult.json.ttl_total_balance);
            }
        },
    });
}


function fnAjaxGetArea() {

    zoneId = $('#zone_id').val();

    if (zoneId != null) {

        $.ajax({
            method: "GET",
            url: "{{ url('ajaxGetArea') }}",
            dataType: "text",
            data: {
                zoneId: zoneId,
            },
            success: function(data) {

                if (data) {

                    $('#area_id')
                        .empty()
                        .html(data);
                    // .trigger('change');
                }
            }
        });
    }
}

function fnAjaxGetBranch() {

    areaId = $('#area_id').val();

    if (areaId != null) {

        $.ajax({
            method: "GET",
            url: "{{ url('ajaxGetBranch') }}",
            dataType: "text",
            data: {
                areaId: areaId,
            },
            success: function(data) {

                if (data) {

                    $('#branch_id')
                        .empty()
                        .html(data);
                    // .trigger('change');
                }
            }
        });
    }
}
</script>
@endsection
