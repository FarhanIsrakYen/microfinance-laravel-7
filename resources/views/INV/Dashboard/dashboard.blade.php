@extends('Layouts.erp_master')
@section('title', 'Inventory')
@section('content')

<?php
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
?>


<div class="w-full p-row minHeight">
    <h3 class="text-center pb-25">INVENTORY DASHBOARD</h3>

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
        </div>

        <div class="tab-pane fade" id="branchID">
            <div class="row">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead>
                            <tr>
                                <th width="3%">SL</th>
                                <th>Branch Name</th>
                                <th>Branch Opening Date</th>
                                <th>Software Start Date</th>
                                <th>Branch Date</th>
                                <!-- <th>Total Customer</th> -->
                                <!-- <th>Last Month Due</th>
                                <th>Total Due</th>
                                <th>Total Balance</th> -->
                                <th width="5%">LAG</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" style="text-align: right!important;"><b>Total</b></td>
                                <!-- <td style="text-align:center;" id="nCustomer"><b>0.00</b></td> -->
                                <!-- <td style="text-align:center;" id="tlmdue"><b>0.00</b></td>
                                <td style="text-align:center;" id="ttldue"><b>0.00</b></td>
                                <td style="text-align:center;" id="ttlbalance"><b>0.00</b></td> -->
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
                "url": "{{route('INVBranchStatus')}}",
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
                // {
                //     data: 'total_customer',
                //     className: 'text-center'
                // },
                // {
                //     data: 'last_m_due',
                //     className: 'text-right'
                // },
                // {
                //     data: 'total_due',
                //     className: 'text-right'
                // },
                // {
                //     data: 'total_balance',
                //     className: 'text-right'
                // },
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