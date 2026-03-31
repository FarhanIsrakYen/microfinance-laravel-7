@extends('Layouts.erp_master_full_width')
@section('content')
<script type="text/javascript" src="{{ asset('assets/js/pdf/tableExport.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.plugin.autotable.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/xlsx.core.min.js') }}"></script>

<!-- Page -->
<?php
use App\Services\CommonService as Common;
$branchId = Auth::user()->branch_id;
$branchInfo = Common::ViewTableFirst('gnl_branchs',
    [['is_delete', 0], ['is_active', 1],
        ['id', Auth::user()->branch_id]],
    ['id', 'branch_name']);
$groupInfo = Common::ViewTableFirst('gnl_groups',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'group_name']);
?>


<div class="panel">
    <div class="panel-body">
        <div class="row align-items-center pb-10 d-print-none">
            @if($branchId == 1)
            <div class="col-lg-2">
                <label class="input-title">Branch</label>
                <div class="input-group">
                        <select class="form-control clsSelect2" name="branch_id" id="branch_id">
                        <option value="">Select All</option>
                        @foreach ($branchData as $row)
                        <option value="{{ $row->id }}">
                            {{ sprintf("%04d", $row->branch_code) . "-" . $row->branch_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-lg-2">
                <label class="input-title">Samity</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" name="samity_id" id="samity_id">
                        <option value="">All</option>
                    </select>
                </div>
            </div>

            @else
            <div class="col-lg-2">
                <label class="input-title">Samity</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" name="samity_id" id="samity_id"
                    >
                        <option value="">All</option>
                        @foreach ($samityData as $row)
                        <option value="{{ $row->id }}">{{  $row->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endif

            <div class="col-lg-2">
                <label class="input-title">Month</label>
                <div class="input-group">
                    <!-- monthYearPicker -->
                    <input type="text" class="form-control" id="month_year" name="month_year" placeholder="MM-YYYY"
                        autocomplete="off">
                </div>
            </div>

            <div class="col-lg-2">
                <label class="input-title">Funding Organization</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" name="funding_org" id="funding_org">
                        <option value="">All</option>
                        @foreach ($fundingOrgData as $row)
                        <option value="{{ $row->id }}">{{  $row->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

        </div>
        <div class="row d-print-none">
            <div class="col-lg-12 pt-20 text-center">
                <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round"
                    id="searchButton">Search</a>
            </div>
        </div>
        <div class="row d-print-none text-right" style="display: none" id="printPDF">
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
        <div class="row text-center d-print-block mt-4" id="reportTitle" style="display: none">
            <div class="col-lg-12" style="color:#000;">
                <strong>{{ $groupInfo->group_name }}</strong><br>
                <strong>{{ $branchInfo->branch_name }}</strong><br>
                <span>Loan Statement (Recoverable Calculation)</span><br>
            </div>
        </div>
        <div class="row" id="ttlRow" style="display: none">
            <div class="col-xl-6 col-lg-6 col-sm-6 col-md-6 col-6">
                <b>Branch Name:</b> <span id="branchName"></span> <br>
                <b>Branch Address:</b> <span id="branchAddress"></span> <br>
                <b>Samity:</b> <span id="samityName"></span> <br>
            </div>
            <div class="col-xl-6 col-lg-6 col-sm-6 col-md-6 col-6 text-right">
                <span><b>Reporting Month-Year:</b> <span id="month_txt"></span></span> <br>
                <span><b>Printed Date:</b> {{ (new Datetime())->format('d-m-Y') }} </span>
            </div>
        </div>

        <div class="row" id="table2" style="display: none">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped prTable">
                        <thead class="text-center">
                            <tr id="rowClass1">
                                <th rowspan="2" width="15%">Week No</th>
                                <th rowspan="2" width="15%">Description</th>
                            </tr>
                        </thead>
                        <tbody id="prTable">
                            <tr>
                                <td rowspan="7" width="10%" class="text-center">1st Week <br>(<span class="m_font_size"
                                        id="firstWeek"></span>
                                    to <span class="m_font_size" id="endfirstWeek"></span>)</td>
                            </tr>
                            <tr id="ttlDisburseFW"><td>Total Disburse Upto Last Week</td></tr>
                            <tr id="disburseFW"><td>Disbursement at last week</td></tr>
                            <tr id="fullyPaidFW"><td>Total Fully Paid Upto Last Week</td></tr>
                            <tr id="overDueFW"><td>Total Over Due Loan</td></tr>
                            <tr id="currentLoanFW"><td>Regular Current Loan</td></tr>
                            <tr id="collectableFW"><td>Net Weekly Collectable</td></tr>
                            <tr>
                                <td rowspan="7" width="10%" class="text-center">2nd Week<br>(<span class="m_font_size"
                                        id="secondWeek"></span>
                                    to <span class="m_font_size" id="endsecondWeek"></span>)</td>
                            </tr>
                            <tr id="ttlDisburseSW"><td>Total Disburse Upto Last Week</td></tr>
                            <tr id="disburseSW"><td>Disbursement at last week</td></tr>
                            <tr id="fullyPaidSW"><td>Total Fully Paid Upto Last Week</td></tr>
                            <tr id="overDueSW"><td>Total Over Due Loan</td></tr>
                            <tr id="currentLoanSW"><td>Regular Current Loan</td></tr>
                            <tr id="collectableSW"><td>Net Weekly Collectable</td></tr>
                            <tr>
                                <td rowspan="7" width="10%" class="text-center">3rd Week<br>(<span class="m_font_size"
                                        id="thirdWeek"></span> to
                                    <span class="m_font_size" id="endthirdWeek"></span>)</td>
                            </tr>
                            <tr id="ttlDisburseTW"><td>Total Disburse Upto Last Week</td></tr>
                            <tr id="disburseTW"><td>Disbursement at last week</td></tr>
                            <tr id="fullyPaidTW"><td>Total Fully Paid Upto Last Week</td></tr>
                            <tr id="overDueTW"><td>Total Over Due Loan</td></tr>
                            <tr id="currentLoanTW"><td>Regular Current Loan</td></tr>
                            <tr id="collectableTW"><td>Net Weekly Collectable</td></tr>
                            <tr>
                                <td rowspan="7" width="10%" class="text-center">4th Week<br>(<span class="m_font_size"
                                        id="forthWeek"></span> to
                                    <span class="m_font_size" id="endforthWeek"></span>)</td>
                            </tr>
                            <tr id="ttlDisburseFRW"><td>Total Disburse Upto Last Week</td></tr>
                            <tr id="disburseFRW"><td>Disbursement at last week</td></tr>
                            <tr id="fullyPaidFRW"><td>Total Fully Paid Upto Last Week</td></tr>
                            <tr id="overDueFRW"><td>Total Over Due Loan</td></tr>
                            <tr id="currentLoanFRW"><td>Regular Current Loan</td></tr>
                            <tr id="collectableFRW"><td>Net Weekly Collectable</td></tr>
                            <tr>
                                <td rowspan="7" width="10%" class="text-center" id="apWeek">5th Week<br>(<span class="m_font_size"
                                        id="fifthWeek"></span> to <span class="m_font_size" id="endFifthWeek"></span>)
                                </td>
                            </tr>
                            <tr id="ttlDisburseFVW"><td>Total Disburse Upto Last Week</td></tr>
                            <tr id="disburseFVW"><td>Disbursement at last week</td></tr>
                            <tr id="fullyPaidFVW"><td>Total Fully Paid Upto Last Week</td></tr>
                            <tr id="overDueFVW"><td>Total Over Due Loan</td></tr>
                            <tr id="currentLoanFVW"><td>Regular Current Loan</td></tr>
                            <tr id="collectableFVW"><td>Net Weekly Collectable</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row" id="noDataTable" style="display: none">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped noDataTable">
                        <thead class="text-center">
                            <tr id="rowClass1">
                                <th rowspan="2" width="15%">Week No</th>
                                <th rowspan="2" width="15%">Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="2" class="text-center">No Data Found</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- End Page -->

<script>

function ajaxDataLoad2(branch_id = null, samity_id = null, month_year = null, funding_org =null, firstWeek, endfirstWeek, secondWeek, endsecondWeek,thirdWeek, endthirdWeek, forthWeek, endforthWeek, fifthWeek, endFifthWeek, lastWeek, endLastWeek) {

    var $rows = $('table tr.addedData');
    if ($rows.length > 0) { 
        $rows.remove(); 
    }

    var $cols = $('table th.addedData');
    if ($cols.length > 0) { 
        $cols.remove(); 
    }

    $.ajax({
        method: "GET",
        dataType: "json",
        url: "{{route('loanStatementDataTble')}}",
        data: {
                branchId: branch_id,
                samityId: samity_id,
                monthYear : month_year,
                fundingOrg: funding_org,
                firstWeek: firstWeek,
                endfirstWeek: endfirstWeek,
                secondWeek: secondWeek,
                endsecondWeek: endsecondWeek,
                thirdWeek: thirdWeek,
                endthirdWeek: endthirdWeek,
                forthWeek: forthWeek,
                endforthWeek: endforthWeek,
                fifthWeek: fifthWeek,
                endFifthWeek: endFifthWeek,
                lastWeek : lastWeek,
                endLastWeek : endLastWeek
            },
        success: function (data) {
            if (data.data == '') {
                $('#table2').hide();
                $('#noDataTable').show();
            }
            var html = '';
            var ttlDisburseFW, disburseFW, fullyPaidFW,overDueFW, currentLoanFW, collectableFW = '';
            var ttlDisburseSW, disburseSW, fullyPaidSW,overDueSW, currentLoanSW, collectableSW = '';
            var ttlDisburseTW, disburseTW, fullyPaidTW,overDueTW, currentLoanTW, collectableTW = '';
            var ttlDisburseFRW, disburseFRW, fullyPaidFRW,overDueFRW, currentLoanFRW, collectableFRW = '';
            var ttlDisburseFVW, disburseFVW, fullyPaidFVW,overDueFVW, currentLoanFVW, collectableFVW = '';

            $.each(data.data, function( key, obj) {
                html += '<th class="addedData" colspan= '+2+'>'+obj[0].product+'</th>';
            });
                html += '<th class="addedData" rowspan= '+2+'>'+ 'Total'+'</th>';
            $("#rowClass1").append(html);

            var i = parseInt(0);
            $.each(data.data, function( key, obj) {
                    if (i == 0) {
                        html = "<tr class ='gender addedData'><th>Male</th><th>Female</th></tr>";
                        $("#rowClass1").after(html);
                    }
                    else {
                        html = "<th class='addedData'>Male</th><th class='addedData'>Female</th>";
                        $(".gender").append(html);
                    }
                    i++;
            });

            $.each(data.data, function( key, obj) {
                //-------------------------- First Week Data ---------------------------------------//
                   ttlDisburseFW += '<th class = "text-right addedData">'+obj[0].ttlDisburseMaleFW+'</th>';
                   ttlDisburseFW += '<th class = "text-right addedData">'+obj[0].ttlDisburseFemaleFW+'</th>';

                   disburseFW += '<th class = "text-right addedData">'+obj[0].disburseLastMaleFW+'</th>';
                   disburseFW += '<th class = "text-right addedData">'+obj[0].disburseLastFemaleFW+'</th>'; 

                   fullyPaidFW += '<th class = "text-right addedData">'+obj[0].fullyPaidMaleFW+'</th>';
                   fullyPaidFW += '<th class = "text-right addedData">'+obj[0].fullyPaidFemaleFW+'</th>';

                   overDueFW += '<th class = "text-right addedData">'+obj[0].overDueLoanMaleFW+'</th>';
                   overDueFW += '<th class = "text-right addedData">'+obj[0].overDueLoanFemaleFW+'</th>';

                   currentLoanFW += '<th class = "text-right addedData">'+obj[0].currentLoanMaleFW+'</th>';
                   currentLoanFW += '<th class = "text-right addedData">'+obj[0].currentLoanFemaleFW+'</th>'; 

                   collectableFW += '<th class = "text-right addedData">'+obj[0].weeklyCollMaleFW+'</th>';
                   collectableFW += '<th class = "text-right addedData">'+obj[0].weeklyCollFemaleFW+'</th>';

                //-------------------------- Second Week Data ---------------------------------------//
                   ttlDisburseSW += '<th class = "text-right addedData">'+obj[0].ttlDisburseMaleSW+'</th>';
                   ttlDisburseSW += '<th class = "text-right addedData">'+obj[0].ttlDisburseFemaleSW+'</th>';

                   disburseSW += '<th class = "text-right addedData">'+obj[0].disburseLastMaleSW+'</th>';
                   disburseSW += '<th class = "text-right addedData">'+obj[0].disburseLastFemaleSW+'</th>'; 

                   fullyPaidSW += '<th class = "text-right addedData">'+obj[0].fullyPaidMaleSW+'</th>';
                   fullyPaidSW += '<th class = "text-right addedData">'+obj[0].fullyPaidFemaleSW+'</th>';

                   overDueSW += '<th class = "text-right addedData">'+obj[0].overDueLoanMaleSW+'</th>';
                   overDueSW += '<th class = "text-right addedData">'+obj[0].overDueLoanFemaleSW+'</th>';

                   currentLoanSW += '<th class = "text-right addedData">'+obj[0].currentLoanMaleSW+'</th>';
                   currentLoanSW += '<th class = "text-right addedData">'+obj[0].currentLoanFemaleSW+'</th>'; 

                   collectableSW += '<th class = "text-right addedData">'+obj[0].weeklyCollMaleSW+'</th>';
                   collectableSW += '<th class = "text-right addedData">'+obj[0].weeklyCollFemaleSW+'</th>';

                //-------------------------- Third Week Data ---------------------------------------//
                   ttlDisburseTW += '<th class = "text-right addedData">'+obj[0].ttlDisburseMaleTW+'</th>';
                   ttlDisburseTW += '<th class = "text-right addedData">'+obj[0].ttlDisburseFemaleTW+'</th>';

                   disburseTW += '<th class = "text-right addedData">'+obj[0].disburseLastMaleTW+'</th>';
                   disburseTW += '<th class = "text-right addedData">'+obj[0].disburseLastFemaleTW+'</th>'; 

                   fullyPaidTW += '<th class = "text-right addedData">'+obj[0].fullyPaidMaleTW+'</th>';
                   fullyPaidTW += '<th class = "text-right addedData">'+obj[0].fullyPaidFemaleTW+'</th>';

                   overDueTW += '<th class = "text-right addedData">'+obj[0].overDueLoanMaleTW+'</th>';
                   overDueTW += '<th class = "text-right addedData">'+obj[0].overDueLoanFemaleTW+'</th>';

                   currentLoanTW += '<th class = "text-right addedData">'+obj[0].currentLoanMaleTW+'</th>';
                   currentLoanTW += '<th class = "text-right addedData">'+obj[0].currentLoanFemaleTW+'</th>'; 

                   collectableTW += '<th class = "text-right addedData">'+obj[0].weeklyCollMaleTW+'</th>';
                   collectableTW += '<th class = "text-right addedData">'+obj[0].weeklyCollFemaleTW+'</th>';

                //-------------------------- Fourth Week Data ---------------------------------------//
                   ttlDisburseFRW += '<th class = "text-right addedData">'+obj[0].ttlDisburseMaleFRW+'</th>';
                   ttlDisburseFRW += '<th class = "text-right addedData">'+obj[0].ttlDisburseFemaleFRW+'</th>';

                   disburseFRW += '<th class = "text-right addedData">'+obj[0].disburseLastMaleFRW+'</th>';
                   disburseFRW += '<th class = "text-right addedData">'+obj[0].disburseLastFemaleFRW+'</th>'; 

                   fullyPaidFRW += '<th class = "text-right addedData">'+obj[0].fullyPaidMaleFRW+'</th>';
                   fullyPaidFRW += '<th class = "text-right addedData">'+obj[0].fullyPaidFemaleFRW+'</th>';

                   overDueFRW += '<th class = "text-right addedData">'+obj[0].overDueLoanMaleFRW+'</th>';
                   overDueFRW += '<th class = "text-right addedData">'+obj[0].overDueLoanFemaleFRW+'</th>';

                   currentLoanFRW += '<th class = "text-right addedData">'+obj[0].currentLoanMaleFRW+'</th>';
                   currentLoanFRW += '<th class = "text-right addedData">'+obj[0].currentLoanFemaleFRW+'</th>'; 

                   collectableFRW += '<th class = "text-right addedData">'+obj[0].weeklyCollMaleFRW+'</th>';
                   collectableFRW += '<th class = "text-right addedData">'+obj[0].weeklyCollFemaleFRW+'</th>';

                //-------------------------- Fifth Week Data ---------------------------------------//
                   ttlDisburseFVW += '<th class = "text-right addedData">'+obj[0].ttlDisburseMaleFVW+'</th>';
                   ttlDisburseFVW += '<th class = "text-right addedData">'+obj[0].ttlDisburseFemaleFVW+'</th>';

                   disburseFVW += '<th class = "text-right addedData">'+obj[0].disburseLastMaleFVW+'</th>';
                   disburseFVW += '<th class = "text-right addedData">'+obj[0].disburseLastFemaleFVW+'</th>'; 

                   fullyPaidFVW += '<th class = "text-right addedData">'+obj[0].fullyPaidMaleFVW+'</th>';
                   fullyPaidFVW += '<th class = "text-right addedData">'+obj[0].fullyPaidFemaleFVW+'</th>';

                   overDueFVW += '<th class = "text-right addedData">'+obj[0].ttlDisburseMaleFVW+'</th>';
                   overDueFVW += '<th class = "text-right addedData">'+obj[0].ttlDisburseFemaleFVW+'</th>';

                   currentLoanFVW += '<th class = "text-right addedData">'+obj[0].currentLoanMaleFVW+'</th>';
                   currentLoanFVW += '<th class = "text-right addedData">'+obj[0].currentLoanFemaleFVW+'</th>'; 

                   collectableFVW += '<th class = "text-right addedData">'+obj[0].weeklyCollMaleFVW+'</th>';
                   collectableFVW += '<th class = "text-right addedData">'+obj[0].weeklyCollFemaleFVW+'</th>';

            });

            ttlDisburseFW += '<th class="text-right addedData">'+ data.ttlDisburseFW +'</th>';
            disburseFW += '<th class="text-right addedData">'+ data.ttlDisburseLastFW +'</th>';
            fullyPaidFW += '<th class="text-right addedData">'+ data.ttlfullyPaidFW +'</th>';
            overDueFW += '<th class="text-right addedData">'+ data.ttlOverDueLoanFW +'</th>';
            currentLoanFW += '<th class="text-right addedData">'+ data.ttlcurrentLoanFW +'</th>';
            collectableFW += '<th class="text-right addedData">'+ data.ttlweeklyCollFW +'</th>';

            ttlDisburseSW += '<th class="text-right addedData">'+ data.ttlDisburseSW +'</th>';
            disburseSW += '<th class="text-right addedData">'+ data.ttlDisburseLastSW +'</th>';
            fullyPaidSW += '<th class="text-right addedData">'+ data.ttlfullyPaidSW +'</th>';
            overDueSW += '<th class="text-right addedData">'+ data.ttlOverDueLoanSW +'</th>';
            currentLoanSW += '<th class="text-right addedData">'+ data.ttlcurrentLoanSW +'</th>';
            collectableSW += '<th class="text-right addedData">'+ data.ttlweeklyCollSW +'</th>';

            ttlDisburseTW += '<th class="text-right addedData">'+ data.ttlDisburseTW +'</th>';
            disburseTW += '<th class="text-right addedData">'+ data.ttlDisburseLastTW +'</th>';
            fullyPaidTW += '<th class="text-right addedData">'+ data.ttlfullyPaidTW +'</th>';
            overDueTW += '<th class="text-right addedData">'+ data.ttlOverDueLoanTW +'</th>';
            currentLoanTW += '<th class="text-right addedData">'+ data.ttlcurrentLoanTW +'</th>';
            collectableTW += '<th class="text-right addedData">'+ data.ttlweeklyCollTW +'</th>';

            ttlDisburseFRW += '<th class="text-right addedData">'+ data.ttlDisburseFRW +'</th>';
            disburseFRW += '<th class="text-right addedData">'+ data.ttlDisburseLastFRW +'</th>';
            fullyPaidFRW += '<th class="text-right addedData">'+ data.ttlfullyPaidFRW +'</th>';
            overDueFRW += '<th class="text-right addedData">'+ data.ttlOverDueLoanFRW +'</th>';
            currentLoanFRW += '<th class="text-right addedData">'+ data.ttlcurrentLoanFRW +'</th>';
            collectableFRW += '<th class="text-right addedData">'+ data.ttlweeklyCollFRW +'</th>';

            ttlDisburseFVW += '<th class="text-right addedData">'+ data.ttlDisburseFVW +'</th>';
            disburseFVW += '<th class="text-right addedData">'+ data.ttlDisburseLastFVW +'</th>';
            fullyPaidFVW += '<th class="text-right addedData">'+ data.ttlfullyPaidFVW +'</th>';
            overDueFVW += '<th class="text-right addedData">'+ data.ttlOverDueLoanFVW +'</th>';
            currentLoanFVW += '<th class="text-right addedData">'+ data.ttlcurrentLoanFVW +'</th>';
            collectableFVW += '<th class="text-right addedData">'+ data.ttlweeklyCollFVW +'</th>';

            $("#ttlDisburseFW").append(ttlDisburseFW);
            $("#disburseFW").append(disburseFW);
            $("#fullyPaidFW").append(fullyPaidFW);
            $("#overDueFW").append(overDueFW);
            $("#currentLoanFW").append(currentLoanFW);
            $("#collectableFW").append(collectableFW);


            $("#ttlDisburseSW").append(ttlDisburseSW);
            $("#disburseSW").append(disburseSW);
            $("#fullyPaidSW").append(fullyPaidSW);
            $("#overDueSW").append(overDueSW);
            $("#currentLoanSW").append(currentLoanSW);
            $("#collectableSW").append(collectableSW);

            $("#ttlDisburseTW").append(ttlDisburseTW);
            $("#disburseTW").append(disburseTW);
            $("#fullyPaidTW").append(fullyPaidTW);
            $("#overDueTW").append(overDueTW);
            $("#currentLoanTW").append(currentLoanTW);
            $("#collectableTW").append(collectableTW);

            $("#ttlDisburseFRW").append(ttlDisburseFRW);
            $("#disburseFRW").append(disburseFRW);
            $("#fullyPaidFRW").append(fullyPaidFRW);
            $("#overDueFRW").append(overDueFRW);
            $("#currentLoanFRW").append(currentLoanFRW);
            $("#collectableFRW").append(collectableFRW);

            $("#ttlDisburseFVW").append(ttlDisburseFVW);
            $("#disburseFVW").append(disburseFVW);
            $("#fullyPaidFVW").append(fullyPaidFVW);
            $("#overDueFVW").append(overDueFVW);
            $("#currentLoanFVW").append(currentLoanFVW);
            $("#collectableFVW").append(collectableFVW);
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert('No Match Found');
        }
    });  
}

$(document).ready(function() {

    $('.clsSelect2').css("width","100%");

    $('#start_date').datepicker({
        dateFormat: 'dd-mm-yy',
        orientation: 'bottom',
        autoclose: true,
        todayHighlight: true,
        changeMonth: true,
        changeYear: true,
        yearRange: '1900:+10',
        onClose: function (selectedDate) {
            $("#end_date").datepicker("option", "minDate", selectedDate);
        }
    });

    $("#end_date").datepicker({
        dateFormat: 'dd-mm-yy',
        orientation: 'bottom',
        autoclose: true,
        todayHighlight: true,
        changeMonth: true,
        changeYear: true,
        yearRange: '1900:+10',
        onClose: function (selectedDate) {
            $("#start_date").datepicker("option", "maxDate", selectedDate);
        }
    });

    $('#month_year').datepicker({
        dateFormat: 'MM-yy',
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        todayButton: false,
        onClose: function(dateText, inst) {
            var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
            var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
            $(this).val($.datepicker.formatDate('MM-yy', new Date(year, month, 1)));
        }
    });

    var branch_id = '';
    $('#branch_id').change(function() {
        branch_id = $(this).val();
        var branchName = $('#branch_id option:selected').text().split('-');
        $('#branchName').html(branchName[1]);
        var branchAddress = '';
        $.ajax({
            type: "POST",
            url: "../getSamities",
            data: {branchId : branch_id},
            dataType: "json",
            success: function (samities) {
                $('#samity_id').empty();
                $('#samity_id').append("<option value=''>"+ 'All'+"</option>");
                if (samities != '') {
                    $.each(samities, function (index, samity) {
                        $('#samity_id').append("<option value="+samity.id+">"+samity.name+"</option>");
                    });
                }
                
            },
            error: function(){
                alert('error!');
            }
        });
        $.ajax({
            type: "POST",
            url: "../getBranchAddress",
            data: {branchId : branch_id},
            dataType: "json",
            success: function (address) {

                if (address != '') {
                    $('#branchAddress').html(address.branch_addr);
                }
                
            },
            error: function(){
                alert('error!');
            }
        });
    });

    var samity_id = '';
    $('#samity_id').change(function() {
        samity_id = $(this).val();
        samityName = $('#samity_id option:selected').text();
    });

    var month_year = '';
    $('#month_year').change(function() {
        month_year = $(this).val();
    });

    var funding_org = $('#funding_org').val();
    $('#funding_org').change(function() {
        funding_org = $(this).val();
    });

    $('#searchButton').click(function() {
        if ('<?php echo($branchId); ?>' == 1) {
            if (branch_id == '') {
                swal({
                    icon: 'warning',
                    title: 'Warning',
                    text: 'Please select A branch',
                });
                return false;
            }
        }
        if ('<?php echo($branchId); ?>' != 1) {
           branch_id =  '<?php echo($branchId); ?>';
        }

        if (samity_id == '') {
            swal({
                icon: 'warning',
                title: 'Warning',
                text: 'Please select Samity',
            });
            return false;
        }

        if (month_year == '') {
            swal({
                icon: 'warning',
                title: 'Warning',
                text: 'Please select Date',
            });
            return false;
        }

        var monthYearsel = $('#month_year').val();
        $('#month_txt').html(monthYearsel);

        var getDate = new Date('01-' + monthYearsel);
        var dayOfMonth = new Date(getDate.getFullYear(), getDate.getMonth() + 1, 0).getDate();
        var weekCount = Math.ceil(dayOfMonth / 7);

        var firstWeek = $.datepicker.formatDate('yy-mm-dd', new Date('01-' + monthYearsel));
        var endfirstWeek = $.datepicker.formatDate('yy-mm-dd', new Date('07-' + monthYearsel));

        var secondWeek = $.datepicker.formatDate('yy-mm-dd', new Date('08-' + monthYearsel));
        var endsecondWeek = $.datepicker.formatDate('yy-mm-dd', new Date('14-' + monthYearsel));

        var thirdWeek = $.datepicker.formatDate('yy-mm-dd', new Date('15-' + monthYearsel));
        var endthirdWeek = $.datepicker.formatDate('yy-mm-dd', new Date('21-' + monthYearsel));

        var forthWeek = $.datepicker.formatDate('yy-mm-dd', new Date('22-' + monthYearsel));
        var endforthWeek = $.datepicker.formatDate('yy-mm-dd', new Date('28-' + monthYearsel));

        var fifthWeek = $.datepicker.formatDate('yy-mm-dd', new Date('29-' + monthYearsel));
        var endFifthWeek = $.datepicker.formatDate('yy-mm-dd', new Date(dayOfMonth + '-' +
            monthYearsel));

        var lastWeek = getDate;
        lastWeek.setDate(lastWeek.getDate() - 1);
        lastWeek = $.datepicker.formatDate('yy-mm-dd', lastWeek);

        var endLastWeek = getDate;
        endLastWeek.setDate(endLastWeek.getDate() - 6);
        endLastWeek = $.datepicker.formatDate('yy-mm-dd', endLastWeek);

        // var lastWeek = (function(d){ d.setDate(d.getDate(firstWeek)-1); return d})(new Date);


        $('#firstWeek').html(firstWeek);
        $('#endfirstWeek').html(endfirstWeek);

        $('#secondWeek').html(secondWeek);
        $('#endsecondWeek').html(endsecondWeek);

        $('#thirdWeek').html(thirdWeek);
        $('#endthirdWeek').html(endthirdWeek);

        $('#forthWeek').html(forthWeek);
        $('#endforthWeek').html(endforthWeek);

        $('#fifthWeek').html(fifthWeek);
        $('#endFifthWeek').html(endFifthWeek);

        if (weekCount < 5) {
            $('#apWeek').hide();
            $('#cTable').find('thead tr:first th:last').attr('colspan', '4');
        }

        $('#samityName').html(samityName);
        if(samity_id != '') {
            $('#table2,#ttlRow,#printPDF,#reportTitle').show('slow');
            ajaxDataLoad2(branch_id, samity_id, month_year,funding_org,firstWeek, endfirstWeek, secondWeek, endsecondWeek,
                thirdWeek, endthirdWeek,forthWeek, endforthWeek, fifthWeek, endFifthWeek,lastWeek,endLastWeek);
        }

    });
    
});


function fnDownloadPDF() {
    $('.clsDataTable,.prTable').tableExport({
        type: 'pdf',
        fileName: 'Loan Statement (Recoverable Calculation)',
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
    $('.clsDataTable,.prTable').tableExport({
        type: 'excel',
        fileName: 'Loan Statement (Recoverable Calculation)',
    });
}
</script>
@endsection
