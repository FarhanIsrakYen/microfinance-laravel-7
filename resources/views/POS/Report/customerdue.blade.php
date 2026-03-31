@extends('Layouts.erp_master_full_width')
@section('content')
<script type="text/javascript" src="{{ asset('assets/js/pdf/tableExport.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.plugin.autotable.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/xlsx.core.min.js') }}"></script>

<!-- Page -->
<?php
$startDate = Common::systemCurrentDate();
$endDate = Common::systemCurrentDate();
$employeeData = Common::ViewTableOrder('hr_employees',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'emp_name'],
    ['emp_name', 'ASC']);

$saledetailsData = Common::ViewTableOrder('pos_sales_d',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'sales_bill_no'],
    ['sales_bill_no', 'ASC']);

$branchData = Common::ViewTableOrder('gnl_branchs',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'branch_name'],
    ['branch_name', 'ASC']);
$CustomerData = Common::ViewTableOrder('pos_customers',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'customer_name'],
    ['customer_name', 'ASC']);
$branchId = Common::getBranchId();
$branchInfo = Common::ViewTableFirst('gnl_branchs',
    [['is_delete', 0], ['is_active', 1],
        ['id', $branchId]],
    ['id', 'branch_name']);
$groupInfo = Common::ViewTableFirst('gnl_groups',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'group_name']);

?>


<!-- <div class="w-full"> -->

