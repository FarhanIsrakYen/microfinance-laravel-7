@extends('Layouts.erp_master')
@section('title', 'Accounting')
@section('content')

<!-- <link rel="stylesheet" href="{{asset('../resources/views/ACC/Dashboard/dashboard.css')}}"> -->

<style>
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

.card-btm-border {
    border-bottom: transparent solid 4px;
}

.card-shadow-success {
    box-shadow: 0 0.46875rem 2.1875rem rgba(58, 196, 125, .03), 0 0.9375rem 1.40625rem rgba(58, 196, 125, .03), 0 0.25rem 0.53125rem rgba(58, 196, 125, .05), 0 0.125rem 0.1875rem rgba(58, 196, 125, .03);
}

.p-row {
    padding: 0px 15px 0px 15px;

}

#OrgstatusID ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

#OrgstatusID li {
    padding-left: 1em;
    text-indent: -.7em;
}

#OrgstatusID li::before {
    content: "4";
    font-family: "Webdings";
    color: red;
}

.order-card {
    height: 100px
}

.dashCard {
    position: absolute;
    right: -10px;
    top: 50%;
    transform: translateY(-50%);
    width: 67px;
    height: 67px;
    border-radius: 2px;
    display: -webkit-flex;
    display: flex;
    -webkit-align-items: center;
    align-items: center;
    -webkit-justify-content: center;
    justify-content: center;
}

.dashCard2 {
    position: absolute;
    top: -2px;
    left: 40%;
    transform: translateY(-50%);
    width: 55px;
    height: 55px;
    border-radius: 50%;
    display: -webkit-flex;
    display: flex;
    -webkit-align-items: center;
    align-items: center;
    -webkit-justify-content: center;
    justify-content: center;
}

.text-insta {
    color: #026466;
}

#cur_month_surplus,
#cur_year_surplus,
#last_month_surplus,
#cum_surplus,
#cur_cash_amount,
#cur_bank_amount,
#ttl_balance {
    color: #17b3a3;
}


@media screen and (max-width: 1024px) and (min-width: 786px) {
    .nmbr-statistic-block .nmbr-statistic-info {
        left: 15%;
        top: 90px;
    }
}

/* .tab-content { */
/* border-top: 5px solid #7dbb9e; */
/* border-bottom: 0px solid #7dbb9e !important;
        } */
</style>

<?php 
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;

$salesAll = Common::ViewTableOrderIn('pos_sales_m', [['is_delete', 0], ['is_active', 1]],
                        ['branch_id', HRS::getUserAccesableBranchIds()],
                        ['id','total_amount'], ['total_amount', 'ASC']);
$today     = (new DateTime())->format('d M, Y');
?>


