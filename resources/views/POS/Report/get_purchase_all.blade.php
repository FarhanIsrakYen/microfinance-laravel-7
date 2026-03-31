@extends('Layouts.erp_master_full_width')
@section('content')
<!-- Page -->
<script type="text/javascript" src="{{ asset('assets/js/pdf/tableExport.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.plugin.autotable.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/xlsx.core.min.js') }}"></script>
<?php
use App\Services\HrService as HRS;
$StartDate = Common::systemCurrentDate();
$EndDate = Common::systemCurrentDate();
$PDeliveryList = Common::ViewTableOrder('pos_purchases_m', [['is_delete', 0], ['is_active', 1], ['delivery_to', '!=', '']], ['id', 'delivery_to'], ['delivery_to', 'ASC']);
$PurchaseNoList = Common::ViewTableOrder('pos_purchases_d', [['is_delete', 0], ['is_active', 1], ['purchase_bill_no', '!=', '']], ['id', 'purchase_bill_no'], ['purchase_bill_no', 'ASC']);
$productList = Common::ViewTableOrder('pos_products', [['is_delete', 0], ['is_active', 1], ['product_name', '!=', '']], ['id', 'product_name'], ['product_name', 'ASC']);
$supplierList = Common::ViewTableOrder('pos_suppliers', [['is_delete', 0], ['is_active', 1], ['sup_name', '!=', '']], ['id', 'sup_name'], ['sup_name', 'ASC']);
$InvoiceNoList = Common::ViewTableOrder('pos_purchases_m', [['is_delete', 0], ['is_active', 1], ['invoice_no', '!=', '']], ['id', 'invoice_no'], ['invoice_no', 'ASC']);
$PGroupList = Common::ViewTableOrder('pos_p_groups', [['is_delete', 0], ['is_active', 1]], ['id', 'group_name'], ['group_name', 'ASC']);
$CategoryList = Common::ViewTableOrder('pos_p_categories', [['is_delete', 0], ['is_active', 1]], ['id', 'cat_name'], ['cat_name', 'ASC']);
$SubCatList = Common::ViewTableOrder('pos_p_subcategories', [['is_delete', 0], ['is_active', 1]], ['id', 'sub_cat_name'], ['sub_cat_name', 'ASC']);
$BrandList = Common::ViewTableOrder('pos_p_brands', [['is_delete', 0], ['is_active', 1]], ['id', 'brand_name'], ['brand_name', 'ASC']);
$BranchID = Common::getBranchId();

$branchInfo = Common::ViewTableFirst('gnl_branchs', [['is_delete', 0], ['is_active', 1], ['id', $BranchID]], ['id', 'branch_name']);
$groupInfo = Common::ViewTableFirst('gnl_groups', [['is_delete', 0], ['is_active', 1]], ['id', 'group_name']);

?>

