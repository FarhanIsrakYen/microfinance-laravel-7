@extends('Layouts.erp_master_full_width')
@section('content')
<script type="text/javascript" src="{{ asset('assets/js/pdf/tableExport.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.plugin.autotable.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/xlsx.core.min.js') }}"></script>
<style>
    .report-side-component{
        display: none;
    }
</style>
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
                            {{ sprintf("%04d", $row->branch_code) . " - " . $row->branch_name }}
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
                <label class="input-title">Start Date</label>
                <div class="input-group">
                    <input type="text" class="form-control datepicker-custom" id="start_date" name="start_date"
                        placeholder="DD-MM-YYYY" value="" autocomplete="off">
                </div>
            </div>

            <div class="col-lg-2">
                <label class="input-title">End Date</label>
                <div class="input-group">
                    <input type="text" class="form-control datepicker-custom" id="end_date" name="end_date"
                        placeholder="DD-MM-YYYY" value="" autocomplete="off">
                </div>
            </div>
            <div class="col-lg-2 ml-auto">
                <label class="input-title"> &nbsp; </label>
                <div class="input-group">
                    <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round"
                    id="searchButton">Search</a>
                </div>
            </div>

        </div>
        {{-- <div class="row d-print-none">
            <div class="col-lg-12 pt-20 text-center">
                <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round"
                    id="searchButton">Search</a>
            </div>
        </div> --}}
        
        <div class="row d-print-none text-right report-side-component" id="printPDF" >
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
                <span>All Collection Report</span><br>
            </div>
        </div>
        <div class="row report-side-component" id="ttlRow">
            <div class="col-xl-12 col-lg-12 col-sm-12 col-md-12 col-12 text-right">
                <span><b>Printed Date:</b> {{ (new Datetime())->format('d-m-Y') }} </span>
            </div>
        </div>
        {{-- <div class="row" id="table1" style="display: none">
            <div class="col-lg-12" id="table1-content">
                <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                    <thead class="text-center">
                        <tr>
                            <th width="5%">SL</th>
                            <th>Samity</th>
                            <th>Component</th>
                            <th>Opening Balance</th>
                            <th>Deposit</th>
                            <th>Withdraw</th>
                            <th>Closing Balance</th>
                        </tr>
                    </thead>
                    <tbody id="clsDataTable"></tbody>
                    <tfoot>
                        <tr>
                            <td class="text-right" colspan="3"><b>Total:</b></td>
                            <td class="text-right"><b class="ttl_ob">0.00</b></td>
                            <td class="text-right"><b class="ttl_deposit">0.00</b></td>
                            <td class="text-right"><b class="ttl_withdraw">0.00</b></td>
                            <td class="text-right"><b class="ttl_cb">0.00</b></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div> --}}
        
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive" id="table-content">
                    
                </div>
            </div>
        </div>
    </div>
</div>

<!-- End Page -->

<script>

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

    var branch_id = '';
    $('#branch_id').change(function() {
        branch_id = $(this).val();

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
    });

    var samity_id = '';
    $('#samity_id').change(function() {
        samity_id = $(this).val();
    });

    var start_date = '';
    $('#start_date').change(function() {
        start_date = $(this).val();
    });

    var end_date = '';
    $('#end_date').change(function() {
        end_date = $(this).val();
    });

    $('#end_date').datepicker('option', 'minDate', new Date(start_date));
    $('#start_date').datepicker('option', 'maxDate', new Date(end_date));

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

        if (start_date == '') {
            swal({
                icon: 'warning',
                title: 'Warning',
                text: 'Please select Date',
            });
            return false;
        }

        if (end_date == '') {
            swal({
                icon: 'warning',
                title: 'Warning',
                text: 'Please select Date',
            });
            return false;
        }
        
        $('#end_date_txt').html(end_date);
        getReportData(branch_id, samity_id, start_date, end_date);
    });
    
});


function fnDownloadPDF() {
    $('.clsDataTable,.prTable').tableExport({
        type: 'pdf',
        fileName: 'Due Register report',
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
        fileName: 'Due Register report',
    });
}

function getReportData(branch_id, samity_id, start_date, end_date){
    let data= {
        branchId: branch_id,
        samityId: samity_id,
        startDate: start_date,
        endDate: end_date
    };
    
    $("#table-content").empty();
    
    $("#table-content").load("{{route('savingsRegisterReportTable')}}" , data, function(response, status, xhr){

        if (status == 'success') {
            $('.report-side-component').show();
            $('#spinnerId').hide('slow');
        //    console.log(response);
        }
        else{
            alert('error');
            $('#spinnerId').hide();
        }
    });
}
</script>
@endsection