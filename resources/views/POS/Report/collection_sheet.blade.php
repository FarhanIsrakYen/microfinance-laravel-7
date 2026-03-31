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
            margin: 10px !important;
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

@include('elements.report.report_filter_options', ['branch' => true,
'employee' => true,
'monthYear' => true
])

<div class="w-full">
    <div class="panel">
        <div class="panel-body panel-search pt-2">

            @include('elements.report.report_heading', ['title' => 'Collection Sheet', 'title_excel' =>
            'Collection_Sheet', 'customerDesig' => true, 'totalCustomer' => true])

            <div class="row dreport ExportDiv" style="display: none">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead class="text-center">
                            <tr>
                                <th width="3%" rowspan="2">SL</th>
                                <th width="8%" rowspan="2">Customer Name</th>
                                <th width="8%" rowspan="2">Bill No</th>
                                <th width="10%" rowspan="2">Products</th>
                                <!-- <th width="10%" rowspan="2">Sales By</th> -->
                                <th width="5%" rowspan="2">Quantity</th>
                                <th width="5%" rowspan="2">Sales Amount</th>
                                <th width="6%" rowspan="2">Collection Amount</th>
                                <th width="5%" rowspan="2">Balance</th>
                                <th width="40%" colspan="5"><span id="setMonth"></span> Collection</th>
                            </tr>
                            <tr>
                                <th width="8%" class="text-center">1st Week <br>(<span class="m_font_size"
                                        id="firstWeek"></span>
                                    to <span class="m_font_size" id="endfirstWeek"></span>)</th>
                                <th width="8%" class="text-center">2nd Week<br>(<span class="m_font_size"
                                        id="secondWeek"></span>
                                    to <span class="m_font_size" id="endsecondWeek"></span>)</th>
                                <th width="8%" class="text-center">3rd Week<br>(<span class="m_font_size"
                                        id="thirdWeek"></span> to
                                    <span class="m_font_size" id="endthirdWeek"></span>)</th>
                                <th width="8%" class="text-center">4th Week<br>(<span class="m_font_size"
                                        id="forthWeek"></span> to
                                    <span class="m_font_size" id="endforthWeek"></span>)</th>
                                <th width="8%" class="text-center" id="apWeek">5th Week<br>(<span class="m_font_size"
                                        id="fifthWeek"></span> to <span class="m_font_size" id="endFifthWeek"></span>)
                                </th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-align: right!important;"><b>TOTAL</b></td>
                                <td class="text-center text-dark font-weight-bold"><b id="ttl_quantity">0</b></td>
                                <td class="text-right text-dark font-weight-bold"><b id="TotalSaleAmt">0.00</b></td>
                                <td class="text-right text-dark font-weight-bold"><b id="collection_amount">0.00</b>
                                </td>
                                <td class="text-right text-dark font-weight-bold"><b id="balance">0.00</b></td>
                                <td class="text-right text-dark font-weight-bold"><b id="ttl_firstWeek">0.00</b></td>
                                <td class="text-right text-dark font-weight-bold"><b id="ttl_secondWeek">0.00</b></td>
                                <td class="text-right text-dark font-weight-bold"><b id="ttl_thirdWeek">0.00</b></td>
                                <td class="text-right text-dark font-weight-bold"><b id="ttl_forthWeek">0.00</b></td>
                                <td class="text-right text-dark font-weight-bold"><b id="ttl_fifthWeek">0.00</b></td>
                            </tr>
                        </tfoot>
                    </table>
                    @include('../elements.signature.signatureSet')
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Page -->

<script>
    $(document).ready(function () {

        $('#branch_id').change(function () {

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
            onClose: function (dateText, inst) {
                var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
                var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
                $(this).val($.datepicker.formatDate('MM-yy', new Date(year, month, 1)));
            }
        });
    });


    function ajaxDataLoad(branch_id = null, employee_id = null, firstWeek, endfirstWeek, secondWeek, endsecondWeek,
        thirdWeek, endthirdWeek, forthWeek, endforthWeek, fifthWeek, endFifthWeek, monthYearsel) {
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
                    endFifthWeek: endFifthWeek,
                    monthYearsel: monthYearsel
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
                //     data: 'customer_no',
                //     className: 'text-center'
                // },
                {
                    data: 'sales_bill_no',
                },
                {
                    data: 'product_name',
                },
                // {
                //     data: 'sales_by',
                // },
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
            drawCallback: function (oResult) {
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

    $(document).ready(function () {
        $('#searchButton').click(function () {
            $('#reportBranch').html($('#branch_id').find("option:selected").text());
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

            if (employee_id != '') {
                $.ajax({
                    method: "GET",
                    url: "{{url('/ajaxEmpDesignationLoad')}}",
                    dataType: "text",
                    data: {
                        employee_id: employee_id
                    },
                    success: function (data) {
                        if (data) {
                            console.log(data);
                            $('#designation')
                                .html(data);

                        }
                    }
                });
            }

            if (month_year != '' && employee_id != '' && branch_id != '') {
                var employee = $('#employee_id option:selected').text();
                $('#empName').html(employee);
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

                $(".wb-minus").trigger('click');
                $('#branch_txt').html($('#branch_id').find("option:selected").text());
                $('#start_date_txt').html($.datepicker.formatDate('dd-mm-yy', new Date(firstWeek)));
                $('#end_date_txt').html($.datepicker.formatDate('dd-mm-yy', new Date(endFifthWeek)));

                ajaxDataLoad(branch_id, employee_id, firstWeek, endfirstWeek, secondWeek, endsecondWeek,
                    thirdWeek, endthirdWeek,
                    forthWeek, endforthWeek, fifthWeek, endFifthWeek, monthYearsel);



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
