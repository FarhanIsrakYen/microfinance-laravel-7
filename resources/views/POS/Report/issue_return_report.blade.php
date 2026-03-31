@extends('Layouts.erp_master_full_width')
@section('content')

@include('elements.report.report_filter_options', ['branchFrom' => true,
'group' => true,
'product' => true,
'issueRetBillNo' => true,
'startDate' => true,
'endDate' => true,
])


<div class="w-full">
    <div class="panel">
        <div class="panel-body panel-search pt-2">

            @include('elements.report.report_heading', ['title' => 'Issue Return Report', 'title_excel' =>
            'Issue_Return_Report'])

            <div class="row ExportDiv">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead>
                            <tr>
                                <th width="3%">SL</th>
                                <th>Issue Return Date</th>
                                <th>Issue Return Bill No</th>
                                <th>Issue Return From(Branch)</th>
                                <th>Product Name</th>
                                <th>Sale Price</th>
                                <th>Total Quantity</th>
                                <th>Total Amount</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" style="text-align: right!important;"><b>TOTAL</b></td>
                                <td class="text-right text-dark font-weight-bold" id="sale_price">0.00</td>
                                <td class="text-right text-dark font-weight-bold" id="product_quantity">0.00</td>
                                <td class="text-right text-dark font-weight-bold" id="total_cost_amount">0.00</td>
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
    function ajaxDataLoad(start_date = null, end_date = null, branch_id = null, product_id = null,
        bill_no = null, group_id = null, cat_id = null, sub_cat_id = null, brand_id = null) {

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
                "url": "{{ route('issueReturnDataTable') }}",
                "dataType": "json",
                "type": "post",
                "data": {
                    _token: "{{ csrf_token() }}",
                    startDate: start_date,
                    endDate: end_date,
                    branchId: branch_id,
                    productId: product_id,
                    bill_no: bill_no,
                    groupId: group_id,
                    catId: cat_id,
                    subCatId: sub_cat_id,
                    brandId: brand_id
                }
            },
            columns: [{
                    data: 'id'
                },
                {
                    data: 'return_date',
                },
                {
                    data: 'Issue Return Bill No',
                },
                {
                    data: 'branch_name'
                },
                {
                    data: 'product_name'
                },
                {
                    data: 'sale_price',
                    className: 'text-right'
                },
                {
                    data: 'product_quantity',
                    className: 'text-center'
                },
                {
                    data: 'total_cost_amount',
                    className: 'text-right'
                },
            ],
            drawCallback: function (oResult) {

                if (oResult.json) {
                    $('#totalRowDiv').html(oResult.json.totalRow);
                    $('#sale_price').html(oResult.json.sale_price);
                    $('#product_quantity').html(oResult.json.product_quantity);
                    $('#total_cost_amount').html(oResult.json.total_cost_amount);
                }
            },
        });
    }

    $(document).ready(function () {

        // ajaxDataLoad();

        $('#searchButton').click(function () {

            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            var branch_id = $('#branch_id').val();
            var product_id = $('#product_id').val();
            var bill_no = $('#issue_r_bill_no').val();
            var group_id = $('#group_id').val();
            var cat_id = $('#cat_id').val();
            var sub_cat_id = $('#sub_cat_id').val();
            var brand_id = $('#brand_id').val();

            $('#reportBranch').html($('#branch_id').find("option:selected").text());
            $('#start_date_txt').html($('#start_date').val());
            $('#end_date_txt').html($('#end_date').val());

            $(".wb-minus").trigger('click');
            ajaxDataLoad(start_date, end_date, branch_id, product_id, bill_no,
                group_id, cat_id, sub_cat_id, brand_id);

        });
    });

</script>
@endsection
