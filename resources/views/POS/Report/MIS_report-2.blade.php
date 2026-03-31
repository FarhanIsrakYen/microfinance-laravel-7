@extends('Layouts.erp_master_full_width')
@section('content')

<?php
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
?>

<script type="text/javascript" src="{{ asset('assets/js/pdf/tableExport.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.plugin.autotable.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/xlsx.core.min.js') }}"></script>
<!-- Page -->
<?php
   use App\Services\HrService as HRS;
    $startDate = Common::systemCurrentDate();
    $endDate = Common::systemCurrentDate();

    $customerData = Common::ViewTableOrderIn('pos_customers',
        [['is_delete', 0], ['is_active', 1]],
        ['branch_id', HRS::getUserAccesableBranchIds()],
        ['customer_no', 'customer_name'],
        ['customer_name', 'ASC']);

    $employeeData = Common::ViewTableOrderIn('hr_employees',
        [['is_delete', 0], ['is_active', 1]],
        ['branch_id', HRS::getUserAccesableBranchIds()],
        ['employee_no', 'emp_name'],
        ['emp_name', 'ASC']);

    $branchData = Common::ViewTableOrderIn('gnl_branchs',
        [['is_delete', 0], ['is_active', 1]],
        ['id', HRS::getUserAccesableBranchIds()],
        ['id', 'branch_name'],
        ['branch_name', 'ASC']);

    $zoneData = Common::ViewTableOrder('gnl_zones',
        [['is_delete', 0], ['is_active', 1]],
        ['id', 'zone_name'],
        ['zone_name', 'ASC']);

    $areaData = Common::ViewTableOrder('gnl_areas',
        [['is_delete', 0], ['is_active', 1]],
        ['id', 'area_name'],
        ['area_name', 'ASC']);

    $groupData = Common::ViewTableOrder('pos_p_groups',
        [['is_delete', 0], ['is_active', 1]],
        ['id', 'group_name'],
        ['group_name', 'ASC']);

    $categoryData = Common::ViewTableOrder('pos_p_categories',
        [['is_delete', 0], ['is_active', 1]],
        ['id', 'cat_name'],
        ['cat_name', 'ASC']);

    $subCatData = Common::ViewTableOrder('pos_p_subcategories',
        [['is_delete', 0], ['is_active', 1]],
        ['id', 'sub_cat_name'],
        ['sub_cat_name', 'ASC']);

    $brandData = Common::ViewTableOrder('pos_p_brands',
        [['is_delete', 0], ['is_active', 1]],
        ['id', 'brand_name'],
        ['brand_name', 'ASC']);

    $branchId = Common::getBranchId();

    $branchInfo = Common::ViewTableFirst('gnl_branchs',
        [['is_delete', 0], ['is_active', 1],
            ['id', $branchId]],
        ['id', 'branch_name']);

    $groupInfo = Common::ViewTableFirst('gnl_groups',
        [['is_delete', 0], ['is_active', 1]],
        ['id', 'group_name']);

    $supplierData = Common::ViewTableOrderIn('pos_suppliers',
                [['is_delete', 0], ['is_active', 1]],
                ['branch_id', HRS::getUserAccesableBranchIds()],
                ['id', 'sup_name'],
                ['id', 'ASC']);

?>