<div class="w-full" style="min-height: calc(100% - 44px)">
    <div class="panel">
        <div class="panel-body">

            <div class="row align-items-center pb-10 d-print-none">
                <div class="col-lg-2">
                    <label class="input-title">Start Date</label>
                    <div class="input-group">
                        <input type="text" class="form-control datepicker-custom" id="start_date" name="StartDate"
                            placeholder="DD-MM-YYYY" value="{{$StartDate}}">
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">End Date</label>
                    <div class="input-group">
                        <input type="text" class="form-control datepicker-custom" id="end_date" name="EndDate"
                            placeholder="DD-MM-YYYY" value="{{$EndDate}}">
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Product name</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="pur_name_id" id="pur_name_id">
                            <option value="">Select All</option>
                            @foreach ($productList as $Row)
                            <option value="{{ $Row->id }}">{{ $Row->product_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Supplier</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="pur_supplier_id" id="pur_supplier_id">
                            <option value="">Select All</option>
                            @foreach ($supplierList as $Row)
                            <option value="{{ $Row->id }}">{{ $Row->sup_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Purchase No</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="purchase_id" id="purchase_id">
                            <option value="">Select All</option>
                            @foreach ($PurchaseNoList as $Row)
                            <option value="{{ $Row->purchase_bill_no }}">{{ $Row->purchase_bill_no }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Invoice No</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="invoice_id" id="invoice_id">
                            <option value="">Select All</option>
                            @foreach ($InvoiceNoList as $Row)
                            <option value="{{ $Row->invoice_no }}">{{ $Row->invoice_no }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row align-items-center pb-10 d-print-none">
                <div class="col-lg-2">
                    <label class="input-title">Group</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="pur_group_id" id="pur_group_id" onchange="fnAjaxSelectBox('pur_cat_id',
                                                        this.value,
                                                        '{{ base64_encode("pos_p_categories")}}',
                                                        '{{base64_encode("prod_group_id")}}',
                                                        '{{base64_encode("id,cat_name")}}',
                                                        '{{url("/ajaxSelectBox")}}');">
                            <option value="">Select All</option>
                            @foreach($PGroupList as $Row)
                            <option value="{{ $Row->id}}">{{ $Row->group_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Category</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="pur_cat_id" id="pur_cat_id" onchange="fnAjaxSelectBox('pur_sub_cat_id',
                                                        this.value,
                                                        '{{base64_encode("pos_p_subcategories")}}',
                                                        '{{base64_encode("prod_cat_id")}}',
                                                        '{{base64_encode("id,sub_cat_name")}}',
                                                        '{{url("/ajaxSelectBox")}}');">
                            <option value="">Select All</option>
                            @foreach ($CategoryList as $Row)
                            <option value="{{ $Row->id }}">{{ $Row->cat_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title"> Sub Category</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="pur_sub_cat_id" id="pur_sub_cat_id" onchange="fnAjaxSelectBox('pur_brand_id',
                                                        this.value,
                                                        '{{base64_encode("pos_p_brands")}}',
                                                        '{{base64_encode("prod_sub_cat_id")}}',
                                                        '{{base64_encode("id,brand_name")}}',
                                                        '{{url("/ajaxSelectBox")}}');">
                            <option value="">Select All</option>
                            @foreach ($SubCatList as $Row)
                            <option value="{{ $Row->id }}">{{ $Row->sub_cat_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Brand</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="pur_brand_id" id="pur_brand_id">
                            <option value="">Select All</option>
                            @foreach ($BrandList as $Row)
                            <option value="{{ $Row->id }}">{{ $Row->brand_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Delivery to</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="delivery_id" id="delivery_id">
                            <option value="">Select All</option>
                            @foreach ($PDeliveryList as $Row)
                            <option value="{{ $Row->id }}">{{ $Row->delivery_to }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2 pt-20 text-center">
                    <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round"
                        id="purchaseReSearch">Search</a>
                </div>
            </div>
            <div class="row d-print-none text-right">
                <div class="col-lg-12">
                    <a href="javascript:void(0)" onClick="window.print();"
                        style="background-color:transparent;border:none;" class="btnPrint mr-2">
                        <i class="fa fa-print fa-lg" style="font-size:20px;"></i>
                    </a>
                    <a href="javascript:void(0)" style="background-color:transparent;border:none;"
                        onclick="fnDownloadPDF();">
                        <i class="fa fa-file-pdf-o fa-lg" style="font-size:20px;"></i>
                    </a>
                    <a href="javascript:void(0)" style="background-color:transparent;border:none;"
                        onclick="fnDownloadExcel();">
                        <i class="fa fa-file-excel-o fa-lg" style="font-size:20px; margin-left: 5px;"></i>
                        {{-- <i class="fa fa-file-pdf-o fa-lg" style="font-size:20px;"></i> --}}
                    </a>
                </div>
            </div>
            <div class="row text-center d-none d-print-block">
                <div class="col-lg-12" style="color:#000;">
                    <strong>{{ $groupInfo->group_name }}</strong><br>
                    <strong>{{ $branchInfo->branch_name }}</strong><br>
                    <span>Purchase Report</span><br>
                    (<span id="start_date_txt">{{$StartDate}}</span>
                    to
                    <span id="end_date_txt">{{ $EndDate }}</span>)
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
            <div class="row">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead>
                            <tr>
                                <th class="text-center" width="3%">SL</th>
                                <th class="text-center">Purchase Date</th>
                                <th>Purchase No</th>
                                <th>Invoice No</th>
                                <th>Supplier Name</th>
                                <th>Branch Name</th>
                                <th>Product Name</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-right">Unit Price</th>
                                <th class="text-right">Total Amount</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <td colspan="7" class="text-right"><b>TOTAL</b></td>
                                <td class="text-center" id="ttl_pro_qnt" style="font-color:#000; font-weight:bold;">
                                    0</td>
                                <td>&nbsp</td>
                                <td class="text-right" id="total_amount" style="font-color:#000; font-weight:bold;">
                                    0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function ajaxDataLoad(SDate = null, EDate = null, PDeliveryID = null,
    PurchaseNo = null, InvoiceNo = null, PGroupID = null, CategoryId = null,
    SubCatID = null, BrandID = null, txt_product_name = null, txt_supplier_name = null) {

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
                _token: "{{csrf_token()}}",
                SDate: SDate,
                EDate: EDate,
                PDeliveryID: PDeliveryID,
                PurchaseNo: PurchaseNo,
                InvoiceNo: InvoiceNo,
                PGroupID: PGroupID,
                CategoryId: CategoryId,
                SubCatID: SubCatID,
                BrandID: BrandID,
                txt_product_name: txt_product_name,
                txt_supplier_name: txt_supplier_name
            }
        },
        columns: [{
                data: 'id',
                className: 'text-center'
            },
            {
                data: 'purchase_date',
                className: 'text-center'
            },
            {
                data: 'purchase_bill_no'
            },
            {
                data: 'invoice_no'
            },
            {
                data: 'supplier_name'
            },
            {
                data: 'branch_name'
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
        drawCallback: function(oResult) {
            //  console.log(oResult.json.totalRow);
            if (oResult.json) {
                $('#totalRowDiv').html(oResult.json.totalRow);
                $('#ttl_pro_qnt').html(oResult.json.total_product_qnt);
                $('#total_amount').html(oResult.json.total_amount);
            }
        },
    });
}

$(document).ready(function() {

    // ajaxDataLoad();

    $('#purchaseReSearch').click(function() {

        var SDate = $('#start_date').val();
        var EDate = $('#end_date').val();
        var PDeliveryID = $('#delivery_id').val();
        var PurchaseNo = $('#purchase_id').val();
        var InvoiceNo = $('#invoice_id').val();
        var PGroupID = $('#pur_group_id').val();
        var CategoryId = $('#pur_cat_id').val();
        var SubCatID = $('#pur_sub_cat_id').val();
        var BrandID = $('#pur_brand_id').val();
        var txt_product_name = $('#txt_product_name').val();
        var txt_supplier_name = $('#txt_supplier_name').val();
        $('#start_date_txt').html(SDate);
        $('#end_date_txt').html(EDate);
        ajaxDataLoad(SDate, EDate, PDeliveryID, PurchaseNo, InvoiceNo, PGroupID, CategoryId, SubCatID,
            BrandID, txt_product_name, txt_supplier_name);
    });
});

function fnDownloadPDF() {
    $('.clsDataTable').tableExport({
        type: 'pdf',
        fileName: 'Purchase report',
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
        fileName: 'purchase report',
    });
}
</script>
@endsection