<div class="panel">
    <div class="panel-body">
        <div class="row align-items-center pb-10 d-print-none">
            <div class="col-lg-2">
                <label class="input-title">Branch</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" name="branch_id" id="branch_id"
                    onchange="fnAjaxSelectBox('employee_id',
                                                    this.value,
                                                    '{{ base64_encode("hr_employees")}}',
                                                    '{{base64_encode("branch_id")}}',
                                                    '{{base64_encode("id,emp_name")}}',
                                                    '{{url("/ajaxSelectBox")}}');
                              fnAjaxSelectBox('customer_id',
                                                              this.value,
                                                              '{{ base64_encode("pos_customers")}}',
                                                              '{{base64_encode("branch_id")}}',
                                                              '{{base64_encode("id,customer_name")}}',
                                                              '{{url("/ajaxSelectBox")}}');">

                        <option value="">Select All</option>
                        @foreach ($branchData as $row)
                        <option value="{{ $row->id }}">{{ $row->branch_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-2">
                <label class="input-title">Sales By</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" name="employee_id" id="employee_id" >
                        <option value="">Select All</option>
                        <!-- @foreach ($employeeData as $Row) -->
                      <!-- <option value="{{ $Row->id }}">{{ $Row->emp_name }}</option> -->
                        <!-- @endforeach -->
                    </select>
                </div>
            </div>
            <div class="col-lg-2">
                <label class="input-title">Customer Name</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" name="customer_id" id="customer_id"
                    >
                        <option value="">Select All</option>

                    </select>
                </div>
            </div>
            <div class="col-lg-2">
                <label class="input-title">Sales Bill No.</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" name="sales_bill_no" id="sales_bill_no">
                        <option value="">Select All</option>
                        @foreach ($saledetailsData as $Row)
                        <option value="{{ $Row->sales_bill_no }}">{{ $Row->sales_bill_no }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-2 pt-20 text-center">
                <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round" id="searchButton">Search</a>
            </div>
        </div>
        <div class="row d-print-none text-right">
            <div class="col-lg-12">
                <a href="javascript:void(0)" onClick="window.print();" style="background-color:transparent;border:none;" class="btnPrint mr-2">
                    <i class="fa fa-print fa-lg" style="font-size:20px;"></i>
                </a>
                <a href="javascript:void(0)" onclick="fnDownloadPDF();">
                    <i class="fa fa-file-pdf-o fa-lg" style="font-size:20px;"></i>
                </a>
                <a href="javascript:void(0)" onclick="fnDownloadExcel();">
                    <i class="fa fa-file-excel-o fa-lg" style="font-size:20px; margin-left: 5px;"></i>
                </a>
            </div>
        </div>
        <div class="row text-center d-none d-print-block">
            <div class="col-lg-12" style="color:#000;">
                <strong>{{ $groupInfo->group_name }}</strong><br>
                <strong>{{ $branchInfo->branch_name }}</strong><br>
                <span>All Collection Report</span><br>
                (<span id="start_date_txt">{{$startDate }}</span>
                to
                <span id="end_date_txt">{{$endDate }}</span>)
            </div>
        </div>
        <div class="row">
            <div class="col-xl-6 col-lg-6 col-sm-6 col-md-6 col-6">
                <span class="d-print-none"><b>Total Row:</b> <span id="totalRowDiv">0</span></span>
            </div>
            <div class="col-xl-6 col-lg-6 col-sm-6 col-md-6 col-6 text-right">
                <span><b>Printed Date:</b> {{ date("d-m-Y") }} </span>
            </div>
        </div>
        <!-- <div class="panel">
            <div class="panel-body" style="border: 1px solid #ebebeb">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-row">
                            <label class="col-lg-3">Reporting Date</label>
                            <label class="col-lg-6">: {{ date("d-m-Y") }}</label>
                        </div>
                        <div class="form-row">
                            <label class="col-lg-3">Customer Name</label>
                            <label class="col-lg-6"><strong></strong></label>
                        </div>
                        <div class="form-row">
                            <label class="col-lg-3">Spouse Name</label>
                            <label class="col-lg-6">:Habibullah mondol</label>
                        </div>
                        <div class="form-row">
                            <label class="col-lg-3">Mobile No </label>
                            <label class="col-lg-6">:01924891095</label>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-row">
                            <label class="col-lg-3"></label>
                            <label class="col-lg-3">National Id</label>
                            <label class="col-lg-6 ">:3313060095824</label>
                        </div>
                        <div class="form-row">
                            <label class="col-lg-3"></label>
                            <label class="col-lg-3">Customer Id</label>
                            <label class="col-lg-6">:Md Shohrab Hossain</label>
                        </div>
                        <div class="form-row">
                            <label class="col-lg-3"></label>
                            <label class="col-lg-3">Samity Name</label>
                            <label class="col-lg-6">:Asar Alo Mohila Somity</label>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead>
                            <tr>
                                <th width="2%" rowspan="2">#</th>
                                <th colspan="5" class="text-center">Customer Information</th>
                                <th colspan="4" class="text-center">Sales Information</th>
                                <th colspan="5" class="text-center">Transaction</th>
                            </tr>
                            <tr>
                                <th width="2%" class="text-center">Code</th>
                                <th width="10%">Name</th>
                                <th width="8%" class="text-center">Mobile</th>
                                <th width="10%" class="text-center">NID</th>
                                <th width="10%">Spouse</th>
                                <th width="10%" class="text-center">Bill No</th>
                                <th  class="text-center">Sales Date</th>
                                <th width="10%">Product</th>
                                <th width="1%" class="text-center">Quantity</th>
                                <th  class="text-right">Sales Amount</th>
                                <th  class="text-right">Processing Fee</th>
                                <th  class="text-right">Gross Total</th>
                                <th  class="text-right">Paid Amount</th>
                                <th  class="text-right">Due Amount</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <td colspan="9" class="text-right" style="font-color:#000; font-weight:bold;">Total:</td>
                                <td  class="Tqnt text-center" style="font-color:#000; font-weight:bold;">0.00</td>
                                <td  class="Tsales text-center" style="font-color:#000; font-weight:bold;">0.00</td>
                                <td  class="Tprocfee text-center" style="font-color:#000; font-weight:bold;">0.00</td>
                                <td  class="Tgross text-center" style="font-color:#000; font-weight:bold;">0.00</td>
                                <td  class="TpaidAmt text-center" style="font-color:#000; font-weight:bold;">0.00</td>
                                <td  class="TdueAmt text-center" style="font-color:#000; font-weight:bold;">0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- </div> -->
<!-- End Page -->

<script>
    function ajaxDataLoad(branch_id = null, employee_id = null, customer_id = null,sales_bill_no= null) {
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
                "url": "{{route('customerDueDatatable')}}",
                "dataType": "json",
                "type": "post",
                "data": {
                    _token: "{{ csrf_token() }}",
                    branchId: branch_id,
                    employeeID: employee_id,
                    CustomerID: customer_id,
                    SaleBillNo:sales_bill_no,
                }
            },
            columns: [{
                    data: 'sl'
                },
                {
                    data: 'customer_code', className:'text-center'
                },
                {
                    data: 'customer_name'
                },
                {
                    data: 'mobile'
                },
                {
                    data: 'cus_nid'
                },
                {
                    data: 'spouse_name', className:'text-left'
                },
                {
                    data: 'bill_no', className:'text-center'
                },
                {
                    data: 'sales_date', className:'text-center',width:'18%'

                },
                {
                    data: 'product' , className:'text-left'
                },
                {
                    data: 'quantity', className:'text-center'
                },
                {
                    data: 'sales_amount', className:'text-right'
                },
                {
                    data: 'processing_fee', className:'text-right'
                },

                {
                    data: 'gross_total', className:'text-right'
                },
                {
                    data: 'paid_amount', className:'text-right'
                },
                {
                    data: 'due_amount', className:'text-right'
                },
            ],
            drawCallback: function(oResult) {
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
    $(document).ready(function() {
        // ajaxDataLoad();
        $('#searchButton').click(function() {
            var branch_id = $('#branch_id').val();
            var employee_id = $('#employee_id').val();
            var customer_id = $('#customer_id').val();
            var sales_bill_no = $('#sales_bill_no').val();

            // console.log(branch_id);
            ajaxDataLoad(branch_id, employee_id, customer_id,sales_bill_no);
        });
    });

    function fnDownloadPDF() {
        $('.clsDataTable').tableExport({
            type: 'pdf',
            fileName: 'Customer Due report',
            jspdf: {
                orientation: 'l',
                format: 'a4',
                margins: {
                    left: 10,
                    right: 10,
                    top: 20,
                    bottom: 20
                },
                autotable: {
                    styles: {
                        overflow: 'linebreak'
                    },
                    tableWidth: 'auto'
                }
            }
        });
    }

    function fnDownloadExcel() {
        $('.clsDataTable').tableExport({
            type: 'excel',
            fileName: 'Customer Due report',
        });
    }
</script>
@endsection
