@extends('master/index')


@section('content')

<div class="page-header">
    <h1>{{ $title }}</h1>
</div>
{{ Form::open() }}
<div class="form-group">
    <label for="report">Reason For Reporting</label>
    {{ Form::textarea('report','',array('class'=>'form-control','id'=>'username','placeholder'=>'Enter Some Details'))}}
</div>
{{ Form::submit('Report',array('class'=>'btn btn-default'))}}
{{ Form::close() }}

@stop

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-46572607-1', 'theracersmedia.com');
  ga('send', 'pageview');

</script>