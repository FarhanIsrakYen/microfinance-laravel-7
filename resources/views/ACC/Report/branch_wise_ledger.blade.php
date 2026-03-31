@extends('Layouts.erp_master_full_width')
@section('content')

<style>
    /* customprint */
    @media print {
        .table>thead td {
            border: 1px solid #000 !important;
        }
    }

</style>


@include('elements.report.report_filter_options', ['project' => true,
'projectType' => true,
'branchAcc' => true,
'ledger' => true,
'voucherType' => true,
'space' => true,
'startDate' => true,
'endDate' => true,
])


<div class="w-full show">
    <div class="panel">
        <div class="panel-body panel-search pt-2">

            @include('elements.report.report_heading', ['title' => 'Consolidated Ledger Report', 'title_excel' =>
            'Consolidated_Ledger_Report', 'ledgerHead' => true, 'projectName' => true, 'projectTypeName' => true])

            <div class="row ExportDiv">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead>
                            <tr class="text-center">
                                <th rowspan="2" width="3%">SL</th>
                                <th rowspan="2" width="33%">Branch</th>
                                <th colspan="2" width="16%">Opening Balance</th>
                                <!-- <th>Dr/Cr</th> -->
                                <th rowspan="2" width="16%">Debit Amount</th>
                                <th rowspan="2" width="16%">Credit Amount</th>
                                <th colspan="2" width="16%">Closing Balance</th>
                                <!-- <th rowspan="2">Dr/Cr</th> -->
                            </tr>
                            <tr>
                                <th>Balance</th>
                                <th width="3%">Dr/Cr</th>
                                <th>Balance</th>
                                <th width="3%">Dr/Cr</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" style="text-align: right!important;"><b>Total</b></td>
                                <td class="text-right text-dark font-weight-bold" id="ttl_ob">0.00</td>
                                <td class="text-right text-dark font-weight-bold" id="ttl_ob_dr_cr"> </td>
                                <td class="text-right text-dark font-weight-bold" id="ttl_debit_amt">0.00</td>
                                <td class="text-right text-dark font-weight-bold" id="ttl_credit_amt">0.00</td>
                                <td class="text-right text-dark font-weight-bold" id="ttl_cb">0.00</td>
                                <td class="text-right text-dark font-weight-bold" id="ttl_cb_dr_cr"></td>

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
    function ajaxDataLoad(start_date = null, end_date = null, project_id = null, project_type_id = null,
        branch_id = null, ledger_id = null, voucher_type_id = null) {

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
                "url": "{{route('BranchLedgerReportDatatable')}}",
                "dataType": "json",
                "type": "post",
                "data": {
                    _token: "{{ csrf_token() }}",
                    startDate: start_date,
                    endDate: end_date,
                    projectID: project_id,
                    projectTypeID: project_type_id,
                    branchID: branch_id,
                    ledgerID: ledger_id,
                    voucherTypeID: voucher_type_id,
                }
            },
            columns: [{
                    data: 'sl',
                    className: 'text-center'
                },
                {
                    data: 'branch'
                },
                {
                    data: 'opening_balance',
                    className: 'text-right'
                },
                {
                    data: 'ob_dr_cr',
                    className: 'text-right'
                },
                {
                    data: 'debit_amount',
                    className: 'text-right'
                },
                {
                    data: 'credit_amount',
                    className: 'text-right'
                },
                {
                    data: 'closing_balance',
                    className: 'text-right'
                },
                {
                    data: 'cb_dr_cr',
                    className: 'text-right'
                },

            ],
            drawCallback: function (oResult) {
                //  console.log(oResult.json.totalRow);
                if (oResult.json) {

                    $('#ttl_ob').html(oResult.json.ttl_opening_balance);
                    $('#ttl_ob_dr_cr').html(oResult.json.ttl_ob_dr_cr);
                    $('#ttl_debit_amt').html(oResult.json.ttl_debit_amt);
                    $('#ttl_credit_amt').html(oResult.json.ttl_credit_amt);
                    $('#ttl_cb').html(oResult.json.ttl_closing_balance);
                    $('#ttl_cb_dr_cr').html(oResult.json.ttl_cb_dr_cr);
                }
            },
        });
    }

    $(document).ready(function () {

        // hide new entry
        $('.page-header-actions').hide();
        var newOption = '<option value="-1" data-select2-id="-1" selected>All With Head Office</option>';
        newOption += '<option value="-2" data-select2-id="-2">All Without Head Office</option>';
        // Append it to the select
        $('#branch_id').prepend(newOption).trigger('change');
        $('#searchButton').click(function () {
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            var project_id = $('#project_id').val();
            var project_type_id = $('#project_type_id').val();
            var branch_id = $('#branch_id').val();
            var ledger_id = $('#ledger_id').val();
            var voucher_type_id = $('#voucher_type_id').val();

            if (ledger_id == '') {
                swal({
                    icon: 'warning',
                    title: 'Warning',
                    text: 'Please select A Ledger',
                });
                return false;
            } else if (ledger_id != '') {
                $('.show').show('slow');
            }

            // Set value in html
            $('#start_date_txt').html(start_date);
            $('#end_date_txt').html(end_date);
            $('#ledgerHead').html($('#ledger_id option:selected').text());

            $('#projectName').html($('#project_id option:selected').text());
            $('#projectTypeName').html($('#project_type_id option:selected').text());
            $('#branchName').html($('#branch_id option:selected').text());

            $('#reportBranch').html($('#branch_id').find("option:selected").text());

            $(".wb-minus").trigger('click');
            ajaxDataLoad(start_date, end_date, project_id, project_type_id, branch_id, ledger_id,
                voucher_type_id);
        });
    });

</script>
@endsection
