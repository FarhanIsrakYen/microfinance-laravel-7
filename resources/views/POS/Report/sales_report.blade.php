@extends('Layouts.erp_master_full_width')
@section('content')

@include('elements.report.report_filter_options', ['zone' => true,
'area' => true,
'branch' => true,
'customer' => true,
'employee' => true,
'salesType' => true,
'startDate' => true,
'endDate' => true,
'group' => true,
'model' => true,
'product' => true
])

<div class="w-full">
    <div class="panel">
        <div class="panel-body panel-search pt-2">

            @include('elements.report.report_heading', ['title' => 'Sales Report', 'title_excel' =>
            'Sales_Report'])

            <div class="row ExportDiv">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead>
                            <tr>
                                <th width="3%">SL</th>
                                <th width="15%">Customer Name</th>
                                <th width="5%">Sales Type</th>
                                <th width="10%">Bill No</th>
                                <th width="13%">Sales Date</th>
                                <th width="15%">Sales By</th>
                                <th width="4%">Quantity</th>
                                <th width="6%">Cash Price</th>
                                <th width="6%">Profit</th>
                                <th width="7%">Processing Fee</th>
                                <th width="9%">Total Sales Amount <br> (With P.F.+ Profit)</th>
                                <th width="7%">1st Installment</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" style="text-align:right!important;"><b>TOTAL</b></td>
                                <td class="text-center tfoot_cls" id="ttl_product_qty">0</td>
                                <td class="text-right tfoot_cls" id="ttl_cash_price">0.00</td>
                                <td class="text-right tfoot_cls" id="ttl_profit">0.00</td>
                                <td class="text-right tfoot_cls" id="ttl_processing_fee">0.00</td>
                                <td class="text-right tfoot_cls" id="ttl_total_sales_amount">0.00 </td>
                                <td class="text-right tfoot_cls" id="ttl_first_installment">0.00</td>
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
    function ajaxDataLoad(start_date = null, end_date = null, customer_id = null, employee_id = null, branch_id = null,
        sales_type = null, zone_id = null, area_id = null, group_id = null, cat_id = null, sub_cat_id = null, brand_id =
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
                "url": "{{ route('saleRDatatable') }}",
                "dataType": "json",
                "type": "post",
                "data": {
                    // _token: "{{ csrf_token() }}",
                    startDate: start_date,
                    endDate: end_date,
                    customerId: customer_id,
                    employeeId: employee_id,
                    branchId: branch_id,
                    sales_type: sales_type,
                    zoneId: zone_id,
                    area_Id: area_id,
                    groupId: group_id,
                    catId: cat_id,
                    subCatId: sub_cat_id,
                    brandId: brand_id
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
                    data: 'emp_name'
                },
                {
                    data: 'total_quantity',
                    className: 'text-center'
                },
                {
                    data: 'cash_price',
                    className: 'text-right'
                },
                {
                    data: 'profit',
                    className: 'text-right'
                },
                {
                    data: 'processing_fee',
                    className: 'text-right'
                },
                {
                    data: 'total_sales_amount',
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
                    $('#ttl_product_qty').html(oResult.json.ttl_product_qty);
                    $('#ttl_cash_price').html(oResult.json.ttl_cash_price);
                    $('#ttl_profit').html(oResult.json.ttl_profit);
                    $('#ttl_processing_fee').html(oResult.json.ttl_processing_fee);
                    $('#ttl_total_sales_amount').html(oResult.json.ttl_total_sales_amount);
                    $('#ttl_first_installment').html(oResult.json.ttl_first_installment);
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
            var zone_id = $('#zone_id').val();
            var area_id = $('#area_id').val();
            var sales_type = $('#sales_type').val();
            var group_id = $('#group_id').val();
            var cat_id = $('#cat_id').val();
            var sub_cat_id = $('#sub_cat_id').val();
            var brand_id = $('#brand_id').val();

            $('#reportBranch').html($('#branch_id').find("option:selected").text());
            $('#start_date_txt').html($('#start_date').val());
            $('#end_date_txt').html($('#end_date').val());

            $(".wb-minus").trigger('click');
            ajaxDataLoad(start_date, end_date, customer_id, employee_id, branch_id, sales_type,
                zone_id,
                area_id, group_id, cat_id, sub_cat_id, brand_id);

        });
    });

    function fnSelectAreaByZoneID() {
        var zone_id = $('#zone_id').val();

        if (zone_id != '') {
            $.ajax({
                method: "GET",
                url: "{{url('/ajaxGetAreabyZone')}}",
                dataType: "text",
                data: {
                    zone_id: zone_id,
                },
                success: function (data) {
                    if (data) {
                        console.log(data);
                        $('#area_id').html(data);
                    }
                }
            });
        }
    }
   $('#branch_id').change(function(){
        var branchId =  $('#branch_id').val();
    
        $.ajax({
                method: "GET",
                url: "{{url('/ajaxGetEmployeeName')}}",
                dataType: "text",
                data: {
                    branchId: branchId,
                },
                success: function(data) {
                    if (data) {

                        $('#employee_id')
                            .find('option')
                            .remove()
                            .end()
                            .append(data);

                    }
                }
            });
    });
</script>
@endsection
