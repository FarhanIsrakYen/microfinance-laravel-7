@extends('Layouts.erp_master')
@section('title', 'POS Dashboard')
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


    @media screen and (max-width: 1024px) and (min-width: 786px){
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


<div class="w-full p-row" style="min-height: 100%">
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

                <!-- <div class="col-lg-3">
                    <a href="{{url('acc/auto_vouchers') }}" target="_blank">
                        <div class="card voucher-card shadow text-center">
                            <div class="card-block" style="border-bottom: 4px solid #026466">
                                <h4 class="m-b-20 text-insta pt-4">Auto Voucher</h4>
                                <span class="dashCard2 shadow-lg" style="background: #026466;">
                                    <i class="fa fa-cube f-left text-white" style="font-size: 30px"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                </div> -->
            </div>

            <!-- <div class="row mt-4 justify-content-center">

                <div class="col-lg-3">
                    <a href="{{url('acc/auto_auth_vouchers') }}" target="_blank">
                        <div class="card voucher-card shadow text-center">
                            <div class="card-block" style="border-bottom: 4px solid #026466">
                                <h4 class="m-b-20 text-insta pt-4">Authorized Voucher</h4>
                                <span class="dashCard2 shadow-lg" style="background: #026466;">
                                    <i class="fa fa-database f-left text-white" style="font-size: 30px"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-lg-3">
                    <a href="{{url('acc/auto_unauth_vouchers') }}" target="_blank">
                        <div class="card voucher-card shadow text-center">
                            <div class="card-block" style="border-bottom: 4px solid #026466">
                                <h4 class="m-b-20 text-insta pt-4">Unauthorized Voucher</h4>
                                <span class="dashCard2 shadow-lg" style="background: #026466;">
                                    <i class="fa fa-bookmark f-left text-white" style="font-size: 30px"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            </div> -->
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
                                <li><span>Current Month Surplus : </span><span id="cur_month_surplus">0</span></li>
                                <li><span>Current Year Surplus : </span><span id="cur_year_surplus">0</span></li>
                            </ul>
                        </div>
                        <div class="col-lg-4 pt-4 pb-4">
                            <ul>
                                <li><span>Last Month Surplus : </span><span id="last_month_surplus">0</span></li>
                                <li><span>Cumulative Surplus Amount : </span><span id="cum_surplus">0</span></li>
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
                                <li><span>Current Cash Amount : </span><span id="cur_cash_amount">0</span></li>
                                <li><span>Total Balance : </span><span id="ttl_balance">0</span></li>
                            </ul>
                        </div>
                        <div class="col-lg-4 pt-4 pb-4">
                            <ul>
                                <li><span>Current Bank Amount : </span><span id="cur_bank_amount">0</span></li>
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
                                    <td class="bg-teal-600"><a href="javascript:void(0)" onclick="brSurplus(this)"><h5>Branch Wise Surplus</h5></a></td>
                                </tr>
                                <tr>
                                    <td><a href="javascript:void(0)" onclick="brCashBank(this)"><h5>Branch Wise Cash And Bank Balance</h5></a></td>
                                </tr>
                                <tr>
                                    <td><a href="javascript:void(0)" onclick="brIncomeExpense(this)"><h5>Branch Wise Income And Expense</h5></a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="col-lg-8 bg-white">
                        <div class="chartTitle blue-grey-800 p-4 text-center">Branch Wise Surplus</div>
                        <div class="chartSubTitle p-2 text-center">As On Current Year Upto : {{ $today }}</div>

                        <div  class="justify-content-center">
                            <div id="piechart"></div>
                            <a href="javascript:void(0)" class="float-right red-800 tab1">See All Branches</a>
                        </div>

                        <div id="see_all_tab1" class="justify-content-center"></div>
                        <div id="see_all_tab2" class="justify-content-center"></div>
                        <div id="see_all_tab3" class="justify-content-center"></div>

                        <div class="justify-content-center">
                            <div id="chart_div"></div>
                            <a href="javascript:void(0)" class="float-right red-800 tab2">See All Branches</a>
                        </div>

                        <div class="justify-content-center">
                            <div id="inc_exp_div"></div>
                            <a href="javascript:void(0)" class="float-right red-800 tab3">See All Branches</a>
                        </div>

                    </div>
                    <div class="col-lg-2 bg-white"></div>
                </div>
            </div>
        </div>
<!-- End Organization Status  -->



        <div class="tab-pane fade" id="BranchStatusID">
            <div class="row" id="branchID">
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
                        <tbody>

                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" style="text-align: right!important;"><b>Total</b></td>
                                <td style="text-align:center;" id="nCustomer"><b>0.00</b></td>
                                <td style="text-align:center;" id="tlmdue"><b>0.00</b></td>
                                <td style="text-align:center;" id="ttldue"><b>0.00</b></td>
                                <td style="text-align:center;" id="ttlbalance"><b>0.00</b></td>
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
                    data: 'soft_start_date',
                    className: 'text-center'
                },
                {
                    data: 'branch_date',
                    className: 'text-center'
                },
                {
                    data: 'pre_month_cash',
                    className: 'text-center'
                },
                {
                    data: 'pre_month_bank',
                    className: 'text-center'
                },
                {
                    data: 'cur_month_cash',
                    className: 'text-center'
                },
                {
                    data: 'cur_month_bank',
                    className: 'text-center'
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
                    $('#nCustomer').html(oResult.json.tcustomer);
                    $('#ttlbalance').html(oResult.json.total_balance);
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
                    $('#cur_month_surplus').html(data.current_month_surplus);
                    $('#cur_year_surplus').html(data.current_year_surplus);
                    $('#last_month_surplus').html(data.last_month_surplus);
                    $('#cum_surplus').html(data.cumulative_surplus);
                    $('#cur_cash_amount').html(data.current_cash_amount);
                    $('#cur_bank_amount').html(data.current_bank_amount);
                    $('#ttl_balance').html(data.total_balance);
                }
            }
        });
    });

    $("#TabID li:eq(0) a").tab('show');

});
</script>


