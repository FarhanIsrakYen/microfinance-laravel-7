<?php 
use App\Services\CommonService as Common;
?>

<nav class="site-navbar navbar navbar-default navbar-fixed-top navbar-mega navbar-inverse d-print-none" role="navigation">
    <div class="navbar-header">
        <button type="button" class="navbar-toggler hamburger hamburger-close navbar-toggler-left hided"
            data-toggle="menubar">
            <span class="sr-only">Toggle navigation</span>
            <span class="hamburger-bar"></span>
        </button>
        <button type="button" class="navbar-toggler collapsed" data-target="#site-navbar-collapse"
            data-toggle="collapse">
            <i class="icon wb-more-horizontal" aria-hidden="true"></i>
        </button>
        <a class="navbar-brand navbar-brand-center" href="{{url('/')}}">
            <img class="navbar-brand-logo navbar-brand-logo-normal" src="{{asset('assets/images/logo.png')}}"
                title="Logo">
            <span>Garnish ERP</span>
        </a>
    </div>

    <div class="navbar-container container-fluid">

        <div class="collapse navbar-collapse navbar-collapse-toolbar" id="site-navbar-collapse">

            <div class="row">
                <div class="col-xl-11 col-lg-11 col-sm-11 col-md-11 col-11 top-heading-branch-data">
                    <!-- <span>
                        <b style="font-family: sans-serif;">
                            Date:
                        </b>
                        {!! Common::systemCurrentDate() !!}
                    </span> -->
                    <!-- &nbsp; -->
                    <span>
                        <b style="font-family: sans-serif;">
                            Branch:
                        </b>
                        <?php 
                            $BranchName = Common::ViewTableLast('gnl_branchs',['id'=> Common::getBranchId(), 'is_approve' => 1], ['branch_name']);

                            if($BranchName){
                                echo $BranchName->branch_name;
                            }
                            ?>
                    </span>
                </div>

                <div class="col-xl-1 col-lg-1 col-sm-1 col-md-1 col-1">
                    <ul class="nav navbar-toolbar navbar-right navbar-toolbar-right">
                        <li class="nav-item dropdown">
                            <a class="nav-link navbar-avatar" data-toggle="dropdown" href="#" aria-expanded="false"
                                data-animation="scale-up" role="button">
                                <span class="avatar avatar-online">
                                    <img src="{{asset('assets/images/portraits/5.jpg')}}" alt="...">
                                    <i></i>
                                </span>
                            </a>
                            <div class="dropdown-menu" role="menu">
                                <a class="dropdown-item" href="javascript:void(0)" role="menuitem"><i
                                        class="icon wb-user" aria-hidden="true"></i>{{ Auth::user()->full_name }}</a>
                                <div class="dropdown-divider" role="presentation"></div>
                                <a class="dropdown-item" href="{{url('logout')}}" role="menuitem">
                                    <i class="icon wb-power" aria-hidden="true"></i> Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>

            </div>

        </div>


    </div>
</nav>