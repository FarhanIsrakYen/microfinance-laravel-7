@extends('Layouts.erp_master_full_width')
@section('content')

<form enctype="multipart/form-data" method="post" id="filterFormId">
    @csrf

    @include('elements.report.report_filter_options', ['zone' => true,
    'area' => true,
    'branch' => true,
    'customer' => true,
    'employee' => true,
    'salesType' => true,
    'group' => true,
    'startDate' => true,
    'endDate' => true,
    'refresh' => true
    ])
</form>

<div class="w-full">
    <div class="panel">
        <div class="panel-body panel-search pt-2">

            @include('elements.report.report_heading', ['title' => 'Sales Details Report', 'title_excel' =>
            'Sales_Details_Report'])

            <div class="row ExportDiv">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead class="text-center">
                            <tr>
                                <th width="3%">SL</th>
                                <th width="15%">Customer Name</th>
                                <th width="7%">Sales Type</th>
                                <th width="10%">Bill No</th>
                                <th width="13%">Sales Date</th>
                                <th width="15%">Sales By</th>
                                <th width="20%">Product Name</th>
                                <th width="7%" class="text-center">Quantity</th>
                                <th width="15%" class="text-right">Total Sales Amount (Without P.F)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            $total_amount=0;
                            $total_quantity=0;
                            ?>
                            @if($QueryData!=null)
                            @foreach ($QueryData as $index => $item)
                            <?php 
                                $total_amount += $item->total_amount;
                                $total_quantity += $item->product_qtn;

                            ?>
                            @if($index == 0)
                            <tr>
                                <td width="3%"
                                    rowspan="{{$QueryData->where('sales_bill_no',$item->sales_bill_no)->count()}}">
                                    {{++$i }}</td>
                                <td width="15%"
                                    rowspan="{{$QueryData->where('sales_bill_no',$item->sales_bill_no)->count()}}">
                                    {{$item->customer_name_txt}}</td>
                                <td width="7%"
                                    rowspan="{{$QueryData->where('sales_bill_no',$item->sales_bill_no)->count()}}">
                                    {{$item->sales_type_txt}}</td>
                                <td width="10%"
                                    rowspan="{{$QueryData->where('sales_bill_no',$item->sales_bill_no)->count()}}">
                                    {{$item->sales_bill_no}}</td>
                                <td width="13%"
                                    rowspan="{{$QueryData->where('sales_bill_no',$item->sales_bill_no)->count()}}">
                                    {{$item->sales_date}}</td>
                                <td width="15%"
                                    rowspan="{{$QueryData->where('sales_bill_no',$item->sales_bill_no)->count()}}">
                                    {{$item->sales_by}}</td>

                                @endif

                                @if (isset($QueryData[$index-1]->sales_bill_no) && $QueryData[$index]->sales_bill_no !=
                                $QueryData[$index-1]->sales_bill_no)
                            <tr>
                                <td width="3%"
                                    rowspan="{{$QueryData->where('sales_bill_no',$item->sales_bill_no)->count()}}">
                                    {{++$i }}</td>
                                <td width="15%"
                                    rowspan="{{$QueryData->where('sales_bill_no',$item->sales_bill_no)->count()}}">
                                    {{$item->customer_name_txt}}</td>
                                <td width="7%"
                                    rowspan="{{$QueryData->where('sales_bill_no',$item->sales_bill_no)->count()}}">
                                    {{$item->sales_type_txt}}</td>
                                <td width="10%"
                                    rowspan="{{$QueryData->where('sales_bill_no',$item->sales_bill_no)->count()}}">
                                    {{$item->sales_bill_no}}</td>
                                <td width="13%"
                                    rowspan="{{$QueryData->where('sales_bill_no',$item->sales_bill_no)->count()}}">
                                    {{$item->sales_date}}</td>
                                <td width="15%"
                                    rowspan="{{$QueryData->where('sales_bill_no',$item->sales_bill_no)->count()}}">
                                    {{$item->sales_by}}</td>

                                @endif

                                <td width="20%">{{$item->product_name_txt}}</td>
                                <td width="7%" class="text-center">{{$item->product_qtn}}</td>
                                <td width="15%" class="text-right">{{$item->total_amount}}</td>

                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7" style="text-align:right!important;"><b>TOTAL</b></td>
                                <td class="text-center text-dark font-weight-bold" id="total_quantity">
                                    {{$total_quantity}}</td>
                                <td class="text-right text-dark font-weight-bold" id="total_amount">
                                    {{number_format($total_amount, 2)}}</td>
                            </tr>
                        </tfoot>
                    </table>
                    @include('../elements.signature.signatureSet')
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function () {

        var branchId = "{{ isset($branchId) ? $branchId : '' }}";
        if (branchId != '') {
            $('#branch_id').val(branchId).attr("selected", "selected");
        }

        var zoneId = "{{ isset($zoneId) ? $zoneId : '' }}";
        if (zoneId != '') {
            $('#zone_id').val(zoneId).attr("selected", "selected");
        }

        var areaId = "{{ isset($areaId) ? $areaId : '' }}";
        if (areaId != '') {
            $('#area_id').val(areaId).attr("selected", "selected");
        }

        var salesType = "{{ isset($salesType) ? $salesType : '' }}";
        if (salesType != '') {
            $('#sales_type').val(salesType).attr("selected", "selected");
        }

        var customerId = "{{ isset($customerId) ? $customerId : '' }}";
        if (customerId != '') {
            $('#customer_id').val(customerId).attr("selected", "selected");
        }

        var employeeId = "{{ isset($employeeId) ? $employeeId : '' }}";
        if (employeeId != '') {
            $('#employee_id').val(employeeId).attr("selected", "selected");
        }

        var groupId = "{{ isset($groupId) ? $groupId : '' }}";
        if (groupId != '') {
            $('#group_id').val(groupId).attr("selected", "selected");
        }

        var catId = "{{ isset($catId) ? $catId : '' }}";
        if (catId != '') {
            $('#cat_id').val(catId).attr("selected", "selected");
        }

        var subCatId = "{{ isset($subCatId) ? $subCatId : '' }}";
        if (subCatId != '') {
            $('#sub_cat_id').val(subCatId).attr("selected", "selected");
        }

        var brandId = "{{ isset($brandId) ? $brandId : '' }}";
        if (brandId != '') {
            $('#brand_id').val(brandId).attr("selected", "selected");
        }

        $('#reportBranch').html($('#branch_id').find("option:selected").text());
        var startDate = "{{ isset($startDate) ? $startDate : '' }}";
        if (startDate != '') {
            $('#start_date_txt').html(startDate);
            $('#start_date').val(startDate);

            setTimeout(function () {
                $(".wb-minus").trigger('click');
            }, 10);
        }

        var endDate = "{{ isset($endDate) ? $endDate : '' }}";
        if (endDate != '') {
            $('#end_date_txt').html(endDate);
            $('#end_date').val(endDate);
        }

        // // // Loader In-Active
        fnLoading(false);
    });

</script>
@endsection
