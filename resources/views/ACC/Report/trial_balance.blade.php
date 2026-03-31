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
'dateRange' => true
])


<div class="w-full show">
    <div class="panel">
        <div class="panel-body panel-search pt-2">

            @include('elements.report.report_heading', ['title' => 'Trial Balance Report', 'title_excel' =>
            'Trial_Balance_Report', 'ledgerHead' => true, 'projectName' => true, 'projectTypeName' => true])

            <div class="row ExportDiv">
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
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td style="text-align: right!important;"><b>Total</b></td>
                                <td class="text-right text-dark font-weight-bold" id="ttl_debit_sum_beg">0.00</td>
                                <td class="text-right text-dark font-weight-bold" id="ttl_credit_sum_beg">0.00</td>
                                <td class="text-right text-dark font-weight-bold" id="ttl_debit_sum_this">0.00</td>
                                <td class="text-right text-dark font-weight-bold" id="ttl_credit_sum_this">0.00</td>
                                <td class="text-right text-dark font-weight-bold" id="ttl_debit_sum_clo">0.00</td>
                                <td class="text-right text-dark font-weight-bold" id="ttl_credit_sum_clo">0.00</td>
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
    function ajaxDataLoad(fiscal_year = null, start_date_fy = null, end_date_fy = null, start_date = null, end_date =
        null,
        project_id = null, project_type_id = null, branch_id = null, depth_level = null, round_up = null, zero_balance =
        null) {

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
                    fiscalYear: fiscal_year,
                    startDateFY: start_date_fy,
                    endDateFY: end_date_fy,
                    startDateY: start_date,
                    endDateY: end_date,
                    projectID: project_id,
                    projectTypeID: project_type_id,
                    branchID: branch_id,
                    depth_level: depth_level,
                    round_up: round_up,
                    zero_balance: zero_balance
                }
            },
            columns: [{
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

            drawCallback: function (oResult) {
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

    $(document).ready(function () {

        $('#fiscal_year').select2({
            'width': '100%'
        });

        var selected = '';
        $('#search_by').change(function () {
            selected = $(this).val();
            if (selected == 1) {
                $('#startDateDivDR,#endDateDivDR').hide('fast');
                $('#fyDiv').show('slow');
            } else if (selected == 3) {
                $('#fyDiv').hide('fast');
                $('#startDateDivDR,#endDateDivDR').show('slow');
            } else {
                $('#fyDiv,#startDateDivDR,#endDateDivDR').hide('');
            }
        });


        $('#searchButton').click(function () {

            var fiscal_year = $('#fiscal_year :selected').val();
            var start_date = $('#start_date_dr').val();
            var end_date = $('#end_date_dr').val();
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
            } else if (selected == 3) {

                $('#start_date_txt').html(start_date);
                $('#end_date_txt').html(end_date);

                $('.title_date').html(end_date);

                $('.show').show('slow');
            }



            // Set value in html

            // $('#ledgerHead').html($('#ledger_id option:selected').text());
            $('#projectName').html($('#project_id option:selected').text());
            $('#projectTypeName').html($('#project_type_id option:selected').text());
            $('#branchName').html($('#branch_id option:selected').text());

            $('#reportBranch').html($('#branch_id').find("option:selected").text());

            $(".wb-minus").trigger('click');
            ajaxDataLoad(fiscal_year, start_date_fy, end_date_fy, start_date, end_date, project_id,
                project_type_id, branch_id, depth_level, round_up, zero_balance);
        });
    });

</script>
@endsection
