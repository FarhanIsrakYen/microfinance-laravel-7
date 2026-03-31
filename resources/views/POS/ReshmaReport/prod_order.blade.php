@extends('Layouts.erp_master_full_width')
@section('content')

<?php 
use App\Services\CommonService as Common;
?>
<!-- Page -->

<script type="text/javascript" src="{{ asset('assets/js/pdf/tableExport.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.plugin.autotable.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/xlsx.core.min.js') }}"></script>
<!-- Page -->
<?php
$startDate = Common::systemCurrentDate();
$endDate = Common::systemCurrentDate();

$requiData = Common::ViewTableOrder('pos_requisitions_m',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'requisition_no'],
    ['requisition_no', 'ASC']);
$supplierData = Common::ViewTableOrder('pos_suppliers',
[['is_delete', 0], ['is_active', 1]],
['id', 'sup_name'],
['sup_name', 'ASC']);
$productData = Common::ViewTableOrder('pos_products',
  [['is_delete', 0], ['is_active', 1]],
  ['id', 'product_name'],
  ['product_name', 'ASC']);
$branchData = Common::ViewTableOrder('gnl_branchs',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'branch_name'],
    ['branch_name', 'ASC']);


$branchId = Common::getbranchId();

$branchInfo = Common::ViewTableFirst('gnl_branchs',
    [['is_delete', 0], ['is_active', 1],
        ['id', $branchId]],
    ['id', 'branch_name']);

$groupInfo = Common::ViewTableFirst('gnl_groups',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'group_name']);

?>

<div class="panel d-print-none">
    <div class="panel-body">
      <form method="post">
          @csrf
        <div class="row align-items-center pb-10 d-print-none">
            <div class="col-lg-2">
                <label class="input-title">Start Date</label>
                <div class="input-group">
                    <input type="text" class="form-control datepicker-custom" id="start_date" name="StartDate" placeholder="DD-MM-YYYY" value="{{ $startDate }}">
                </div>
            </div>

            <div class="col-lg-2">
                <label class="input-title">End Date</label>
                <div class="input-group">
                    <input type="text" class="form-control datepicker-custom" id="end_date" name="EndDate" placeholder="DD-MM-YYYY" value="{{ $endDate }}">
                </div>
            </div>

            <div class="col-lg-2">
                <label class="input-title">Requisition No</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" name="requisition_no" id="requisition_no">
                        <option value="">Select All</option>
                        @foreach ($requiData as $row)
                        <option value="{{ $row->requisition_no }}">{{ $row->requisition_no }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-2">
                <label class="input-title">Requisition From </label>
                <div class="input-group">
                    <select class="form-control clsSelect2" name="branch_from" id="branch_id">
                        <option value="">Select All</option>

                        @foreach ($branchData as $row)
                        <option value="{{ $row->id }}">{{ $row->branch_name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-2">
                <label class="input-title">Supplier</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" name="supplier_id" id="supplier_id">
                        <option value="">Select All</option>

                        @foreach ($supplierData as $row)
                        <option value="{{ $row->id }}">{{ $row->sup_name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-2">
                <label class="input-title">Product Name</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" name="product_id" id="product_id">
                        <option value="">Select All</option>

                        @foreach ($productData as $row)
                        <option value="{{ $row->id }}">{{ $row->product_name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-12 pt-20 text-center">
                  <button type="submit" class="btn btn-primary btn-round" id="validateButton2">Search</button>
                {{--<a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round" id="requisitionSearch">Search</a>--}}
            </div>
        </div>

  </form>
    </div>
</div>
</div>
<div class="panel">
    <div class="panel-body pdf-export">


        <div class="row text-center  d-print-block">
            <div class="col-lg-12" style="color:#000;">
                <strong>{{ $groupInfo->group_name }}</strong><br>
                <strong>{{ $branchInfo->branch_name }}</strong><br>
                <span>Product Order Report</span><br>
                (<span id="start_date_txt">{{ date('d-M-Y', strtotime($startDate)) }}</span>
                to
                <span id="end_date_txt">{{ date('d-M-Y', strtotime($endDate)) }}</span>)
            </div>
        </div>
        <div class="row d-print-none text-right" data-html2canvas-ignore="true">
            <div class="col-lg-12">
                <a href="javascript:void(0)" onClick="window.print();" class="btnPrint mr-2">
                    <i class="fa fa-print fa-lg" style="font-size:20px;"></i>
                </a>
                <a href="javascript:void(0)" onclick="getPDF();">
                    <i class="fa fa-file-pdf-o fa-lg" style="font-size:20px;"></i>
                </a>
                <a href="javascript:void(0)" onclick="fnDownloadXLSX();">
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
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead>
                            <tr>
                                <th width="3%">SL</th>
                                <th width="5%">Order No</th>
                                <th width="10%"> Order Date</th>
                                <th  width="15%">Delivery Date</th>
                                <th width="15%">Delivery Place</th>
                                <th width="15%">Supplier</th>
                                <th width="15%">Product Name</th>
                                <th width="6%">Quantity</th>
                            </tr>
                        </thead>

                        <tbody>
                          <?php  $i = 1;

                          ?>
                          {{--  @foreach($requisitionData as $row)
                            <tr>
                                <td class="text-center">{{ $i++ }}</td>
                                <td>{{ $row->requisition_date}}</td>
                                <td>{{$row->requisition_no}}</td>
                               <td>{{$row->branch_from}}</td>
                               <td>{{$row->branch_from}}</td>
                                <td>{{$row->sup_name}}</td>
                                <td>{{$row->product_name}}</td>
                                <td class="text-center">{{$row->product_quantity}}</td>

                            </tr>
                            @endforeach--}}
                        </tbody>

                        <!-- <tfoot> -->
                        <!-- <tr>
                                        <td colspan="5" style="text-align:right!important;"><strong>Total:</strong></td>
                                        <td class="text-right"><strong id="TQuantity"></strong></td>
                                        <td class="text-right"><strong id="TUnitPrice"></strong></td>
                                        <td class="text-right"><strong id="TAmount">0.00</strong></td>
                                    </tr> -->
                        <!-- </tfoot> -->
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
</div>
<!-- End Page -->
 <script>
    function fnDownloadXLSX() {
        $('.clsDataTable').tableExport({
            type: 'xlsx',
            fileName: 'Product Order Report',
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

            pdf.save("product_order_report.pdf");
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
