@extends('Layouts.erp_master_full_width')
@section('content')

@include('elements.report.report_filter_options', ['branch' => true,
'customer' => true,
'employee' => true,
'salesBillNo' => true
])

<div class="w-full">
    <div class="panel">
        <div class="panel-body panel-search pt-2">

            @include('elements.report.report_heading', ['title' => 'Customer Due Report', 'title_excel' =>
            'Customer_Due_Report'])

            <div class="row ExportDiv">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead>
                            <tr>
                                <th width="3%" rowspan="2">SL</th>
                                <th rowspan="2" width="15%">Customer Information</th>
                                <th colspan="4" width="35%">Sales Information</th>
                                <th colspan="5" width="47%">Transaction</th>
                            </tr>
                            <tr>
                                <th>Bill No</th>
                                <th>Sales Date</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Sales Amount</th>
                                <th>Processing Fee</th>
                                <th>Gross Total</th>
                                <th>Paid Amount</th>
                                <th>Due Amount</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" style="text-align: right!important; font-weight:bold;">Total</td>
                                <td class="Tqnt text-right text-dark font-weight-bold">0.00</td>
                                <td class="Tsales text-right text-dark font-weight-bold">0.00</td>
                                <td class="Tprocfee text-right text-dark font-weight-bold">0.00</td>
                                <td class="Tgross text-right text-dark font-weight-bold">0.00</td>
                                <td class="TpaidAmt text-right text-dark font-weight-bold">0.00</td>
                                <td class="TdueAmt text-right text-dark font-weight-bold">0.00</td>
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

<style>
    @media print {
        @page {
            size: landscape;
            margin: 5px !important;
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
    function ajaxDataLoad(branch_id = null, employee_id = null, customer_id = null, sales_bill_no = null) {
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
                "url": "{{ url('pos/report/customer_due') }}",
                "dataType": "json",
                "type": "post",
                "data": {
                    _token: "{{ csrf_token() }}",
                    branchID: branch_id,
                    employeeID: employee_id,
                    CustomerID: customer_id,
                    SaleBillNo: sales_bill_no,
                }
            },
            columns: [{
                    data: 'sl',
                    className: 'text-center'
                },
                {
                    data: 'customer_info'
                },
                // {
                //     data: 'customer_name'
                // },
                // {
                //     data: 'mobile'
                // },
                // {
                //     data: 'cus_nid'
                // },
                // {
                //     data: 'spouse_name',
                // },
                {
                    data: 'bill_no',
                },
                {
                    data: 'sales_date',
                    // width: '20%'
                },
                {
                    data: 'product',
                },
                {
                    data: 'quantity',
                    className: 'text-center',
                    // width: '5%'
                },
                {
                    data: 'sales_amount',
                    className: 'text-right'
                },
                {
                    data: 'processing_fee',
                    className: 'text-right'
                },
                {
                    data: 'gross_total',
                    className: 'text-right'
                },
                {
                    data: 'paid_amount',
                    className: 'text-right'
                },
                {
                    data: 'due_amount',
                    className: 'text-right'
                },
            ],
            drawCallback: function (oResult) {
                if (oResult.json) {
                    $('#totalRowDiv').html(oResult.json.totalRow);
                    $('.Tqnt').html(oResult.json.ttl_qnt);
                    $('.Tsales').html(oResult.json.ttl_sales_amount);
                    $('.Tprocfee').html(oResult.json.ttl_service_charge);
                    $('.Tpayable_amt').html(oResult.json.total_payable_amount);
                    $('.Tgross').html(oResult.json.ttl_gross_amount);
                    $('.TpaidAmt').html(oResult.json.ttl_paid_amount);
                    $('.TdueAmt').html(oResult.json.ttl_due_amount);
                }
            },
        });
    }
    $(document).ready(function () {
        $('#searchButton').click(function () {
            var branch_id = $('#branch_id').val();
            var employee_id = $('#employee_id').val();
            var customer_id = $('#customer_id').val();
            var sales_bill_no = $('#sales_bill_no').val();

            $('#reportBranch').html($('#branch_id').find("option:selected").text());
            $('#start_date_txt').html($('#start_date').val());
            $('#end_date_txt').html($('#end_date').val());

            $(".wb-minus").trigger('click');
            ajaxDataLoad(branch_id, employee_id, customer_id, sales_bill_no);
        });
    });

    $('#branch_id').change(function(){
        var branchId =  $('#branch_id').val();
    
       
         $.ajax({
                method: "GET",
                url: "{{url('/ajaxGetBranchCustomer')}}",
                dataType: "text",
                data: {
                    branchId: branchId,
                },
                success: function(data) {
                    if (data) {

                        $('#customer_id')
                            .find('option')
                            .remove()
                            .end()
                            .append(data);

                    }
                }
            });



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

 $.ajax({
                method: "GET",
                url: "{{url('/ajaxGetSalesBillNo')}}",
                dataType: "text",
                data: {
                    branchId: branchId,
                },
                success: function(data) {
                    if (data) {

                        $('#sales_bill_no')
                            .find('option')
                            .remove()
                            .end()
                            .append(data);

                    }
                }
            });

    });


    // $("#branch_id").on('change', function () {
    //     fnAjaxSelectBox('employee_id',
    //         this.value,
    //         '{{ base64_encode("hr_employees")}}',
    //         '{{base64_encode("branch_id")}}',
    //         '{{base64_encode("employee_no,emp_name")}}',
    //         '{{url("/ajaxSelectBox")}}');
    //     fnAjaxSelectBox('customer_id',
    //         this.value,
    //         '{{ base64_encode("pos_customers")}}',
    //         '{{base64_encode("branch_id")}}',
    //         '{{base64_encode("customer_no,customer_name")}}',
    //         '{{url("/ajaxSelectBox")}}');
    // });

</script>
@endsection
