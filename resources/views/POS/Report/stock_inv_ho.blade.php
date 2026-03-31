@extends('Layouts.erp_master_full_width')
@section('content')

@include('elements.report.report_filter_options', ['group' => true,
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

            @include('elements.report.report_heading', ['title' => 'Stock Inventory Report For Head Office',
            'title_excel' =>
            'Stock_Report_INV_HO'])

            <div class="row ExportDiv">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead class="text-center">
                            <tr>
                                <th rowspan="3" width="3%">SL</th>
                                <th rowspan="3" width="12%">Product Name</th>
                                <th rowspan="2" colspan="2">Opening Stock</th>
                                <th colspan="6">Add Current Prieod</th>
                                <th colspan="4">Less Current Prieod</th>
                                <th rowspan="2" colspan="2">Closing Stock</th>
                            </tr>
                            <tr>
                                <th colspan="2">Purchases</th>
                                <th colspan="2">Issue Return</th>
                                <th colspan="2">Adjustment</th>
                                <th colspan="2">Purchase Return</th>
                                <th colspan="2">Issue</th>
                                <!-- {{-- <th colspan="2">Adjustment</th> --}} -->
                            </tr>
                            <tr>
                                <th>Qty</th>
                                <th>Amount</th>

                                <th>Qty</th>
                                <th>Amount</th>

                                <th>Qty</th>
                                <th>Amount</th>

                                <th>Qty</th>
                                <th>Amount</th>

                                <th>Qty</th>
                                <th>Amount</th>

                                <th>Qty</th>
                                <th>Amount</th>

                                <th>Qty</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="text-right"><b>TOTAL:</b></td>
                                <td class="text-center"><b id="oSQtn">0</b></td>
                                <td class="text-right"><b id="oSAmt">0.00</b></td>

                                <td class="text-center"><b id="pQtn">0</b></td>
                                <td class="text-right"><b id="pAmt">0.00</b></td>

                                <td class="text-center"><b id="isRQtn">0</b></td>
                                <td class="text-right"><b id="isRAmt">0.00</b></td>

                                <td class="text-center"><b id="adjQtn">0</b></td>
                                <td class="text-right"><b id="adjAmt">0.00</b></td>

                                <td class="text-center"><b id="pRQtn">0</b></td>
                                <td class="text-right"><b id="pRAmt">0.00</b></td>

                                <td class="text-center"><b id="isQtn">0</b></td>
                                <td class="text-right"><b id="isAmt">0.00</b></td>

                                <td class="text-center"><b id="cStockQtn">0</b></td>
                                <td class="text-right"><b id="cStockAmt">0.00</b></td>
                            </tr>
                        </tfoot>
                    </table>
                    @include('../elements.signature.signatureSet')
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* customprint */
    @media print {
        @page {
            size: landscape;
            margin: 10px !important;
        }

        .table>thead th {
            font-size: 80% !important;
            padding: 2x !important;
        }

        .table>tbody td {
            font-size: 80% !important;
            padding: 2x !important;
        }

        .table>tfoot td {
            font-size: 80% !important;
            padding: 2x !important;
        }

    }

</style>

<script>
    $(document).ready(function () {

        $('#searchButton').click(function () {

            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            var zone_id = $('#zone_id').val();
            var area_id = $('#area_id').val();
            var supplier_id = $('#supplier_id').val();
            var group_id = $('#group_id').val();
            var cat_id = $('#cat_id').val();
            var sub_cat_id = $('#sub_cat_id').val();
            var brand_id = $('#brand_id').val();
            var model_id = $('#model_id').val();
            var product_id = $('#product_id').val();
            var stock = $('#stock').val();

            $('#start_date_txt').html(start_date);
            $('#end_date_txt').html(end_date);

            $(".wb-minus").trigger('click');
            ajaxDataLoad(start_date, end_date, zone_id, area_id, supplier_id, group_id, cat_id,
                sub_cat_id, brand_id, model_id, product_id, stock);

        });



    });

    function ajaxDataLoad(start_date = null, end_date = null, zone_id = null, area_id = null, supplier_id = null,
        group_id = null, cat_id = null, sub_cat_id = null, brand_id = null, model_id = null, product_id = null, stock =
        null) {

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
                "url": "{{ url('pos/report/stock_inv_ho') }}",
                "dataType": "json",
                "type": "post",
                "data": {
                    _token: "{{ csrf_token() }}",
                    startDate: start_date,
                    endDate: end_date,
                    zoneId: zone_id,
                    areaId: area_id,
                    supplierId: supplier_id,
                    groupId: group_id,
                    catId: cat_id,
                    subCatId: sub_cat_id,
                    brandId: brand_id,
                    modelId: model_id,
                    productId: product_id,
                    stock: stock
                }
            },
            columns: [{
                    data: 'id'
                },
                {
                    data: 'product_name'
                },
                {
                    data: 'openning_stock_qtn',
                    className: 'text-center'
                },
                {
                    data: 'openning_stock_amt',
                    className: 'text-right'
                },
                {
                    data: 'purchase_qtn',
                    className: 'text-center'
                },
                {
                    data: 'purchase_amt',
                    className: 'text-right'
                },
                {
                    data: 'issue_return_qtn',
                    className: 'text-center'
                },
                {
                    data: 'issue_return_amt',
                    className: 'text-right'
                },
                {
                    data: 'adj_qtn',
                    className: 'text-center'
                },
                {
                    data: 'adj_amt',
                    className: 'text-right'
                },
                {
                    data: 'purchase_return_qtn',
                    className: 'text-center'
                },
                {
                    data: 'purchase_return_amt',
                    className: 'text-right'
                },
                {
                    data: 'issue_qtn',
                    className: 'text-center'
                },
                {
                    data: 'issue_amt',
                    className: 'text-right'
                },
                {
                    data: 'stock_qtn',
                    className: 'text-center'
                },
                {
                    data: 'stock_amt',
                    className: 'text-right'
                }
            ],
            drawCallback: function (oResult) {

                if (oResult.json) {
                    $('#totalRowDiv').html(oResult.json.totalRow);
                    $('#oSQtn').html(oResult.json.os_ttl_qtn);
                    $('#oSAmt').html(oResult.json.os_ttl_amt);

                    $('#pQtn').html(oResult.json.pur_ttl_qtn);
                    $('#pAmt').html(oResult.json.pur_ttl_amt);

                    $('#isRQtn').html(oResult.json.isr_ttl_qtn);
                    $('#isRAmt').html(oResult.json.isr_ttl_amt);

                    $('#adjQtn').html(oResult.json.adj_ttl_qtn);
                    $('#adjAmt').html(oResult.json.adj_ttl_amt);

                    $('#pRQtn').html(oResult.json.pr_ttl_qtn);
                    $('#pRAmt').html(oResult.json.pr_ttl_amt);

                    $('#isQtn').html(oResult.json.is_ttl_qtn);
                    $('#isAmt').html(oResult.json.is_ttl_amt);

                    $('#cStockQtn').html(oResult.json.stock_ttl_qtn);
                    $('#cStockAmt').html(oResult.json.stock_ttl_amt);
                }
            }
        });
    }

</script>
@endsection
