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

$employeeData = Common::ViewTableOrder('hr_employees',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'emp_name'],
    ['emp_name', 'ASC']);

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

<!-- <div class="w-full"> -->

    <div class="panel">
        <div class="panel-body">
            <div class="row align-items-center pb-10 d-print-none">
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
                <div class="col-lg-2">
                    <label class="input-title">Sales By</label>
                    <div class="input-group">
                       <select class="form-control clsSelect2" name="employee_id" id="employee_id">
                            <option value="">Select All</option>
                            @foreach ($employeeData as $row)
                            <option value="{{ $row->id }}">{{ $row->emp_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-2">
                    <label class="input-title">Month</label>
                    <div class="input-group">
                        <input type="text" class="form-control monthYearPicker" id="month_year" name="month_year"
                            placeholder="MM-YYYY" autocomplete="off">
                    </div>
                </div>
                <div class="col-lg-2 pt-20 text-center">
                    <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round"
                        id="searchButton">Search</a>
                </div>
            </div>
            <div class="row d-print-none text-right dreport" style="display: none">
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
                    <!-- (<span id="start_date_txt">{{$startDate }}</span> -->
                    to
                    <!-- <span id="end_date_txt">{{$endDate }}</span>) -->
                </div>
            </div>
            <div class="row dreport" style="display: none">
                <div class="col-xl-6 col-lg-6 col-sm-6 col-md-6 col-6">
                    <span class="d-print-none"><b>Total Row:</b> <span id="totalRowDiv">0</span></span>
                </div>
                <div class="col-xl-6 col-lg-6 col-sm-6 col-md-6 col-6 text-right">
                    <span><b>Printed Date:</b> {{ date("d-m-Y") }} </span>
                </div>
            </div>
            <div class="row dreport" style="display: none">
                <div class="col-lg-12">
                    <div class="table-responsive">
                        <table class="table w-full table-hover table-bordered table-striped clsDataTable" id="cTable">
                            <thead>
                                <tr>
                                    <th width="5%" rowspan="2">#</th>
                                    <th rowspan="2">Customer Name</th>
                                    <th rowspan="2">Customer Code</th>
                                    <th rowspan="2">Bill No.</th>
                                    <th rowspan="2">Products</th>
                                    <th rowspan="2">Quantity</th>
                                    <th rowspan="2">Sales Amount</th>
                                    <th rowspan="2">Collection Amount</th>
                                    <th rowspan="2">Balance</th>
                                    <th colspan="5"><span id="setMonth"></span>'s Saving Collection</th>
                                </tr>
                                <tr>
                                    <th>1st Week <br><span id="firstWeek"></span></th>
                                    <th>2nd Week<br><span id="secondWeek"></span></th>
                                    <th>3rd Week<br><span id="thirdWeek"></span></th>
                                    <th>4th Week<br><span id="forthWeek"></span></th>
                                    <th id="apWeek">5th Week<br><span id="fifthWeek"></span></th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="text-right"><b>Total:</b></td>
                                    <td class="text-center"><b id="ttl_quantity">0.00</b></td>
                                    <td class="text-center"><b id="TotalSaleAmt">0.00</b></td>
                                    <td class="text-center"><b id="collection_amount">0.00</b></td>
                                    <td class="text-center"><b id="balance">0.00</b></td>
                                    <td colspan="5" class="text-center" id="total_payable_amount"><b>0.00</b></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- </div> -->
<!-- End Page -->

<script>
function ajaxDataLoad( branch_id = null, employee_id = null, month_year ) {
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
            "url": "{{route('collectionsheetDatatable')}}",
            "dataType": "json",
            "type": "post",
            "data": {
                _token: "{{ csrf_token() }}",
                branchId: branch_id,
                employeeId: employee_id,
                monthYear: month_year,
            }
        },
        columns: [{
                data: 'sl',
                className: 'text-center'
            },
            {
                data: 'customer_name'
            },
            {
                data: 'customer_code',
                className: 'text-center'
            },
            {
                data: 'sales_bill_no',
                className: 'text-center'
            },
            {
                data: 'product_name',
            },
            {
                data: 'total_quantity',
                className: 'text-center'
            },
            {
                data: 'sales_amount',
                className: 'text-right'
            },
            {
                data: 'collection_amount',
                className: 'text-right'
            },
            {
                data: 'collection_amount',
                className: 'text-right'
            },
            {
                data: 'sales_amount',
                className: 'text-right'
            },
            {
                data: 'sales_amount',
                className: 'text-right'
            },
            {
                data: 'sales_amount',
                className: 'text-right'
            },
            {
                data: 'sales_amount',
                className: 'text-right'
            },
            {
                data: 'sales_amount',
                className: 'text-right'
            },

        ],
        drawCallback: function(oResult) {
            if (oResult.json) {
                $('#totalRowDiv').html(oResult.json.totalRow);
                $('#TotalSaleAmt').html(oResult.json.ttl_sales_amount);
                $('#service_charge').html(oResult.json.ttl_service_charge);
                $('#balance').html(oResult.json.ttl_collection_amount);
                $('#collection_amount').html(oResult.json.ttl_collection_amount);
                $('#ttl_quantity').html(oResult.json.ttl_quantity);
            }
        },
    });
}

function GetMonthName(monthNumber) {
      var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
      return months[monthNumber];
}

function weekCount(year, month_number, startDayOfWeek) {
    // month_number is in the range 1..12

    // Get the first day of week week day (0: Sunday, 1: Monday, ...)
    var firstDayOfWeek = startDayOfWeek || 0;

    var firstOfMonth = new Date(year, month_number-1, 1);
    var lastOfMonth = new Date(year, month_number, 0);
    var numberOfDaysInMonth = lastOfMonth.getDate();
    var firstWeekDay = (firstOfMonth.getDay() - firstDayOfWeek + 7) % 7;

    var used = firstWeekDay + numberOfDaysInMonth;

    return Math.ceil( used / 7) ;
}

$(document).ready(function() {
    

    $('#searchButton').click(function() {
        // $('#apWeek').remove();

        var getDate = '01-'+ $(".monthYearPicker").val();
        $('#firstWeek').html(getDate);
        getDate = new Date(getDate.split("-").reverse().join("-"));

        var getDate2 = new Date( getDate.getFullYear(), getDate.getMonth(), getDate.getDate()+7 );
        var getDate3 = new Date( getDate2.getFullYear(), getDate2.getMonth(), getDate2.getDate()+7 );
        var getDate4 = new Date( getDate3.getFullYear(), getDate3.getMonth(), getDate3.getDate()+7 );
        var getDate5 = new Date( getDate4.getFullYear(), getDate4.getMonth(), getDate4.getDate()+7 );

        getDate2 = ("0" +getDate2.getDate()).slice(-2) + "-" + ("0" +getDate2.getMonth()).slice(-2) + "-"+ getDate2.getFullYear();
        getDate3 = ("0" +getDate3.getDate()).slice(-2) + "-" + ("0" +getDate3.getMonth()).slice(-2) + "-"+ getDate3.getFullYear();
        getDate4 = ("0" +getDate4.getDate()).slice(-2) + "-" + ("0" +getDate4.getMonth()).slice(-2) + "-"+ getDate4.getFullYear();
        getDate5 = ("0" +getDate5.getDate()).slice(-2) + "-" + ("0" +getDate5.getMonth()).slice(-2) + "-"+ getDate5.getFullYear();

        // var secondwk = dateFormat(getDate2, "mm/dd/yy");
        $('#secondWeek').html(getDate2);
        $('#thirdWeek').html(getDate3);
        $('#forthWeek').html(getDate4);
        $('#fifthWeek').html(getDate5);

        var month = getDate.getMonth(getDate);
        var year = getDate.getFullYear(getDate);
        var wc = weekCount(year,month,6);

        $('#setMonth').html(GetMonthName(month));

        if (wc < 5) {
            // $("#cTable").find('thead tr:last').append($('<th id="apWeek">5th Week</th>'));
            $('#apWeek').hide();
            $('#cTable').find('thead tr:first th:last').attr('colspan', '4');

        }
        // Get search value
        var month_year = $('#month_year').val();
        var employee_id = $('#employee_id').val();
        var branch_id = $('#branch_id').val();
        if (month_year != '') {
            $('.dreport').show();
            ajaxDataLoad( branch_id, employee_id, month_year); 
        }
        
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