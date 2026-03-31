@extends('Layouts.erp_master_full_width')
@section('content')

<?php
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;

$startDate = (isset($startDate) && !empty($startDate)) ? $startDate :  Common::systemCurrentDate();
$endDate = (isset($endDate) && !empty($endDate)) ? $endDate :  Common::systemCurrentDate();
$branchId = (isset($branchId) && !empty($branchId)) ? $branchId :  '';
$supplierId = (isset($supplierId) && !empty($supplierId)) ? $supplierId :  '';
$productId = (isset($productId) && !empty($productId)) ? $productId :  '';



$supplierData = Common::ViewTableOrder('inv_suppliers',
[['is_delete', 0], ['is_active', 1]],
['id', 'sup_name'],
['sup_name', 'ASC']);

$productData = Common::ViewTableOrder('inv_products',
  [['is_delete', 0], ['is_active', 1]],
  ['id', 'product_name', 'product_code'],
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

                    {!! HTML::forBranchFeildSearch('all', 'delivery_place', 'branch_id', 'Delivery Place', null)
                    !!}

                    <div class="col-lg-2">
                        <label class="input-title">Product Name</label>
                        <div class="input-group">
                            <select class="form-control clsSelect2" name="product_id">
                                <option value="">All</option>
                                @foreach ($productData as $row)
                                <option value="{{ $row->id }}" <?= ($productId == $row->id) ? 'Selected': '' ?>>
                                    {{ $row->product_code ? $row->product_name." (".$row->product_code.")" : $row->product_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

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

                    <div class="col-lg-2 pt-20">
                        <button type="submit" class="btn btn-primary btn-round" id="validateButton2">Search</button>
                        <a href="{{url('inv/report/product_order')}}" class="btn btn-danger btn-round">Refresh</a>

                        <!-- <button type="submit" class="btn btn-primary btn-round" value="pdfButton" name="pdfButton" id="pdfButton">Export PDF</button> -->

                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="w-full">
    <div class="panel">
        <div class="panel-body">

            <div class="row text-dark ExportHeading">
                <div class="col-xl-12 col-lg-12 col-sm-12 col-md-12 col-12" style="text-align:center;">
                    <br>
                    <strong>{{ $groupInfo->group_name }}</strong><br>
                    <strong>Head Office</strong><br>
                    <span>Product Order Report</span><br>
                    (<span id="start_date_txt">{{ date('d-M-Y', strtotime($startDate)) }}</span>
                    to
                    <span id="end_date_txt">{{ date('d-M-Y', strtotime($endDate)) }}</span>)
                </div>
                <br><br>
            </div>

            <div class="row d-print-none text-right">
                <div class="col-lg-12">
                    <a href="javascript:void(0)" onClick="window.print();"
                        style="background-color:transparent;border:none;" class="btnPrint mr-2">
                        <i class="fa fa-print fa-lg" style="font-size:20px;"></i>
                    </a>

                    <a href="javascript:void(0)" style="background-color:transparent;border:none;"
                        onclick="getDownloadPDF();">
                        <i class="fa fa-file-pdf-o fa-lg" style="font-size:20px; margin-left: 5px;"></i>
                    </a>

                    <a href="javascript:void(0)" title="Excel" style="background-color:transparent;border:none;"
                        onclick="fnDownloadExcel('ExportHeading,ExportDiv', 'Order_Report_{{ (new Datetime())->format('d-m-Y') }}');">
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

            <div class="row ExportDiv">
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
                        <?php
                            $i = 0;
                            $TotalQnt = 0;
                            $ProdOrderArr = array();

                            foreach($ProdOrderData as $row){

                                $TotalQnt += $row->product_quantity;
                                $rSpam = count($DataSetNew[$row->order_no]);
                        ?>
                            <tr>
                                <?php
                                if(!in_array($row->order_no, $ProdOrderArr)){
                                    $i++;
                                    array_push($ProdOrderArr, $row->order_no);
                                    ?>
                                    <td rowspan="<?=$rSpam?>" class="text-center"><?=$i?></td>
                                    <td rowspan="<?=$rSpam?>"><?= $row->order_no ?></td>
                                    <td rowspan="<?=$rSpam?>"><?= $row->order_date ?></td>
                                    <td rowspan="<?=$rSpam?>"><?= $row->delivery_date ?></td>
                                    <td rowspan="<?=$rSpam?>"><?= $row->delivery_place ?></td>
                                    <?php
                                }
                                ?>
                                <td><?= $row->sup_name ?></td>
                                <td><?= $row->product_code ? $row->product_name. " (".$row->product_code.")" :
                                $row->product_name ?></td>
                                <td class="text-center"><?= $row->product_quantity ?></td>
                            </tr>
                        <?php
                            }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="7" style="text-align:right!important;"><strong>Total</strong></td>
                            <td class="text-center"><strong><?=$TotalQnt?></strong></td>
                        </tr>
                    </tfoot>
                </table>
                @include('../elements.signature.signatureSet')
            </div>
        </div>
    </div>
</div>
<!-- End Page -->
@endsection