<div class="w-full p-row minHeight">
    <h3 class="text-center pb-25">ACCOUNTING DASHBOARD</h3>

    <ul class="nav nav-tabs" id="TabID">
        <li class="nav-item">
            <a href="#AccConfigID" class="nav-link" data-toggle="tab">Accounting Configuration</a>
        </li>

        <li class="nav-item">
            <a href="#VouchersID" class="nav-link" data-toggle="tab">Vouchers</a>
        </li>

        <li class="nav-item">
            <a href="#OrgstatusID" id="org_tab" class="nav-link" data-toggle="tab">Organization Status</a>
        </li>

        <li class="nav-item">
            <a href="#BranchStatusID" id="branch_tab" class="nav-link" data-toggle="tab">Branch Status</a>
        </li>
    </ul>

    <div class="tab-content" style="background:none;">
        <!-- Accounting Configuration  -->
        <div class="tab-pane fade" id="AccConfigID">
            <div class="row">
                <div class="col-md-4 col-xl-3">
                    <a href="{{url('acc/ledger') }}" target="_blank">
                        <div class="card order-card shadow">
                            <div class="card-block" style="border-left: 4px solid #00bbdd">
                                <h4 class="m-b-20 text-info">Ledger Account</h4>
                                <div class="dashCard shadow-lg" style="background: #00bbdd;">
                                    <i class="fa fa-book f-left text-white" style="font-size: 60px"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-4 col-xl-3">
                    <a href="{{url('acc/acc_ob') }}" target="_blank">
                        <div class="card order-card shadow">
                            <div class="card-block" style="border-left: 4px solid #00bbdd">
                                <h4 class="m-b-20 text-info">Opening Balance</h4>
                                <div class="dashCard shadow-lg" style="background: #00bbdd;">
                                    <i class="fa fa-dollar f-left text-white" style="font-size: 60px"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <!-- End Accounting Configuration  -->

        <!-- Voucher Button  -->
        <div class="tab-pane fade" id="VouchersID">
            <div class="row mt-4">
                <div class="col-lg-3">
                    <a href="{{url('acc/vouchers') }}" target="_blank">
                        <div class="card voucher-card shadow text-center">
                            <div class="card-block" style="border-bottom: 4px solid #026466">
                                <h4 class="m-b-20 text-insta pt-4">Voucher</h4>
                                <span class="dashCard2 shadow-lg" style="background: #026466;">
                                    <i class="fa fa-file-text f-left text-white" style="font-size: 30px"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-lg-3">
                    <a href="{{url('acc/auth_vouchers') }}" target="_blank">
                        <div class="card voucher-card shadow text-center">
                            <div class="card-block" style="border-bottom: 4px solid #026466">
                                <h4 class="m-b-20 text-insta pt-4">Authorized Voucher</h4>
                                <span class="dashCard2 shadow-lg" style="background: #026466;">
                                    <i class="fa fa-check-square-o f-left text-white" style="font-size: 30px"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-lg-3">
                    <a href="{{url('acc/unauth_vouchers') }}" target="_blank">
                        <div class="card voucher-card shadow text-center">
                            <div class="card-block" style="border-bottom: 4px solid #026466">
                                <h4 class="m-b-20 text-insta pt-4">Unauthorized Voucher</h4>
                                <span class="dashCard2 shadow-lg" style="background: #026466;">
                                    <i class="fa fa-building f-left text-white" style="font-size: 30px"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <!-- End Voucher Button  -->

        <!-- Organization Status  -->
        <div class="tab-pane fade" id="OrgstatusID">
            <div style="border: 1px solid #ccc">
                <div class="card reverse m-4">
                    <div class="row">
                        <div class="col-lg-1 text-center" style="background: #7caa9d; font-size: 30px;">
                            <i class="fas fa-bar-chart fa-7x text-white" style="padding-top: 30px;">

                            </i>
                        </div>
                        <div class="col-lg-4 pt-4 pb-4">
                            <ul>
                                <li><span id="cur_month_surplus">Current month profit: 0</span></li>
                                <li><span id="cur_year_surplus">Current year profit: 0</span></li>
                            </ul>
                        </div>
                        <div class="col-lg-4 pt-4 pb-4">
                            <ul>
                                <li><span id="last_month_surplus">Last month profit: 0</span></li>
                                <li><span id="cum_surplus">Cumulative profit amount: 0</span></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="card reverse m-4">
                    <div class="row">
                        <div class="col-lg-1 text-center" style="background: #7caa9d; font-size: 30px;">
                            <i class="fa fa-money fa-7x text-white" style="padding-top: 30px;">

                            </i>
                        </div>
                        <div class="col-lg-4 pt-4 pb-4">
                            <ul>
                                <li><span>Current cash amount : </span><span id="cur_cash_amount">0</span></li>
                                <li><span>Total balance : </span><span id="ttl_balance">0</span></li>
                            </ul>
                        </div>
                        <div class="col-lg-4 pt-4 pb-4">
                            <ul>
                                <li><span>Current bank amount : </span><span id="cur_bank_amount">0</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div style="border: 1px solid #ccc" class="mt-20">
                <div class="row p-4">
                    <div class="col-lg-2">
                        <table class="table table-bordered table-striped bg-white">
                            <thead>
                                <tr class="bg-white">
                                    <th>
                                        <h4>Graphical Report</h4>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="bg-teal-600">
                                        <a href="javascript:void(0);" onclick="brSurplus(this)">
                                            <h5>Branch Wise Profits</h5>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <a href="javascript:void(0);" onclick="brCashBank(this)">
                                            <h5>Branch Wise Cash And Bank Balance</h5>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <a href="javascript:void(0);" onclick="brIncomeExpense(this)">
                                            <h5>Branch Wise Income And Expense</h5>
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="col-lg-10 bg-white text-center">
                        <br>
                        <span id="chartTitle" class="blue-grey-800 p-4 font-weight-500 font-size-18">Branch Wise
                            Profits</span><br>
                        <span id="chartSubTitle">As On Current Year Upto : {{ $today }}</span><br>

                        <div class="row justify-content-center" id="showChart"></div>
                        <div id="showChart_link" class="float-right"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Organization Status  -->



        <div class="tab-pane fade" id="BranchStatusID">
            <div class="card reverse row">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead>
                            <tr>
                                <th width="3%">SL</th>
                                <th>Branch Name</th>
                                <th>Soft Opening Date</th>
                                <th>Branch Date</th>
                                <th>Prev. Month Cash</th>
                                <th>Prev. Month Bank</th>
                                <th>Current Month Cash</th>
                                <th>Current Month Bank</th>
                                <th>Total </th>
                                <th>Progress </th>
                                <th width="5%">LAG</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-align: right!important;"><b>Total</b></td>
                                <td style="text-align:right;" id="ttl_pre_cash"><b>0.00</b></td>
                                <td style="text-align:right;" id="ttl_pre_bank"><b>0.00</b></td>
                                <td style="text-align:right;" id="ttl_cur_cash"><b>0.00</b></td>
                                <td style="text-align:right;" id="ttl_cur_bank"><b>0.00</b></td>
                                <td style="text-align:right;" id="ttl_balance"><b>0.00</b></td>
                                <td style="text-align:center;"></td>
                                <td style="text-align:center;"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <!-- </div> -->
            </div>
        </div>

    </div>
