@extends('Layouts.erp_master_full_width')
@section('content')

@include('elements.report.report_filter_options', [
'product' => true,
'startDate' => true,
'endDate' => true,
])


<div class="w-full">
    <div class="panel">
        <div class="panel-body panel-search pt-2">

            @include('elements.report.report_heading', ['title' => 'Purchase Return Report', 'title_excel' =>
            'Purchase_Return_Report'])

            <div class="row ExportDiv">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead>
                            <tr>
                                <th width="3%">SL</th>
                                <th>Return Date</th>
                                <th>Return Bill No</th>
                                <th>Product Name</th>
                                <th>Product Quantity</th>
                                <th>Unit Price</th>
                                <th>Total Amount</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-align: right!important;"><b>TOTAL</b></td>
                                <td class="text-center text-dark font-weight-bold" id="product_quantity">0</td>
                                <td class="text-right text-dark font-weight-bold" id="unit_cost_price">0</td>
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
    function ajaxDataLoad(start_date = null, end_date = null, product_id = null) {

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
                "url": "{{ route('PurReturnDataTable') }}",
                "dataType": "json",
                "type": "post",
                "data": {
                    _token: "{{ csrf_token() }}",
                    startDate: start_date,
                    endDate: end_date,
                    productId: product_id
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
                    data: 'product_name'
                },
                {
                    data: 'product_quantity',
                    className: 'text-center'
                },
                {
                    data: 'unit_cost_price',
                    className: 'text-right'
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
                    $('#unit_cost_price').html(oResult.json.unit_cost_price);
                    $('#total_cost_price').html(oResult.json.total_cost_price);
                }
            },
        });
    }

    $(document).ready(function () {
        $('#searchButton').click(function () {
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            var product_id = $('#product_id').val();
            $('#start_date_txt').html(start_date);
            $('#end_date_txt').html(end_date);

            $(".wb-minus").trigger('click');
            ajaxDataLoad(start_date, end_date, product_id);
        });



    });

</script>
@endsection
