@extends('Layouts.erp_master_full_width')
@section('content')

@include('elements.report.report_filter_options', ['branch' => true,
'endDate' => true,
])


<div class="w-full">
    <div class="panel">
        <div class="panel-body panel-search pt-2">

            @include('elements.report.report_heading', ['title' => 'Current Due Report', 'title_excel' =>
            'Current_Due_Report'])

            <div class="row ExportDiv">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead>
                            <tr>
                                <th width="3%">SL</th>
                                <th>Customer Name</th>
                                <th>Bill No.</th>
                                <th width="10%">Sales Date</th>
                                <th>Sales Amount</th>
                                <th>Total Installment No</th>
                                <th>1st Installment</th>
                                <th>Installment</th>
                                <th>Last Installment</th>
                                <th>Payable Amount</th>
                                <th>Paid Amount</th>
                                <th>Current Due</th>
                                <th>Balance</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-align:right!important;"><b>Total</b></td>
                                <td class="text-right"><b id="ttlSalesAmount">0.00</b></td>
                                <td colspan="4"></td>
                                <td class="text-right"><b id="ttlPayableAmount">0.00</b></td>
                                <td class="text-right"><b id="ttlPaidAmount">0.00</b></td>
                                <td class="text-right"><b id="ttlCurentDue">0.00</b></td>
                                <td class="text-right"><b id="ttlBalance">0.00</b></td>
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
    $(document).ready(function () {

        $('#searchButton').click(function () {
            var end_date = $('#end_date').val();
            var branch_id = $('#branch_id').val();

            $('#reportBranch').html($('#branch_id').find("option:selected").text());
            $('#start_date_txt,#text_to').html('');
            $('#end_date_txt').html($('#end_date').val());

            $(".wb-minus").trigger('click');
            ajaxDataLoad(end_date, branch_id);
        });
    });

    function ajaxDataLoad(end_date = null, branch_id = null) {

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
                "url": "{{ url('pos/report/current_due') }}",
                "dataType": "json",
                "type": "post",
                "data": {
                    endDate: end_date,
                    branchId: branch_id,
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
                    data: 'sales_bill_no'
                },
                {
                    data: 'sales_date',
                    className: 'text-center'
                },
                {
                    data: 'sales_amount',
                    className: 'text-right'
                },
                {
                    data: 'installment',
                    className: 'text-center'
                },
                {
                    data: 'first_installment',
                    className: 'text-right'
                },
                {
                    data: 'installment_amount',
                    className: 'text-right'
                },
                {
                    data: 'last_installment',
                    className: 'text-right'
                },
                {
                    data: 'payable_amount',
                    className: 'text-right'
                },
                {
                    data: 'paid_amount',
                    className: 'text-right'
                },
                {
                    data: 'current_due',
                    className: 'text-right'
                },
                {
                    data: 'total_balance',
                    className: 'text-right'
                }
            ],
            drawCallback: function (oResult) {

                if (oResult.json) {
                    $('#totalRowDiv').html(oResult.json.totalRow);
                    $('#ttlSalesAmount').html(oResult.json.ttl_sales_amount);
                    $('#ttlPayableAmount').html(oResult.json.ttl_payable_amount);
                    $('#ttlPaidAmount').html(oResult.json.ttl_paid_amt);
                    $('#ttlCurentDue').html(oResult.json.ttl_current_due);
                    $('#ttlBalance').html(oResult.json.ttl_total_balance);
                    // $('#InsAmt').html(oResult.json.ttl_inst_amount);
                }
            }
        });
    }

</script>
@endsection
