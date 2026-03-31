@extends('Layouts.erp_master_full_width')
@section('content')

@include('elements.report.report_filter_options', ['group' => true,
'product' => true,
'supplier' => true,
'purchaseNo' => true,
'orderNo' => true,
'invoiceNo' => true,
'startDate' => true,
'endDate' => true,
])

<div class="w-full">
    <div class="panel">
        <div class="panel-body panel-search pt-2">

            @include('elements.report.report_heading', ['title' => 'Purchase Report', 'title_excel' =>
            'Purchase_Report'])


            <div class="row ExportDiv">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead>
                            <tr>
                                <th width="3%">SL</th>
                                <th>Purchase Date</th>
                                <th>Purchase No</th>
                                <th>Order No</th>
                                <th>Invoice No</th>
                                <th>Supplier Name</th>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total Amount</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7" style="text-align: right!important;"><b>TOTAL</b></td>
                                <td class="text-center text-dark font-weight-bold" id="ttl_pro_qnt">0</td>
                                <td>&nbsp</td>
                                <td class="text-right text-dark font-weight-bold" id="total_amount">0.00</td>
                            </tr>
                        </tfoot>
                    </table>

                    @include('elements.signature.signatureSet')
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function ajaxDataLoad(SDate = null, EDate = null, txt_product_name = null,
        txt_supplier_name = null, PurchaseNo = null, InvoiceNo = null, OrderNo = null, PGroupID = null, CategoryId =
        null,
        SubCatID = null, BrandID = null) {

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
                "url": "{{route('purchasereportDatatable')}}",
                "dataType": "json",
                "type": "post",
                "data": {
                    SDate: SDate,
                    EDate: EDate,
                    txt_product_name: txt_product_name,
                    txt_supplier_name: txt_supplier_name,
                    PurchaseNo: PurchaseNo,
                    InvoiceNo: InvoiceNo,
                    OrderNo: OrderNo,
                    PGroupID: PGroupID,
                    CategoryId: CategoryId,
                    SubCatID: SubCatID,
                    BrandID: BrandID,
                    // PDeliveryID : PDeliveryID
                }
            },
            columns: [{
                    data: 'id',
                    className: 'text-center'
                },
                {
                    data: 'purchase_date',
                },
                {
                    data: 'purchase_bill_no',
                },
                {
                    data: 'order_no',
                },
                {
                    data: 'invoice_no',
                },
                {
                    data: 'supplier_name'
                },
                // {
                //     data: 'branch_name'
                // },
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
                //  console.log(oResult.json.totalRow);
                if (oResult.json) {
                    $('#totalRowDiv').html(oResult.json.totalRow);
                    $('#ttl_pro_qnt').html(oResult.json.total_product_qnt);
                    $('#total_amount').html(oResult.json.total_amount);
                }
            },
        });
    }

    $(document).ready(function () {

        $('#searchButton').click(function () {

            var SDate = $('#start_date').val();
            var EDate = $('#end_date').val();
            var txt_product_name = $('#product_id').val();
            var txt_supplier_name = $('#supplier_id').val();
            var PurchaseNo = $('#purchase_id').val();
            var InvoiceNo = $('#invoice_id').val();
            var OrderNo = $('#order_id').val();
            var PGroupID = $('#group_id').val();
            var CategoryId = $('#cat_id').val();
            var SubCatID = $('#sub_cat_id').val();
            var BrandID = $('#brand_id').val();
            var PDeliveryID = $('#delivery_id').val();

            $(".wb-minus").trigger('click');
            $('#start_date_txt').html($('#start_date').val());
            $('#end_date_txt').html($('#end_date').val());
            ajaxDataLoad(SDate, EDate, txt_product_name, txt_supplier_name, PurchaseNo, InvoiceNo,
                OrderNo, PGroupID,
                CategoryId, SubCatID,
                BrandID);

            // $('#reportBranch').html($('#branch_id').find("option:selected").text());


        });

    });

</script>

@endsection
