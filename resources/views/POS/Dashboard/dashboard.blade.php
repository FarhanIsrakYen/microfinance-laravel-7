@extends('Layouts.erp_master')
@section('title', 'Point of Sales')
@section('content')

<?php
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;

$sign = '+';
$incSalesToday = 0;
if ($dataSet['sales_pre_day'] != 0) {
    $incSalesToday = ($dataSet['sales_today'] - $dataSet['sales_pre_day']) / $dataSet['sales_pre_day'] * 100;
    if ($incSalesToday >= 0) {
        $sign = '+';
    } else {
        $sign = '-';
    }
}

$incSalesMonth = 0;
if ($dataSet['sales_pre_month'] != 0) {
    $incSalesMonth = ($dataSet['sales_this_month'] - $dataSet['sales_pre_month']) / $dataSet['sales_pre_month'] * 100;
    if ($incSalesMonth >= 0) {
        $sign = '+';
    } else {
        $sign = '-';
    }

}
$incSalesYear = 0;
if ($dataSet['sales_pre_year'] != 0) {
    $incSalesYear = ($dataSet['sales_this_year'] - $dataSet['sales_pre_year']) / $dataSet['sales_pre_year'] * 100;
    if ($incSalesYear >= 0) {
        $sign = '+';
    } else {
        $sign = '-';
    }

}

$incCollectionToday = 0;
if ($dataSet['collection_pre_day'] != 0) {
    $incCollectionToday = ($dataSet['collection_today'] - $dataSet['collection_pre_day']) / $dataSet['collection_pre_day'] * 100;
    if ($incCollectionToday >= 0) {
        $sign = '+';
    } else {
        $sign = '-';
    }

}

$incCollectionMonth = 0;
if ($dataSet['collection_pre_month'] != 0) {
    $incCollectionMonth = ($dataSet['collection_this_month'] - $dataSet['collection_pre_month']) / $dataSet['collection_pre_month'] * 100;
    if ($incCollectionMonth >= 0) {
        $sign = '+';
    } else {
        $sign = '-';
    }

}
$incCollectionYear = 0;
if ($dataSet['collection_pre_year'] != 0) {
    $incCollectionYear = ($dataSet['collection_this_year'] - $dataSet['collection_pre_year']) / $dataSet['collection_pre_year'] * 100;
    if ($incCollectionYear >= 0) {
        $sign = '+';
    } else {
        $sign = '-';
    }

}

?>


