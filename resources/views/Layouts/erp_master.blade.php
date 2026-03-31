<!DOCTYPE html>
<html class="no-js css-menubar js-menubar disable-scrolling" lang="en">

<head>
    <?php
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    ?>
    <!-- Meta Start  -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="description" content="bootstrap admin template">
    <meta name="author" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title id="tabTitle">@yield('title')</title>

    <link rel="apple-touch-icon" href="{{ asset('assets/images/apple-touch-icon.png') }}">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{asset('assets/css/bootstrap-extend.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/site.min.css')}}">

    <!-- Plugins -->
    <link rel="stylesheet" href="{{asset('assets/vendor/animsition/animsition.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/vendor/switchery/switchery.min.css')}}">

    <!-- Plugins -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/flag-icon-css/flag-icon.min.css') }}">

    <!-- <link rel="stylesheet" href="{{asset('assets/vendor/chartist/chartist.min.css')}}"> -->
    <!-- <link rel="stylesheet" href="{{asset('assets/vendor/chartist-plugin-tooltip/chartist-plugin-tooltip.min.css')}}"> -->
    <link rel="stylesheet" href="{{asset('assets/vendor/aspieprogress/asPieProgress.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/vendor/jquery-selective/jquery-selective.min.css')}}">
    <!-- <link rel="stylesheet" href="{{asset('assets/vendor/bootstrap-datepicker/bootstrap-datepicker.min.css')}}"> -->
    <link rel="stylesheet" href="{{asset('assets/vendor/clockpicker/clockpicker.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/vendor/asscrollable/asScrollable.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/dashboard/team.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/apps/projects.min.css')}}">


    <!-- Fonts -->
    <link rel="stylesheet" href="{{asset('assets/fonts/web-icons/web-icons.min.css')}}">
    <link rel='stylesheet' href='http://fonts.googleapis.com/css?family=Roboto:300,400,500,300italic'>

    <script src="{{asset('assets/vendor/jquery/jquery.min.js')}}"></script>

    <!-- For jquery datepicker  -->
    <link rel="stylesheet" href="{{asset('assets/vendor/jquery-ui/jquery-ui.min.css')}}">
    <script src="{{asset('assets/vendor/jquery-ui/jquery-ui.min.js')}}"></script>

    <!-- Scripts -->
    <!-- You can set up your own media query breakpoints -->
    <script src="{{asset('assets/vendor/breakpoints/breakpoints.min.js')}}"></script>
    <script>
        Breakpoints();

    </script>

    <!-- JS Load  -->
    <script src="{{asset('assets/vendor/babel-external-helpers/babel-external-helpers.min.js')}}"></script>

    <script src="{{asset('assets/js/Plugin.min.js')}}"></script>

    <!-- font icon link  -->
    <link rel="stylesheet" href="{{asset('assets/fonts/font-awesome/font-awesome.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/fonts/brand-icons/brand-icons.min.css')}}">

    <!--- Form Validation Start --->
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/forms/form-validation.css')}}">
    <script src="{{asset('assets/js/forms/validator.min.js')}}"></script>
    <!--- Form Validation End --->

    <!-------------------- toastr start ---------------------->
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/toastr.min.css')}}">
    <script type="text/javascript" src="{{asset('assets/js/toastr.min.js')}}"></script>
    <!-------------------- toastr end ---------------------->


    <!-------------------- Sweetalert ---------------------->
    <script type="text/javascript" src="{{asset('assets/js/sweetalert.min.js')}}"></script>
    <!-- Meta End -->

    <!-- Select2 Option  -->
    <link href="{{asset('assets/css-js/select2/select2.min.css')}}" rel="stylesheet" />
    <script src="{{asset('assets/css-js/select2/select2.min.js')}}"></script>

    <!-- For File Upload input feild design  -->
    <script src="{{ asset('assets/js/Plugin/input-group-file.min.js')}}"></script>

    <!-- datatable -----  -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/datatables.net-bs4/dataTables.bootstrap4.min.css') }}">

    <script src="{{ asset('assets/vendor/datatables.net/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables.net-bs4/dataTables.bootstrap4.min.js') }}"></script>

    <!-- <script src="{{ asset('assets/js/Plugin/datatables.js') }}"></script> -->
    <!-- <script src="{{ asset('assets/js/tables/datatable.js') }}"></script> -->

    <!-- Notice Ticker  -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css-js/gn-notice-ticker/gn-notice-ticker.css') }}">
    <script src="{{ asset('assets/css-js/gn-notice-ticker/gn-notice-ticker.js') }}"></script>

    <!-- Custom Ajax JS FILE  -->
    <script src="{{asset('assets/js/custom-ajax.js')}}"></script>

    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/saiful-custom.css') }}">
    <link rel="stylesheet" href="{{asset('assets/css/custom.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/custom-print.css')}}">

    <!-- CSRF token added to ajax  -->
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

    </script>
</head>

