@extends('Layouts.erp_master_full_width')
@section('content')

<style>
    .m_font_size {
        font-size: 10px;
    }

    .clsSelect2 {
        width: 100%;
    }

    .ui-datepicker-calendar {
        display: none;
    }

    @media print {
        @page { 
            size: landscape;
            margin: 10px!important;
        }

        /* .table > thead th {
            font-size: 70%!important;
            padding: 2px!important;
        }
        
        .table > tbody td {
            font-size: 70%!important;
            padding: 2px!important;
        }

        .table > tfoot td {
            font-size: 70%!important;
            padding: 2px!important;
        } */

    }
</style>

<?php 
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
use App\Services\HrService as HRS;

$startDate = Common::systemCurrentDate();
$endDate = Common::systemCurrentDate();

$employeeData = Common::ViewTableOrderIn('hr_employees',
    [['is_delete', 0], ['is_active', 1]],
    ['branch_id', HRS::getUserAccesableBranchIds()],
    ['id','employee_no', 'emp_name', 'emp_code'],
    ['emp_code', 'ASC']);

$branchData = Common::ViewTableOrderIn('gnl_branchs',
    [['is_delete', 0], ['is_active', 1], ['is_approve', 1]],
    ['id', HRS::getUserAccesableBranchIds()],
    ['id', 'branch_name'],
    ['branch_code', 'ASC']);

$branchId = Common::getBranchId();
$branchInfo = Common::ViewTableFirst('gnl_branchs',
    [['is_delete', 0], ['is_active', 1],
        ['id', $branchId]],
    ['id', 'branch_name']);

$groupInfo = Common::ViewTableFirst('gnl_groups',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'group_name']);

?>

