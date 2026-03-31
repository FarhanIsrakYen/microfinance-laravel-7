@extends('Layouts.erp_master_full_width')
@section('content')

<?php 
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
?>

<!-- Page -->
<script type="text/javascript" src="{{ asset('assets/js/pdf/tableExport.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.plugin.autotable.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/xlsx.core.min.js') }}"></script>
<!-- Page -->

<?php
$startDate = (isset($startDate) && !empty($startDate)) ? $startDate :  Common::systemCurrentDate();
$endDate = (isset($endDate) && !empty($endDate)) ? $endDate :  Common::systemCurrentDate();
$branchId = (isset($branchId) && !empty($branchId)) ? $branchId :  '';
$supplierId = (isset($supplierId) && !empty($supplierId)) ? $supplierId :  '';
$productId = (isset($productId) && !empty($productId)) ? $productId :  '';

// $requiData = Common::ViewTableOrder('inv_requisitions_m',
//     [['is_delete', 0], ['is_active', 1]],
//     ['id', 'requisition_no'],
//     ['requisition_no', 'ASC']);

$supplierData = Common::ViewTableOrder('inv_suppliers',
[['is_delete', 0], ['is_active', 1]],
['id', 'sup_name'],
['sup_name', 'ASC']);

$productData = Common::ViewTableOrder('inv_products',
  [['is_delete', 0], ['is_active', 1]],
  ['id', 'product_name', 'prod_barcode'],
  ['product_name', 'ASC']);


// $branchInfo = Common::ViewTableFirst('gnl_branchs',
//     [['is_delete', 0], ['is_active', 1],
//         ['id', 1]],
//     ['id', 'branch_name']);

$groupInfo = Common::ViewTableFirst('gnl_groups',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'group_name']);

?>

