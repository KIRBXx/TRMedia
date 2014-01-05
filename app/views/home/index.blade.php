<!DOCTYPE html>
<html class="full" lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>The Racers Media - Motorsport Media Sharing</title>
    @yield('metaDescription')
    <link rel="shortcut icon" href="{{ siteSettings('favIcon') }}" type="image/x-icon"/>
    <!--[if IE 8]>
    <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:300italic,600italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css'>
    {{ HTML::style('static/css/bootstrap.min.css',array('id'=>"colors")) }}
    {{ HTML::style('static/css/jquery-ui.css') }}
    {{ HTML::style('static/css/jquery.fileupload.css') }}
    {{ HTML::style('static/css/jquery.fileupload-ui.css') }}
    {{ HTML::style('static/css/tagmanager.css') }}
    {{ HTML::style('static/css/style.css') }}
    <link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
    @yield('style')
    <style type="text/css">
        .full {
        @foreach(getFeaturedImage() as $featuredImage)
        background: url({{ asset(zoomCrop('uploads/'.$featuredImage->image_name.'.' . $featuredImage->type,1920,1080)) }}) no-repeat fixed;
        @endforeach
            -webkit-background-size: cover;
            -moz-background-size: cover;
            -o-background-size: cover;
            background-size: cover;
        }
    </style>
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
</head>

<body>

@include('master/navbar')


<div class="home-centerDiv">
    <h1>{{ siteSettings('siteName') }}</h1>
    <h3>{{ siteSettings('description') }}</h3>
    <a href="{{ url('gallery') }}" class="btn btn-info btn-lg">Browse Gallery</a>
    <a href="{{ url('login') }}" class="btn btn-info btn-lg">Login to Site</a>
</div>



<!-- javascript -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
{{ HTML::script('static/js/jquery-ui.min.js') }}
{{ HTML::script('static/js/bootstrap.min.js') }}
{{ HTML::script('static/js/jquery.timeago.js') }}
{{ HTML::script('static/js/bootstrap-datepicker.js') }}
{{ HTML::script('static/js/multiupload.js') }}
{{ HTML::script('static/js/custom.js') }}
@yield('extrafooter')
</body>
</html>