<div class="w-full p-row">
    <h3 class="text-center pb-25">MIS DASHBOARD</h3>

    <ul class="nav nav-tabs" id="TabID">
        <li class="nav-item">
            <a href="#OrgstatusID" class="nav-link" data-toggle="tab">Organization Status</a>
        </li>
        <li class="nav-item">
            <a href="#branchID" id="branch_tab" class="nav-link" data-toggle="tab">Branch Status</a>
        </li>

    </ul>
    <div class="tab-content" style="background:none;">

        <div class="tab-pane fade" id="OrgstatusID">

            <div class="row">

                <div class="col-xl-3 col-md-3 info-panel">
                    <div class="card card-shadow def_border">
                        <div class="iconL">
                            <i class="fa fa-university" aria-hidden="true" style="font-size: 80px;"></i>
                        </div>
                        <div class="card-block p-20">
                            <button type="button" class="btn btn-floating btn-sm def_btn">
                                <i class="icon fa-university text-white"></i>
                            </button>
                            <span class="ml-15 font-weight-500 font-size-18">Branchs</span>
                            <div class="content-text text-center mb-0">
                                <span class="font-size-40 font-weight-100 def_text">
                                    {{ $dataSet['branchCount'] }}</span>
                                <p class="font-weight-100 m-0 def_text">Total Branches</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-3 info-panel">
                    <div class="card card-shadow def_border">
                        <div class="iconL">
                            <i class="fa fa-group" aria-hidden="true" style="font-size: 80px;"></i>
                        </div>
                        <div class="card-block bg-white p-20">
                            <button type="button" class="btn btn-floating btn-sm def_btn">
                                <i class="icon fa-group text-white"></i>
                            </button>
                            <span class="ml-15 font-weight-500 font-size-18">Customers</span>
                            <div class="content-text text-center mb-0">
                                <span
                                    class="font-size-40 font-weight-100 def_text">{{ $dataSet['customerCount'] }}</span>
                                <p class="font-weight-100 m-0 def_text">Total Customers</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-3 info-panel">
                    <div class="card card-shadow def_border">
                        <div class="iconL">
                            <i class="fa fa-cube" aria-hidden="true" style="font-size: 80px;"></i>
                        </div>
                        <div class="card-block bg-white p-20">
                            <button type="button" class="btn btn-floating btn-sm def_btn">
                                <i class="icon fa-cube text-white"></i>
                            </button>
                            <span class="ml-15 font-weight-500 font-size-18">Products</span>
                            <div class="content-text text-center mb-0">
                                <span
                                    class="font-size-40 font-weight-100 def_text">{{ $dataSet['productCount'] }}</span>
                                <p class="font-weight-100 m-0 def_text">Total Products</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-3 info-panel">
                    <div class="card card-shadow def_border">
                        <div class="iconL">
                            <i class="fa fa-shield" aria-hidden="true" style="font-size: 80px;"></i>
                        </div>
                        <div class="card-block bg-white p-20">
                            <button type="button" class="btn btn-floating btn-sm def_btn">
                                <i class="icon fa-shield text-white"></i>
                            </button>
                            <span class="ml-15 font-weight-500 font-size-18">Product Brands</span>
                            <div class="content-text text-center mb-0">
                                <span class="font-size-40 font-weight-100 def_text">{{ $dataSet['brandCount'] }}</span>
                                <p class="font-weight-100 m-0 def_text">Total Product Brands</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">

                <div class="col-xl-3 col-md-3 info-panel">
                    <div class="card card-shadow {{($incSalesToday >= 0) ? 'pos_border' : 'neg_border'}}">
                        <div class="iconL">
                            <i class="fa fa-calendar-check-o" aria-hidden="true" style="font-size: 80px;"></i>
                        </div>
                        <div class="card-block bg-white p-20">
                            <button type="button"
                                class="btn btn-floating btn-sm {{($incSalesToday >= 0) ? 'pos_btn' : 'btn-danger'}}">
                                <i class="icon fa-calendar-check-o text-white"></i>
                            </button>
                            <span class="ml-15 font-weight-500 font-size-18 ">Sales - Today</span>
                            <div class="content-text text-center mb-0">

                                <i
                                    class="icon font-size-20 {{($incSalesToday >= 0) ? 'text-success wb-triangle-up' : 'text-danger wb-triangle-down'}}"></i>

                                <span
                                    class="font-size-40 font-weight-100 {{($incSalesToday >= 0) ? 'pos_text' : 'text-danger'}} ">
                                    {{ number_format($dataSet['sales_today'], 2) }}
                                </span>
                                <p class="font-weight-100 m-0 {{($incSalesToday >= 0) ? 'pos_text' : 'text-danger'}}">
                                    {{($incSalesToday >= 0) ? '+' : '-'}}
                                    {{ round($incSalesToday) }} % From previous Day
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-3 info-panel">
                    <div class="card card-shadow {{($incSalesMonth >= 0) ? 'pos_border' : 'neg_border'}}">
                        <div class="iconL">
                            <i class="fa fa-calendar-plus-o" aria-hidden="true" style="font-size: 80px;"></i>
                        </div>
                        <div class="card-block bg-white p-20">
                            <button type="button"
                                class="btn btn-floating btn-sm {{($incSalesMonth >= 0) ? 'pos_btn' : 'btn-danger'}}">
                                <i class="icon fa-calendar-plus-o text-white"></i>
                            </button>
                            <span class="ml-15 font-weight-500 font-size-18">Sales - This Month</span>

                            <div class="content-text text-center mb-0">

                                <i
                                    class="icon font-size-20 {{($incSalesMonth >= 0) ? 'text-success wb-triangle-up' : 'text-danger wb-triangle-down'}}"></i>

                                <span
                                    class="font-size-40 font-weight-100 {{($incSalesMonth >= 0) ? 'pos_text' : 'text-danger'}}">
                                    {{ number_format($dataSet['sales_this_month'], 2) }}
                                </span>

                                <p class="font-weight-100 m-0 {{($incSalesMonth >= 0) ? 'pos_text' : 'text-danger'}}">
                                    {{($incSalesMonth >= 0) ? '+' : '-'}}
                                    {{ round($incSalesMonth) }} % From previous Month
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-3 info-panel">
                    <div class="card card-shadow {{($incSalesYear >= 0) ? 'pos_border' : 'neg_border'}}">
                        <div class="iconL">
                            <i class="fa fa-calendar" aria-hidden="true" style="font-size: 80px;"></i>
                        </div>
                        <div class="card-block bg-white p-20">
                            <button type="button"
                                class="btn btn-floating btn-sm {{($incSalesYear >= 0) ? 'pos_btn' : 'btn-danger'}}">
                                <i class="icon fa-calendar text-white"></i>
                            </button>
                            <span class="ml-15 font-weight-500 font-size-18">Sales - Year
                                <small style="font-size:8px;">
                                    ({{ $dataSet['cur_yr_start']->format('M,Y') }} -
                                    {{ $dataSet['cur_yr_end']->format('M,Y') }})
                                </small>
                            </span>

                            <div class="content-text text-center mb-0">
                                <i
                                    class="icon font-size-20 {{($incSalesYear >= 0) ? 'text-success wb-triangle-up' : 'text-danger wb-triangle-down'}}"></i>

                                <span
                                    class="font-size-40 font-weight-100 {{($incSalesYear >= 0) ? 'pos_text' : 'text-danger'}}">
                                    {{ number_format($dataSet['sales_this_year'], 2) }}
                                </span>
                                <p class="font-weight-100 m-0 {{($incSalesYear >= 0) ? 'pos_text' : 'text-danger'}}">
                                    {{($incSalesYear >= 0) ? '+' : '-'}}
                                    {{ round($incSalesYear) }} % From previous Year
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-3 info-panel">
                    <div class="card card-shadow def_border">
                        <div class="iconL">
                            <i class="fa fa-calendar-o" aria-hidden="true" style="font-size: 80px;"></i>
                        </div>
                        <div class="card-block bg-white p-20">
                            <button type="button" class="btn btn-floating btn-sm def_btn">
                                <i class="icon fa-calendar-o text-white"></i>
                            </button>
                            <span class="ml-15 font-weight-500 font-size-18">Sales - All</span>
                            <div class="content-text text-center mb-0">
                                <span
                                    class="font-size-40 font-weight-100 def_text">{{ number_format($dataSet['sales_all'], 2) }}</span>
                                <p class="font-weight-100 m-0 def_text">
                                    Total Sales
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="row mt-4">

                <div class="col-xl-3 col-md-3 info-panel">
                    <div class="card card-shadow {{($incCollectionToday >= 0) ? 'pos_border' : 'neg_border'}}">
                        <div class="iconL">
                            <i class="fa fa-gg" aria-hidden="true" style="font-size: 80px;"></i>
                        </div>
                        <div class="card-block bg-white p-20">
                            <button type="button"
                                class="btn btn-floating btn-sm {{($incCollectionToday >= 0) ? 'pos_btn' : 'btn-danger'}}">
                                <i class="icon fa-gg text-white"></i>
                            </button>
                            <span class="ml-15 font-weight-500 font-size-18">Collection - Today</span>

                            <div class="content-text text-center mb-0">

                                <i
                                    class="icon font-size-20 {{($incCollectionToday >= 0) ? 'text-success wb-triangle-up' : 'text-danger wb-triangle-down'}}"></i>

                                <span
                                    class="font-size-40 font-weight-100 {{($incCollectionToday >= 0) ? 'pos_text' : 'text-danger'}}">
                                    {{ number_format($dataSet['collection_today'], 2) }}
                                </span>
                                <p
                                    class="font-weight-100 m-0 {{($incCollectionToday >= 0) ? 'pos_text' : 'text-danger'}}">
                                    {{($incCollectionToday >= 0) ? '+' : '-'}}
                                    {{ round($incCollectionToday) }} % From previous Day
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-3 info-panel">
                    <div class="card card-shadow {{($incCollectionMonth >= 0) ? 'pos_border' : 'neg_border'}}">
                        <div class="iconL">
                            <i class="fa fa-gg-circle" aria-hidden="true" style="font-size: 80px;"></i>
                        </div>
                        <div class="card-block bg-white p-20">
                            <button type="button"
                                class="btn btn-floating btn-sm {{($incCollectionMonth >= 0) ? 'pos_btn' : 'btn-danger'}}">
                                <i class="icon fa-gg-circle text-white"></i>
                            </button>
                            <span class="ml-15 font-weight-500 font-size-18">Collection - This Month</span>
                            <div class="content-text text-center mb-0">

                                <i
                                    class="icon font-size-20 {{($incCollectionMonth >= 0) ? 'text-success wb-triangle-up' : 'text-danger wb-triangle-down'}}"></i>

                                <span
                                    class="font-size-40 font-weight-100 {{($incCollectionMonth >= 0) ? 'pos_text' : 'text-danger'}}">
                                    {{ number_format($dataSet['collection_this_month'], 2) }}
                                </span>
                                <p
                                    class="font-weight-100 m-0 {{($incCollectionMonth >= 0) ? 'pos_text' : 'text-danger'}}">
                                    {{($incCollectionMonth >= 0) ? '+' : '-'}}
                                    {{ round($incCollectionMonth) }} % From previous Month
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-3 info-panel">
                    <div class="card card-shadow {{($incCollectionYear >= 0) ? 'pos_border' : 'neg_border'}}">
                        <div class="iconL">
                            <i class="fa fa-credit-card" aria-hidden="true" style="font-size: 80px;"></i>
                        </div>
                        <div class="card-block bg-white p-20">
                            <button type="button"
                                class="btn btn-floating btn-sm {{($incCollectionYear >= 0) ? 'pos_btn' : 'btn-danger'}}">
                                <i class="icon fa-credit-card text-white"></i>
                            </button>
                            <span class="ml-15 font-weight-500 font-size-17">Collection-Year
                                <small style="font-size:8px;">
                                    ({{ $dataSet['cur_yr_start']->format('M,Y') }} -
                                    {{ $dataSet['cur_yr_end']->format('M,Y') }})
                                </small>
                            </span>
                            <div class="content-text text-center mb-0">

                                <i
                                    class="icon font-size-20 {{($incCollectionYear >= 0) ? 'text-success wb-triangle-up' : 'text-danger wb-triangle-down'}}"></i>

                                <span
                                    class="font-size-40 font-weight-100 {{($incCollectionYear >= 0) ? 'pos_text' : 'text-danger'}}">
                                    {{ number_format($dataSet['collection_this_year'], 2) }}
                                </span>
                                <p
                                    class="font-weight-100 m-0 {{($incCollectionYear >= 0) ? 'pos_text' : 'text-danger'}}">
                                    {{($incCollectionYear >= 0) ? '+' : '-'}}
                                    {{ round($incCollectionYear) }} % From previous Year
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-3 info-panel">
                    <div class="card card-shadow def_border">
                        <div class="iconL">
                            <i class="fa fa-money" aria-hidden="true" style="font-size: 80px;"></i>
                        </div>
                        <div class="card-block bg-white p-20">
                            <button type="button" class="btn btn-floating btn-sm def_btn">
                                <i class="icon fa-money text-white"></i>
                            </button>
                            <span class="ml-15 font-weight-500 font-size-18">Collection - All</span>
                            <div class="content-text text-center mb-0">
                                <span
                                    class="font-size-40 font-weight-100 def_text">{{ number_format($dataSet['collection_all'], 2) }}</span>
                                <p class="font-weight-100 m-0 def_text">
                                    Total Collection
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-xl-3 col-md-3 info-panel">
                    <div class="card card-shadow"
                        style="overflow: hidden;border: #8f5a3f solid 1px; border-bottom: #8f5a3f solid 4px;">
                        <div class="iconL">
                            <i class="fa fa-hourglass-1" aria-hidden="true" style="font-size: 80px;"></i>
                        </div>
                        <div class="card-block bg-white p-20">
                            <button type="button" class="btn btn-floating btn-sm" style="background: #8f5a3f">
                                <i class="icon fa-hourglass-1 text-white"></i>
                            </button>
                            <span class="ml-15 font-weight-500 font-size-18">Total Due</span>
                            <div class="content-text text-center mb-0">
                                <i class="text-danger icon wb-triangle-up font-size-20"></i>
                                <span class="font-size-40 font-weight-100" style="color: #8f5a3f">0</span>
                                <p class="font-weight-100 m-0" style="color: #8f5a3f">Total Due</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-3 info-panel">
                    <div class="card card-shadow"
                        style="overflow: hidden;border: #8f5a3f solid 1px; border-bottom: #8f5a3f solid 4px;">
                        <div class="iconL">
                            <i class="fa fa-clock-o" aria-hidden="true" style="font-size: 80px;"></i>
                        </div>
                        <div class="card-block bg-white p-20">
                            <button type="button" class="btn btn-floating btn-sm" style="background: #8f5a3f">
                                <i class="icon fa-clock-o text-white"></i>
                            </button>
                            <span class="ml-15 font-weight-500 font-size-18">Over Due</span>
                            <div class="content-text text-center mb-0">
                                <i class="text-danger icon wb-triangle-up font-size-20"></i>
                                <span class="font-size-40 font-weight-100" style="color: #8f5a3f">0</span>
                                <p class="font-weight-100 m-0" style="color: #8f5a3f">Over Due</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-3 info-panel">
                    <div class="card card-shadow"
                        style="overflow: hidden;border: #8f5a3f solid 1px; border-bottom: #8f5a3f solid 4px;">
                        <div class="iconL">
                            <i class="fa fa-hourglass-2" aria-hidden="true" style="font-size: 80px;"></i>
                        </div>
                        <div class="card-block bg-white p-20">
                            <button type="button" class="btn btn-floating btn-sm" style="background: #8f5a3f">
                                <i class="icon fa-hourglass-2 text-white"></i>
                            </button>
                            <span class="ml-15 font-weight-500 font-size-18">Cumulative Due</span>
                            <div class="content-text text-center mb-0">
                                <i class="icon wb-triangle-up font-size-20"></i>
                                <span class="font-size-40 font-weight-100" style="color: #8f5a3f">0</span>
                                <p class="font-weight-100 m-0" style="color: #8f5a3f">Cumulative Due </p>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="col-xl-3 col-md-3 info-panel">
                    <div class="card card-shadow"
                        style="overflow: hidden;border: #8f5a3f solid 1px; border-bottom: #8f5a3f solid 4px;">
                        <div class="iconL">
                            <i class="fa fa-balance-scale" aria-hidden="true" style="font-size: 80px;"></i>
                        </div>
                        <div class="card-block bg-white p-20">
                            <button type="button" class="btn btn-floating btn-sm" style="background: #8f5a3f">
                                <i class="icon fa-balance-scale text-white"></i>
                            </button>
                            <span class="ml-15 font-weight-500 font-size-18">Balance</span>
                            <div class="content-text text-center mb-0">
                                <i class="icon wb-triangle-up font-size-20"></i>
                                <span class="font-size-40 font-weight-100" style="color: #8f5a3f">0</span>
                                <p class="font-weight-100 m-0" style="color: #8f5a3f">Total Balance</p>

                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>


        <div class="tab-pane fade" id="branchID">

            <div class="row" id="branchID">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead>
                            <tr>
                                <th width="3%">SL</th>
                                <th>Branch Name</th>
                                <th>Branch Opening Date</th>
                                <th>Software Start Date</th>
                                <th>Branch Date</th>
                                <th>Total Customer</th>
                                <th>Last Month Due</th>
                                <th>Total Due</th>
                                <th>Total Balance</th>
                                <th width="5%">LAG</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" style="text-align: right!important;"><b>Total</b></td>
                                <td style="text-align:center;" id="nCustomer"><b>0.00</b></td>
                                <td style="text-align:center;" id="tlmdue"><b>0.00</b></td>
                                <td style="text-align:center;" id="ttldue"><b>0.00</b></td>
                                <td style="text-align:center;" id="ttlbalance"><b>0.00</b></td>
                                <td style="text-align:center;"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>


