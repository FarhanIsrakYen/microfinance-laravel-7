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
'ledgerCash' => true,
'voucherType' => true,
'space' => true,
'startDate' => true,
'endDate' => true
])

<div class="w-full show">
    <div class="panel">
        <div class="panel-body panel-search pt-2">

            @include('elements.report.report_heading', ['title' => 'Cash Book', 'title_excel' =>
            'Cash_Book', 'ledgerHead' => true, 'projectName' => true, 'projectTypeName' => true])

            <div class="row ExportDiv">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead>
                            <tr>
                                <th class="text-center" width="3%">SL</th>
                                <th class="text-center" width="8%">Date</th>
                                <th width="14%">Voucher Code</th>
                                <th width="25%">Account Head</th>
                                <th class="text-left" width="20%">Narration/Cheque Details</th>
                                <th class="text-left">Dedit Amount</th>
                                <th class="text-left">Credit Amount</th>
                                <th class="text-center">Balance</th>
                                <th class="text-right" width="3%">Dr/Cr</th>
                            </tr>
                        </thead>
                        <thead style="background-color: #fff; color:#000; text-align:left;">
                            <tr>
                                <td colspan="3"></td>
                                <td colspan="2">Opening Balance</td>
                                <td class="text-right" id="ob_ttl_debit_amt">0.00</td>
                                <td class="text-right" id="ob_ttl_credit_amt">0.00</td>
                                <td class="text-right" id="ob_ttl_balance">0.00</td>
                                <td class="text-center" id="ob_dr_or_cr">Dr</td>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" style="text-align: right!important;"><b>Sub Total</b></td>
                                <td class="text-right text-dark font-weight-bold" id="sub_ttl_debit_amt">0.00</td>
                                <td class="text-right text-dark font-weight-bold" id="sub_ttl_credit_amt">0.00</td>
                                <td class="text-right text-dark font-weight-bold" id="sub_ttl_balance">0.00</td>
                                <td class="text-center text-dark font-weight-bold" id="sub_ttl_dr_or_cr">Dr</td>

                            </tr>
                            <tr>
                                <td colspan="5" style="text-align: right!important;"><b>Total</b></td>
                                <td class="text-right text-dark font-weight-bold" id="ttl_debit_amt">0.00</td>
                                <td class="text-right text-dark font-weight-bold" id="ttl_credit_amt">0.00</td>
                                <td class="text-right text-dark font-weight-bold" id="ttl_balance">0.00</td>
                                <td class="text-center text-dark font-weight-bold" id="ttl_dr_or_cr">Dr</td>

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
        branch_id = null, ledger_cash = null, voucher_type_id = null) {

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
                "url": "{{route('CashBookReportDatatable')}}",
                "dataType": "json",
                "type": "post",
                "data": {
                    _token: "{{ csrf_token() }}",
                    startDate: start_date,
                    endDate: end_date,
                    projectID: project_id,
                    projectTypeID: project_type_id,
                    branchID: branch_id,
                    ledgerID: ledger_cash,
                    voucherTypeID: voucher_type_id,
                }
            },
            columns: [{
                    data: 'sl',
                    className: 'text-center'
                },
                {
                    data: 'voucher_date',
                    className: 'text-center'
                },
                {
                    data: 'voucher_code',
                    className: 'text-center'
                },
                {
                    data: 'account_head'
                },
                {
                    data: 'local_narration'
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
                    data: 'balance',
                    className: 'text-right'
                },
                {
                    data: 'debit_or_credit',
                    className: 'text-center'
                },
            ],
            drawCallback: function (oResult) {
                //  console.log(oResult.json.totalRow);
                if (oResult.json) {
                    $('#sub_ttl_debit_amt').html(oResult.json.sub_ttl_debit_amt);
                    $('#sub_ttl_credit_amt').html(oResult.json.sub_ttl_credit_amt);
                    $('#sub_ttl_balance').html(oResult.json.sub_ttl_balance);
                    $('#sub_ttl_dr_or_cr').html(oResult.json.sub_ttl_dr_or_cr);


                    $('#ob_ttl_debit_amt').html(oResult.json.ob_ttl_debit_amt);
                    $('#ob_ttl_credit_amt').html(oResult.json.ob_ttl_credit_amt);
                    $('#ob_ttl_balance').html(oResult.json.ob_ttl_balance);
                    $('#ob_dr_or_cr').html(oResult.json.ob_dr_or_cr);

                    $('#ttl_debit_amt').html(oResult.json.ttl_debit_amt);
                    $('#ttl_credit_amt').html(oResult.json.ttl_credit_amt);
                    $('#ttl_balance').html(oResult.json.ttl_balance);
                    $('#ttl_dr_or_cr').html(oResult.json.ttl_dr_or_cr);
                }
            },
        });
    }

    $(document).ready(function () {

        $('.page-header-actions').hide();

        var newOption = '<option value="-1" data-select2-id="-1" selected>All</option>';
        newOption += '<option value="-2" data-select2-id="-2">All Branch Office</option>';

        // Append it to the select
        $('#branch_id').prepend(newOption).trigger('change');

        $('#searchButton').click(function () {


            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            var project_id = $('#project_id').val();
            var project_type_id = $('#project_type_id').val();
            var branch_id = $('#branch_id').val();
            var ledger_cash = $('#ledger_cash').val();
            var voucher_type_id = $('#voucher_type_id').val();

            // if (ledger_cash == '') {
            //     swal({
            //         icon: 'warning',
            //         title: 'Warning',
            //         text: 'Please select A Ledger',
            //     });
            //     return false;
            // }
            // else if (ledger_cash != '') {
            //     $('.show').show('slow');
            // }

            $('.show').show('slow');

            // Set value in html
            $('#start_date_txt').html(start_date);
            $('#end_date_txt').html(end_date);

            $ledgerId = $('#ledger_cash option:selected').val();
            if ($ledgerId == '') {
                var ledger = $('#ledger_cash option:selected').text() + ' Ledger Head';
            } else
                var ledger = $('#ledger_cash option:selected').text();
            // ledger = ledger.substr(ledger.lastIndexOf("-") + 1);

            var project = $('#project_id option:selected').text();
            project = project.substr(project.lastIndexOf("-") + 1);

            $('#ledgerHead').html(ledger);
            $('#projectName').html(project);
            $('#projectTypeName').html($('#project_type_id option:selected').text());
            $('#branchName').html($('#branch_id option:selected').text());

            $('#reportBranch').html($('#branch_id').find("option:selected").text());

            $(".wb-minus").trigger('click');
            ajaxDataLoad(start_date, end_date, project_id, project_type_id, branch_id, ledger_cash,
                voucher_type_id);
        });
    });

</script>
@endsection
