@extends('Layouts.erp_master_full_width')
@section('content')

@include('elements.report.report_filter_options', ['branch' => true,
'startDate' => true,
'endDate' => true,
'salesBillNo' => true,
])

<div class="w-full">
    <div class="panel">
        <div class="panel-body panel-search pt-2">

            @include('elements.report.report_heading', ['title' => 'All Collection Report', 'title_excel' =>
            'All_Collection_Report'])

            <div class="row ExportDiv">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead class="text-center">
                            <tr>
                                <th width="3%">SL</th>
                                <th>Collection Date</th>
                                <th>Collection No</th>
                                <th>Customer Name</th>
                                <th>Sales Bill No</th>
                                <th>Sales Amount<br>(1)</th>
                                <th>Processing Fee<br>(2)</th>
                                <th>Total Sales Amount<br>(1+2)</th>
                                <th>Collection Amount<br>(3)</th>
                                <th>Total Collection Amount<br>(2+3)</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" style="text-align: right!important;"><b>Total</b></td>
                                <td class="text-right text-dark font-weight-bold"><b id="sales_amount">0.00</b><sup
                                        style="color:red; font-size:10px;">*</sup></td>
                                <td class="text-right text-dark font-weight-bold"><b id="processing_fee">0.00</b></td>
                                <td class="text-right text-dark font-weight-bold"><b id="total_sales">0.00</b><sup
                                        style="color:red; font-size:10px;">*</sup></td>
                                <td class="text-right text-dark font-weight-bold"><b id="collection_amount">0.00</b>
                                </td>
                                <td class="text-right text-dark font-weight-bold"><b id="total_collection">0.00</b></td>
                            </tr>
                        </tfoot>
                    </table>
                    <p><b>N.B:</b> <span style="color:red;">*</span> - Ignore total amount calculation for duplicate
                        sales bill no. </p>
                    @include('../elements.signature.signatureSet')
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Page -->

<script>
    function ajaxDataLoad(start_date = null, end_date = null, salesBillNo = null,  branch_id = null) {

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
                "url": "{{route('allcollectionDataTable')}}",
                "dataType": "json",
                "type": "post",
                "data": {
                    _token: "{{ csrf_token() }}",
                    startDate: start_date,
                    endDate: end_date,
                    salesBillNo: salesBillNo,
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
                    data: 'collection_no',
                },
                {
                    data: 'customer_name'
                },
                {
                    data: 'sales_bill_no',
                },
                {
                    data: 'sales_amount',
                    className: 'text-right'
                },
                {
                    data: 'processing_fee',
                    className: 'text-right'
                },
                {
                    data: 'total_sales',
                    className: 'text-right'
                },
                {
                    data: 'collection_amount',
                    className: 'text-right'
                }, {
                    data: 'total_collection',
                    className: 'text-right'
                },
            ],
            drawCallback: function (oResult) {
                if (oResult.json) {
                    $('#totalRowDiv').html(oResult.json.totalRow);
                    $('#sales_amount').html(oResult.json.ttl_sales_amount);
                    $('#processing_fee').html(oResult.json.ttl_processing_fee);
                    $('#total_sales').html(oResult.json.ttl_total_sales);
                    $('#collection_amount').html(oResult.json.ttl_collection_amount);
                    $('#total_collection').html(oResult.json.ttl_total_collection);
                }
            },
        });
    }

    $(document).ready(function () {

        $('#searchButton').click(function () {
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            var salesBillNo = $('#sales_bill_no').val();
            var branch_id = $('#branch_id').val();

            $('#reportBranch').html($('#branch_id').find("option:selected").text());
            $('#start_date_txt').html(start_date);
            $('#end_date_txt').html(end_date);

            $(".wb-minus").trigger('click');
            ajaxDataLoad(start_date, end_date, salesBillNo, branch_id);
        });
    });

    $('#branch_id').change(function(){
        var branchId =  $('#branch_id').val();
    
        $.ajax({
                method: "GET",
                url: "{{url('/ajaxGetSalesBillNo')}}",
                dataType: "text",
                data: {
                    branchId: branchId,
                },
                success: function(data) {
                    if (data) {

                        $('#sales_bill_no')
                            .find('option')
                            .remove()
                            .end()
                            .append(data);

                    }
                }
            });
    });

</script>
@endsection