<script>
$(document).ready(function() {

    $('#branch_tab').click(function() {
        $('.clsDataTable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            // lengthMenu: [[10, 20, 30, 50, -1], [10, 20, 30, 50, "All"]],
            paging: false,
            ordering: false,
            info: false,
            searching: false,
            ajax: {
                "url": "{{route('POSBranchStatus')}}",
                "dataType": "json",
                "type": "post",
                "data": {
                    _token: "{{csrf_token()}}",
                }
            },
            columns: [{
                    data: 'sl',
                    className: 'text-center'
                },
                {
                    data: 'branch_name'
                },
                {
                    data: 'branch_opening_date',
                    className: 'text-center'
                },
                {
                    data: 'soft_start_date',
                    className: 'text-center'
                },
                {
                    data: 'branch_date',
                    className: 'text-center'
                },
                {
                    data: 'total_customer',
                    className: 'text-center'
                },
                {
                    data: 'last_m_due',
                    className: 'text-right'
                },
                {
                    data: 'total_due',
                    className: 'text-right'
                },
                {
                    data: 'total_balance',
                    className: 'text-right'
                },
                {
                    data: 'LAG',
                    orderable: false,
                    className: 'text-center'
                },
            ],
            drawCallback: function(oResult) {

                if (oResult.json) {
                    $('#nCustomer').html(oResult.json.tcustomer);
                    $('#ttlbalance').html(oResult.json.total_balance);
                }
            },
        });
    });

    $("#TabID li:eq(0) a").tab('show');

});
</script>

