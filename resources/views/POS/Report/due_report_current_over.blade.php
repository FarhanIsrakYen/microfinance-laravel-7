@extends('Layouts.erp_master_full_width')
@section('content')

@include('elements.report.report_filter_options', ['zone' => true,
'area' => true,
'branch' => true,
'endDate' => true,
])

<div class="w-full">
    <div class="panel">
        <div class="panel-body panel-search pt-2">

            @include('elements.report.report_heading', ['title' => 'Current And Over Due Report', 'title_excel' =>
            'Current_Over_Due_Report'])

            <div class="row ExportDiv">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead>
                            <tr>
                                <th width="3%">SL</th>
                                <th>Customer Name</th>
                                <th>Bill No</th>

                                <th width="10%">Sales Date</th>
                                <th>Sales Amount</th>

                                <th>Total Installment No</th>
                                <th>1st Installment</th>
                                <th>Installment</th>
                                <th>Last Installment</th>
                                <th width="10%">Last Installment Date</th>

                                <th>Payable Amount</th>
                                <th>Paid Amount</th>
                                <th>Current Due</th>
                                <th>Over Due</th>
                                <th>Balance</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-align:right!important;"><b>Total:</b></td>
                                <td class="text-right"><b id="ttlSalesAmount">0.00</b></td>
                                <td colspan="5"></td>

                                <td class="text-right"><b id="ttlPayableAmount">0.00</b></td>
                                <td class="text-right"><b id="ttlPaidAmount">0.00</b></td>
                                <td class="text-right"><b id="ttlCurentDue">0.00</b></td>
                                <td class="text-right"><b id="ttlOverDue">0.00</b></td>
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
        var zone_id = $('#zone_id').val();
        var area_id = $('#area_id').val();
        var branch_id = $('#branch_id').val();

        $(".wb-minus").trigger('click');
        ajaxDataLoad(end_date, zone_id, area_id, branch_id);
    });

    $('#reportBranch').html($('#branch_id').find("option:selected").text());
    $('#end_date_txt').html($('#end_date').val());
    });
    });

    function ajaxDataLoad(end_date = null, zone_id = null, area_id = null, branch_id = null) {

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
                "url": "{{ url('pos/report/current_n_over_due') }}",
                "dataType": "json",
                "type": "post",
                "data": {
                    endDate: end_date,
                    zoneId: zone_id,
                    areaId: area_id,
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
                    data: 'last_installment_date',
                    className: 'text-center'
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
                    data: 'over_due',
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

                    $('#ttlPaidAmount').html(oResult.json.ttl_paid_amount);
                    $('#ttlCurentDue').html(oResult.json.ttl_current_due);
                    $('#ttlOverDue').html(oResult.json.ttl_over_due);

                    $('#ttlBalance').html(oResult.json.ttl_total_balance);
                }
            },
        });

</script>
@endsection
