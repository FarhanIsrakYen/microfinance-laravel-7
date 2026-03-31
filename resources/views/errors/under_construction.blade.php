
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Under Cnstruction</title>

<link href="https://fonts.googleapis.com/css?family=Muli:400" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Passion+One" rel="stylesheet">

<!-- <link type="text/css" rel="stylesheet" href="css/font-awesome.min.css" /> -->
<style type="text/css">
  * {
  -webkit-box-sizing: border-box;
          box-sizing: border-box;
}

body {
  padding: 0;
  margin: 0;
}

#notfound {
  position: relative;
  height: 100vh;
}

#notfound .notfound-bg {
  position: absolute;
  width: 100%;
  height: 100%;
  background-image: url({{asset("../resources/views/errors/image_2020_06_03T13_52_05_304Z.png")}});
  background-size: cover;
  /*background-position: center;*/
}

#notfound .notfound-bg:after {
  content: '';
  position: absolute;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.25);
}

#notfound .notfound {
  position: absolute;
  left: 60%;
  top: 50%;
  -webkit-transform: translate(-50%, -50%);
      -ms-transform: translate(-50%, -50%);
          transform: translate(-50%, -50%);
}


.notfound-search {
  position: relative;
  padding-right: 120px;
  max-width: 420px;
  width: 100%;
  margin: 30px auto 20px;
}

.notfound-search input {
  font-family: 'Muli', sans-serif;
  width: 100%;
  height: 40px;
  padding: 3px 15px;
  color: #fff;
  font-weight: 400;
  font-size: 18px;
  background: #222225;
  border: none;
}

.notfound-search button {
  font-family: 'Muli', sans-serif;
  position: absolute;
  right: 0px;
  top: 0px;
  width: 120px;
  height: 40px;
  text-align: center;
  border: none;
  background: #ff00b4;
  cursor: pointer;
  padding: 0;
  color: #fff;
  font-weight: 400;
  font-size: 16px;
  text-transform: uppercase;
}

/*.notfound a {
  font-family: 'Muli', sans-serif;
  display: inline-block;
  font-weight: 400;
  text-decoration: none;
  background-color: transparent;
  color: #222225;
  text-transform: uppercase;
  font-size: 14px;
}*/

.notfound a {
  font-family: 'Montserrat', sans-serif;
  font-size: 14px;
  text-decoration: none;
  text-transform: uppercase;
  background: #ff6300;
  display: inline-block;
  padding: 15px 30px;
  border-radius: 40px;
  color: #fff;
  font-weight: 700;
  -webkit-box-shadow: 0px 4px 15px -5px #0046d5;
          box-shadow: 0px 4px 15px -5px #0046d5;
}

.notfound-social {
  margin-bottom: 15px;
}
.notfound-social > a {
  display: inline-block;
  height: 40px;
  line-height: 40px;
  width: 40px;
  font-size: 14px;
  color: #fff;
  background-color: #222225;
  margin: 3px;
  -webkit-transition: 0.2s all;
  transition: 0.2s all;
}
.notfound-social>a:hover {
  color: #fff;
  background-color: #ff00b4;
}

@media only screen and (max-width: 480px) {
  .notfound .notfound-404 {
    height: 146px;
  }

  .notfound .notfound-404 h1 {
    font-size: 146px;
  }

  .notfound h2 {
    font-size: 22px;
  }
}
</style>
</head>
<body>
<div id="notfound">
  <div class="notfound-bg"></div>
  <div class="notfound">
    <!-- <div class="notfound-404">
      <h1>Oops!</h1>
    </div>
    <h2>Page Under Construction</h2> -->

    <a href="{{ url('/') }}" onclick="goBack()">Go To Home Page</a>
  </div>
</div>

<script async src="https://www.googletagmanager.com/gtag/js?id=UA-23581568-13" type="f9414ad47c9bcedb6e71416c-text/javascript"></script>
<script type="f9414ad47c9bcedb6e71416c-text/javascript">
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-23581568-13');
</script>
<script src="https://ajax.cloudflare.com/cdn-cgi/scripts/7089c43e/cloudflare-static/rocket-loader.min.js" data-cf-settings="f9414ad47c9bcedb6e71416c-|49" defer=""></script>
<script type="text/javascript">
        function goBack() {
          window.history.go(-1);
        }
      </script>
</body>
</html>
