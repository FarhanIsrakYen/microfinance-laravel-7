@extends('Layouts.erp_master_full_width')
@section('content')

@include('elements.report.report_filter_options', ['branch' => true,
'startDate' => true,
'endDate' => true,
])

<div class="w-full">
    <div class="panel">
        <div class="panel-body panel-search pt-2">

            @include('elements.report.report_heading', ['title' => 'Total Sales Summary Report', 'title_excel' =>
            'Sales_Summary_Report'])

            <div class="row ExportDiv">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead>
                            <tr>
                                <th width="5%">SL</th>
                                <th>Branch Name</th>
                                <th>Total Quantity</th>
                                <th>Total Sales Amount (With PF.+Profit)</th>
                                <th>1st Installment</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" style="text-align:right!important;"><b>Total</b></td>
                                <td class="text-center"><b id="tQtn">0</b></td>
                                <td class="text-right"><b id="tSAmt">0.00</b></td>
                                <td class="text-right"><b id="fInstall">0.00</b></td>
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
            var group_id = $('#group_id').val();
            var cat_id = $('#cat_id').val();
            var sub_cat_id = $('#sub_cat_id').val();
            var brand_id = $('#brand_id').val();
            var model_id = $('#model_id').val();

            $(".wb-minus").trigger('click');
            ajaxDataLoad(start_date, end_date, branch_id, group_id, cat_id, sub_cat_id,
                brand_id, model_id);

            $('#reportBranch').html($('#branch_id').find("option:selected").text());
            $('#start_date_txt').html($('#start_date').val());
            $('#end_date_txt').html($('#end_date').val());
        });

    });

    function ajaxDataLoad(start_date = null, end_date = null, branch_id = null, group_id = null,
        cat_id = null, sub_cat_id = null, brand_id = null, model_id = null) {

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
                "url": "{{ route('TSalesSummaryDatatable') }}",
                "dataType": "json",
                "type": "post",
                "data": {
                    startDate: start_date,
                    endDate: end_date,
                    branchId: branch_id,
                    groupId: group_id,
                    catId: cat_id,
                    subCatId: sub_cat_id,
                    brandId: brand_id,
                    modelId: model_id
                }
            },
            columns: [{
                    data: 'sl',
                    className: 'text-center'
                },
                {
                    data: 'branch_name',
                },
                {
                    data: 'product_qtn',
                    className: 'text-center'
                },
                {
                    data: 'total_sales_amt',
                    className: 'text-right'
                },
                {
                    data: 'first_installment',
                    className: 'text-right'
                },
            ],
            drawCallback: function (oResult) {

                if (oResult.json) {
                    $('#totalRowDiv').html(oResult.json.totalRow);
                    $('#tQtn').html(oResult.json.total_quantity);
                    $('#tSAmt').html(oResult.json.total_sales_amount);
                    $('#fInstall').html(oResult.json.total_first_installment);

                }
            },
        });
    }

</script>
@endsection
