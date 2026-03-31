@extends('Layouts.erp_master_full_width')
@section('content')

<?php 
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
use App\Services\HrService as HRS;

$startDate = Common::systemCurrentDate();
$endDate = Common::systemCurrentDate();



$branchId = Common::getBranchId();

$branchInfo = Common::ViewTableFirst('gnl_branchs',
            [['is_delete', 0], ['is_active', 1],
            ['id', $branchId]],
            ['id', 'branch_name']);

$groupInfo = Common::ViewTableFirst('gnl_groups',
            [['is_delete', 0], ['is_active', 1]],
            ['id', 'group_name']);

?>
<div class="w-full d-print-none">
    <div class="panel">
        <div class="panel-body">
            <div class="row align-items-center pb-10 d-print-none">
                <div class="col-lg-2">
                    <label class="input-title">Month</label>
                    <div class="input-group">
                      <input type="text" class="form-control monthYearPicker" id="month_year" name="month_year"
                          placeholder="MM-YYYY" autocomplete="off">
                    </div>
                </div>
                {!! HTML::forBranchFeildSearch_new('one') !!}

                <div class="col-lg-2 pt-20 text-center">
                    <a href="javascript:void(0)" name="searchButton" id="searchButton" class="btn btn-primary btn-round">Search</a>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="w-full">
    <div class="panel">
        <div class="panel-body pdf-export">
            <div class="row text-center d-none d-print-block">
                <div class="col-lg-12" style="color:#000;">
                    <strong>{{ $groupInfo->group_name }}</strong><br>
                    <strong>{{ $branchInfo->branch_name }}</strong><br>
                    <span>Management Information Reports</span><br>
                    (<span id="start_date_txt">{{ date('d-M-Y', strtotime($startDate)) }}</span>
                    to
                    <span id="end_date_txt">{{ date('d-M-Y', strtotime($endDate)) }}</span>)
                </div>
            </div>
            <div class="row d-print-none text-right"  data-html2canvas-ignore="true">
                <div class="col-lg-12">
                    <a href="javascript:void(0)" onClick="window.print();" style="background-color:transparent;border:none;" class="btnPrint mr-2">
                        <i class="fa fa-print fa-lg" style="font-size:20px;"></i>
                    </a>
                    <a href="javascript:void(0)" style="background-color:transparent;border:none;" onclick="getPDF();">
                        <i class="fa fa-file-pdf-o fa-lg" style="font-size:20px;"></i>
                    </a>
                    <a href="javascript:void(0)" title="excel" style="background-color:transparent;border:none;" onclick="fnDownloadXLSX();">
                        <i class="fa fa-file-excel-o fa-lg" style="font-size:20px; margin-left: 5px;"></i>
                    </a>

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
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable" id="report-table">
                        <thead>
                            <tr>
                                <th rowspan="2" >Branch Name</th>
                                <th colspan="6">Status Upto Last Month</th>
                                <th colspan="4">Sales On Current Month</th>
                                <th colspan="2">Current Month</th>
                                <th rowspan="2">Total Collection Amount</th>
                                <th rowspan="2">This Month Due</th>
                                <th colspan="6">Status Upto Current Month</th>
                            </tr>
                            <tr>
                                <th>No.Of Customer</th>
                                <th>No.Of Product</th>
                                <th>Total Sales Amount</th>
                                <!-- <th>Total Installment (Without 1st ins)</th>
      <th>Total 1st Installment (with service charge)</th>
      <th>Total Service Charge</th> -->
                                <th>Total Collection Amount</th>
                                <th>Balance</th>
                                <th>Total Due</th>
                                <th>No.Of Customer</th>
                                <th>No.Of Product</th>
                                <th>Sales Amount</th>
                                <th>1<sup>st</sup> Ins.</th>
                                <th>Recoverable Amount</th>
                                <th>Collection Amount</th>
                                <th>No.Of Customer</th>
                                <th>No.Of Product</th>
                                <th>Total Sales Amount</th>
                                <!-- <th>Total Installment (Without 1st ins)</th>
      <th>Total 1st Installment (with service charge)</th>
      <th>Total Service Charge</th> -->
                                <th>Total Collection Amount</th>
                                <th>Balance</th>
                                <th>Total Due</th>
                            </tr>
                            <tr>
                                <th>1</th>
                                <th>2</th>
                                <th>3</th>
                                <!-- <th>Total Installment (Without 1st ins)</th>
      <th>Total 1st Installment (with service charge)</th>
      <th>Total Service Charge</th> -->
                                <th>4</th>
                                <th>5</th>
                                <th>6=4-5</th>
                                <th>7</th>
                                <th>8</th>
                                <th>9</th>
                                <th>10</th>
                                <th>11</th>
                                <th>12</th>
                                <th>13</th>
                                <th>14</th>
                                <th>15</th>
                                <!-- <th>Total Installment (Without 1st ins)</th>
      <th>Total 1st Installment (with service charge)</th>
      <th>Total Service Charge</th> -->
                                <th>16</th>
                                <th>17</th>
                                <th>18</th>
                                <th>19</th>
                                <th>20=18-19</th>
                                <th>21</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- <tr>
                                <td class="text-center" colspan="13">No entry found</td>
                            </tr> -->

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {

        // ajaxDataLoad();

        $('#searchButton').click(function() {

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

            $('#start_date_txt').html(start_date);
            $('#end_date_txt').html(end_date);

            ajaxDataLoad(start_date, end_date, zone_id, area_id, branch_id, supplier_id, group_id, cat_id, sub_cat_id, brand_id, model_id, product_id, stock);
        });
    });

    function ajaxDataLoad(start_date = null, end_date = null, zone_id = null, area_id = null, branch_id = null, supplier_id = null, group_id = null, cat_id = null, sub_cat_id = null, brand_id = null, model_id = null, product_id = null, stock =
        null) {

        $('#report-table').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            // lengthMenu: [[10, 20, 30, 50, -1], [10, 20, 30, 50, "All"]],
            paging: false,
            ordering: false,
            info: false,
            searching: false,
            "ajax": {
                "url": "{{ route('branchRDatatable') }}",
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
                    data: 'id',
                    className: 'text-center'
                },
                {
                    data: 'product_name'
                },
                {
                    data: 'model_name'
                },
                {
                    data: 'sys_barcode'
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
                    data: 'purchase_return_qtn',
                    className: 'text-center'
                },
                {
                    data: 'purchase_return_amt',
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
            drawCallback: function(oResult) {

                if (oResult.json) {
                    $('#totalRowDiv').html(oResult.json.totalRow);
                    // $('#o_stock').html(oResult.json.ttlOStock);
                    // $('#purchase').html(oResult.json.ttlPurchase);
                    // $('#purchase_r').html(oResult.json.ttlPurchaseR);
                    // $('#transfer_in').html(oResult.json.ttlTransferIn);
                    // $('#transfer_out').html(oResult.json.ttlTransferOut);
                    // $('#sales').html(oResult.json.ttlSales);
                    // $('#sales_r').html(oResult.json.ttlSalesR);
                    // $('#adj').html(oResult.json.ttlAdj);
                    // $('#stock').html(oResult.json.ttlStock);
                }
            }
        });
    }

    $('#branch_id').change(function() {

        fnAjaxSelectBox('supplier_id',
            this.value,
            '{{base64_encode("pos_suppliers")}}',
            '{{base64_encode("branch_id")}}',
            '{{base64_encode("id,sup_name")}}',
            '{{url("/ajaxSelectBox")}}');
    });
