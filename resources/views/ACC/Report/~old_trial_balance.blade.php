@extends('Layouts.erp_master_full_width')
@section('content')

<?php 
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
?>

<style type="text/css">
    .ui-datepicker-calendar{
        display: none;
    }
    #yearlyDiv .ui-datepicker-month{
        display: none;
    }

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

$projectData = Common::ViewTableOrder('gnl_projects', 
            [['is_delete', 0], ['is_active', 1], 
            ['project_name', '!=', '']], 
            ['id', 'project_name'], 
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


$BranchID = Common::getBranchId();

$branchInfo = Common::ViewTableFirst('gnl_branchs', [['is_delete', 0], ['is_active', 1], ['id', $BranchID]], ['id', 'branch_name']);
$groupInfo = Common::ViewTableFirst('gnl_groups', [['is_delete', 0], ['is_active', 1]], ['id', 'group_name']);

?>

<div class="w-full d-print-none">
    <div class="panel">
        <div class="panel-body">

            <div class="row align-items-center pb-10 d-print-none">

                <div class="col-lg-2">
                    <label class="input-title">Project</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="project_id" id="project_id">
                            @foreach ($projectData as $Row)
                            <option value="{{ $Row->id }}">{{ $Row->project_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
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

                <div class="col-lg-2">
                    <label class="input-title">Search By</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" id="search_by">
                            <option value="">Select</option>
                            <option value="1">Monthly</option>
                            <option value="2">Yearly</option>
                        </select>
                    </div>
                </div>

                <div class="col-lg-2" style="display: none" id="monthlyDiv">
                    <label class="input-title">Month</label>
                    <div class="input-group">
                        <!-- monthYearPicker -->
                        <input type="text" class="form-control" id="monthly" name="monthly" placeholder="MM-YYYY"
                            autocomplete="off">
                    </div>
                </div>

                <div class="col-lg-2" style="display: none" id="yearlyDiv">
                    <label class="input-title">Year</label>
                    <div class="input-group">
                        <!-- YearPicker -->
                        <input type="text" class="form-control" id="yearly" name="yearly" placeholder="MM-YYYY"
                            autocomplete="off">
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
                    <span>Trial Balance Report</span><br>
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
                            <span>{{ date("d-m-Y") }}</span>
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
                                <th colspan="2" width="">Balance at the beginning</th>
                                <th colspan="2">During This Period</th>
                                <th colspan="2">Closing Balance(Cumulative)</th>
                            </tr>
                            <tr>
                                <th>Dr</th>
                                <th>Cr</th>
                                <th>Dr</th>
                                <th>Cr</th>
                                <th>Dr</th>
                                <th>Cr</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <td style="text-align: right!important;"><b>Total</b></td>
                                <td class="text-right" id="ttl_debit_sum_beg" style="font-color:#000; font-weight:bold;">
                                    0.00</td>
                                <td class="text-right" id="ttl_credit_sum_beg" style="font-color:#000; font-weight:bold;">
                                    0.00</td>
                                <td class="text-right" id="ttl_debit_sum_this" style="font-color:#000; font-weight:bold;">
                                    0.00</td>
                                <td class="text-right" id="ttl_credit_sum_this" style="font-color:#000; font-weight:bold;">
                                    0.00</td>
                                <td class="text-right" id="ttl_debit_sum_clo" style="font-color:#000; font-weight:bold;">
                                    0.00</td>
                                <td class="text-right" id="ttl_credit_sum_clo" style="font-color:#000; font-weight:bold;">
                                    0.00</td>

                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
<script>
function ajaxDataLoad(monthly = null, yearly = null, startDateM = null, endDateM = null, startDateY = null, endDateY = null,
    project_id = null, project_type_id = null, branch_id = null) {

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
            "url": "{{route('TBalanceReportDatatable')}}",
            "dataType": "json",
            "type": "post",
            "data": {
                _token: "{{ csrf_token() }}",
                monthly : monthly,
                yearly : yearly,
                startDateM : startDateM,
                endDateM : endDateM,
                startDateY : startDateY,
                endDateY : endDateY,
                projectID: project_id,
                projectTypeID: project_type_id,
                branchID: branch_id,
            }
        },
        columns: [
            {
                data: 'particular_name',
                "width": "35%"
            },
            {
                data: 'debit_beg_txt',
                className: 'text-right'
            },
            {
                data: 'credit_beg_txt',
                className: 'text-right'
            },
            {
                data: 'debit_dur_txt',
                className: 'text-right'
            },
            {
                data: 'credit_dur_txt',
                className: 'text-right'
            },
            {
                data: 'debit_clo_txt',
                className: 'text-right'
            },
            {
                data: 'credit_clo_txt',
                className: 'text-right'
            },
        ],

        drawCallback: function(oResult) {
            //  console.log(oResult.json.totalRow);
            if (oResult.json) {
                $('#ttl_debit_sum_beg').html(oResult.json.ttl_debit_beg);
                $('#ttl_credit_sum_beg').html(oResult.json.ttl_credit_beg);

                $('#ttl_debit_sum_this').html(oResult.json.ttl_debit_dur);
                $('#ttl_credit_sum_this').html(oResult.json.ttl_credit_dur);

                $('#ttl_debit_sum_clo').html(oResult.json.ttl_debit_clo);
                $('#ttl_credit_sum_clo').html(oResult.json.ttl_credit_clo);

                // $('#ttl_closing_debit').html(oResult.json.sum(closing_debit));
                // $('#ttl_closing_credit').html(oResult.json.sum(closing_credit));
            }


            // // SUM of Closing Balance Dr
            // Table.columns(5, {
            //     page: 'current'
            //     }).every(function() {

            //         if($('.dataTable tbody tr').hasClass("txHead")){
            //             var sumD = this
            //             .data()
            //             .reduce(function(a, b) {
            //             var x = parseFloat(a) || 0;
            //             var y = parseFloat(b) || 0;
            //             return x + y;
            //             }, 0);
            //         }
                
            //     $('#ttl_closing_debit').html(sumD);
            // });

            // // SUM of Closing Balance Cr
            // Table.columns(6, {
            //     page: 'current'
            //     }).every(function() {

            //         if($('.dataTable tbody tr').hasClass("txHead")){
            //             var sumC = this
            //             .data()
            //             .reduce(function(a, b) {
            //             var x = parseFloat(a) || 0;
            //             var y = parseFloat(b) || 0;
            //             return x + y;
            //             }, 0);
            //         }
                
            //     $('#ttl_closing_credit').html(sumC);
            // });

        },

        // "createdRow": function ( row, data, index ) {
            
        //     if ( data['group_head'] == 1) {
        //         $('td', row).css('font-weight', '580');
        //         $('td', row).css('border-bottom', '1px solid #808080');
        //     }
        //     if ( data['group_head'] == 0) {
        //         $(row).addClass( 'txHead' );
        //     }
            
        // },

    });
}

