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

    $zoneData = Common::ViewTableOrder('gnl_zones',
        [['is_delete', 0], ['is_active', 1]],
        ['id', 'zone_name'],
        ['zone_name', 'ASC']);

    $areaData = Common::ViewTableOrder('gnl_areas',
        [['is_delete', 0], ['is_active', 1]],
        ['id', 'area_name'],
        ['area_name', 'ASC']);

    $branchData = Common::ViewTableOrderIn('gnl_branchs',
        [['is_delete', 0], ['is_active', 1]],
        ['id', HRS::getUserAccesableBranchIds()],
        ['id', 'branch_name'],
        ['branch_name', 'ASC']);

    $supplierData = Common::ViewTableOrderIn('inv_suppliers',
            [['is_delete', 0], ['is_active', 1]],
            ['branch_id', HRS::getUserAccesableBranchIds()],
            ['id', 'sup_name'],
            ['id', 'ASC']);

    $groupData = Common::ViewTableOrder('inv_p_groups',
        [['is_delete', 0], ['is_active', 1]],
        ['id', 'group_name'],
        ['group_name', 'ASC']);

    $branchId = Common::getBranchId();

    $branchInfo = Common::ViewTableFirst('gnl_branchs',
        [['is_delete', 0], ['is_active', 1],
            ['id', $branchId]],
        ['id', 'branch_name']);

    $groupInfo = Common::ViewTableFirst('gnl_groups',
        [['is_delete', 0], ['is_active', 1]],
        ['id', 'group_name']);

?>

<div class="panel">
    <div class="panel-body">
        <form method="post">
            @csrf
            <div class="row align-items-center pb-10 d-print-none">

                <div class="col-lg-2">
                    <label class="input-title">Month</label>
                    <div class="input-group">
                        <!-- monthYearPicker -->
                        <input type="text" class="form-control" id="month_year" name="month_year" placeholder="MM-YYYY" autocomplete="off">
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
                    <button type="submit" class="btn btn-primary btn-round" id="validateButton2">Search</button>
                </div>
            </div>
        </form>

        <div class="row text-center">
            <div class="col-lg-12" style="color:#000;">
                <strong>{{ $groupInfo->group_name }}</strong><br>
                <strong>{{ $branchInfo->branch_name }}</strong><br>
                <span>MIS Report 1</span><br>
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
            <div class="col-md-12">
                <h4 style="color:#000;">01. Statement of Outlet & Customer</h4>
                <table class="table w-full table-hover table-bordered table-striped table-responsive clsDataTable text-center">
                    <thead>
                        <tr>
                            <th rowspan="2">Name Of Supplier</th>
                            <th rowspan="2">Name Of Product Group</th>
                            <th rowspan="2">No Of Out Let</th>
                            <th colspan="5">Cumulative Customer(End of the Prieod)</th>
                            <th colspan="4">New Customer(Current Prieod)</th>
                            <th colspan="5">Total Customer(End of the Current Prieod)</th>
                        </tr>
                        <tr>
                            <th>Cash</th>
                            <th>Credit</th>
                            <th>Total Customer</th>
                            <th>Full Paid(Credit)</th>
                            <th>Current Customer</th>

                            <th>Cash</th>
                            <th>Credit</th>
                            <th>Total Customer</th>
                            <th>Full Paid</th>

                            <th>Cash</th>
                            <th>Credit</th>
                            <th>Total Customer</th>
                            <th>Full Paid Customer</th>
                            <th>Customer(End Of the current period)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>2</td>
                            <td>3</td>
                            <td>4</td>
                            <td>5</td>
                            <td>6=4+5</td>
                            <td>7</td>
                            <td>8=5-7</td>
                            <td>9</td>
                            <td>10</td>
                            <td>11=9+10</td>
                            <td>12</td>
                            <td>13=4+9</td>
                            <td>14=5+10</td>
                            <td>15=6+11</td>
                            <td>16=7+12</td>
                            <td>17=14-16</td>
                        </tr>
                        @if(!empty($misData))
                            @foreach($misData as $key => $row)
                                <tr>
                                    <td rowspan="{{ count($row) + 1 }}">{{ $key }}</td>
                                </tr>
                                @foreach($row as $key => $value)
                                    <tr>
                                        <td>{{ $value }}</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<script>

    $(document).ready(function(){
        $('#month_year').datepicker({
            dateFormat: 'MM-yy',
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            todayButton: false,
            onClose: function(dateText, inst) {
                var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
                var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
                $(this).val($.datepicker.formatDate('MM-yy', new Date(year, month, 1)));
            }
        });
    });

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
