@extends('Layouts.erp_master_full_width')
@section('content')

<style type="text/css">
    .dataTable thead th,
    .dataTable tbody td {
        padding: 3px;
    }

</style>

@include('elements.report.report_filter_options', ['project' => true,
'projectType' => true,
'branchAcc' => true,
'vTypeReceipt' => true,
'depthLevel' => true,
'roundUp' => true,
'zeroBalance' => true,
'searchBy' => true,
'currentYear' => true,
'dateRange' => true
])

<div class="w-full show">
    <div class="panel">
        <div class="panel-body panel-search pt-2">

            @include('elements.report.report_heading', ['title' => 'Receipt Payment Report', 'title_excel' =>
            'Receipt_Payment', 'projectName' => true, 'projectTypeName' => true])
            <div class="row ExportDiv">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead class="text-center">
                            <tr>
                                <th width="50%">Particulars</th>
                                <th width="10%">Notes</th>
                                <th>This Month</th>
                                <th>This Year</th>
                                <th>Cumulative</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    @include('../elements.signature.signatureSet')
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function ajaxDataLoad(prev_fiscal = null, start_date_fy = null, end_date_fy = null,
        start_date_cm = null, start_date_cy = null, end_date_cy = null,
        start_date_dm = null, start_date_dr = null, end_date_dr = null,
        project_id = null, project_type_id = null, branch_id = null,
        selected = null, depth_level = null, round_up = null, zero_balance = null, voucher_type = null
    ) {

        // console.log('test');
        // return false;

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
                "url": "{{route('ReceiptPaymentReportDatatable')}}",
                "dataType": "json",
                "type": "post",
                "data": {
                    _token: "{{ csrf_token() }}",
                    prevFiscalYear: prev_fiscal,
                    startDateFY: start_date_fy,
                    endDateFY: end_date_fy,
                    startDateCM: start_date_cm,
                    startDateCY: start_date_cy,
                    endDateCY: end_date_cy,
                    startDateDM: start_date_dm,
                    startDateDR: start_date_dr,
                    endDateDR: end_date_dr,
                    projectID: project_id,
                    projectTypeID: project_type_id,
                    branchID: branch_id,
                    selected: selected,
                    depth_level: depth_level,
                    round_up: round_up,
                    zero_balance: zero_balance,
                    voucherType: voucher_type,
                }
            },
            columns: [{
                    data: 'particular_name'
                },
                {
                    data: 'notes',
                },
                {
                    data: 'balance_month_txt',
                    className: 'text-right'
                },
                // {
                //     data: 'month_dr_cr',
                //     className: 'text-right'
                // },
                {
                    data: 'balance_dur_txt',
                    className: 'text-right'
                },
                // {
                //     data: 'current_dr_cr',
                //     className: 'text-right'
                // },
                {
                    data: 'closing_balance_txt',
                    className: 'text-right'
                }
            ],
            // "columnDefs": [
            //     { "visible" : false, "targets": [2,3,4,5] }
            // ],
            // drawCallback: function(oResult) {
            //     //  console.log(oResult.json.totalRow);
            //     if (oResult.json) {

            //         if (selected == 1) {
            //             Table.columns([3]).visible(true);
            //         }

            //         if (selected == 2) {
            //             Table.columns([2,3,4]).visible(true);
            //         }
            //         if (selected == 3) {
            //             Table.columns([2,3,4]).visible(false);
            //             Table.columns([5]).visible(true);
            //         }
            //     }
            // }

        });
    }

    $(document).ready(function () {

        // $('#start_date_dr').datepicker("option", "minDate", new Date( brOPYear, brOPMonth, brOPDay));
        var newOption = '<option value="-1" data-select2-id="-1" selected>All (With HO)</option>';
        newOption += '<option value="-2" data-select2-id="-2">All (Without HO)</option>';


        // Append it to the select
        $('#branch_id').prepend(newOption).trigger('change');


        $('#fiscal_year').select2({
            'width': '100%'
        });

        var selected = '';
        $('#search_by').change(function () {
            selected = $(this).val();
            if (selected == 1) {
                $('.table-bordered thead:first-child th:eq(2)').html('Previuos Year');
                $('#endDateDivCY,#startDateDivDR,#endDateDivDR').hide('fast');
                $('#fyDiv').show('slow');
            } else if (selected == 2) {
                $('.table-bordered thead:first-child th:eq(2)').html('This Month');
                $('#fyDiv,#startDateDivDR,#endDateDivDR').hide('fast');
                $('#endDateDivCY').show('slow');
            } else if (selected == 3) {
                $('.table-bordered thead:first-child th:eq(2)').html('This Month');
                $('#fyDiv,#endDateDivCY').hide('fast');
                $('#startDateDivDR,#endDateDivDR').show('slow');
            } else {
                $('#fyDiv,#endDateDivCY').hide('');
            }
        });


        $('#searchButton').click(function () {

            var start_date_cy = $('#start_date_cy').val();
            var project_id = $('#project_id').val();
            var project_type_id = $('#project_type_id').val();
            var branch_id = $('#branch_id').val();
            var voucher_type = $('#voucher_type').val();
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
            } else if (selected == 1) {

                var fiscal_year = $('#fiscal_year :selected').val();
                var fiscal_year_txt = $('#fiscal_year :selected').text();

                var fy = fiscal_year_txt.split('-');
                var fyOne = fy[0] - 1;
                var fyTwo = fy[1] - 1;
                var prev_fiscal = fyOne + '-' + fyTwo;

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

                var enddate = start_date_fy.split('-');
                var month = enddate[1];
                var year = enddate[2];
                var start_date_dm = '01-' + month + '-' + year;
                var prev_month = month - 1;
                var endDateMonth = new Date(year, prev_month, 0).getDate();

                if (endDateMonth < 10) {
                    endDateMonth = '0' + endDateMonth
                };
                if (prev_month < 10) {
                    prev_month = '0' + prev_month
                };

                var end_date_py_ob = endDateMonth + '-' + prev_month + '-' + year;

                // var endDateY = start_date_cy.split('-');
                // var month_ob = endDateY[1] - 1;
                // var year_ob = endDateY[2];
                // var endDateYear = new Date( year_ob , month_ob , 0).getDate();
                // var end_date_fy_ob = endDateYear + '-' + month_ob + '-' + year_ob;
                // alert(end_date_fy_ob);


                $('#start_date_txt').html(start_date_fy);
                $('#end_date_txt').html(end_date_fy);

                $('.title_date').html(end_date_fy);


                $('.show').show('slow');
            } else if (selected == 2) {
                var end_date_cy = $('#end_date_cy').val();
                var enddate = end_date_cy.split('-');
                var month = enddate[1];
                var year = enddate[2];
                var start_date_cm = '01-' + month + '-' + year;
                var prev_month = month - 1;
                var endDateMonth = new Date(year, prev_month, 0).getDate();

                if (endDateMonth < 10) {
                    endDateMonth = '0' + endDateMonth
                };
                if (prev_month < 10) {
                    prev_month = '0' + prev_month
                };

                var end_date_cm_ob = endDateMonth + '-' + prev_month + '-' + year;

                var endDateY = start_date_cy.split('-');
                var month_ob = endDateY[1] - 1;
                var year_ob = endDateY[2];
                var endDateYear = new Date(year_ob, month_ob, 0).getDate();
                var end_date_cy_ob = endDateYear + '-' + month_ob + '-' + year_ob;


                if (end_date_cy == '') {
                    swal({
                        icon: 'warning',
                        title: 'Warning',
                        text: 'Please select Date',
                    });
                    return false;
                }

                $('#start_date_txt').html(start_date_cy);
                $('#end_date_txt').html(end_date_cy);

                $('.title_date').html(end_date_cy);

                $('.show').show('slow');
                // Table.columns( [2,3] ).visible( true );
            } else if (selected == 3) {

                var start_date_dr = $('#start_date_dr').val();
                var end_date_dr = $('#end_date_dr').val();

                var enddate = end_date_dr.split('-');
                var month = enddate[1];
                var year = enddate[2];
                var start_date_dm = '01-' + month + '-' + year;
                var prev_month = month - 1;
                var endDateMonth = new Date(year, prev_month, 0).getDate();

                if (endDateMonth < 10) {
                    endDateMonth = '0' + endDateMonth
                };
                if (prev_month < 10) {
                    prev_month = '0' + prev_month
                };

                var end_date_dm_ob = endDateMonth + '-' + prev_month + '-' + year;

                var endDateY = start_date_cy.split('-');
                var month_ob = endDateY[1] - 1;
                var year_ob = endDateY[2];
                var endDateYear = new Date(year_ob, month_ob, 0).getDate();
                var end_date_dr_ob = endDateYear + '-' + month_ob + '-' + year_ob;

                if (start_date_dr == '' || end_date_dr == '') {
                    swal({
                        icon: 'warning',
                        title: 'Warning',
                        text: 'Please select Date',
                    });
                    return false;
                }

                $('#start_date_txt').html(start_date_dr);
                $('#end_date_txt').html(end_date_dr);

                $('.title_date').html(end_date_dr);

                $('.show').show('slow');
            }

            // Set value in html

            // $('#ledgerHead').html($('#ledger_id option:selected').text());
            $('#projectName').html($('#project_id option:selected').text());
            $('#projectTypeName').html($('#project_type_id option:selected').text());
            $('#branchName').html($('#branch_id option:selected').text());

            $('#reportBranch').html($('#branch_id').find("option:selected").text());

            $(".wb-minus").trigger('click');
            ajaxDataLoad(prev_fiscal, start_date_fy, end_date_fy,
                start_date_cm, start_date_cy, end_date_cy,
                start_date_dm, start_date_dr, end_date_dr,
                project_id, project_type_id, branch_id, selected, depth_level, round_up,
                zero_balance, voucher_type);
        });
    });

</script>
@endsection
