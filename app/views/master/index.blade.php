<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{{ $title }}} - {{ siteSettings('siteName') }}</title>
    @yield('metaDescription')
    <link rel="shortcut icon" href="{{ siteSettings('favIcon') }}" type="image/x-icon"/>
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:300italic,600italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css'>
    {{ HTML::style('static/css/bootstrap.min.css',array('id'=>"colors")) }}
    {{ HTML::style('static/css/jquery-ui.css') }}
    {{ HTML::style('static/css/datepicker.css') }}
    {{ HTML::style('static/css/blueimp-gallery.min.css') }}
    {{ HTML::style('static/css/jquery.fileupload.css') }}
    {{ HTML::style('static/css/jquery.fileupload-ui.css') }}
    {{ HTML::style('static/css/tagmanager.css') }}
    {{ HTML::style('static/css/style.css') }}
    <link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
    @yield('style')
	<link href="//theracersmedia.com/donation-box/css/udb.css?ver=1.50" rel="stylesheet">
	<link href="http://www.theracersmedia.com/layered-popups/css/style.css?ver=1.65" rel="stylesheet" type="text/css"> 
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>

      <div id='warning'>
        You are using a version of Internet Explorer which is no longer supported.
        Some features may not work correctly. Upgrade to a modern browser, such as
        <a href='https://www.google.com/chrome'>Chrome</a> or <a href='http://getfirefox.com'>Firefox</a>.
      </div>
    <![endif]-->
</head>
<body>
@include('master/navbar')
<div class="container">
    <div class="row">
        @yield('custom')
        <div class="col-md-9">
            @include('master/notices')
            @yield('content')
        </div>
        @section('sidebar')
        @include('gallery/sidebar')
        @show
    </div>
    @yield('pagination')
    @include('master/footer')
</div>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
{{ HTML::script('static/js/jquery-ui.min.js') }}
{{ HTML::script('static/js/bootstrap.min.js') }}
{{ HTML::script('static/js/blueimp-gallery.min.js') }}
{{ HTML::script('static/js/jquery.timeago.js') }}
{{ HTML::script('static/js/bootstrap-datepicker.js') }}
{{ HTML::script('static/js/multiupload.js') }}
{{ HTML::script('static/js/custom.js') }}

@yield('extrafooter')
<script src="//theracersmedia.com/donation-box/js/udb-jsonp.js?ver=1.50"></script>
<script src="http://www.theracersmedia.com/layered-popups/js/ulp-jsonp.js?ver=1.65"></script>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-46572607-1', 'theracersmedia.com');
  ga('send', 'pageview');

</script>
</body>
</html>
