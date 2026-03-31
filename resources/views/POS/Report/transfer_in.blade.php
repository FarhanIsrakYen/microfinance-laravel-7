@extends('Layouts.erp_master_full_width')
@section('content')

@include('elements.report.report_filter_options', ['branch' => true,
'group' => true,
'product' => true,
'startDate' => true,
'endDate' => true,
])

<div class="w-full">
    <div class="panel">
        <div class="panel-body panel-search pt-2">

            @include('elements.report.report_heading', ['title' => 'Transfer In Report', 'title_excel' =>
            'Transfer_In_Report'])

            <div class="row ExportDiv">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead>
                            <tr>
                                <th width="5%">SL</th>
                                <th>Transfer Date</th>
                                <th>Transfer Bill No</th>
                                <th>Transfer From</th>
                                <th>Transfer To</th>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total Amount</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" style="text-align:right!important;"><strong>Total</strong></td>
                                <td class="text-center"><strong id="TQuantity"></strong></td>
                                <td class="text-right"><strong id="TUnitPrice"></strong></td>
                                <td class="text-right"><strong id="TAmount">0.00</strong></td>
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
    function ajaxDataLoad(startDate = null, endDate = null, branchId = null, productId = null, groupId = null, catId =
        null,
        subCatId = null, brandID = null) {

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
                "url": "{{route('transferinDataTable')}}",
                "dataType": "json",
                "type": "post",
                "data": {
                    _token: "{{csrf_token()}}",
                    startDate: startDate,
                    endDate: endDate,
                    branchId: branchId,
                    productId: productId,
                    groupId: groupId,
                    catId: catId,
                    subCatId: subCatId,
                    brandID: brandID

                }
            },
            columns: [{
                    data: 'id',
                    className: 'text-center'
                },
                {
                    data: 'transfer_date'
                },
                {
                    data: 'transfer_bill_no'
                },
                {
                    data: 'branch_from'
                },
                {
                    data: 'branch_to'
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
                $('#totalRowDiv').html(oResult.json.totalRow);
                $('#TQuantity').html(oResult.json.totalQuantity);
                $('#TUnitPrice').html(oResult.json.totalUnitPrice);
                $('#TAmount').html(oResult.json.totalAmount);
            },
        });
    }

    $(document).ready(function () {
        $('#searchButton').click(function () {
            var startDate = $('#start_date').val();
            var endDate = $('#end_date').val();
            var branchId = $('#branch_id').val();
            var productId = $('#product_id').val();
            var groupId = $('#group_id').val();
            var catId = $('#cat_id').val();
            var subCatId = $('#sub_cat_id').val();
            var brandID = $('#brand_id').val();

            $('#reportBranch').html($('#branch_id').find("option:selected").text());
            $('#start_date_txt').html($('#start_date').val());
            $('#end_date_txt').html($('#end_date').val());

            $(".wb-minus").trigger('click');
            ajaxDataLoad(startDate, endDate, branchId, productId, groupId, catId, subCatId, brandID);
        });
    });

</script>
@endsection