//MIS branch report
function fnDownloadXLSX() {
    $('.clsDataTable').tableExport({
        type: 'xlsx',
        fileName: 'MIS branch report',
        displayTableName: true,

        // ignoreColumn: [1, 4],
    });
}
function getPDF() {

    var HTML_Width = $(".pdf-export").width();
    var HTML_Height = $(".pdf-export").height();
    var top_left_margin = 15;
    var PDF_Width = HTML_Width + (top_left_margin * 2);
    var PDF_Height = (PDF_Width * 1.5) + (top_left_margin * 2);
    var canvas_image_width = HTML_Width;
    var canvas_image_height = HTML_Height;

    var totalPDFPages = Math.ceil(HTML_Height / PDF_Height) - 1;


    html2canvas($(".pdf-export")[0], {
        allowTaint: true
    }).then(function(canvas) {
        canvas.getContext('2d');

        console.log(canvas.height + "  " + canvas.width);


        var imgData = canvas.toDataURL("image/jpeg", 1.0);
        var pdf = new jsPDF('p', 'pt', [PDF_Width, PDF_Height]);
        pdf.addImage(imgData, 'JPG', top_left_margin, top_left_margin, canvas_image_width,
            canvas_image_height);


        for (var i = 1; i <= totalPDFPages; i++) {
            pdf.addPage(PDF_Width, PDF_Height);
            pdf.addImage(imgData, 'JPG', top_left_margin, -(PDF_Height * i) + (top_left_margin * 4),
                canvas_image_width, canvas_image_height);
        }

        pdf.save("MIS branch_report.pdf");
    });
};
</script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/tableExport.min.js') }}"></script>

<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.debug.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.plugin.autotable.js') }}"></script>

<script type="text/javascript" src="{{ asset('assets/js/pdf/xlsx.core.min.js') }}"></script>
<!-- <script type="text/javascript" src="{{ asset('assets/js/pdf/FileSaver.min.js') }}"></script> -->


<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.3/jspdf.min.js"></script> -->
<script src="https://html2canvas.hertzen.com/dist/html2canvas.js"></script>
@endsection
