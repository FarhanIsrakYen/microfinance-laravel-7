@extends('Layouts.erp_master_full_width')
@section('content')

@include('elements.report.report_filter_options', ['branch' => true,
'customer' => true,
'product' => true,
'startDate' => true,
'endDate' => true,
])

<div class="w-full">
    <div class="panel">
        <div class="panel-body panel-search pt-2">

            @include('elements.report.report_heading', ['title' => 'Customer Wise Sales Report', 'title_excel' =>
            'Customer_Sales_Report'])

            <div class="row ExportDiv">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead>
                            <tr>
                                <th width="3%">SL</th>
                                <th width="15%">Customer Name</th>
                                <th width="10%">Customer mobile</th>
                                <th width="7%">Sales Type</th>
                                <th width="10%">Bill No</th>
                                <th width="10%">Sales Date</th>
                                <th>Product</th>
                                <th width="8%">Quantity</th>
                                <th width="9%">Total Sales Amount <br> (With P.F.+ Profit)</th>
                                <th width="9%">Paid Amount</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7" style="text-align:right!important;"><b>TOTAL</b></td>
                                <td class="text-center tfoot_cls" id="ttl_product_qty">0</td>
                                <td class="text-right tfoot_cls" id="ttl_sales_amount">0.00 </td>
                                <td class="text-right tfoot_cls" id="ttl_paid_amount">0.00</td>
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
    function ajaxDataLoad(start_date = null, end_date = null, customer_id = null, branch_id = null, product_id = null) {
        // group_id = null, cat_id = null, sub_cat_id = null, brand_id = null

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
                "url": "{{ route('CustDetailsDataTable') }}",
                "dataType": "json",
                "type": "post",
                "data": {
                    // _token: "{{ csrf_token() }}",
                    startDate: start_date,
                    endDate: end_date,
                    customerId: customer_id,
                    branchId: branch_id,
                    productId: product_id
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
                    data: 'customer_mobile'
                },
                {
                    data: 'sales_type'
                },
                {
                    data: 'sales_bill_no',
                    // width: '15%'
                },
                {
                    data: 'sales_date',
                    // width: '15%'
                },
                {
                    data: 'product_name'
                },
                {
                    data: 'total_quantity',
                    className: 'text-center'
                },

                {
                    data: 'total_sales_amount',
                    className: 'text-right'
                },
                {
                    data: 'paid_amount',
                    className: 'text-right'
                }
            ],
            drawCallback: function (oResult) {

                if (oResult.json) {
                    $('#totalRowDiv').html(oResult.json.totalRow);
                    $('#ttl_product_qty').html(oResult.json.ttl_product_qty);
                    $('#ttl_sales_amount').html(oResult.json.ttl_sales_amount);
                    $('#ttl_paid_amount').html(oResult.json.ttl_paid_amount);
                }
            },
        });
    }

    $(document).ready(function () {

        $('#searchButton').click(function () {

            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            var customer_id = $('#customer_id').val();
            var employee_id = $('#employee_id').val();
            var branch_id = $('#branch_id').val();
            var product_id = $('#product_id').val();

            if (customer_id == '') {
                swal({
                    icon: 'warning',
                    title: 'Warning',
                    text: 'Please select a Customer',
                });
                return false;
            }

            $(".wb-minus").trigger('click');
            $('#reportBranch').html($('#branch_id').find("option:selected").text());
            $('#start_date_txt').html($('#start_date').val());
            $('#end_date_txt').html($('#end_date').val());

            ajaxDataLoad(start_date, end_date, customer_id, branch_id, product_id);
        });

        // Generate customer no. On Changing Brannch
        $('#branch_id').change(function () {
            var BranchID = $('#branch_id').val();
            if (BranchID != '') {
                fnAjaxSelectBox('customer_id',
                    BranchID,
                    '{{base64_encode("pos_customers")}}',
                    '{{base64_encode("branch_id")}}',
                    '{{base64_encode("id,customer_name,customer_no")}}',
                    '{{url("/ajaxSelectBox")}}');
            }
        });
    });

</script>
@endsection
