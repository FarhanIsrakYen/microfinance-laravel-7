@extends('Layouts.erp_master_full_width')
@section('content')

@include('elements.report.report_filter_options', ['zone' => true,
'area' => true,
'branch' => true,
'product' => true,
'startDate' => true,
'endDate' => true,
])

<div class="w-full">
    <div class="panel">
        <div class="panel-body panel-search pt-2">

            @include('elements.report.report_heading', ['title' => 'Sales Return Report', 'title_excel' =>
            'Sales_Return_Report'])

            <div class="row ExportDiv">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead>
                            <tr>
                                <th width="3%">SL</th>
                                <th>Return Date</th>
                                <th>Return Bill No</th>
                                <th>Sales Bill No</th>
                                <th>Branch</th>
                                <th>Product Name</th>
                                <th>Product Quantity</th>
                                <th>Total Amount</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" style="text-align: right!important;"><b>TOTAL</b></td>
                                <td class="text-center text-dark font-weight-bold" id="product_quantity">0</td>
                                <td class="text-right text-dark font-weight-bold" id="total_cost_price">0.00</td>
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
    function ajaxDataLoad(zone_id = null, area_id = null, branch_id = null, customer_id = null, employee_id = null,
        sales_type = null, product_id = null,
        start_date = null, end_date = null, ) {

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
                "url": "{{ route('SalesReturnDataTable') }}",
                "dataType": "json",
                "type": "post",
                "data": {
                    _token: "{{ csrf_token() }}",
                    zoneId: zone_id,
                    areaId: area_id,
                    branchId: branch_id,
                    customerId: customer_id,
                    empId: employee_id,
                    saleTypeId: sales_type,
                    productId: product_id,
                    startDate: start_date,
                    endDate: end_date,
                }
            },
            columns: [{
                    data: 'id',
                    className: 'text-center'
                },
                {
                    data: 'return_date',
                },
                {
                    data: 'return_bill_no',
                },
                {
                    data: 'sales_bill_no',
                },
                {
                    data: 'branch_name',
                },
                {
                    data: 'product_name'
                },
                {
                    data: 'product_quantity',
                    className: 'text-center'
                },
                {
                    data: 'total_cost_price',
                    className: 'text-right'
                },
            ],
            drawCallback: function (oResult) {

                if (oResult.json) {
                    $('#totalRowDiv').html(oResult.json.totalRow);
                    $('#product_quantity').html(oResult.json.product_quantity);
                    $('#total_cost_price').html(oResult.json.total_cost_price);
                }
            },
        });
    }

    $(document).ready(function () {

        $('#searchButton').click(function () {
            var zone_id = $('#zone_id').val();
            var area_id = $('#area_id').val();
            var branch_id = $('#branch_id').val();
            var customer_id = $('#customer_id').val();
            var employee_id = $('#employee_id').val();
            var sales_type = $('#sales_type').val();
            var product_id = $('#product_id').val();
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();

            $('#reportBranch').html($('#branch_id').find("option:selected").text());
            $('#start_date_txt').html($('#start_date').val());
            $('#end_date_txt').html($('#end_date').val());

            $(".wb-minus").trigger('click');
            ajaxDataLoad(zone_id, area_id, branch_id, customer_id, employee_id, sales_type, product_id,
                start_date, end_date);
        });
    });

</script>
@endsection
