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

