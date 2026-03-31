@extends('Layouts.erp_master_full_width')
@section('content')

<?php 
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
?>

<style type="text/css">
    .dataTable thead th ,.dataTable tbody td {
        padding: 3px;
    }
</style>

<!-- Page -->
<script type="text/javascript" src="{{ asset('assets/js/pdf/tableExport.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.plugin.autotable.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/xlsx.core.min.js') }}"></script>
<?php
use App\Services\HrService as HRS;
$start_date = Common::systemCurrentDate();
$end_date = Common::systemCurrentDate();


$companyID = Common::getCompanyId();
$today     = (new DateTime())->format('Y-m-d'); 

$projectData = Common::ViewTableOrder('gnl_projects', 
            [['is_delete', 0], ['is_active', 1], 
            ['project_name', '!=', '']], 
            ['id', 'project_name','project_code'], 
            ['project_name', 'ASC']);

$projectTypeData = Common::ViewTableOrder('gnl_project_types', 
            [['is_delete', 0], ['is_active', 1], 
            ['project_type_name', '!=', '']], 
            ['id', 'project_type_name'], 
            ['project_type_name', 'ASC']);

$ledgerData = Common::ViewTableOrder('acc_account_ledger', 
            [['is_delete', 0], ['is_active', 1],['is_group_head', 0]], 
            ['id', 'name','code'], 
            ['name', 'ASC']);

$voucherTypeData = Common::ViewTableOrder('acc_voucher_type', 
            [['is_delete', 0],['is_active', 1]], 
            ['id', 'short_name'], 
            ['short_name', 'ASC']);

$fiscalYearData = Common::ViewTableOrder('gnl_fiscal_year', 
            [['is_delete', 0],['is_active', 1],['company_id',$companyID]], 
            ['id', 'fy_name','fy_start_date','fy_end_date'], 
            ['fy_name', 'ASC']);

$current_fiscal_year = DB::table('gnl_fiscal_year')
                    ->select('id','fy_start_date')
                    ->where('company_id',$companyID)
                    ->where('fy_start_date', '<=', $today)
                    ->where('fy_end_date', '>=', $today)
                    ->orderBy('id', 'DESC')
                    ->first();

$levelData = DB::table('acc_account_ledger')
                    ->select('id','level')
                    ->where([['is_delete', 0],['is_active', 1]])
                    ->groupBy('level')
                    ->get();

$BranchID = Common::getBranchId();

$branchInfo = Common::ViewTableFirst('gnl_branchs', [['is_delete', 0], ['is_active', 1], ['id', $BranchID]], ['id', 'branch_name']);
$groupInfo = Common::ViewTableFirst('gnl_groups', [['is_delete', 0], ['is_active', 1]], ['id', 'group_name']);