<body class="animsition site-navbar-small dashboard">

    <div class="loader d-print-none">
        <div class="loader-cube-grid"></div>
    </div>

    <!-- Nav Start  modules-->
    @if($current_route_name=='/')
    @include('elements.topbar')
    @else
    @include('elements.navbar')
    @endif
    <!-- Nav End -->

    <!-- Notice Bar -->
    <?php
        use Carbon\Carbon;
        $branchID = Auth::user()->branch_id;
        $systemTime = (Carbon::now())->format('Y-m-d H:i');
        $activeNotice = DB::table('gnl_notice')
                        ->where([['is_delete',0], ['is_active',1]])
                        ->where(function ($activeNotice) use ($systemTime){
                            $activeNotice->where([['start_time','<=', $systemTime], ['end_time','>=',$systemTime]])
                                ->orWhere([['start_time', null], ['end_time', null]]);
                        })
                        ->where(function ($activeNotice) use ($branchID) {
                            $activeNotice->where('branch_id', 'LIKE', "%,{$branchID},%")
                                ->orWhere('branch_id', 'LIKE', "{$branchID},%")
                                ->orWhere('branch_id', 'LIKE', "%,{$branchID}")
                                ->orWhere('branch_id', 'LIKE', "{$branchID}");
                        })
                        ->select('notice_body')
                        ->get(); 
    ?>
    <!-- *********************** -->
    @if(count($activeNotice) > 0)
    <div class="gn-notice-show d-print-none" id="noticeTicker">
        <div class="gn-label">NOTICES</div>
        <div class="gn-notices">
            <ul>
                @foreach($activeNotice as $notice)
                <li><a href="javascript:void(0);"><i class="fa fa-align-justify"></i> {{ $notice->notice_body }}</a>
                </li>
                @endforeach
            </ul>
        </div>
        <div class="gn-controls">
            <button><span class="gn-arrow gn-prev"></span></button>
            <button><span class="gn-action"></span></button>
            <button><span class="gn-arrow gn-next"></span></button>
        </div>
    </div>
    @endif
    <!-- *********************** -->

    <?php
    $ignoreInnerLayout = [
        '/',
        'login',
        'modules',
        'gnl',
        'pos',
        'acc',
        'mfn',
        'fam',
        'inv',
        'proc',
        'bill',
        'hr'
    ];

    $routes = explode('/', $current_route_name);
    ?>

    @if(in_array($current_route_name, $ignoreInnerLayout))
    @yield('content')
    @else

    <div class="page forLoader">
        <div class="page-header d-print-none">
            <h4 class="" id="pageName"></h4>
            <ol class="breadcrumb text-uppercase">
                <li class="breadcrumb-item">
                    <a href="{{url('modules')}}">Home</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{url($routes[0]) }}">{{ $routes[0] }}</a>
                </li>
                <li class="breadcrumb-item" id="brActiveMenu">
                    <?php
                    if (isset($routes[2]) && ($routes[2] == 'add' || $routes[2] == 'edit' || $routes[2] == 'view')) {?>
                    <a href="{{ url('/'.$routes[0]. '/'. $routes[1] ) }}">{{ $routes[1] }}</a>
                    <?php
                    } else {
                        if(isset($routes[1])){
                        ?>
                    <a href="javascript:void(0)">{{ $routes[1] }}</a>
                    <?php
                    }
                    }
                    ?>
                </li>

                <?php
                    if (isset($routes[2]) && ($routes[2] == 'add' || $routes[2] == 'edit' || $routes[2] == 'view')) {
                        $TiText = '';
                        if ($routes[2] == 'add') {
                            $TiText = 'Entry';
                        } elseif ($routes[2] == 'edit') {
                            $TiText = 'Update';
                        } elseif ($routes[2] == 'view') {
                            $TiText = 'Details';
                        }
                        ?>
                <li class="breadcrumb-item active">{{ $TiText }}</li>
                <?php
                    }
                ?>
            </ol>

            <div class="page-header-actions">
                @foreach ($GlobalRole as $role)
                @if($role['set_status'] == 1)

                <a class="btn btn-sm btn-primary btn-outline btn-round" href="{{url($role['route_link'])}}">
                    <i class="icon wb-link" aria-hidden="true"></i>
                    <span class="hidden-sm-down">{{ $role['name'] }}</span>
                </a>

                @endif
                @endforeach

            </div>
        </div>

        <!--Error Message Show-->
        @if($errors->any())
        <div class="alert alert-danger">
            <strong>Oopps! </strong> Something went wrong.
            <ul>
                @foreach($errors->all() as $error)
                <li> {{ $error }} </li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="page-content">
            <div class="panel">
                <div class="panel-body">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    @endif
    
    <a id="back-to-top" href="#" class="btn btn-primary btn-lg back-to-top" role="button">
        <i class="fas fa-chevron-up"></i>
    </a>

    @include('elements.footer')

    <!-- Meta 2 -->
    <!-- Core  -->
    <!-- <script src="assets/vendor/babel-external-helpers/babel-external-helpers.js"></script>
       <script src="assets/vendor/jquery/jquery.js"></script> -->
    <script src="{{asset('assets/vendor/popper-js/umd/popper.min.js')}}"></script>
    <script src="{{asset('assets/vendor/bootstrap/bootstrap.min.js')}}"></script>
    <script src="{{asset('assets/vendor/animsition/animsition.min.js')}}"></script>
    <script src="{{asset('assets/vendor/mousewheel/jquery.mousewheel.min.js')}}"></script>
    <script src="{{asset('assets/vendor/asscrollbar/jquery-asScrollbar.min.js')}}"> </script>
    <script src="{{asset('assets/vendor/asscrollable/jquery-asScrollable.min.js')}}"></script>

    <!-- Plugins -->
    <!-- <script src="{{asset('assets/vendor/switchery/switchery.min.js')}}"></script> -->
    <script src="{{asset('assets/vendor/intro-js/intro.min.js')}}"></script>
    <script src="{{asset('assets/vendor/screenfull/screenfull.min.js')}}"></script>
    <script src="{{asset('assets/vendor/slidepanel/jquery-slidePanel.min.js')}}"></script>
    <!-- <script src="{{asset('assets/vendor/chartist/chartist.min.js')}}"></script> -->
    <!-- <script src="{{asset('assets/vendor/chartist-plugin-tooltip/chartist-plugin-tooltip.min.js')}}"></script> -->
    <script src="{{asset('assets/vendor/aspieprogress/jquery-asPieProgress.min.js')}}"></script>
    <script src="{{asset('assets/vendor/matchheight/jquery.matchHeight-min.js')}}"></script>
    <script src="{{asset('assets/vendor/jquery-selective/jquery-selective.min.js')}}"></script>
    <!-- <script src="{{asset('assets/vendor/bootstrap-datepicker/bootstrap-datepicker.min.js')}}"></script> -->
    <script src="{{asset('assets/vendor/clockpicker/bootstrap-clockpicker.min.js')}}"></script>

    <!-- Scripts -->
    <script src="{{asset('assets/js/Component.min.js')}}"></script>
    <!-- <script src="assets/js/Plugin.js"></script> -->
    <script src="{{asset('assets/js/Base.min.js')}}"></script>
    <script src="{{asset('assets/js/Config.min.js')}}"></script>

    <script src="{{asset('assets/js/Section/Menubar.min.js')}}"></script>
    <script src="{{asset('assets/js/Section/Sidebar.min.js')}}"></script>
    <script src="{{asset('assets/js/Section/PageAside.min.js')}}"></script>
    <script src="{{asset('assets/js/Plugin/menu.min.js')}}"></script>

    <!-- Config -->
    <script src="{{asset('assets/js/config/colors.min.js')}}"></script>
    <script src="{{asset('assets/js/config/tour.min.js')}}"></script>
    <!-- <script>Config.set('assets', '../assets');</script> -->

    <!-- Page -->
    <script src="{{asset('assets/js/Site.min.js')}}"></script>
    <script src="{{asset('assets/js/Plugin/asscrollable.min.js')}}"></script>
    <script src="{{asset('assets/js/Plugin/slidepanel.min.js')}}"></script>
    <script src="{{asset('assets/js/Plugin/switchery.min.js')}}"></script>
    <script src="{{asset('assets/js/Plugin/matchheight.min.js')}}"></script>
    <script src="{{asset('assets/js/Plugin/aspieprogress.min.js')}}"></script>
    <!-- <script src="{{asset('assets/js/Plugin/bootstrap-datepicker.js')}}"></script> -->
    <script src="{{asset('assets/js/Plugin/asscrollable.min.js')}}"></script>

    <script src="{{asset('assets/js/dashboard/team.min.js')}}"></script>

    <script src="{{ asset('assets/vendor/asrange/jquery-asRange.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/bootbox/bootbox.min.js') }}"></script>

    <!-- Custom JS FILE  -->
    <script src="{{asset('assets/js/custom-js.js')}}"></script>


    <script type="text/javascript">
        // toastr js \
        @if(Session::has('message'))

        var type = "{{Session::get('alert-type', 'info')}}";

        var console_error = "{{Session::get('console_error')}}";

        console.log(console_error);

        switch (type) {

            case 'info':
                toastr.info("{{Session::get('message')}}");
                break;
            case 'success':
                toastr.success("{{Session::get('message')}}");
                break;
            case 'warning':
                toastr.warning("{{Session::get('message')}}");
                break;
            case 'error':
                toastr.error("{{Session::get('message')}}");
                break;
        }
        @endif


        $(document).ready(function () {
            $('#noticeTicker').gnlNotices();
        });

        // ## Implement Start Date into Date Function
        $(document).ready(function () {
            var softStartDate =
                "{{ (new Datetime(App\Services\CommonService::getBranchSoftwareStartDate()))->format('Y-m-d') }}";
            $('.datepicker-custom:not(#customer_dob,#gr_dob,#emp_dob), .datepicker:not(#customer_dob,#gr_dob,#emp_dob)').datepicker("option", "minDate", new Date(softStartDate));
        });

    </script>
</body>

</html>