<div class="w-full d-print-none">
    <div class="panel">
        <div class="panel-body">
            <form method="post">
                @csrf
                <div class="row align-items-center pb-10 d-print-none">

                    <div class="col-lg-2">
                        <label class="input-title">Start Date</label>
                        <div class="input-group">
                            <input type="text" class="form-control datepicker-custom" name="StartDate"
                                value="{{ $startDate }}">
                        </div>
                    </div>

                    <div class="col-lg-2">
                        <label class="input-title">End Date</label>
                        <div class="input-group">
                            <input type="text" class="form-control datepicker-custom" name="EndDate"
                                value="{{ $endDate }}">
                        </div>
                    </div>

                    <!-- <div class="col-lg-2">
                        <label class="input-title">Requisition No</label>
                        <div class="input-group">
                            <select class="form-control clsSelect2" name="requisition_no">
                                <option value="">Select All</option>
                                foreach ($requiData as $row)
                                <option value="$row->requisition_no">$row->requisition_no</option>
                                endforeach
                            </select>
                        </div>
                        </div> -->

                    {!! HTML::forBranchFeildSearch_new('all', 'branch_from', 'branch_id', 'Requisition From', $branchId)
                    !!}

                    <div class="col-lg-2">
                        <label class="input-title">Supplier</label>
                        <div class="input-group">
                            <select class="form-control clsSelect2" name="supplier_id">
                                <option value="">All</option>
                                @foreach ($supplierData as $row)
                                <option value="{{ $row->id }}" <?= ($supplierId == $row->id) ? 'Selected': '' ?>>
                                    {{ $row->sup_name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-2">
                        <label class="input-title">Product Name</label>
                        <div class="input-group">
                            <select class="form-control clsSelect2" name="product_id">
                                <option value="">All</option>
                                @foreach ($productData as $row)
                                <option value="{{ $row->id }}" <?= ($productId == $row->id) ? 'Selected': '' ?>>
                                    {{ $row->product_name." (".$row->prod_barcode.")" }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-2 pt-20">
                        <button type="submit" class="btn btn-primary btn-round" id="validateButton2">Search</button>
                        <a href="{{url('inv/report/requisition')}}" class="btn btn-danger btn-round">Refresh</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="w-full">
    <div class="panel">
        <div class="panel-body pdf-export">

            <div class="row text-center  d-print-block">
                <!-- <div class="col-lg-12" style="color:#000;"> -->
                    <table class="w-full pdf-export">
                        <tr>
                            <td><strong>{{ $groupInfo->group_name }}</strong></td>
                        </tr>
                        <tr>
                            <td><strong>Head Office</strong></td>
                        </tr>
                        <tr>
                            <td><span>Requisition Report</span></td>
                        </tr>
                        <tr>
                            <td>(<span id="start_date_txt">{{ date('d-M-Y', strtotime($startDate)) }}</span>
                                to
                                <span id="end_date_txt">{{ date('d-M-Y', strtotime($endDate)) }}</span>)
                            </td>
                        </tr>
                    </table>
                <!-- </div> -->
            </div>

            <!-- <div class="row text-center  d-print-block">
                <div class="col-lg-12" style="color:#000;">
                    <strong>{{ $groupInfo->group_name }}</strong><br>
                    <strong>Head Office</strong><br>
                    <span>Requisition Report</span><br>
                    (<span id="start_date_txt">{{ date('d-M-Y', strtotime($startDate)) }}</span>
                    to
                    <span id="end_date_txt">{{ date('d-M-Y', strtotime($endDate)) }}</span>)
                </div>
            </div> -->

            <div class="row d-print-none text-right" data-html2canvas-ignore="true">
                <div class="col-lg-12">
                    <a href="javascript:void(0)" onClick="window.print();"
                        style="background-color:transparent;border:none;" class="btnPrint mr-2">
                        <i class="fa fa-print fa-lg" style="font-size:20px;"></i>
                    </a>
                    <a href="javascript:void(0)" style="background-color:transparent;border:none;"
                        onclick="fnDownloadPDF();">
                        <i class="fa fa-file-pdf-o fa-lg" style="font-size:20px;"></i>
                    </a>
                    <!-- <a href="javascript:void(0)" style="background-color:transparent;border:none;" onclick="getPDF();">
                        <i class="fa fa-file-pdf-o fa-lg" style="font-size:20px; margin-left: 5px;"></i>
                    </a> -->
                    <a href="javascript:void(0)" title="xlsx" style="background-color:transparent;border:none;"
                        onclick="fnDownloadXLSX();">
                        <i class="fa fa-file-excel-o fa-lg" style="font-size:20px; margin-left: 5px;"></i>
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-6 col-lg-6 col-sm-6 col-md-6 col-6">
                    <!-- <span class="d-print-none"><b>Total Row:</b> <span id="totalRowDiv">0</span></span> -->
                </div>
                <div class="col-xl-6 col-lg-6 col-sm-6 col-md-6 col-6 text-right">
                    <span><b>Printed Date:</b> {{ date("d-m-Y") }} </span>
                </div>
            </div>

            <div class="row">
                <table class="table w-full table-hover table-bordered table-striped clsDataTable pdf-export">
                    <thead>
                        <tr>
                            <th width="3%">SL</th>
                            <th width="5%">Requisition Date</th>
                            <th width="10%">Requisition No</th>
                            <th width="10%">Requisition From</th>
                            <!-- <th width="15%" class="text-left">Requisition To</th> -->
                            <th width="15%">Supplier</th>
                            <th width="15%">Product Name</th>
                            <th width="5%">Quantity</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php  
                            $i = 0;
                            $TotalQnt = 0;
                            $RequisitionArr = array();

                            foreach($requisitionData as $row){
                                
                                $TotalQnt += $row->product_quantity;
                                $rSpam = count($DataSetNew[$row->requisition_no]);
                        ?>
                        <tr>
                            <?php
                                if(!in_array($row->requisition_no, $RequisitionArr)){
                                    $i++;
                                    array_push($RequisitionArr, $row->requisition_no);
                                    ?>
                            <td rowspan="<?=$rSpam?>" class="text-center"><?=$i?></td>
                            <td rowspan="<?=$rSpam?>" class="text-center"><?= $row->requisition_date ?></td>
                            <td rowspan="<?=$rSpam?>" class="text-center"><?= $row->requisition_no ?></td>
                            <td rowspan="<?=$rSpam?>" class="text-left"><?= $row->branch_from ?></td>
                            <?php
                                }
                                ?>
                            <td class="text-left"><?= $row->sup_name ?></td>
                            <td class="text-left"><?= $row->product_name. " (".$row->prod_barcode.")" ?></td>
                            <td class="text-center"><?= $row->product_quantity ?></td>
                        </tr>
                        <?php
                            }
                        ?>
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="6" style="text-align:right!important;"><strong>Total</strong></td>
                            <td class="text-center"><strong><?=$TotalQnt?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- End Page -->

<script>
function fnDownloadXLSX() {
    $('.clsDataTable').tableExport({
        type: 'xlsx',
        fileName: 'Requisition',
        displayTableName: true,

        // ignoreColumn: [1, 4],
    });
}

////////////////////////////////
function fnDownloadPDF() {
    $('.pdf-export').tableExport({
        outputMode: 'file',
        tableName: 'area Wise Sales Report',
        type: 'pdf',
        fileName: 'area Wise Sales Report',
        trimWhitespace: true,
        RTL: false,
        jspdf: {
            orientation: 'p',
            unit: 'pt',
            format: 'bestfit',
            autotable: {
                styles: {
                    cellPadding: 2,
                    rowHeight: 12,
                    fontSize: 8,
                    fillColor: 255,
                    textColor: 50,
                    fontStyle: 'normal',
                    overflow: 'ellipsize',
                    halign: 'inherit',
                    valign: 'middle',
                },
                tableWidth: 'auto',
            }
        }
    });
}
////////////////////////////////////////

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

        pdf.save("Requisition.pdf");
    });
}
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