<div class="w-full d-print-none">
    <div class="panel">
        <div class="panel-body">

            <div class="row align-items-center pb-10 d-print-none">
                {!! HTML::forBranchFeildSearch_new('one')!!}

                <div class="col-lg-2">
                    <label class="input-title">Sales By</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="employee_id" id="employee_id">
                            <option value="">Select One</option>
                            @foreach ($employeeData as $row)
                            <option value="{{ $row->employee_no }}">{{sprintf("%04d", $row->emp_code)}} -
                                {{ $row->emp_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Month</label>
                    <div class="input-group">
                        <!-- monthYearPicker -->
                        <input type="text" class="form-control" id="month_year" name="month_year" placeholder="MM-YYYY"
                            autocomplete="off">
                    </div>
                </div>


                <div class="col-lg-2 pt-20 text-center">
                    <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round"
                        id="searchButton">Search</a>
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
                    <strong id="branch_txt">{{ $branchInfo->branch_name }}</strong><br>
                    <span>Collection Sheet for </span><br>
                    (<span id="start_date_txt">{{ (new Datetime($startDate))->format('d-m-Y') }}</span>
                    TO
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
                        onclick="fnDownloadExcel('ExportHeading,ExportDiv', 'Collection_Sheet_{{ (new Datetime())->format('d-m-Y') }}');">
                        <i class="fa fa-file-excel-o fa-lg" style="font-size:20px; margin-left: 5px;"></i>
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-6 col-lg-6 col-sm-6 col-md-6 col-6">
                    <span class="d-print-none"><b>Total Row:</b> <span id="totalRowDiv">0</span></span>
                </div>
                <div class="col-xl-6 col-lg-6 col-sm-6 col-md-6 col-6 text-right">
                    <span><b>Printed Date:</b> {{ (new Datetime())->format('d-m-Y') }} </span>
                </div>
            </div>

            <div class="row dreport ExportDiv" style="display: none">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead class="text-center">
                            <tr>
                                <th width="3%" rowspan="2">SL</th>
                                <th width="8%" rowspan="2">Customer Name</th>
                                <th width="8%" rowspan="2">Bill No</th>
                                <th width="10%" rowspan="2">Products</th>
                                <th width="5%" rowspan="2">Quantity</th>
                                <th width="5%" rowspan="2">Sales Amount</th>
                                <th width="6%" rowspan="2">Collection Amount</th>
                                <th width="5%" rowspan="2">Balance</th>
                                <th width="50%" colspan="5"><span id="setMonth"></span> Collection</th>
                            </tr>
                            <tr>
                                <th width="10%" class="text-center">1st Week <br>(<span class="m_font_size"
                                        id="firstWeek"></span>
                                    to <span class="m_font_size" id="endfirstWeek"></span>)</th>
                                <th width="10%" class="text-center">2nd Week<br>(<span class="m_font_size"
                                        id="secondWeek"></span>
                                    to <span class="m_font_size" id="endsecondWeek"></span>)</th>
                                <th width="10%" class="text-center">3rd Week<br>(<span class="m_font_size"
                                        id="thirdWeek"></span> to
                                    <span class="m_font_size" id="endthirdWeek"></span>)</th>
                                <th width="10%" class="text-center">4th Week<br>(<span class="m_font_size"
                                        id="forthWeek"></span> to
                                    <span class="m_font_size" id="endforthWeek"></span>)</th>
                                <th width="10%" class="text-center" id="apWeek">5th Week<br>(<span class="m_font_size"
                                        id="fifthWeek"></span> to <span class="m_font_size" id="endFifthWeek"></span>)
                                </th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-align: right!important;"><b>TOTAL</b></td>
                                <td class="text-center text-dark font-weight-bold"><b id="ttl_quantity">0</b></td>
                                <td class="text-right text-dark font-weight-bold"><b id="TotalSaleAmt">0.00</b></td>
                                <td class="text-right text-dark font-weight-bold"><b id="collection_amount">0.00</b></td>
                                <td class="text-right text-dark font-weight-bold"><b id="balance">0.00</b></td>
                                <td class="text-right text-dark font-weight-bold"><b id="ttl_firstWeek">0.00</b></td>
                                <td class="text-right text-dark font-weight-bold"><b id="ttl_secondWeek">0.00</b></td>
                                <td class="text-right text-dark font-weight-bold"><b id="ttl_thirdWeek">0.00</b></td>
                                <td class="text-right text-dark font-weight-bold"><b id="ttl_forthWeek">0.00</b></td>
                                <td class="text-right text-dark font-weight-bold"><b id="ttl_fifthWeek">0.00</b></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Page -->

<script>
    $(document).ready(function() {

        $('#branch_id').change(function() {

            fnAjaxSelectBoxCode('employee_id',
                $(this).val(),
                '{{base64_encode("hr_employees")}}',
                '{{base64_encode("branch_id")}}',
                '{{base64_encode("employee_no,emp_code,emp_name")}}',
                '{{url("/ajaxSelectBoxCode")}}'
            );

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
    });


    function ajaxDataLoad(branch_id = null, employee_id = null, firstWeek, endfirstWeek, secondWeek, endsecondWeek,
        thirdWeek, endthirdWeek, forthWeek, endforthWeek, fifthWeek, endFifthWeek) {
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
                    branchId: branch_id,
                    employeeId: employee_id,
                    firstWeek: firstWeek,
                    endfirstWeek: endfirstWeek,
                    secondWeek: secondWeek,
                    endsecondWeek: endsecondWeek,
                    thirdWeek: thirdWeek,
                    endthirdWeek: endthirdWeek,
                    forthWeek: forthWeek,
                    endforthWeek: endforthWeek,
                    fifthWeek: fifthWeek,
                    endFifthWeek: endFifthWeek
                }
            },
            columns: [{
                    data: 'sl',
                    className: 'text-center'
                },
                {
                    data: 'customer_name'
                },
                // {
                //     data: 'customer_code',
                //     className: 'text-center'
                // },
                {
                    data: 'sales_bill_no',
                },
                {
                    data: 'product_name',
                },
                {
                    data: 'product_quantity',
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
                    data: 'balance_amount',
                    className: 'text-right'
                },
                {
                    data: 'first_week',
                    className: 'text-right'
                },
                {
                    data: 'second_week',
                    className: 'text-right'
                },
                {
                    data: 'third_week',
                    className: 'text-right'
                },
                {
                    data: 'fourth_week',
                    className: 'text-right'
                },
                {
                    data: 'fifth_week',
                    className: 'text-right'
                },

            ],
            drawCallback: function(oResult) {
                if (oResult.json) {
                    $('#totalRowDiv').html(oResult.json.totalRow);
                    $('#TotalSaleAmt').html(oResult.json.ttl_sales_amount);
                    // $('#service_charge').html(oResult.json.ttl_service_charge);
                    $('#collection_amount').html(oResult.json.ttl_collection_amount);
                    $('#balance').html(oResult.json.ttl_due_amount);
                    $('#ttl_quantity').html(oResult.json.ttl_quantity);

                    $('#ttl_firstWeek').html(oResult.json.ttl_first_week);
                    $('#ttl_secondWeek').html(oResult.json.ttl_second_week);
                    $('#ttl_thirdWeek').html(oResult.json.ttl_third_week);
                    $('#ttl_forthWeek').html(oResult.json.ttl_fourth_week);
                    $('#ttl_fifthWeek').html(oResult.json.ttl_fifth_week);

                }
            },
        });
    }

    $(document).ready(function() {
        $('#searchButton').click(function() {
            // $('#apWeek').remove();

            var monthYearsel = $('#month_year').val();
            $('#month_txt').html(monthYearsel);

            var getDate = new Date('01-' + monthYearsel);
            var dayOfMonth = new Date(getDate.getFullYear(), getDate.getMonth() + 1, 0).getDate();
            var weekCount = Math.ceil(dayOfMonth / 7);

            var firstWeek = $.datepicker.formatDate('dd-M', new Date('01-' + monthYearsel));
            var endfirstWeek = $.datepicker.formatDate('dd-M', new Date('07-' + monthYearsel));

            var secondWeek = $.datepicker.formatDate('dd-M', new Date('08-' + monthYearsel));
            var endsecondWeek = $.datepicker.formatDate('dd-M', new Date('14-' + monthYearsel));

            var thirdWeek = $.datepicker.formatDate('dd-M', new Date('15-' + monthYearsel));
            var endthirdWeek = $.datepicker.formatDate('dd-M', new Date('21-' + monthYearsel));

            var forthWeek = $.datepicker.formatDate('dd-M', new Date('22-' + monthYearsel));
            var endforthWeek = $.datepicker.formatDate('dd-M', new Date('28-' + monthYearsel));

            var fifthWeek = $.datepicker.formatDate('dd-M', new Date('29-' + monthYearsel));
            var endFifthWeek = $.datepicker.formatDate('dd-M', new Date(dayOfMonth + '-' +
                monthYearsel));


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
                $('.clsDataTable').find('thead tr:first th:last').attr('colspan', '4');
            }

            // Get search value
            $('#setMonth').html(monthYearsel);
            var month_year = $('#month_year').val();
            var employee_id = $('#employee_id').val();
            var branch_id = $('#branch_id').val();

            if (month_year != '' && employee_id != '' && branch_id != '') {
                $('.dreport').show();

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

                ajaxDataLoad(branch_id, employee_id, firstWeek, endfirstWeek, secondWeek, endsecondWeek,
                    thirdWeek, endthirdWeek,
                    forthWeek, endforthWeek, fifthWeek, endFifthWeek);
            } else {
                if (branch_id == '') {
                    swal({
                        icon: 'warning',
                        title: 'Warning',
                        text: 'Please select Branch',
                    });
                    return false;
                }

                if (employee_id == '') {
                    swal({
                        icon: 'warning',
                        title: 'Warning',
                        text: 'Please select employee',
                    });
                    return false;
                }

                if (month_year == '') {
                    swal({
                        icon: 'warning',
                        title: 'Warning',
                        text: 'Please select month',
                    });
                    return false;
                }
            }

        });
    });

</script>
@endsection