</div>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
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
                "url": "{{route('BranchStatus')}}",
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
                    data: 'soft_start_date'
                },
                {
                    data: 'branch_date'
                },
                {
                    data: 'pre_month_cash',
                    className: 'text-right'
                },
                {
                    data: 'pre_month_bank',
                    className: 'text-right'
                },
                {
                    data: 'cur_month_cash',
                    className: 'text-right'
                },
                {
                    data: 'cur_month_bank',
                    className: 'text-right'
                },
                {
                    data: 'total',
                    className: 'text-right'
                },
                {
                    data: 'progress',
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
                    $('#ttl_pre_cash').html(oResult.json.ttl_pre_cash);
                    $('#ttl_pre_bank').html(oResult.json.ttl_pre_bank);
                    $('#ttl_cur_cash').html(oResult.json.ttl_cur_cash);
                    $('#ttl_cur_bank').html(oResult.json.ttl_cur_bank);
                    $('#ttl_balance').html(oResult.json.ttl_balance);
                }
            },
        });
    });

    $('#org_tab').click(function() {

        $.ajax({
            method: "GET",
            url: "{{route('orgStatus')}}",
            // dataType: "text",
            // // data: {
            // //     BranchId: BranchId
            // // },
            success: function(data) {
                if (data) {
                    if (data.current_month_surplus < 0) {
                        $('#cur_month_surplus').html("Current month loss: " + data
                            .current_month_surplus);
                    } else {
                        $('#cur_month_surplus').html("Current month profit: " + data
                            .current_month_surplus);
                    }

                    if (data.current_year_surplus < 0) {
                        $('#cur_year_surplus').html("Current year loss: " + data
                            .current_year_surplus);
                    } else {
                        $('#cur_year_surplus').html("Current year profit: " + data
                            .current_year_surplus);
                    }

                    if (data.last_month_surplus < 0) {
                        $('#last_month_surplus').html("Last month loss: " + data
                            .last_month_surplus);
                    } else {
                        $('#last_month_surplus').html("Last month profit: " + data
                            .last_month_surplus);
                    }

                    if (data.cumulative_surplus < 0) {
                        $('#cum_surplus').html("Cumulative loss amount: " + data
                            .cumulative_surplus);
                    } else {
                        $('#cum_surplus').html("Cumulative profit amount: " + data
                            .cumulative_surplus);
                    }

                    $('#cur_cash_amount').html(data.current_cash_amount);
                    $('#cur_bank_amount').html(data.current_bank_amount);
                    $('#ttl_balance').html(data.total_balance);
                }
            }
        });
    });

    $("#TabID li:eq(0) a").tab('show');

    brSurplus();
});
</script>


