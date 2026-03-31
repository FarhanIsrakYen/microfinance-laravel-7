<!DOCTYPE html>
<html>
  <head>
    <title>Login Page</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="apple-touch-icon" href="{{asset('assets/images/apple-touch-icon.png')}}">
    <link rel="shortcut icon" href="{{asset('assets/images/favicon.ico')}}">

     <!-- Stylesheets -->
    <link rel="stylesheet" href="{{asset('assets/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/bootstrap-extend.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/site.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/custom.css')}}">

    <!-- Plugins -->
    <link rel="stylesheet" href="{{asset('assets/vendor/animsition/animsition.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/pages/login-v2.min.css')}}">


    <!-- Fonts -->
    <!-- <link rel="stylesheet" href="{{asset('assets/fonts/web-icons/web-icons.min.css')}}"> -->
    <!-- <link rel="stylesheet" href="{{asset('assets/fonts/brand-icons/brand-icons.min.css')}}"> -->
    <!-- <link rel='stylesheet' href='http://fonts.googleapis.com/css?family=Roboto:300,400,500,300italic'> -->

    <link rel="stylesheet" type="text/css" href="{{url('style.css')}}">
    <!-- Scripts -->
    <script src="{{asset('assets/vendor/breakpoints/breakpoints.min.js')}}"></script>
    <script>
        Breakpoints();
    </script>
  </head>
  <body class="animsition page-login-v2 layout-full page-dark">

    <div class="page" data-animsition-in="fade-in" data-animsition-out="fade-out">
      <div class="page-content">
          <div class="page-brand-info">
              <div class="brand">
                  <img class="brand-img" src="{{asset('assets/images/logo@2x.png')}}" alt="...">
                  <h2 class="brand-text font-size-40">Garnish</h2>
              </div>
              <p class="font-size-20">

              </p>
          </div>

          <div class="page-login-main animation-slide-right animation-duration-1">
              <div class="brand hidden-md-up">
                  <img class="brand-img" src="{{asset('assets/images/logo-colored@2x.png')}}" alt="...">
                  <h3 class="brand-text font-size-40">Garnish ERP</h3>
              </div>
              <div class="brand d-flex justify-content-center">
                  <img class="brand-img" src="{{asset('assets//images/logo-colored.png')}}" alt="...">
                  <div><h2 class="brand-text font-size-18 teal-800">Garnish ERP</h2></div>
              </div>

              <form action="{{url('post-login')}}" method="POST" id="logForm">
                  {{ csrf_field() }}
                  <div class="form-label-group">
                      <input type="text" name="username" id="inputUsername" class="form-control" placeholder="Username" >
                      <label for="inputUsername">Username</label>
                      @if ($errors->has('username'))
                      <span class="error">{{ $errors->first('username') }}</span>
                      @endif
                  </div>
                  <div class="form-label-group">
                      <input type="password" name="password" id="inputPassword" class="form-control" placeholder="Password">
                      <label for="inputPassword">Password</label>
                      @if ($errors->has('password'))
                      <span class="error">{{ $errors->first('password') }}</span>
                      @endif
                    </div>
                  <div class="form-group clearfix">
                      <div class="checkbox-custom checkbox-inline checkbox-primary float-left">
                          <input type="checkbox" id="rememberMe" name="rememberMe">
                          <label for="rememberMe">Remember me</label>
                      </div>
                      <a class="float-right orange-800" href="forgot-password.html">Forgot password?</a>
                  </div>
                  <button type="submit" class="btn btn-block bg-teal-800 text-white btn-round">Log in</button>
              </form>

              <footer class="page-copyright">
                  <p><img class="brand-img" src="{{asset('assets/images/logo-colored@2x.png')}}" alt="Garnish Technology"></p>
                  <p>© {{date('Y')}}. All RIGHT RESERVED.</p>
              </footer>
          </div>
      </div>
    </div>

    <!-- Core  -->
    <script src="{{asset('assets/vendor/jquery/jquery.min.js')}}"></script>
    <script src="{{asset('assets/vendor/babel-external-helpers/babel-external-helpers.min.js')}}"></script>

    <script src="{{asset('assets/vendor/popper-js/umd/popper.min.js')}}"></script>
    <script src="{{asset('assets/vendor/bootstrap/bootstrap.min.js')}}"></script>
    <script src="{{asset('assets/vendor/animsition/animsition.min.js')}}"></script>
    



    <!-- Scripts -->
    <script src="{{asset('assets/js/Component.min.js')}}"></script>
    <script src="{{asset('assets/js/Plugin.min.js')}}"></script>
    <script src="{{asset('assets/js/Base.min.js')}}"></script>
    <script src="{{asset('assets/js/Config.min.js')}}"></script>

    <script src="{{asset('assets/js/Section/Menubar.min.js')}}"></script>
    <script src="{{asset('assets/js/Section/Sidebar.min.js')}}"></script>


    <!-- Page -->
    <script src="{{asset('assets/js/Site.min.js')}}"></script>

    <script src="{{asset('assets/js/dashboard/team.min.js')}}"></script>

    <!-------------------- toastr start ---------------------->
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/toastr.min.css')}}">
    <script type="text/javascript" src="{{asset('assets/js/toastr.min.js')}}"></script>
    <!-------------------- toastr end ---------------------->

    <script type="text/javascript">
            // toastr js \
            @if (Session::has('message'))

                var type = "{{Session::get('alert-type', 'info')}}";

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

        </script>
  </body>
</html>