<style type="text/css">
.def_border {
    border: #27b6af solid 1px;
    border-bottom: #27b6af solid 4px;
}

.pos_border {
    border: #354f55 solid 1px;
    border-bottom: #354f55 solid 4px;
    overflow: hidden;
}

.neg_border {
    border: #F32013 solid 1px;
    border-bottom: #F32013 solid 4px;
    overflow: hidden;
}

.pos_btn {
    background: #354f55;
}

.pos_text {
    color: #354f55;
}

.def_btn {
    background: #27b6af;
}

.def_text {
    color: #27b6af;
}

.iconL {
    color: rgba(0, 0, 0, 0.1);
    position: absolute;
    right: 5px;
    bottom: 15px;
    z-index: 1;
}

.box-widget {
    background: #fff;
    border: 1px solid #e4e5e7;
    margin-bottom: 30px;
}

.nmbr-statistic-block {
    padding: 30px 30px 30px 30px;
    min-height: 170px;
    position: relative;
}

@media screen and (max-width: 1024px) and (min-width: 786px) .nmbr-statistic-block .nmbr-statistic-info {
    left: 15%;
    top: 90px;
}

.card-btm-border {
    border-bottom: transparent solid 4px;
}

.card-shadow-success {
    box-shadow: 0 0.46875rem 2.1875rem rgba(58, 196, 125, .03), 0 0.9375rem 1.40625rem rgba(58, 196, 125, .03), 0 0.25rem 0.53125rem rgba(58, 196, 125, .05), 0 0.125rem 0.1875rem rgba(58, 196, 125, .03);
}

.p-row {
    padding: 0px 15px 0px 15px;
}
</style>

@endsection