?>
<input type="hidden" name="" id="start_date_cy" value="{{ (new DateTime($current_fiscal_year->fy_start_date))->format('d-m-Y')  }}">
<div class="w-full d-print-none">
    <div class="panel">
        <div class="panel-body">

            <div class="row align-items-center pb-10 d-print-none">

                <div class="col-lg-1">
                    <label class="input-title">Project</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="project_id" id="project_id">
                            @foreach ($projectData as $Row)
                            <option value="{{ $Row->id }}">{{ sprintf("%03d",$Row->project_code) }} - {{ $Row->project_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-1">
                    <label class="input-title">Project Type</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="project_type_id" id="project_type_id">
                            @foreach ($projectTypeData as $Row)
                            <option value="{{ $Row->id }}">{{ $Row->project_type_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {!! HTML::forBranchFeildSearch() !!}

                <div class="col-lg-1">
                    <label class="input-title">Depth Level</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="depth_level" id="depth_level">
                            <option>All</option>
                            @foreach ($levelData as $Row)
                            <option value="{{ $Row->level }}">Level-{{ $Row->level }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-1">
                    <label class="input-title">Round Up</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="round_up" id="round_up">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                </div>

                <div class="col-lg-1">
                    <label class="input-title">'0' Balance</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="zero_balance" id="zero_balance">
                            <option value="1">Yes</option>
                            <option value="2">No</option>
                        </select>
                    </div>
                </div>

                <div class="col-lg-1">
                    <label class="input-title">Search By</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" id="search_by">
                            <option value="">Select</option>
                            <option value="1">Fiscal Year</option>
                            <option value="2">Current Year</option>
                            <option value="3">Date Range</option>
                        </select>
                    </div>
                </div>

                <!-- Select box for fiscal Year [Option 1]-->
                <div class="col-lg-1" style="display: none" id="fyDiv">
                    <label class="input-title">Fiscal Year</label>
                    <div class="input-group">
                        <select class="form-control" name="fiscal_year" id="fiscal_year">
                            <option value="">Select</option>
                            @foreach ($fiscalYearData as $Row)
                            <option value="{{ $Row->id }}" data-startdate = "{{ (new DateTime($Row->fy_start_date))->format('d-m-Y') }}" 
                                data-enddate = "{{ (new DateTime($Row->fy_end_date))->format('d-m-Y') }}">
                                {{ $Row->fy_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- End Date Datepicker for current year [Option 2]--->
                <div class="col-lg-1" style="display: none" id="endDateDivCY">
                    <label class="input-title">Date To</label>
                    <div class="input-group">
                        <input type="text" class="form-control datepicker-custom" id="end_date_cy" name="end_date_cy"
                            placeholder="DD-MM-YYYY" autocomplete="off">
                    </div>
                </div>

                <!-- Start Date Datepicker for Date Range [Option 3]--->
                <div class="col-lg-1" style="display: none" id="startDateDivDR">
                    <label class="input-title">Start Date</label>
                    <div class="input-group">
                        <input type="text" class="form-control datepicker-custom" id="start_date_dr" name="start_date_dr"
                            placeholder="DD-MM-YYYY" autocomplete="off">
                    </div>
                </div>

                <!-- End Date Datepicker for Date Range [Option 3]--->
                <div class="col-lg-1" style="display: none" id="endDateDivDR">
                    <label class="input-title">End Date</label>
                    <div class="input-group">
                        <input type="text" class="form-control datepicker-custom" id="end_date_dr" name="end_date_dr"
                            placeholder="DD-MM-YYYY" autocomplete="off">
                    </div>
                </div>

            </div>

            <div class="row align-items-center pb-10 d-print-none">
                <div class="col-lg-12 pt-20 text-center">
                    <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round"
                        id="trialBalanceSearch">Search</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="w-full show" style="min-height: calc(100% - 44px);display: none;">
    <div class="panel">
        <div class="panel-body pdf-export">

            <div class="row text-center  d-print-block">
                <div class="col-lg-12" style="color:#000;">
                    <strong>{{ $groupInfo->group_name }}</strong><br>
                    <strong>{{ $branchInfo->branch_name }}</strong><br>
                    <span>Statement of Comprehensive Income</span><br>
                    As On <span class="title_date">{{ $end_date }}</span>
                    <!-- (<span class="start_date_txt">{{ $start_date }}</span>
                    to
                    <span class="end_date_txt">{{ $end_date }}</span>) -->
                </div>
            </div>
            <div class="row d-print-none text-right" data-html2canvas-ignore="true">
                <div class="col-lg-12">
                    <a href="javascript:void(0)" onClick="window.print();"
                        style="background-color:transparent;border:none;" class="btnPrint mr-2">
                        <i class="fa fa-print fa-lg" style="font-size:20px;"></i>
                    </a>
                    <a href="javascript:void(0)" style="background-color:transparent;border:none;"
                        onclick="getPDF();">
                        <i class="fa fa-file-pdf-o fa-lg" style="font-size:20px;"></i>
                    </a>
                    <a href="javascript:void(0)" style="background-color:transparent;border:none;"
                        onclick="fnDownloadXLSX();">
                        <i class="fa fa-file-excel-o fa-lg" style="font-size:20px; margin-left: 5px;"></i>
                        {{-- <i class="fa fa-file-pdf-o fa-lg" style="font-size:20px;"></i> --}}
                    </a>
                </div>
            </div>

            <div class="row">       

                <div class="col-lg-12" style="font-size: 12px;">
                    <span style="color: black; float: right;">
                        <span style="font-weight: bold;">Reporting Date: </span>
                        <span class="start_date_txt">{{$start_date}}</span> to 
                        <span class="end_date_txt">{{ $end_date }}</span>
                    </span>

                    <br>
                    <span>
                        <span style="color: black;" class="float-left">
                            <span style="font-weight: bold;">Project Name: </span>
                            <span id="projectName"></span>
                        </span>
                    </span>
                    <br>

                    <span>
                        <span style="color: black;" class="float-left">
                            <span style="font-weight: bold;">Project Type: </span>
                            <span id="projectTypeName"></span>
                        </span>
                        <span style="color: black;"class="float-right">
                            <span style="font-weight: bold;">Printed Date: </span>
                            <span>{{ (new DateTime())->format('d-m-Y') }}</span>
                        </span>
                    </span>
                    <br>

                    <span>
                        <span style="color: black;" class="float-left">
                            <span style="font-weight: bold;">Branch Name: </span>
                            <span id="branchName"></span>
                        </span>
                    </span>

                </div>
            </div>

            <div class="row mt-4">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead class="text-center">
                            <tr>
                                <!-- <th rowspan="2" width="3%" >SL</th> -->
                                <th rowspan="2" width="35%">Particulars</th>
                                <th rowspan="2">Notes</th>
                                <!-- <th colspan="2">Balance at the beginning</th> -->
                                <th colspan="2">This Month</th>
                                <th colspan="2">This Year</th>
                                <th colspan="2">Cumulative</th>
                            </tr>
                            <tr>
                                <th>Balance</th>
                                <th>Dr/Cr</th>
                                <th>Balance</th>
                                <th>Dr/Cr</th>
                                <th>Balance</th>
                                <th>Dr/Cr</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
<script>
function ajaxDataLoad(fiscal_year = null, start_date_fy = null,  end_date_fy = null, start_date_cy = null, end_date_cy = null,
    start_date_dr = null, end_date_dr = null, project_id = null, project_type_id = null, branch_id = null , selected = null, 
    depth_level = null, round_up = null, zero_balance = null) {

    const Table = $('.clsDataTable').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        // lengthMenu: [[10, 20, 30, 50, -1], [10, 20, 30, 50, "All"]],
        paging: false,
        ordering: false,
        info: false,
        searching: false,
        "ajax": {
            "url": "{{route('IncomeStatementDatatable')}}",
            "dataType": "json",
            "type": "post",
            "data": {
                _token: "{{ csrf_token() }}",
                fiscalYear : fiscal_year,
                startDateFY : start_date_fy,
                endDateFY : end_date_fy,
                startDateCY : start_date_cy,
                endDateCY : end_date_cy,
                startDateDR : start_date_dr,
                endDateDR : end_date_dr,
                projectID: project_id,
                projectTypeID: project_type_id,
                branchID: branch_id,
                selected: selected,
                depth_level: depth_level,
                round_up: round_up,
                zero_balance:zero_balance
            }
        },
        columns: [
            {
                data: 'particular_name',
                "width": "35%"
            },
            {
                data: 'notes',
            },
            {
                data: 'balance_month_txt',
                className: 'text-right'
            },
            {
                data: 'month_dr_cr',
                className: 'text-right'
            },
            {
                data: 'balance_dur_txt',
                className: 'text-right'
            },
            {
                data: 'current_dr_cr',
                className: 'text-right'
            },
            {
                data: 'closing_balance_txt',
                className: 'text-right'
            },
            {
                data: 'closing_dr_cr',
                className: 'text-right'
            },
        ],
        "columnDefs": [
            { "visible" : false, "targets": [2,3] }
        ],
        drawCallback: function(oResult) {
            //  console.log(oResult.json.totalRow);
            if (oResult.json) {

                if (selected == 2) {
                    Table.columns([2,3]).visible(true);
                }
                if (selected == 3) {
                    Table.columns([2,3]).visible(true);
                    Table.columns([4,5]).visible(false);
                }
            }
        }

    });
}

$(document).ready(function() {


    $('#start_date_dr').datepicker({
        dateFormat: 'dd-mm-yy',
        orientation: 'bottom',
        autoclose: true,
        todayHighlight: true,
        changeMonth: true,
        changeYear: true,
        yearRange: '1900:+10',
        onClose: function( selectedDate ) {
            $( "#end_date_dr" ).datepicker( "option", "minDate", selectedDate );
        }
    });

    $("#end_date_dr").datepicker({
        dateFormat: 'dd-mm-yy',
        orientation: 'bottom',
        autoclose: true,
        todayHighlight: true,
        changeMonth: true,
        changeYear: true,
        yearRange: '1900:+10',
        onClose: function( selectedDate ) {
            $( "#start_date_dr" ).datepicker( "option", "maxDate", selectedDate );
        }
    });


    $("#end_date_cy").datepicker({
        dateFormat: 'dd-mm-yy',
        orientation: 'bottom',
        autoclose: true,
        changeMonth: true,
        changeYear: true,
        yearRange: '1900:+10',
        maxDate : 0
    }).keydown(false);

    $('#fiscal_year').select2({'width': '100%'});

    var selected = '';
    $('#search_by').change(function() {
        selected = $(this).val();
        if(selected == 1){
            $('#endDateDivCY,#startDateDivDR,#endDateDivDR').hide('fast');
            $('#fyDiv').show('slow');
        }
        else if(selected == 2){
            $('#fyDiv,#startDateDivDR,#endDateDivDR').hide('fast');
            $('#endDateDivCY').show('slow');
        }
        else if(selected == 3){
            $('#fyDiv,#endDateDivCY').hide('fast');
            $('#startDateDivDR,#endDateDivDR').show('slow');
        }
        else {
            $('#fyDiv,#endDateDivCY').hide('');
        }
    });


    $('#trialBalanceSearch').click(function() {

        
        var start_date_cy = $('#start_date_cy').val();
        var project_id = $('#project_id').val();
        var project_type_id = $('#project_type_id').val();
        var branch_id = $('#branch_id').val();
        var depth_level = $('#depth_level').val();
        var round_up = $('#round_up').val();
        var zero_balance = $('#zero_balance').val();

        if (selected == '') {
            swal({
                icon: 'warning',
                title: 'Warning',
                text: 'Please select an item from Search By',
            });
            return false;
        }
        else if (selected == 1) {

            var fiscal_year = $('#fiscal_year :selected').val();

            if (fiscal_year == '') {
                swal({
                    icon: 'warning',
                    title: 'Warning',
                    text: 'Please select Fiscal Year',
                });
                return false;
            }

            var start_date_fy = $('#fiscal_year :selected').data('startdate');
            var end_date_fy = $('#fiscal_year :selected').data('enddate');
            $('.start_date_txt').html(start_date_fy);
            $('.end_date_txt').html(end_date_fy);

            $('.title_date').html(end_date_fy);


            $('.show').show('slow');
        }

        else if (selected == 2) {
            
            var end_date_cy = $('#end_date_cy').val();

            if (end_date_cy == '') {
                swal({
                    icon: 'warning',
                    title: 'Warning',
                    text: 'Please select Date',
                });
                return false;
            }

            $('.start_date_txt').html(start_date_cy);
            $('.end_date_txt').html(end_date_cy);

            $('.title_date').html(end_date_cy);

            $('.show').show('slow');
            // Table.columns( [2,3] ).visible( true );
        }

        else if (selected == 3) {
            
            var start_date_dr = $('#start_date_dr').val();
            var end_date_dr = $('#end_date_dr').val();

            if (start_date_dr == '' || end_date_dr == '') {
                swal({
                    icon: 'warning',
                    title: 'Warning',
                    text: 'Please select Date',
                });
                return false;
            }

            $('.start_date_txt').html(start_date_dr);
            $('.end_date_txt').html(end_date_dr);

            $('.title_date').html(end_date_dr);

            $('.show').show('slow');
        }



        // Set value in html
        
        // $('#ledgerHead').html($('#ledger_id option:selected').text());
        $('#projectName').html($('#project_id option:selected').text());
        $('#projectTypeName').html($('#project_type_id option:selected').text());
        $('#branchName').html($('#branch_id option:selected').text());
        
        ajaxDataLoad(fiscal_year, start_date_fy,end_date_fy, start_date_cy, end_date_cy, start_date_dr, end_date_dr, 
        project_id, project_type_id, branch_id,selected, depth_level, round_up, zero_balance);
    });
});

// purchase report
function fnDownloadXLSX() {
    $('.clsDataTable').tableExport({
        type: 'xlsx',
        fileName: 'Income Statement Report',
        displayTableName: true,

        // ignoreColumn: [1, 4],
    });
}
function getPDF() {

    var HTML_Width = $(".pdf-export").width();
    var HTML_Height = $(".pdf-export").height();
    var top_left_margin = 15;
    var PDF_Width = HTML_Width + (top_left_margin * 2);
    var PDF_Height = (PDF_Width * 1.5) + (top_left_margin * 2);
    var canvas_image_width = HTML_Width;
    var canvas_image_height = HTML_Height;

    var totalPDFPages = Math.ceil(HTML_Height / PDF_Height) - 1;


    html2canvas($(".pdf-export")[0], {
        allowTaint: true
    }).then(function(canvas) {
        canvas.getContext('2d');

        console.log(canvas.height + "  " + canvas.width);


        var imgData = canvas.toDataURL("image/jpeg", 1.0);
        var pdf = new jsPDF('p', 'pt', [PDF_Width, PDF_Height]);
        pdf.addImage(imgData, 'JPG', top_left_margin, top_left_margin, canvas_image_width,
            canvas_image_height);


        for (var i = 1; i <= totalPDFPages; i++) {
            pdf.addPage(PDF_Width, PDF_Height);
            pdf.addImage(imgData, 'JPG', top_left_margin, -(PDF_Height * i) + (top_left_margin * 4),
                canvas_image_width, canvas_image_height);
        }

        pdf.save("Trial_Balance_Report.pdf");
    });
};
</script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/tableExport.min.js') }}"></script>

<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.debug.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.plugin.autotable.js') }}"></script>

<script type="text/javascript" src="{{ asset('assets/js/pdf/xlsx.core.min.js') }}"></script>
<!-- <script type="text/javascript" src="{{ asset('assets/js/pdf/FileSaver.min.js') }}"></script> -->


<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.3/jspdf.min.js"></script> -->
<script src="https://html2canvas.hertzen.com/dist/html2canvas.js"></script>
@endsection