<script>
function brSurplus(elm) {
    $('#chartTitle').html('Branch Wise Profits');
    $('#chartSubTitle').html('As On Current Year Upto: ' + new Date());
    $('#showChart_link').html(
        '<a href="javascript:void(0);" onclick="brSurplus_all();" class="float-right red-800">See All Branches</a>');

    ////////////////
    if (elm !== undefined) {
        $('.bg-teal-600').removeClass('bg-teal-600');
    }
    $(elm).parent().addClass("bg-teal-600");

    google.load("visualization", "1", {
        packages: ["corechart"]
    });
    google.setOnLoadCallback(createPIE);

    function createPIE() {
        // SET CHART OPTIONS.
        var options = {
            'height': 500,
            is3D: true,
            colors: ['#99b898', '#fecea8', '#ff847c', '#e84a5f', '#474747'],
            legend: {
                alignment: 'center',
                position: 'top'
            },
        };

        $.ajax({
            method: "GET",
            url: "{{route('org_graph_surplus')}}",
            success: function(data) {
                var arrValues = [
                    ['Branch', 'Profits']
                ]; // Define an array

                $.each(data.surplusPercent, function(key, value) {
                    arrValues.push([key, parseFloat(value)]);
                });

                // // Create a datatable and add the array (with data) in it
                var figures = google.visualization.arrayToDataTable(arrValues)

                // The type of chart (piechart).
                var chart = new google.visualization.PieChart(document.getElementById('showChart'));

                chart.draw(figures, options); // Draw graph with the data and options.
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert('Got an Error');
            }
        });
    }
}

function brSurplus_all() {
    $('#chartTitle').html('Branch Wise Profits');
    $('#chartSubTitle').html('As On Current Year Upto: ' + new Date());

    $('#showChart_link').html('');

    google.charts.load('current', {
        packages: ['corechart', 'bar']
    });
    google.charts.setOnLoadCallback(drawColColors);

    function drawColColors() {
        var dataTable = new google.visualization.DataTable();
        dataTable.addColumn('string', 'Branch');
        dataTable.addColumn('number', 'Profits');

        var options = {
            bar: {
                groupWidth: "50%"
            },
            colors: ['#9575cd'],
            height: 1500,
            hAxis: {
                title: 'Amount (BDT)',
                titleTextStyle: {
                    bold: true,
                },
                format: 'short',
                textStyle: {
                    italic: true
                },
                textPosition: 'out',
                slantedText: true,
            },
            vAxis: {
                title: 'All Branchs',
                titleTextStyle: {
                    bold: true,
                },
                format: 'none'
            },
            legend: {
                position: 'top',
                alignment: 'center',
            },
            chartArea: {
                top: 70,
                bottom: 100,
            },
        };

        $.ajax({
            method: "GET",
            url: "{{route('org_graph_surplus_all')}}",
            success: function(data) {

                $.each(data.surplus, function(key, value) {
                    dataTable.addRow([key, parseFloat(value)]);
                });

                // The type of chart (piechart).
                var chart = new google.visualization.BarChart(document.getElementById('showChart'));

                chart.draw(dataTable, options); // Draw graph with the data and options.
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert('Got an Error');
            }
        });
    }
}

