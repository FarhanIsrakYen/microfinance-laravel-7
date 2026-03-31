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

            @include('elements.report.report_heading', ['title' => 'Stock Report For Head Office', 'title_excel' =>
            'Stock_Report_HO'])

            <div class="row ExportDiv">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead class="text-center">
                            <tr>
                                <th rowspan="3" width="3%">SL</th>
                                <th rowspan="3" width="13%">Product Name</th>
                                <th rowspan="2" colspan="2" width="11%">Opening Stock</th>
                                <th colspan="8" width="31%">Add Current Prieod</th>
                                <th colspan="8" width="31%">Less Current Prieod</th>
                                <th rowspan="2" colspan="2" width="11%">Closing Stock</th>
                            </tr>
                            <tr>
                                <th colspan="2">Issue</th>
                                <th colspan="2">Sales Return</th>
                                <th colspan="2">Transfer In</th>
                                <th colspan="2">Adjustment</th>
                                <th colspan="2">Sales</th>
                                <th colspan="2">Issue Return</th>
                                <th colspan="2">Transfer Out</th>
                                <th colspan="2">Adjustment</th>
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
                                <td colspan="2" class="text-right"><b>TOTAL</b></td>
                                <td class="text-center"><b id="oSQtn">0</b></td>
                                <td class="text-right"><b id="oSAmt">0.00</b></td>

                                <td class="text-center"><b id="isQtn">0</b></td>
                                <td class="text-right"><b id="isAmt">0.00</b></td>

                                <td class="text-center"><b id="sRQtn">0</b></td>
                                <td class="text-right"><b id="sRAmt">0.00</b></td>

                                <td class="text-center"><b id="tInQtn">0</b></td>
                                <td class="text-right"><b id="tInAmt">0.00</b></td>

                                <td class="text-center"><b id="adjQtn">0</b></td>
                                <td class="text-right"><b id="adjAmt">0.00</b></td>

                                <td class="text-center"><b id="sQtn">0</b></td>
                                <td class="text-right"><b id="sAmt">0.00</b></td>

                                <td class="text-center"><b id="isRQtn">0</b></td>
                                <td class="text-right"><b id="isRAmt">0.00</b></td>

                                <td class="text-center"><b id="tOutQtn">0</b></td>
                                <td class="text-right"><b id="tOutAmt">0.00</b></td>

                                <td class="text-center"><b id="adj2Qtn">0</b></td>
                                <td class="text-right"><b id="adj2Amt">0.00</b></td>

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
            font-size: 70% !important;
            padding: 2px !important;
        }

        .table>tbody td {
            font-size: 70% !important;
            padding: 2px !important;
        }

        .table>tfoot td {
            font-size: 70% !important;
            padding: 2px !important;
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
            var branch_id = $('#branch_id').val();
            var supplier_id = $('#supplier_id').val();
            var group_id = $('#group_id').val();
            var cat_id = $('#cat_id').val();
            var sub_cat_id = $('#sub_cat_id').val();
            var brand_id = $('#brand_id').val();
            var model_id = $('#model_id').val();
            var product_id = $('#product_id').val();
            var stock = $('#stock').val();

            $('#reportBranch').html($('#branch_id').find("option:selected").text());
            $('#start_date_txt').html(start_date);
            $('#end_date_txt').html(end_date);

            $(".wb-minus").trigger('click');
            ajaxDataLoad(start_date, end_date, zone_id, area_id, branch_id, supplier_id, group_id,
                cat_id, sub_cat_id, brand_id, model_id, product_id, stock);
        });



    });

    function ajaxDataLoad(start_date = null, end_date = null, zone_id = null, area_id = null, branch_id = null,
        supplier_id = null, group_id = null, cat_id = null, sub_cat_id = null, brand_id = null, model_id = null,
        product_id = null, stock = null) {

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
                "url": "{{ url('pos/report/stock_inv_branch') }}",
                "dataType": "json",
                "type": "post",
                "data": {
                    _token: "{{ csrf_token() }}",
                    startDate: start_date,
                    endDate: end_date,
                    zoneId: zone_id,
                    areaId: area_id,
                    branchId: branch_id,
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
                    data: 'product_name',
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
                    data: 'issue_qtn',
                    className: 'text-center'
                },
                {
                    data: 'issue_amt',
                    className: 'text-right'
                },
                {
                    data: 'sales_return_qtn',
                    className: 'text-center'
                },
                {
                    data: 'sales_return_amt',
                    className: 'text-right'
                },
                {
                    data: 'transfer_in_qtn',
                    className: 'text-center'
                },
                {
                    data: 'transfer_in_amt',
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
                    data: 'sales_qtn',
                    className: 'text-center'
                },
                {
                    data: 'sales_amt',
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
                    data: 'transfer_out_qtn',
                    className: 'text-center'
                },
                {
                    data: 'transfer_out_amt',
                    className: 'text-right'
                },
                {
                    data: 'adj_qtn2',
                    className: 'text-center'
                },
                {
                    data: 'adj_amt2',
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

                    $('#isQtn').html(oResult.json.is_ttl_qtn);
                    $('#isAmt').html(oResult.json.is_ttl_amt);

                    $('#sRQtn').html(oResult.json.sr_ttl_qtn);
                    $('#sRAmt').html(oResult.json.sr_ttl_amt);

                    $('#tInQtn').html(oResult.json.tin_ttl_qtn);
                    $('#tInAmt').html(oResult.json.tin_ttl_amt);

                    $('#adjQtn').html(oResult.json.adj_ttl_qtn);
                    $('#adjAmt').html(oResult.json.adj_ttl_amt);

                    $('#sQtn').html(oResult.json.sales_ttl_qtn);
                    $('#sAmt').html(oResult.json.sales_ttl_amt);

                    $('#isRQtn').html(oResult.json.isr_ttl_qtn);
                    $('#isRAmt').html(oResult.json.isr_ttl_amt);

                    $('#tOutQtn').html(oResult.json.tout_ttl_qtn);
                    $('#tOutAmt').html(oResult.json.tout_ttl_amt);

                    $('#adj2Qtn').html(oResult.json.adj2_ttl_qtn);
                    $('#adj2Amt').html(oResult.json.adj2_ttl_amt);

                    $('#cStockQtn').html(oResult.json.stock_ttl_qtn);
                    $('#cStockAmt').html(oResult.json.stock_ttl_amt);
                }
            }
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

</script>
@endsection
