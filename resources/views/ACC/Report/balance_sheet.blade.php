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
'depthLevel' => true,
'roundUp' => true,
'zeroBalance' => true,
'searchBy' => true,
'currentYear' => true
])

<div class="w-full show">
    <div class="panel">
        <div class="panel-body panel-search pt-2">

            @include('elements.report.report_heading', ['title' => 'Statement of Financial Position', 'title_excel' =>
            'Balance_Sheet', 'projectName' => true, 'projectTypeName' => true])

            <div class="row ExportDiv">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead class="text-center">
                            <tr>
                                <th width="60%" style="text-align: center !important">Particulars</th>
                                <th style="text-align: center !important">Previous Year<br>
                                    (<span id="prev_year"></span> )</th>
                                <th style="text-align: center !important">Current Year<br>
                                    (<span id="current_year"></span> )</th>
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
    function ajaxDataLoad(prev_fiscal_year = null, start_date_fy = null, end_date_fy = null,
        prev_fiscal_year_cy = null, start_date_cy = null, end_date_cy = null,
        project_id = null, project_type_id = null, branch_id = null, selected = null,
        depth_level = null, round_up = null, zero_balance = null, prev_year_ret = null,
        prev_year_ret_cy = null) {


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
                "url": "{{route('BalanceSheetReportDatatable')}}",
                "dataType": "json",
                "type": "post",
                "data": {
                    _token: "{{ csrf_token() }}",
                    prevFiscalYear: prev_fiscal_year,
                    startDateFY: start_date_fy,
                    endDateFY: end_date_fy,
                    prevFiscalYearCY: prev_fiscal_year_cy,
                    startDateCY: start_date_cy,
                    endDateCY: end_date_cy,
                    projectID: project_id,
                    projectTypeID: project_type_id,
                    branchID: branch_id,
                    selected: selected,
                    depth_level: depth_level,
                    round_up: round_up,
                    zero_balance: zero_balance,
                    prevYearRetrained: prev_year_ret,
                    prevYearRetrainedCY: prev_year_ret_cy
                }
            },
            columns: [{
                    data: 'particular_name',
                },
                {
                    data: 'previous_balance_txt',
                    className: 'text-right'
                },
                {
                    data: 'balance_dur_txt',
                    className: 'text-right'
                },
            ]

        });
    }

    $(document).ready(function () {

        $('#fiscal_year').select2({
            'width': '100%'
        });

        var selected = '';
        $('#search_by').change(function () {
            selected = $(this).val();
            if (selected == 1) {
                $('#endDateDivCY,#startDateDivDR,#endDateDivDR').hide('fast');
                $('#fyDiv').show('slow');
            } else if (selected == 2) {
                $('#fyDiv,#startDateDivDR,#endDateDivDR').hide('fast');
                $('#endDateDivCY').show('slow');
            } else {
                $('#fyDiv,#endDateDivCY').hide('');
            }
        });


        $('#searchButton').click(function () {

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
            } else if (selected == 1) {

                var fiscal_year = $('#fiscal_year :selected').val();
                var fiscal_year_txt = $('#fiscal_year :selected').text();
                $('#current_year').html(fiscal_year_txt); // Table Column Current Year set Value
                var values = fiscal_year_txt.split('-');
                var currentFiscalOne = values[0];
                var currentFiscalTwo = values[1];

                var prevFiscalOne = currentFiscalOne - 1;
                var prevFiscalTwo = currentFiscalTwo - 1;

                var prev_fiscal_year = ' ' + prevFiscalOne + '-' + prevFiscalTwo;

                var prev_year_ret = prev_fiscal_year.split('-');
                var prevYearOne = prev_year_ret[0];
                var prevYearTwo = prev_year_ret[1];

                var prevFiscalRetOne = prevYearOne - 1;
                var prevFiscalRetTwo = prevYearTwo - 1;

                var prev_year_ret = ' ' + prevFiscalRetOne + '-' + prevFiscalRetTwo;


                $('#prev_year').html(prev_fiscal_year); // Table Column Previuos Year set Value


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
                $('#start_date_txt').html(start_date_fy);
                $('#end_date_txt').html(end_date_fy);

                $('.title_date').html(end_date_fy);


                $('.show').show('slow');
            } else if (selected == 2) {

                var end_date_cy = $('#end_date_cy').val();

                var current_fiscal_year = $('#start_date_cy').data('fiscal');
                $('#current_year').html(current_fiscal_year); // Table Column Current Year set Value
                var values = current_fiscal_year.split('-');
                var currentFiscalOne = values[0];
                var currentFiscalTwo = values[1];

                var prevFiscalOne = currentFiscalOne - 1;
                var prevFiscalTwo = currentFiscalTwo - 1;

                var prev_fiscal_year_cy = ' ' + prevFiscalOne + '-' + prevFiscalTwo;

                var prev_year_ret = prev_fiscal_year_cy.split('-');
                var prevYearOne = prev_year_ret[0];
                var prevYearTwo = prev_year_ret[1];

                var prevFiscalRetOne = prevYearOne - 1;
                var prevFiscalRetTwo = prevYearTwo - 1;

                var prev_year_ret_cy = ' ' + prevFiscalRetOne + '-' + prevFiscalRetTwo;



                $('#prev_year').html(prev_fiscal_year_cy); // Table Column Previuos Year set Value

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
            }


            // Set value in html

            // $('#ledgerHead').html($('#ledger_id option:selected').text());
            $('#projectName').html($('#project_id option:selected').text());
            $('#projectTypeName').html($('#project_type_id option:selected').text());
            $('#branchName').html($('#branch_id option:selected').text());

            $('#reportBranch').html($('#branch_id').find("option:selected").text());

            $(".wb-minus").trigger('click');
            ajaxDataLoad(prev_fiscal_year, start_date_fy, end_date_fy, prev_fiscal_year_cy,
                start_date_cy, end_date_cy,
                project_id, project_type_id, branch_id, selected, depth_level, round_up,
                zero_balance, prev_year_ret,
                prev_year_ret_cy);
        });
    });

</script>
@endsection
