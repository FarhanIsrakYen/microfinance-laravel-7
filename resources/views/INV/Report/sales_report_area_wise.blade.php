@extends('Layouts.erp_master_full_width')
@section('content')

<?php 
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;

$startDate = (isset($startDate) && !empty($startDate)) ? $startDate :  Common::systemCurrentDate();
$endDate = (isset($endDate) && !empty($endDate)) ? $endDate :  Common::systemCurrentDate();

$groupData = Common::ViewTableOrder('inv_p_groups',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'group_name'],
    ['id', 'ASC']);

$categoryData = Common::ViewTableOrder('inv_p_categories',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'cat_name'],
    ['cat_name', 'ASC']);

$subCatData = Common::ViewTableOrder('inv_p_subcategories',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'sub_cat_name'],
    ['sub_cat_name', 'ASC']);

$brandData = Common::ViewTableOrder('inv_p_brands',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'brand_name'],
    ['brand_name', 'ASC']);

$modelData = Common::ViewTableOrder('inv_p_models',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'model_name'],
    ['id', 'ASC']);

$areaData = Common::ViewTableOrder('gnl_areas',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'area_name'],
    ['area_name', 'ASC']);

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
            <form method="post">
                @csrf
                <div class="row align-items-center pb-10 d-print-none">
                  <!-- ((empty($reqData)) ? $startDate : $reqData['StartDate']) -->
                    <div class="col-lg-2">
                        <label class="input-title">Start Date</label>
                        <div class="input-group">
                            <input type="text" class="form-control datepicker-custom" id="start_date" name="StartDate"
                                placeholder="DD-MM-YYYY"
                                value="{{ $startDate }}">
                        </div>
                    </div>
                    <!-- ((empty($reqData)) ? $endDate : $reqData['EndDate']) -->
                    <div class="col-lg-2">
                        <label class="input-title">End Date</label>
                        <div class="input-group">
                            <input type="text" class="form-control datepicker-custom" id="end_date" name="EndDate"
                                placeholder="DD-MM-YYYY"
                                value="{{ $endDate }}">
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <label class="input-title">Area</label>
                        <div class="input-group">
                            <select class="form-control clsSelect2" name="area_id" id="area_id">
                                <option value="">Select All</option>
                                @foreach($areaData as $row)
                                <option value="{{ $row->id}}"
                                    {{ ((empty($reqData)) ? '' : (($reqData['area_id'] == $row->id) ? 'selected' : '')) }}>
                                    {{ $row->area_name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <label class="input-title">Group</label>
                        <div class="input-group">
                            <select class="form-control clsSelect2" name="group_id" id="group_id" onchange="fnAjaxSelectBox('cat_id',
                                                            this.value,
                                                            '{{ base64_encode("inv_p_categories")}}',
                                                            '{{base64_encode("prod_group_id")}}',
                                                            '{{base64_encode("id,cat_name")}}',
                                                            '{{url("/ajaxSelectBox")}}');">

                                <option value="">Select All</option>
                                @foreach($groupData as $row)
                                <option value="{{ $row->id}}"
                                    {{ ((empty($reqData)) ? '' : (($reqData['group_id'] == $row->id) ? 'selected' : '')) }}>
                                    {{ $row->group_name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <label class="input-title">Category</label>
                        <div class="input-group">
                            <select class="form-control clsSelect2" name="cat_id" id="cat_id" onchange="fnAjaxSelectBox('sub_cat_id',
                                                            this.value,
                                                            '{{base64_encode("inv_p_subcategories")}}',
                                                            '{{base64_encode("prod_cat_id")}}',
                                                            '{{base64_encode("id,sub_cat_name")}}',
                                                            '{{url("/ajaxSelectBox")}}');">
                                <option value="">Select All</option>
                                @foreach ($categoryData as $row)
                                <option value="{{ $row->id }}"
                                    {{ ((empty($reqData)) ? '' : (($reqData['cat_id'] == $row->id) ? 'selected' : '')) }}>
                                    {{ $row->cat_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <label class="input-title">Sub Category</label>
                        <div class="input-group">
                            <select class="form-control clsSelect2" name="sub_cat_id" id="sub_cat_id" onchange="fnAjaxSelectBox('brand_id',
                                                              this.value,
                                                              '{{base64_encode("inv_p_brands")}}',
                                                              '{{base64_encode("prod_sub_cat_id")}}',
                                                              '{{base64_encode("id,brand_name")}}',
                                                              '{{url("/ajaxSelectBox")}}');">
                                <option value="">Select All</option>
                                @foreach ($subCatData as $row)
                                <option value="{{ $row->id }}"
                                    {{ ((empty($reqData)) ? '' : (($reqData['sub_cat_id'] == $row->id) ? 'selected' : '')) }}>
                                    {{ $row->sub_cat_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row align-items-center pb-10 d-print-none">
                    <div class="col-lg-2">
                        <label class="input-title">Brand</label>
                        <div class="input-group">
                            <select class="form-control clsSelect2" name="brand_id" id="brand_id" onchange="fnAjaxSelectBox('model_id',
                                                              this.value,
                                                              '{{base64_encode("inv_p_models")}}',
                                                              '{{base64_encode("prod_brand_id")}}',
                                                              '{{base64_encode("id,model_name")}}',
                                                              '{{url("/ajaxSelectBox")}}');">
                                <option value="">Select All</option>
                                @foreach ($brandData as $row)
                                <option value="{{ $row->id }}"
                                    {{ ((empty($reqData)) ? '' : (($reqData['brand_id'] == $row->id) ? 'selected' : '')) }}>
                                    {{ $row->brand_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-2">
                        <label class="input-title">Model</label>
                        <div class="input-group">
                            <select class="form-control clsSelect2" name="model_id" id="model_id" onchange="fnAjaxSelectBox('product_id',
                                                              this.value,
                                                              '{{base64_encode("inv_products")}}',
                                                              '{{base64_encode("prod_model_id")}}',
                                                              '{{base64_encode("id,product_name")}}',
                                                              '{{url("/ajaxSelectBox")}}');">
                                <option value="">Select All</option>
                                @foreach ($modelData as $row)
                                <option value="{{ $row->id }}"
                                    {{ ((empty($reqData)) ? '' : (($reqData['model_id'] == $row->id) ? 'selected' : '')) }}>
                                    {{ $row->model_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-2 pt-20 text-center">
                        {{-- <a name="searchButton" id="searchButton" class="btn btn-primary btn-round">Search</a> --}}
                        <button type="submit" class="btn btn-primary btn-round" id="validateButton2">Search</button>
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
                    <strong>{{ $branchInfo->branch_name }}</strong><br>
                    <span>Area Wise Sales Report</span><br>
                    (<span id="start_date_txt">{{ date('d-M-Y', strtotime(((!empty($reqData)) ? $reqData['StartDate'] : ''))) }}</span>
                    To
                    <span id="end_date_txt">{{ date('d-M-Y', strtotime(((!empty($reqData)) ? $reqData['EndDate'] : ''))) }}</span>)
                    <br><br>
                </div>
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
                        onclick="fnDownloadExcel('ExportHeading,ExportDiv', 'Area_Sales_Report_{{ (new Datetime())->format('d-m-Y') }}');">
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
                            <th width="3%" rowspan="2">SL</th>
                            <th rowspan="2">Branch Name</th>
                            <th colspan="2">Number Of Customer</th>
                            <th colspan="2">Number Of Quantity</th>
                            <th colspan="2">Amount</th>
                            <th colspan="3">Total</th>
                        </tr>
                        <tr>
                            <th>Cash</th>
                            <th>Credit</th>
                            <th>Cash</th>
                            <th>Credit</th>
                            <th>Cash</th>
                            <th>Credit</th>
                            <th>Customer</th>
                            <th>Quantity</th>
                            <th>Amount</th>
                        </tr>
                    </thead>

                    <?php
                        $final_ttl_cash_cust = 0;
                        $final_ttl_credit_cust = 0;
                        $final_ttl_cash_qtn = 0;
                        $final_ttl_credit_qtn = 0;
                        $final_ttl_cash_amt = 0;
                        $final_ttl_credit_amt = 0;
                        $final_ttl_cust = 0;
                        $final_ttl_qtn = 0;
                        $final_ttl_amt = 0;
                    ?>
                    @if(!empty($DataSet))
                    @foreach($DataSet as $key => $row)
                    <tbody>
                        <?php
                        $i = 1;
                        $ttl_cash_cust = 0;
                        $ttl_credit_cust = 0;
                        $ttl_cash_qtn = 0;
                        $ttl_credit_qtn = 0;
                        $ttl_cash_amt = 0;
                        $ttl_credit_amt = 0;
                        $ttl_cust = 0;
                        $ttl_qtn = 0;
                        $ttl_amt = 0;
                    ?>

                        <tr>
                            <td colspan="11">
                                <h5>{{ $key }}</h5>
                            </td>
                        </tr>
                        @foreach($row as $data)
                        <tr>
                            <td class="text-center">{{ $i++ }}</td>
                            <td>
                                {{ $data['branch_name'] }}
                            </td>
                            <td class="text-center">
                                {{ $data['ttl_cash_cust'] }}
                                <?php $ttl_cash_cust += $data['ttl_cash_cust'] ?>
                            </td>
                            <td class="text-center">
                                {{ $data['ttl_credit_cust'] }}
                                <?php $ttl_credit_cust += $data['ttl_credit_cust'] ?>
                            </td>
                            <td class="text-center">
                                {{ $data['ttl_cash_qtn'] }}
                                <?php $ttl_cash_qtn += $data['ttl_cash_qtn'] ?>
                            </td>
                            <td class="text-center">
                                {{ $data['ttl_credit_qtn'] }}
                                <?php $ttl_credit_qtn += $data['ttl_credit_qtn'] ?>
                            </td>
                            <td class="text-right">
                                {{ $data['ttl_cash_amt'] }}
                                <?php $ttl_cash_amt += $data['ttl_cash_amt'] ?>
                            </td>
                            <td class="text-right">
                                {{ $data['ttl_credit_amt'] }}
                                <?php $ttl_credit_amt += $data['ttl_credit_amt'] ?>
                            </td>
                            <td class="text-center">
                                {{ $data['ttl_cust'] }}
                                <?php $ttl_cust += $data['ttl_cust'] ?>
                            </td>
                            <td class="text-center">
                                {{ $data['ttl_qtn'] }}
                                <?php $ttl_qtn += $data['ttl_qtn'] ?>
                            </td>
                            <td class="text-right">
                                {{ $data['ttl_amt'] }}
                                <?php $ttl_amt += $data['ttl_amt'] ?>
                            </td>
                        </tr>
                        @endforeach
                        <tr>
                            <td colspan="2" class="text-right"><b>{{ $reqData['total'] }}</b></td>
                            
                            <td id="ttl_cust_c" class="text-center text-dark font-weight-bold">
                                {{ $ttl_cash_cust }}
                                <?php $final_ttl_cash_cust += $ttl_cash_cust ?>
                            </td>
                            <td id="ttl_cust_cr" class="text-center text-dark font-weight-bold">
                                {{ $ttl_credit_cust }}
                                <?php $final_ttl_credit_cust += $ttl_credit_cust ?>
                            </td>

                            <td id="ttl_qtn_c" class="text-center text-dark font-weight-bold">
                                {{ $ttl_cash_qtn }}
                                <?php $final_ttl_cash_qtn += $ttl_cash_qtn ?>
                            </td>
                            <td id="ttl_qtn_cr" class="text-center text-dark font-weight-bold">
                                {{ $ttl_credit_qtn }}
                                <?php $final_ttl_credit_qtn += $ttl_credit_qtn ?>
                            </td>

                            <td id="ttl_amt_c" class="text-right text-dark font-weight-bold">
                                {{ $ttl_cash_amt }}
                                <?php $final_ttl_cash_amt += $ttl_cash_amt ?>
                            </td>
                            <td id="ttl_amt_cr" class="text-right text-dark font-weight-bold">
                                {{ $ttl_credit_amt }}
                                <?php $final_ttl_credit_amt += $ttl_credit_amt ?>
                            </td>
                            <td id="ttl_ttl_cust" class="text-center text-dark font-weight-bold">
                                {{ $ttl_cust }}
                                <?php $final_ttl_cust += $ttl_cust ?>
                            </td>
                            <td id="ttl_ttl_qtn" class="text-center text-dark font-weight-bold">
                                {{ $ttl_qtn }}
                                <?php $final_ttl_qtn += $ttl_qtn ?>
                            </td>
                            <td id="ttl_ttl_amt" class="text-right text-dark font-weight-bold">
                                {{ $ttl_amt }}
                                <?php $final_ttl_amt += $ttl_amt ?>
                            </td>
                        </tr>
                    </tbody>
                    @endforeach
                    <tfoot>
                        <tr>
                            <td colspan="2" class="text-right"><b>TOTAL</b></td>

                            <td id="ttl_cust_c" class="text-center text-dark font-weight-bold">
                                {{ $final_ttl_cash_cust }}
                            </td>
                            <td id="ttl_cust_cr" class="text-center text-dark font-weight-bold">
                                {{ $final_ttl_credit_cust }}
                            </td>

                            <td id="ttl_qtn_c" class="text-center text-dark font-weight-bold">
                                {{ $final_ttl_cash_qtn }}
                            </td>
                            <td id="ttl_qtn_cr" class="text-center text-dark font-weight-bold">
                                {{ $final_ttl_credit_qtn }}
                            </td>
                            <td id="ttl_amt_c" class="text-right text-dark font-weight-bold">
                                {{ $final_ttl_cash_amt }}
                            </td>
                            <td id="ttl_amt_cr" class="text-right text-dark font-weight-bold">
                                {{ $final_ttl_credit_amt }}
                            </td>
                            <td id="ttl_ttl_cust" class="text-center text-dark font-weight-bold">
                                {{ $final_ttl_cust }}
                            </td>
                            <td id="ttl_ttl_qtn" class="text-center text-dark font-weight-bold">
                                {{ $final_ttl_qtn }}
                            </td>
                            <td id="ttl_ttl_amt" class="text-right text-dark font-weight-bold">
                                {{ $final_ttl_amt }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