<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<!-- <script type="text/javascript">
// Load google charts
google.charts.load('current', {
    'packages': ['corechart']
});
google.charts.setOnLoadCallback(drawChart);

// Draw the chart and set the chart values
function drawChart() {
    // var data = google.visualization.arrayToDataTable([
    //     ['Task', 'Hours per Day'],
    //     ['Zirani', 8],
    //     ['Kamlapur', 2],
    //     ['Seed Store', 4],
    //     ['Hasnabad', 2],
    //     ['Head Office', 8]
    // ]);

    // Optional; add a title and set the width and height of the chart
    var options = {
        'title': 'Branch Wise Surplus',
        'width': 550,
        'height': 400,
        is3D: true,
        colors: ['#99b898', '#fecea8', '#ff847c', '#e84a5f', '#474747']
    };

    // Display the chart inside the <div> element with id="piechart"
    var chart = new google.visualization.PieChart(document.getElementById('piechart'));
    chart.draw(data, options);
}
</script> -->


<script>
    // $('#piechart').show();
    $('#chart_div,.tab2,.tab3').hide();
    brSurplus();
    // VISUALIZATION API AND THE PIE CHART PACKAGE.

    function brSurplus(elm) {
        // console.log(elm);
        if(elm !== undefined){
            $('.bg-teal-600').removeClass('bg-teal-600');
        }
        
        $('#chart_div,.tab2,.tab3,#see_all_tab1,see_all_tab2').hide();
        $('#piechart,.tab1').show();
        $(elm).parent().addClass("bg-teal-600");
        $('.chartTitle').text('Branch Wise Surplus');

        google.load("visualization", "1", { packages: ["corechart"] });
        google.setOnLoadCallback(createPIE);

        function createPIE() {
            // SET CHART OPTIONS.
            var options = {
                'height': 400,
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
                success: function (data) {
                    var arrValues = [['Branch', 'Surplus']];        // Define an array

                    $.each(data.surplusPercent, function(key, value) {
                        arrValues.push([key, value]);
                    });

                    // // Create a datatable and add the array (with data) in it
                    var figures = google.visualization.arrayToDataTable(arrValues)

                    // The type of chart (piechart).
                    var chart = new google.visualization.PieChart(document.getElementById('piechart'));

                    chart.draw(figures, options);      // Draw graph with the data and options.
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    alert('Got an Error');
                }
            });
        }
    }

    
</script>

