<!doctype html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - {{ siteSettings('siteName') }}</title>
    @yield('metaDescription')
    <link rel="shortcut icon" href="{{ siteSettings('favIcon') }}" type="image/x-icon"/>
    <!--[if IE 8]>
    <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    {{ HTML::style('static/css/bootstrap.min.css',array('id'=>"colors")) }}
    {{ HTML::style('static/css/style.css') }}
    {{ HTML::style('static/css/admin.css') }}
    {{ HTML::style('static/css/jquery-ui.css') }}
    {{ HTML::style('static/css/jquery.fileupload.css') }}
    {{ HTML::style('static/css/jquery.fileupload-ui.css') }}
    {{ HTML::style('static/css/tagmanager.css') }}
    <link rel="stylesheet" href="http://cdn.oesmith.co.uk/morris-0.4.3.min.css">
    <link rel="stylesheet" href="http://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css"/>
    @yield('style')
</head>
<body>
@include('master/navbar')
@yield('carousel')

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <a href="{{ url('admin') }}" type="button" class="btn btn-info btn-xs">Back to admin panel</a>
            @include('master/notices')
            @yield('container')
        </div>
    </div>
    @include('master/footer')
</div>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
{{ HTML::script('static/js/jquery-ui.min.js') }}
{{ HTML::script('static/js/bootstrap.min.js') }}
{{ HTML::script('static/js/jquery.tablesorter.js') }}
{{ HTML::script('static/js/jquery.timeago.js') }}
{{ HTML::script('static/js/multiupload.js') }}
{{ HTML::script('static/js/custom.js') }}
{{ HTML::script('static/js/jquery.tablesorter.js') }}
@yield('extrafooter')
<script src="//cdnjs.cloudflare.com/ajax/libs/ckeditor/4.2/ckeditor.js"></script>
<script>
    $(function() {
        $("table").tablesorter({debug: true});
    });
</script>
@yield('chart-js')
</body>
</html>