function brCashBank(elm) {

    $('#chartTitle').html('Branch Wise Cash & Bank balance');
    $('#chartSubTitle').html('Last Update: ' + new Date());

    $('#showChart_link').html(
        '<a href="javascript:void(0);" onclick="brCashBank_all();" class="float-right red-800">See All Branches</a>'
        );

    ////////////////
    $('.bg-teal-600').removeClass('bg-teal-600');
    $(elm).parent().addClass("bg-teal-600");

    google.charts.load('current', {
        packages: ['corechart', 'bar']
    });
    google.charts.setOnLoadCallback(drawColColors);

    function drawColColors() {
        var dataTable = new google.visualization.DataTable();
        dataTable.addColumn('string', 'Branch');
        dataTable.addColumn('number', 'Cash');
        dataTable.addColumn('number', 'Bank');

        var options = {
            bar: {
                groupWidth: "50%"
            },
            colors: ['#9575cd', '#33ac71'],
            // 'width': 650,
            'height': 500,
            hAxis: {
                title: 'Top 10 Branchs',
                titleTextStyle: {
                    bold: true,
                },
                format: 'none',
                textStyle: {
                    // bold: true,
                    italic: true
                },
                textPosition: 'out',
                slantedText: true,
            },
            vAxis: {
                title: 'Amount (BDT)',
                titleTextStyle: {
                    bold: true,
                },
                format: 'short'
            },
            legend: {
                alignment: 'center',
                position: 'top'
            },
            chartArea: {
                top: 70,
                bottom: 100,
            },
        };

        $.ajax({
            method: "GET",
            url: "{{route('org_graph_cb')}}",
            success: function(data) {
                var counter = 0;

                $.each(data.brCashBank, function(key, obj) {
                    if (counter < 10) {
                        counter++;
                        var res = key.split("-");
                        dataTable.addRow([res[1], parseFloat(obj.cash), parseFloat(obj.bank)]);
                    } else return false;
                });

                // The type of chart (piechart).
                var chart = new google.visualization.ColumnChart(document.getElementById('showChart'));

                chart.draw(dataTable, options); // Draw graph with the data and options.
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert('Got an Error');
            }
        });
    }
}

function brCashBank_all() {

    $('#chartTitle').html('Branch Wise Cash & Bank balance');
    $('#chartSubTitle').html('Last Update: ' + new Date());

    $('#showChart_link').html('');

    google.charts.load('current', {
        packages: ['corechart', 'bar']
    });
    google.charts.setOnLoadCallback(drawColColors);

    function drawColColors() {
        var dataTable = new google.visualization.DataTable();
        dataTable.addColumn('string', 'Branch');
        dataTable.addColumn('number', 'Cash');
        dataTable.addColumn('number', 'Bank');

        var options = {
            bar: {
                groupWidth: "50%"
            },
            colors: ['#9575cd', '#33ac71'],
            height: 1500,
            hAxis: {
                title: 'Amount (BDT)',
                titleTextStyle: {
                    bold: true,
                },
                format: 'short',
                textStyle: {
                    // bold: true,
                    italic: true
                },
                textPosition: 'out',
                slantedText: true,
            },
            vAxis: {
                title: 'All Branchs',
                titleTextStyle: {
                    bold: true,
                },
                format: 'none'
            },
            legend: {
                position: 'top',
                alignment: 'center',
            },
            chartArea: {
                top: 70,
                bottom: 100,
            },
        };
        $.ajax({
            method: "GET",
            url: "{{route('org_graph_cb')}}",
            success: function(data) {

                $.each(data.brCashBank, function(key, obj) {
                    dataTable.addRow([key, parseFloat(obj.cash), parseFloat(obj.bank)]);
                });

                // The type of chart (piechart).
                var chart = new google.visualization.BarChart(document.getElementById('showChart'));

                chart.draw(dataTable, options); // Draw graph with the data and options.
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert('Got an Error');
            }
        });
    }
}

