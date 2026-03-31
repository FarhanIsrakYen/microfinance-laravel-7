@extends('Layouts.erp_master')
@section('title', 'POS Dashboard')
@section('content')
<style type="text/css">
    .iconL{
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
    @media screen and (max-width: 1024px) and (min-width: 786px)
        .nmbr-statistic-block .nmbr-statistic-info {
            left: 15%;
            top: 90px;
        }
    .card-btm-border {
        border-bottom: transparent solid 4px;
    }
    .card-shadow-success {
    box-shadow: 0 0.46875rem 2.1875rem rgba(58,196,125,.03), 0 0.9375rem 1.40625rem rgba(58,196,125,.03), 0 0.25rem 0.53125rem rgba(58,196,125,.05), 0 0.125rem 0.1875rem rgba(58,196,125,.03);
}
</style>

<div class="w-full">
    <div class="page-content">
        <h3 class="text-center pb-25">MIS DASHBOARD</h3>

        <!-- <div class="row">
            <div class="col-lg-3">
                <div class="card card-shadow" id="widgetLineareaOne">
                    <div class="card-block p-20 pt-10">
                      <div class="clearfix">
                        <div class="grey-800 float-left py-10">
                          <i class="icon wb-user grey-600 font-size-24 vertical-align-bottom mr-5"></i>User
                        </div>
                        <span class="float-right grey-700 font-size-30">1,253</span>
                      </div>
                      <div class="mb-20 grey-500">
                        <i class="icon fa-level-up green-500 font-size-16"></i> 15%
                        From this yesterday
                      </div>
                      <div class="ct-chart h-50"><svg xmlns:ct="http://gionkunz.github.com/chartist-js/ct" width="100%" height="100%" class="ct-chart-line"><g class="ct-grids"></g><g><g class="ct-series ct-series-a"><path d="M0,50L0,50C19.438,45.833,38.876,43.056,58.314,37.5C77.752,31.944,97.19,12.5,116.629,12.5C136.067,12.5,155.505,25,174.943,25C194.381,25,213.819,6.25,233.257,6.25C252.695,6.25,272.133,35,291.571,35C311.01,35,330.448,31.25,349.886,31.25C369.324,31.25,388.762,43.75,408.2,50L408.2,50Z" class="ct-area"></path></g></g><g class="ct-labels"></g></svg></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card card-shadow" id="widgetLineareaTwo">
                    <div class="card-block p-20 pt-10">
                    <div class="clearfix">
                        <div class="grey-800 float-left py-10">
                          <i class="icon fa-bolt grey-600 font-size-24 vertical-align-bottom mr-5"></i>VISITS
                        </div>
                        <span class="float-right grey-700 font-size-30">2,425</span>
                    </div>
                    <div class="mb-20 grey-500">
                        <i class="icon fa-level-up green-500 font-size-16"></i> 34.2%
                        From this week
                    </div>
                    <div class="ct-chart h-50">
                        <svg xmlns:ct="http://gionkunz.github.com/chartist-js/ct" width="100%" height="100%" class="ct-chart-line"><g class="ct-grids"></g><g><g class="ct-series ct-series-a"><path d="M0,50L0,50C17.008,47.917,34.017,46.97,51.025,43.75C68.033,40.53,85.042,22.5,102.05,22.5C119.058,22.5,136.067,25,153.075,25C170.083,25,187.092,15,204.1,15C221.108,15,238.117,21.25,255.125,21.25C272.133,21.25,289.142,8.75,306.15,8.75C323.158,8.75,340.167,13.699,357.175,18.75C374.183,23.801,391.192,39.583,408.2,50L408.2,50Z" class="ct-area"></path></g></g><g class="ct-labels"></g>
                        </svg>
                    </div>
                    </div>
                </div>
            </div>
        </div> -->

        <div class="row">
            <div class="col-xl-3 col-md-3 info-panel">
                <div class="card card-shadow" style="overflow: hidden;">
                    <div class="iconL">
                        <i class="fa fa-university" aria-hidden="true" style="font-size: 80px;"></i>
                    </div>
                    <div class="card-block bg-white p-20">
                        <button type="button" class="btn btn-floating btn-sm btn-warning">
                            <i class="icon fa-university"></i>
                        </button>
                        <span class="ml-15 font-weight-400">Branchs</span>
                        <div class="content-text text-center mb-0">
                            <i class="text-danger icon wb-triangle-up font-size-20"></i>
                            <span class="font-size-40 font-weight-100">399</span>
                            <p class="blue-grey-400 font-weight-100 m-0">Total Branches</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-3 info-panel">
                <div class="card card-shadow" style="overflow: hidden;">
                    <div class="iconL">
                        <i class="fa fa-group" aria-hidden="true" style="font-size: 80px;"></i>
                    </div>
                    <div class="card-block bg-white p-20">
                        <button type="button" class="btn btn-floating btn-sm btn-warning">
                            <i class="icon fa-group"></i>
                        </button>
                        <span class="ml-15 font-weight-400">Customers</span>
                        <div class="content-text text-center mb-0">
                            <i class="text-danger icon wb-triangle-up font-size-20"></i>
                            <span class="font-size-40 font-weight-100">399</span>
                            <p class="blue-grey-400 font-weight-100 m-0">Total Customers</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-3 info-panel">
                <div class="card card-shadow" style="overflow: hidden;">
                    <div class="iconL">
                        <i class="fa fa-cube" aria-hidden="true" style="font-size: 80px;"></i>
                    </div>
                    <div class="card-block bg-white p-20">
                        <button type="button" class="btn btn-floating btn-sm btn-warning">
                            <i class="icon fa-cube"></i>
                        </button>
                        <span class="ml-15 font-weight-400">Products</span>
                        <div class="content-text text-center mb-0">
                            <i class="text-danger icon wb-triangle-up font-size-20"></i>
                            <span class="font-size-40 font-weight-100">399</span>
                            <p class="blue-grey-400 font-weight-100 m-0">Total Products</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-3 info-panel">
                <div class="card card-shadow" style="overflow: hidden;">
                    <div class="iconL">
                        <i class="fa fa-shield" aria-hidden="true" style="font-size: 80px;"></i>
                    </div>
                    <div class="card-block bg-white p-20">
                        <button type="button" class="btn btn-floating btn-sm btn-warning">
                            <i class="icon fa-shield"></i>
                        </button>
                        <span class="ml-15 font-weight-400">Product Brands</span>
                        <div class="content-text text-center mb-0">
                            <i class="text-danger icon wb-triangle-up font-size-20"></i>
                            <span class="font-size-40 font-weight-100">399</span>
                            <p class="blue-grey-400 font-weight-100 m-0">Total Product Brands</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-xl-3 col-md-3 info-panel">
                <div class="card card-shadow" style="overflow: hidden;">
                    <div class="iconL">
                        <i class="fa fa-calendar-check-o" aria-hidden="true" style="font-size: 80px;"></i>
                    </div>
                    <div class="card-block bg-white p-20">
                        <button type="button" class="btn btn-floating btn-sm btn-info">
                            <i class="icon fa-calendar-check-o"></i>
                        </button>
                        <span class="ml-15 font-weight-400">Sales - Today</span>
                        <div class="content-text text-center mb-0">
                            <i class="text-danger icon wb-triangle-up font-size-20"></i>
                            <span class="font-size-40 font-weight-100">399</span>
                            <p class="blue-grey-400 font-weight-100 m-0">+45% From previous month</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-3 info-panel">
                <div class="card card-shadow" style="overflow: hidden;">
                    <div class="iconL">
                        <i class="fa fa-calendar-plus-o" aria-hidden="true" style="font-size: 80px;"></i>
                    </div>
                    <div class="card-block bg-white p-20">
                        <button type="button" class="btn btn-floating btn-sm btn-info">
                            <i class="icon fa-calendar-plus-o"></i>
                        </button>
                        <span class="ml-15 font-weight-400">Sales - This Month</span>
                        <div class="content-text text-center mb-0">
                            <i class="text-danger icon wb-triangle-up font-size-20"></i>
                            <span class="font-size-40 font-weight-100">399</span>
                            <p class="blue-grey-400 font-weight-100 m-0">+45% From previous month</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-3 info-panel">
                <div class="card card-shadow" style="overflow: hidden;">
                    <div class="iconL">
                        <i class="fa fa-calendar" aria-hidden="true" style="font-size: 80px;"></i>
                    </div>
                    <div class="card-block bg-white p-20">
                        <button type="button" class="btn btn-floating btn-sm btn-info">
                            <i class="icon fa-calendar"></i>
                        </button>
                        <span class="ml-15 font-weight-400">Sales - This Year</span>
                        <div class="content-text text-center mb-0">
                            <i class="text-danger icon wb-triangle-up font-size-20"></i>
                            <span class="font-size-40 font-weight-100">399</span>
                            <p class="blue-grey-400 font-weight-100 m-0">+45% From previous month</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-3 info-panel">
                <div class="card card-shadow" style="overflow: hidden;">
                    <div class="iconL">
                        <i class="fa fa-calendar-o" aria-hidden="true" style="font-size: 80px;"></i>
                    </div>
                    <div class="card-block bg-white p-20">
                        <button type="button" class="btn btn-floating btn-sm btn-info">
                            <i class="icon fa-calendar-o"></i>
                        </button>
                        <span class="ml-15 font-weight-400">Sales - All</span>
                        <div class="content-text text-center mb-0">
                            <i class="text-danger icon wb-triangle-up font-size-20"></i>
                            <span class="font-size-40 font-weight-100">399</span>
                            <p class="blue-grey-400 font-weight-100 m-0">+45% From previous month</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-xl-3 col-md-3 info-panel">
                <div class="card card-shadow" style="overflow: hidden;">
                    <div class="iconL">
                        <i class="fa fa-gg" aria-hidden="true" style="font-size: 80px;"></i>
                    </div>
                    <div class="card-block bg-white p-20">
                        <button type="button" class="btn btn-floating btn-sm bg-success">
                            <i class="icon fa-gg"></i>
                        </button>
                        <span class="ml-15 font-weight-400">Collection - Today</span>
                        <div class="content-text text-center mb-0">
                            <i class="text-danger icon wb-triangle-up font-size-20"></i>
                            <span class="font-size-40 font-weight-100">399</span>
                            <p class="blue-grey-400 font-weight-100 m-0">+45% From previous month</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-3 info-panel">
                <div class="card card-shadow" style="overflow: hidden;">
                    <div class="iconL">
                        <i class="fa fa-gg-circle" aria-hidden="true" style="font-size: 80px;"></i>
                    </div>
                    <div class="card-block bg-white p-20">
                        <button type="button" class="btn btn-floating btn-sm bg-success">
                            <i class="icon fa-gg-circle"></i>
                        </button>
                        <span class="ml-15 font-weight-400">Collection - This Month</span>
                        <div class="content-text text-center mb-0">
                            <i class="text-danger icon wb-triangle-up font-size-20"></i>
                            <span class="font-size-40 font-weight-100">399</span>
                            <p class="blue-grey-400 font-weight-100 m-0">+45% From previous month</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-3 info-panel">
                <div class="card card-shadow" style="overflow: hidden;">
                    <div class="iconL">
                        <i class="fa fa-credit-card" aria-hidden="true" style="font-size: 80px;"></i>
                    </div>
                    <div class="card-block bg-white p-20">
                        <button type="button" class="btn btn-floating btn-sm bg-success">
                            <i class="icon fa-credit-card"></i>
                        </button>
                        <span class="ml-15 font-weight-400">Collection - This Year</span>
                        <div class="content-text text-center mb-0">
                            <i class="text-danger icon wb-triangle-up font-size-20"></i>
                            <span class="font-size-40 font-weight-100">399</span>
                            <p class="blue-grey-400 font-weight-100 m-0">+45% From previous month</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-3 info-panel">
                <div class="card card-shadow" style="overflow: hidden;">
                    <div class="iconL">
                        <i class="fa fa-money" aria-hidden="true" style="font-size: 80px;"></i>
                    </div>
                    <div class="card-block bg-white p-20">
                        <button type="button" class="btn btn-floating btn-sm bg-success">
                            <i class="icon fa-money"></i>
                        </button>
                        <span class="ml-15 font-weight-400">Collection - All</span>
                        <div class="content-text text-center mb-0">
                            <i class="text-danger icon wb-triangle-up font-size-20"></i>
                            <span class="font-size-40 font-weight-100">399</span>
                            <p class="blue-grey-400 font-weight-100 m-0">+45% From previous month</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-xl-3 col-md-3 info-panel">
                <div class="card card-shadow" style="overflow: hidden;">
                    <div class="iconL">
                        <i class="fa fa-hourglass-1" aria-hidden="true" style="font-size: 80px;"></i>
                    </div>
                    <div class="card-block bg-white p-20">
                        <button type="button" class="btn btn-floating btn-sm btn-danger">
                            <i class="icon fa-hourglass-1"></i>
                        </button>
                        <span class="ml-15 font-weight-400">Total Due</span>
                        <div class="content-text text-center mb-0">
                            <i class="text-danger icon wb-triangle-up font-size-20"></i>
                            <span class="font-size-40 font-weight-100">399</span>
                            <p class="blue-grey-400 font-weight-100 m-0">+45% From previous month</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-3 info-panel">
                <div class="card card-shadow" style="overflow: hidden;">
                    <div class="iconL">
                        <i class="fa fa-clock-o" aria-hidden="true" style="font-size: 80px;"></i>
                    </div>
                    <div class="card-block bg-white p-20">
                        <button type="button" class="btn btn-floating btn-sm btn-danger">
                            <i class="icon fa-clock-o"></i>
                        </button>
                        <span class="ml-15 font-weight-400">Over Due</span>
                        <div class="content-text text-center mb-0">
                            <i class="text-danger icon wb-triangle-up font-size-20"></i>
                            <span class="font-size-40 font-weight-100">399</span>
                            <p class="blue-grey-400 font-weight-100 m-0">+45% From previous month</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-3 info-panel">
                <div class="card card-shadow" style="overflow: hidden;">
                    <div class="iconL">
                        <i class="fa fa-hourglass-2" aria-hidden="true" style="font-size: 80px;"></i>
                    </div>
                    <div class="card-block bg-white p-20">
                        <button type="button" class="btn btn-floating btn-sm btn-danger">
                            <i class="icon fa-hourglass-2"></i>
                        </button>
                        <span class="ml-15 font-weight-400">Cumulative Due</span>
                        <div class="content-text text-center mb-0">
                            <i class="text-danger icon wb-triangle-up font-size-20"></i>
                            <span class="font-size-40 font-weight-100">399</span>
                            <p class="blue-grey-400 font-weight-100 m-0">+45% From previous month</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-3 info-panel">
                <div class="card card-shadow" style="overflow: hidden;">
                    <div class="iconL">
                        <i class="fa fa-balance-scale" aria-hidden="true" style="font-size: 80px;"></i>
                    </div>
                    <div class="card-block bg-white p-20">
                        <button type="button" class="btn btn-floating btn-sm btn-danger">
                            <i class="icon fa-balance-scale"></i>
                        </button>
                        <span class="ml-15 font-weight-400">Balance</span>
                        <div class="content-text text-center mb-0">
                            <i class="text-danger icon wb-triangle-up font-size-20"></i>
                            <span class="font-size-40 font-weight-100">399</span>
                            <p class="blue-grey-400 font-weight-100 m-0">+45% From previous month</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection