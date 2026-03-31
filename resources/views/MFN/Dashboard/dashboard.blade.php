@extends('Layouts.erp_master')
@section('title', 'MFN Dashboard')
@section('content')
{{-- <style>
    .full-height {
        height: 100vh;
    }

    .flex-center {
        align-items: center;
        display: flex;
        justify-content: center;
    }

    .position-ref {
        position: relative;
    }

    .top-right {
        position: absolute;
        right: 10px;
        top: 18px;
    }

    .content {
        text-align: center;
    }

    .title {
        font-size: 44px;
    }
     .links > a {
        color: #636b6f;
        padding: 0 25px;
        font-size: 13px;
        font-weight: 600;
        letter-spacing: .1rem;
        text-decoration: none;
        text-transform: uppercase;
    }
</style>
<div class="flex-center position-ref full-height">
    <div class="content">
        <div class="title m-b-md">
            Micro Finance Dashboard
        </div>
        <div class="links">
            <a href="{{ url('gnl') }}">General Configuration</a>
            <a href="{{ url('pos') }}">POS</a>
            <a href="{{ url('acc') }}">ACC</a>
            <a href="{{ url('hr') }}">HR & Payroll</a>
            <a href="{{ url('mfn') }}">Micro Finance</a>
            <a href="{{ url('fam') }}">Fixed Asset Management</a>
            <a href="{{ url('inv') }}">Inventory</a>
            <a href="{{ url('proc') }}">Procurement</a>
        </div>
    </div>
</div>
@endsection --}}


{{-- @extends('Layouts.erp_master')
@section('title', 'MicroFinance')
@section('content') --}}

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

<div class="w-full p-row minHeight">
    <h3 class="text-center pb-25">Microfinance DASHBOARD</h3>

    <ul class="nav nav-tabs" id="TabID">
        <li class="nav-item">
            <a href="#mfnHomeID" class="nav-link" data-toggle="tab">Home</a>
        </li>
        <li class="nav-item">
            <a href="#BranchStatusID" id="branch_tab" class="nav-link" data-toggle="tab">Branch Status</a>
        </li>
    </ul>

    <div class="tab-content" style="background:none;">
        <!-- MicroFinance Configuration  -->
        <div class="tab-pane fade" id="mfnHomeID">
            <div class="row">
                {{-- <div class="col-md-4 col-xl-3">
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
                </div> --}}
            </div>
        </div>
        <!-- End MicroFinance Configuration  -->

        <div class="tab-pane fade" id="BranchStatusID">
            <div class="card reverse row" id='branch-status-table'>
                
            </div>
        </div>

    </div>
</div>
<script>
var isBranchStatusCalled = false;
$(document).ready(function() {

    $('#branch_tab').click(function() {
        if(isBranchStatusCalled == false){
            $('#spinnerId').show();
            $("#branch-status-table").load("{{ route('mfnBranchStatus') }}"
            , function(response, status, xhr){

                if (status == 'success') {                
                    isBranchStatusCalled = true;
                }
                else{
                    alert('error');
                    $('#spinnerId').hide();
                }
            });
        }

        
    });
    $("#TabID li:eq(0) a").tab('show');
});
</script>
@endsection