$(document).ready(function() {
    
    $('#monthly').datepicker({
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

    $('#yearly').datepicker({
        dateFormat: 'yy',
        // changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        todayButton: false,
        onClose: function(dateText, inst) {
            // var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
            var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
            $(this).val($.datepicker.formatDate('yy', new Date(year, 1, 1)));
        }
    });

    var selected = '';
    $('#search_by').change(function() {
        selected = $(this).val();
        if(selected == 1){
            $('#yearlyDiv').hide('fast');
            $('#monthlyDiv').show('slow');
        }
        else if(selected == 2){
            $('#monthlyDiv').hide('fast');
            $('#yearlyDiv').show('slow');
        }
        else {
            $('#monthlyDiv,#yearlyDiv').hide('');
        }
    });


    $('#trialBalanceSearch').click(function() {

        var monthly = $('#monthly').val();
        var yearly = $('#yearly').val();
        var project_id = $('#project_id').val();
        var project_type_id = $('#project_type_id').val();
        var branch_id = $('#branch_id').val();

        // for monthly selction, generate date
        var getDateM = new Date('01-' + monthly);
        var dayOfMonth = new Date(getDateM.getFullYear(), getDateM.getMonth() + 1, 0).getDate();
        var startDateM = $.datepicker.formatDate('dd-mm-yy', new Date('01-' + monthly));
        var endDateM = $.datepicker.formatDate('dd-mm-yy', new Date(dayOfMonth+ '-' + monthly));

        // for yearly selction, generate date
        // var getDateY = new Date('01-01' + yearly);
        var startDateY = $.datepicker.formatDate('dd-mm-yy', new Date('01-01-' + yearly));
        var endDateY = $.datepicker.formatDate('dd-mm-yy', new Date('12-31-' + yearly));

        if (selected == '') {
            swal({
                icon: 'warning',
                title: 'Warning',
                text: 'Please select an item from Search By',
            });
            return false;
        }
        else if (selected == 1) {

            yearly = ''; // set year value to null

            if (monthly == '') {
                swal({
                    icon: 'warning',
                    title: 'Warning',
                    text: 'Please select Month',
                });
                return false;
            }
            $('.start_date_txt').html(startDateM);
            $('.end_date_txt').html(endDateM);

            $('.title_date').html(monthly);


            $('.show').show('slow');
        }

        else if (selected == 2) {
            
            monthly = ''; // set month value to null
            if (yearly == '') {
                swal({
                    icon: 'warning',
                    title: 'Warning',
                    text: 'Please select Year',
                });
                return false;
            }
            $('.start_date_txt').html(startDateY);
            $('.end_date_txt').html(endDateY);

            $('.title_date').html(yearly);

            $('#yearly').focusin(function(){
              $('.ui-datepicker-month').css({ "display": "none" });
            });



            $('.show').show('slow');
        }



        // Set value in html
        
        // $('#ledgerHead').html($('#ledger_id option:selected').text());
        $('#projectName').html($('#project_id option:selected').text());
        $('#projectTypeName').html($('#project_type_id option:selected').text());
        $('#branchName').html($('#branch_id option:selected').text());
        
        ajaxDataLoad(monthly,yearly,startDateM, endDateM, startDateY, endDateY, project_id, project_type_id, branch_id);
    });
});

// purchase report
function fnDownloadXLSX() {
    $('.clsDataTable').tableExport({
        type: 'xlsx',
        fileName: 'Trial Balance Report',
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
