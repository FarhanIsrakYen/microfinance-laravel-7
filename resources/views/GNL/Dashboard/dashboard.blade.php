@extends('Layouts.erp_master')
@section('title', 'Garnish Technology')
@section('content')


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
</style>

<?php 
// use App\Services\CommonService as Common;
$today     = (new DateTime())->format('d M, Y');
?>

<!-- style="min-height: 100%" -->
<div class="w-full p-row minHeight">
    <br><br><br>
    <div class="row">
        <div class="col-lg-3">
            <a href="{{url('gnl/sys_user') }}" target="_blank">
                <div class="card voucher-card shadow text-center">
                    <div class="card-block" style="border-bottom: 4px solid #026466">
                        <h4 class="m-b-20 text-insta pt-4">System Users</h4>
                        <span class="dashCard2 shadow-lg" style="background: #026466;">
                            <i class="fa fa-file-text f-left text-white" style="font-size: 30px"></i>
                        </span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-3">
            <a href="{{url('gnl/sys_role') }}" target="_blank">
                <div class="card voucher-card shadow text-center">
                    <div class="card-block" style="border-bottom: 4px solid #026466">
                        <h4 class="m-b-20 text-insta pt-4">User Role</h4>
                        <span class="dashCard2 shadow-lg" style="background: #026466;">
                            <i class="fa fa-check-square-o f-left text-white" style="font-size: 30px"></i>
                        </span>
                    </div>
                </div>
            </a>
        </div>

        <!-- <div class="col-lg-3">
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
        </div> -->
    </div>

</div>

@endsection