<script type="text/javascript">

    function brCashBank(elm) {
        $('.bg-teal-600').removeClass('bg-teal-600');
        $('#piechart,.tab1,.tab3,#see_all_tab2,#see_all_tab1').hide();
        $('#chart_div,.tab2').show();
        $(elm).parent().addClass("bg-teal-600");
        $('.chartTitle').text('Branch Wise Cash And Bank Amount');
    
        google.charts.load('current', {packages: ['corechart', 'bar']});
        google.charts.setOnLoadCallback(drawColColors);

        function drawColColors() {
            var dataTable = new google.visualization.DataTable();
            dataTable.addColumn('string', 'Branch');
            dataTable.addColumn('number', 'Cash');
            dataTable.addColumn('number', 'Bank');
            var counter = 0;
            var options = {
                colors: ['#9575cd', '#33ac71'],
                // 'width': 650,
                'height': 500,
                hAxis: {
                    title: '',
                    // format: 'h:mm a',
                    viewWindow: {
                        min: [7, 30, 0],
                        max: [17, 30, 0]
                    }
                },
                vAxis: {
                    title: 'Amount (BDT)'
                },
                legend: {
                    alignment: 'center',
                    position: 'top'
                },
                chartArea: {
                    width: '50%',
                },
            };

            $.ajax({
                method: "GET",
                url: "{{route('org_graph_cb')}}",
                success: function (data) {

                    $.each(data.brCashBank, function(key, obj) {
                        if (counter < 10) {
                            counter ++;
                            dataTable.addRow([key, obj.cash, obj.bank]);
                        }
                        else return false;
                    });

                    // The type of chart (piechart).
                    var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));

                    chart.draw(dataTable, options);      // Draw graph with the data and options.
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    alert('Got an Error');
                }
            });

            
        }
    }
</script>
<script type="text/javascript">

    function brIncomeExpense(elm) {
        $('.bg-teal-600').removeClass('bg-teal-600');
        $('#piechart,#chart_div,.tab1,.tab2,.tab3,#see_all_tab1,#see_all_tab2,#see_all_tab3').hide();
        $('#inc_exp_div,.tab3').show();
        $(elm).parent().addClass("bg-teal-600");
        $('.chartTitle').text('Branch Wise Income And Expenses');
    
        google.charts.load('current', {packages: ['corechart', 'bar']});
        google.charts.setOnLoadCallback(drawColColors);

        function drawColColors() {
            var dataTable = new google.visualization.DataTable();
            dataTable.addColumn('string', 'Branch');
            dataTable.addColumn('number', 'Income');
            dataTable.addColumn('number', 'Expense');
            var counter = 0;
            var options = {
                colors: ['#9575cd', '#33ac71'],
                // 'width': 650,
                'height': 500,
                hAxis: {
                    title: '',
                    // format: 'h:mm a',
                    viewWindow: {
                        min: [7, 30, 0],
                        max: [17, 30, 0]
                    }
                },
                vAxis: {
                    title: 'Amount (BDT)'
                },
                legend: {
                    alignment: 'center',
                    position: 'top'
                },
                chartArea: {
                    width: '50%',
                },
            };

            $.ajax({
                method: "GET",
                url: "{{route('org_graph_ie')}}",
                success: function (data) {

                    $.each(data.brIncExp, function(key, obj) {
                        if (counter < 10) {
                            counter ++;
                            dataTable.addRow([key, obj.income, obj.expense]);
                        }
                        else return false;
                    });

                    // The type of chart (piechart).
                    var chart = new google.visualization.ColumnChart(document.getElementById('inc_exp_div'));

                    chart.draw(dataTable, options);      // Draw graph with the data and options.
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    alert('Got an Error');
                }
            });

            
        }
    }
</script>

<script type="text/javascript">
    $('#see_all_tab1').hide();
    $('.tab1').click(function(){
        $('#piechart,#chart_div,.tab1,.tab2,.tab3,#see_all_tab2,#see_all_tab3').hide();
        $('#see_all_tab1').show();
        seeAllTab1();
    });
    function seeAllTab1() {
        google.charts.load('current', {packages: ['corechart', 'bar']});
        google.charts.setOnLoadCallback(drawColColors);

        function drawColColors() {
            var dataTable = new google.visualization.DataTable();
            dataTable.addColumn('string', 'Branch');
            dataTable.addColumn('number', 'Surplus');
            var options = {
                colors: ['#9575cd'],
                'height': 1000,
                hAxis: {
                    title: 'Amount (BDT)',
                    viewWindow: {
                        min: [7, 30, 0],
                        max: [17, 30, 0]
                    }
                },
                vAxis: {
                    title: ''
                },
                chartArea: {
                    top: 32,
                    bottom: 32,
                },
                legend: {
                    position: 'top',
                    alignment: 'center',
                }
            };
            $.ajax({
                method: "GET",
                url: "{{route('org_graph_surplus')}}",
                success: function (data) {

                    $.each(data.surplus, function(key, value) {
                        dataTable.addRow([key, value]);
                    });

                    // The type of chart (piechart).
                    var chart = new google.visualization.BarChart(document.getElementById('see_all_tab1'));

                    chart.draw(dataTable, options);      // Draw graph with the data and options.
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    alert('Got an Error');
                }
            });    
        }
    }

</script>
<script type="text/javascript">
    $('#see_all_tab2').hide();
    $('.tab2').click(function(){
        $('#chart_div,#piechart,#inc_exp_div,.tab1,.tab2,.tab3,#see_all_tab1,#see_all_tab3').hide();
        $('#see_all_tab2').show();
        seeAllTab2();
    });
    
    function seeAllTab2(){
        google.charts.load('current', {packages: ['corechart', 'bar']});
        google.charts.setOnLoadCallback(drawColColors);

        function drawColColors() {
            var dataTable = new google.visualization.DataTable();
            dataTable.addColumn('string', 'Branch');
            dataTable.addColumn('number', 'Cash');
            dataTable.addColumn('number', 'Bank');
            var options = {
                colors: ['#9575cd', '#33ac71'],
                'height': 1000,
                hAxis: {
                    title: 'Amount (BDT)',
                },
                vAxis: {
                    title: ''
                },
                chartArea: {
                    top: 32,
                    bottom: 32,
                },
                legend: {
                    position: 'top',
                    alignment: 'center',
                }
            };
            $.ajax({
                method: "GET",
                url: "{{route('org_graph_cb_all')}}",
                success: function (data) {

                    $.each(data.brCashBank, function(key, obj) {
                        dataTable.addRow([key, obj.cash, obj.bank]);
                    });

                    // The type of chart (piechart).
                    var chart = new google.visualization.BarChart(document.getElementById('see_all_tab2'));

                    chart.draw(dataTable, options);      // Draw graph with the data and options.
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    alert('Got an Error');
                }
            });  
        }
    }

</script>
<script type="text/javascript">
    $('#see_all_tab3').hide();
    $('.tab3').click(function(){
        $('#chart_div,#piechart,#inc_exp_div,.tab1,.tab2,.tab3,#seeAllTab1,#seeAllTab2').hide();
        $('#see_all_tab3').show();
        seeAllTab3();
    });
    
    function seeAllTab3(){
        google.charts.load('current', {packages: ['corechart', 'bar']});
        google.charts.setOnLoadCallback(drawColColors);

        function drawColColors() {
            var dataTable = new google.visualization.DataTable();
            dataTable.addColumn('string', 'Branch');
            dataTable.addColumn('number', 'Income');
            dataTable.addColumn('number', 'Expense');
            var options = {
                colors: ['#9575cd', '#33ac71'],
                'height': 1000,
                hAxis: {
                    title: 'Amount (BDT)',
                },
                vAxis: {
                    title: ''
                },
                chartArea: {
                    top: 32,
                    bottom: 32,
                },
                legend: {
                    position: 'top',
                    alignment: 'center',
                }
            };
            $.ajax({
                method: "GET",
                url: "{{route('org_graph_ie_all')}}",
                success: function (data) {

                    $.each(data.brIncomeExpense, function(key, obj) {
                        dataTable.addRow([key, obj.income, obj.expense]);
                    });

                    // The type of chart (piechart).
                    var chart = new google.visualization.BarChart(document.getElementById('see_all_tab3'));

                    chart.draw(dataTable, options);      // Draw graph with the data and options.
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    alert('Got an Error');
                }
            });  
        }
    }

</script>
@endsection