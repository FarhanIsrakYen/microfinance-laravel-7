@extends('Layouts.erp_master_full_width')
@section('content')

@include('elements.report.report_filter_options', ['branch' => true,
'group' => true,
'model' => true,
'product' => true,
'supplier' => true,
'stock' => true,
'startDate' => true,
'endDate' => true,
])

<div class="w-full">
    <div class="panel">
        <div class="panel-body panel-search pt-2">

            @include('elements.report.report_heading', ['title' => 'Stock Report For Branch', 'title_excel' =>
            'Stock_Report_branch'])

            <div class="row ExportDiv">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead>
                            <tr>
                                <th width="3%">SL</th>
                                <th>Product Name</th>
                                <th>Opening Balance</th>
                                <th>Issue</th>
                                <th>Issue Return</th>
                                <th>Transfer In</th>
                                <th>Transfer Out</th>
                                <th>Sales</th>
                                <th>Sales Return</th>
                                <th>Adj</th>
                                <th>Stock</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td style="text-align:right!important;" colspan="2"><b>TOTAL</b></td>

                                <td class="text-center text-dark font-weight-bold" id="o_stock">0</td>

                                <td class="text-center text-dark font-weight-bold" id="issue">0</td>

                                <td class="text-center text-dark font-weight-bold" id="issue_r">0</td>

                                <td class="text-center text-dark font-weight-bold" id="transfer_in">0</td>

                                <td class="text-center text-dark font-weight-bold" id="transfer_out">0</td>

                                <td class="text-center text-dark font-weight-bold" id="sales">0</td>

                                <td class="text-center text-dark font-weight-bold" id="sales_r">0</td>

                                <td class="text-center text-dark font-weight-bold" id="adj">0</td>

                                <td class="text-center text-dark font-weight-bold" id="stock">0</td>
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

            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            var branch_id = $('#branch_id').val();
            var supplier_id = $('#supplier_id').val();
            var group_id = $('#group_id').val();
            var cat_id = $('#cat_id').val();
            var sub_cat_id = $('#sub_cat_id').val();
            var brand_id = $('#brand_id').val();
            var model_id = $('#model_id').val();
            var product_id = $('#product_id').val();
            var stockSearch = $('#stockSearch').val();

            $('#reportBranch').html($('#branch_id').find("option:selected").text());
            $('#start_date_txt').html(start_date);
            $('#end_date_txt').html(end_date);

            $(".wb-minus").trigger('click');
            ajaxDataLoad(start_date, end_date, branch_id, supplier_id, group_id, cat_id, sub_cat_id,
                brand_id, model_id, product_id, stockSearch);
        });
    });

    function ajaxDataLoad(start_date = null, end_date = null, branch_id = null, supplier_id = null, group_id = null,
        cat_id = null, sub_cat_id = null, brand_id = null, model_id = null, product_id = null, stockSearch = null) {

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
                "url": "{{ url('pos/report/stock_branch') }}",
                "dataType": "json",
                "type": "post",
                "data": {
                    startDate: start_date,
                    endDate: end_date,
                    branchId: branch_id,
                    supplierId: supplier_id,
                    groupId: group_id,
                    catId: cat_id,
                    subCatId: sub_cat_id,
                    brandId: brand_id,
                    modelId: model_id,
                    productId: product_id,
                    stockSearch: stockSearch
                }
            },
            columns: [{
                    data: 'id',
                    className: 'text-center'
                },
                {
                    data: 'product_name'
                },
                {
                    data: 'openning_stock',
                    className: 'text-center'
                },
                {
                    data: 'Issue',
                    className: 'text-center'
                },
                {
                    data: 'IssueReturn',
                    className: 'text-center'
                },
                {
                    data: 'transfer_in',
                    className: 'text-center'
                },
                {
                    data: 'transfer_out',
                    className: 'text-center'
                },
                {
                    data: 'sales',
                    className: 'text-center'
                },
                {
                    data: 'sales_return',
                    className: 'text-center'
                },
                {
                    data: 'adj',
                    className: 'text-center'
                },
                {
                    data: 'stock',
                    className: 'text-center'
                },
            ],
            drawCallback: function (oResult) {

                if (oResult.json) {
                    $('#totalRowDiv').html(oResult.json.totalRow);
                    $('#o_stock').html(oResult.json.ttlOStock);
                    // $('#purchase').html(oResult.json.ttlPurchase);
                    // $('#purchase_r').html(oResult.json.ttlPurchaseR);
                    $('#issue').html(oResult.json.ttlIssue);
                    $('#issue_r').html(oResult.json.ttlIssueR);
                    $('#transfer_in').html(oResult.json.ttlTransferIn);
                    $('#transfer_out').html(oResult.json.ttlTransferOut);
                    $('#sales').html(oResult.json.ttlSales);
                    $('#sales_r').html(oResult.json.ttlSalesR);
                    $('#adj').html(oResult.json.ttlAdj);
                    $('#stock').html(oResult.json.ttlStock);
                }
            },
        });
    }

    $('#branch_id').change(function () {

        fnAjaxSelectBox('supplier_id',
            this.value,
            '{{base64_encode("pos_suppliers")}}',
            '{{base64_encode("branch_id")}}',
            '{{base64_encode("id,sup_name")}}',
            '{{url("/ajaxSelectBox")}}');
    });

    // function getDownloadPDF() {
    //     $('.clsDataTablecopy').tableExport({
    //         type: 'pdf',
    //         fileName: 'ho_stock_report',
    //         jspdf: {
    //             orientation: 'l',
    //             format: 'a4',
    //             margins: {
    //                 left: 10,
    //                 right: 10,
    //                 top: 20,
    //                 bottom: 20
    //             },
    //             autotable: {
    //                 styles: {
    //                     overflow: 'linebreak'
    //                 },
    //                 tableWidth: 'auto'
    //             }
    //         }
    //     });
    // }

</script>

<!-- {{-- for save file which is download --}} -->
{{-- <!-- <script type="text/javascript" src="{{ asset('assets/js/pdf/tableExport.min.js') }}"></script> --> --}}

<!-- {{-- for export datatable into pdf --}} -->
{{-- <!-- <script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.min.js') }}"></script> --> --}}
{{-- <!-- <script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.plugin.autotable.js') }}"></script> --> --}}
@endsection
