@extends('Layouts.erp_master_full_width')
@section('content')

@include('elements.report.report_filter_options', ['branch' => true,
'startDate' => true,
'endDate' => true,
])


<div class="w-full">
    <div class="panel">
        <div class="panel-body panel-search pt-2">

            @include('elements.report.report_heading', ['title' => 'Collection Report With Profit', 'title_excel' =>
            'Collection_With_Profit'])

            <div class="row ExportDiv">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead>
                            <tr>
                                <th width="5%">SL</th>
                                <th width="10%">Collection Date</th>
                                <th width="20%">Customer Name</th>
                                <th width="15%">Sales Bill No</th>
                                <!-- <th width="5">Customer Code</th> -->
                                <th width="10%">Collection Amount(With PF)</th>
                                <th width="10%">Processing Fee</th>
                                <th width="10%">Net Collection Amount</th>
                                <th width="10%">Collection Amount(Pr.)</th>
                                <th width="10%">Profit</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-align: right!important;"><b>Total</b></td>
                                <td class="text-right text-dark font-weight-bold" id="pf_collection_amount"><b>0.00</b>
                                </td>
                                <td class="text-right text-dark font-weight-bold" id="service_charge"><b>0.00</b></td>
                                <td class="text-right text-dark font-weight-bold" id="collection_amount"><b>0.00</b>
                                </td>
                                <td class="text-right text-dark font-weight-bold" id="principal_amount"><b>0.00</b></td>
                                <td class="text-right text-dark font-weight-bold" id="installment_profit"><b>0.00</b>
                                </td>
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
    function ajaxDataLoad(start_date = null, end_date = null, branch_id = null) {
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
                "url": "{{route('collectionProfitDatatable')}}",
                "dataType": "json",
                "type": "post",
                "data": {
                    _token: "{{ csrf_token() }}",
                    startDate: start_date,
                    endDate: end_date,
                    branchId: branch_id
                }
            },
            columns: [{
                    data: 'sl',
                    className: 'text-center'
                },
                {
                    data: 'collection_date',
                },
                {
                    data: 'customer_name'
                },
                {
                    data: 'sales_bill_no',

                },
                // {
                //     data: 'customer_no',

                // },
                {
                    data: 'pf_collection_amount',
                    className: 'text-right'
                },
                {
                    data: 'service_charge',
                    className: 'text-right'
                },
                {
                    data: 'collection_amount',
                    className: 'text-right'
                },
                {
                    data: 'principal_amount',
                    className: 'text-right'
                },
                {
                    data: 'installment_profit',
                    className: 'text-right'
                },
            ],
            drawCallback: function (oResult) {
                if (oResult.json) {
                    $('#totalRowDiv').html(oResult.json.totalRow);
                    $('#pf_collection_amount').html(oResult.json.total_pf_col_amount);
                    $('#service_charge').html(oResult.json.ttl_service_charge);
                    $('#collection_amount').html(oResult.json.ttl_collection_amount);
                    $('#principal_amount').html(oResult.json.ttl_principal_amount);
                    $('#installment_profit').html(oResult.json.ttl_installment_profit);
                }
            },
        });
    }
    $(document).ready(function () {
        $('#searchButton').click(function () {
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            var branch_id = $('#branch_id').val();

            $('#reportBranch').html($('#branch_id').find("option:selected").text());
            $('#start_date_txt').html(start_date);
            $('#end_date_txt').html(end_date);

            $(".wb-minus").trigger('click');
            ajaxDataLoad(start_date, end_date, branch_id);
        });


    });

</script>
@endsection