function brIncomeExpense(elm) {

    $('#chartTitle').html('Branch Wise Income & Expenses');
    $('#chartSubTitle').html('As On Current Year Upto: ' + new Date());

    $('#showChart_link').html(
        '<a href="javascript:void(0);" onclick="brIncomeExpense_all();" class="float-right red-800">See All Branches</a>'
        );

    ///////////////
    $('.bg-teal-600').removeClass('bg-teal-600');
    $(elm).parent().addClass("bg-teal-600");

    google.charts.load('current', {
        packages: ['corechart', 'bar']
    });
    google.charts.setOnLoadCallback(drawColColors);

    function drawColColors() {
        var dataTable = new google.visualization.DataTable();
        dataTable.addColumn('string', 'Branch');
        dataTable.addColumn('number', 'Income');
        dataTable.addColumn('number', 'Expense');
        var counter = 0;

        var options = {
            bar: {
                groupWidth: "50%"
            },
            colors: ['#9575cd', '#33ac71'],
            // 'width': 650,
            'height': 500,
            hAxis: {
                title: 'Top 10 Branchs',
                titleTextStyle: {
                    bold: true,
                },
                format: 'none',
                textStyle: {
                    // bold: true,
                    italic: true
                },
                textPosition: 'out',
                slantedText: true,
            },
            vAxis: {
                title: 'Amount (BDT)',
                titleTextStyle: {
                    bold: true,
                },
                format: 'short'
            },
            legend: {
                alignment: 'center',
                position: 'top'
            },
            chartArea: {
                top: 70,
                bottom: 100,
            },
        };

        $.ajax({
            method: "GET",
            url: "{{route('org_graph_ie')}}",
            success: function(data) {

                $.each(data.brIncExp, function(key, obj) {

                    if (counter < 10) {
                        counter++;

                        var res = key.split("-");
                        dataTable.addRow([res[1], parseFloat(obj.income), parseFloat(obj.expense)]);
                    } else return false;
                });

                // The type of chart (piechart).
                var chart = new google.visualization.ColumnChart(document.getElementById('showChart'));

                chart.draw(dataTable, options); // Draw graph with the data and options.
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert('Got an Error');
            }
        });


    }
}

function brIncomeExpense_all() {

    $('#chartTitle').html('Branch Wise Income & Expenses');
    $('#chartSubTitle').html('As On Current Year Upto: ' + new Date());

    $('#showChart_link').html('');

    google.charts.load('current', {
        packages: ['corechart', 'bar']
    });
    google.charts.setOnLoadCallback(drawColColors);

    function drawColColors() {
        var dataTable = new google.visualization.DataTable();
        dataTable.addColumn('string', 'Branch');
        dataTable.addColumn('number', 'Income');
        dataTable.addColumn('number', 'Expense');

        var options = {
            bar: {
                groupWidth: "50%"
            },
            colors: ['#9575cd', '#33ac71'],
            height: 1500,
            hAxis: {
                title: 'Amount (BDT)',
                titleTextStyle: {
                    bold: true,
                },
                format: 'short',
                textStyle: {
                    // bold: true,
                    italic: true
                },
                textPosition: 'out',
                slantedText: true,
            },
            vAxis: {
                title: 'All Branchs',
                titleTextStyle: {
                    bold: true,
                },
                format: 'none'
            },
            legend: {
                position: 'top',
                alignment: 'center',
            },
            chartArea: {
                top: 70,
                bottom: 100,
            },
        };

        $.ajax({
            method: "GET",
            url: "{{route('org_graph_ie')}}",
            success: function(data) {

                $.each(data.brIncExp, function(key, obj) {
                    dataTable.addRow([key, parseFloat(obj.income), parseFloat(obj.expense)]);
                });

                // The type of chart (piechart).
                var chart = new google.visualization.BarChart(document.getElementById('showChart'));

                chart.draw(dataTable, options); // Draw graph with the data and options.
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert('Got an Error');
            }
        });
    }
}
</script>
@endsection