<div class="panel">
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
                    <label class="input-title">Zone</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="zone_id" id="zone_id">
                            <option value="">Select Option</option>
                            @foreach($zoneData as $row)
                            <option value="{{ $row->id}}">{{ $row->zone_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Area</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="area_id" id="area_id">
                            <option value="">Select Option</option>
                            @foreach ($areaData as $row)
                            <option value="{{ $row->id }}">{{ $row->area_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {!! HTML::forBranchFeildSearch_new('all') !!}

                <div class="col-lg-2">
                    <label class="input-title">Supplier</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="supplier_id" id="supplier_id">
                            <option value="">Select Option</option>
                            @foreach ($supplierData as $row)
                            <option value="{{ $row->id }}">{{ $row->sup_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Group</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="group_id" id="group_id">
                            <option value="">Select Option</option>
                            @foreach($groupData as $row)
                            <option value="{{ $row->id}}">{{ $row->group_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row align-items-center pb-10 d-print-none">
                <div class="col-lg-12 pt-20 text-center">
                    <a href="javascript:void(0)" name="searchButton" id="searchButton" class="btn btn-primary btn-round">Search</a>
                </div>
            </div>
        </form>

        <div class="row text-center">
            <div class="col-lg-12" style="color:#000;">
                <strong>{{ $groupInfo->group_name }}</strong><br>
                <strong>{{ $branchInfo->branch_name }}</strong><br>
                <span>MIS Report 2</span><br>
                <strong>Report Period: <span id="txtPeriod"></span></strong>
            </div>
        </div>

        <div class="row d-print-none text-right">
            <div class="col-lg-12">
                <a href="javascript:void(0)" onClick="window.print();" style="background-color:transparent;border:none;" class="btnPrint mr-2">
                    <i class="fa fa-print fa-lg" style="font-size:20px;"></i>
                </a>
                <a href="javascript:void(0)" style="background-color:transparent;border:none;" onclick="fnDownloadPDF();">
                    <i class="fa fa-file-pdf-o fa-lg" style="font-size:20px;"></i>
                </a>
                <a href="javascript:void(0)" style="background-color:transparent;border:none;" onclick="fnDownloadExcel();">
                    <i class="fa fa-file-excel-o fa-lg" style="font-size:20px; margin-left: 5px;"></i>
                    {{-- <i class="fa fa-file-pdf-o fa-lg" style="font-size:20px;"></i> --}}
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-6 col-lg-6 col-sm-6 col-md-6 col-6">
                {{-- <span class="d-print-none"><b>Total Row:</b> <span id="totalRowDiv">0</span></span> --}}
            </div>
            <div class="col-xl-6 col-lg-6 col-sm-6 col-md-6 col-6 text-right">
                <span><b>Printed Date:</b> {{ date("d-m-Y") }} </span>
            </div>
        </div>
        <div class="row">
            <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                <thead>
                    <tr>
                        <th rowspan="3">Name Of Supplier</th>
                        <th rowspan="3">Name Of Product Group</th>
                        <th rowspan="3">Due (End of the last Prieod)</th>
                        <th rowspan="3">Regular Recoverable(Current Prieod)</th>
                        <th colspan="4">Current Month Recovered</th>
                        <th rowspan="3">New Due Amount(Current Prieod)</th>
                        <th colspan="1">End of the Current Prieod</th>
                        <th colspan="5">Overdue Classification</th>
                    </tr>
                    <tr>
                        <th rowspan="2">Regular</th>
                        <th rowspan="2">Due</th>
                        <th rowspan="2">Advance</th>
                        <th rowspan="2">Total</th>

                        <th rowspan="2">Total Due</th>
                        <!-- <th rowspan="2">Total Due Customer</th> -->

                        <th colspan="1">Watchful(1-30 Days)</th>
                        <th colspan="1">Substandard (31-180 Days)</th>
                        <th colspan="1">Doubtful(181-365 Days)</th>
                        <th rowspan="2">Bad debt(365+)</th>
                        <th rowspan="2">Total Due</th>
                    </tr>
                    <tr>
                        <th>Due</th>
                        <th>Due</th>
                        <th>Due</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>2</td>
                        <td>3</td>
                        <td>4</td>
                        <td>5</td>
                        <td>6</td>
                        <td>7</td>
                        <td>8=(5+6+7)</td>
                        <td>9=(4-5)</td>
                        <td>10=(3-6+9)</td>
                        <!-- <td>11</td> -->
                        <td>12</td>
                        <td>13</td>
                        <td>14</td>
                        <td>15</td>
                        <td>16=(12+13+14+15)</td>
                    </tr>
                </tbody>
            </table>
             @include('../elements.signature.signatureSet')
        </div>
    </div>
</div>


<script>

    function fnDownloadPDF() {
        $('.clsDataTable').tableExport({
            type: 'pdf',
            fileName: 'sales-details-report',
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
            type: 'xlsx',
            fileName: 'sales-details-report',
        });
    }
</script>
@endsection
