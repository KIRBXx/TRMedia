@extends('admin/master')

@section('container')
<h2>{{ $title }}</h2>
<hr>
<h5>Reported {{ $report->type }}: {{ $report->report }}</h5>
<h5>Reported By: <a href="{{ url('user/'.$report->user->username) }}">{{ $report->user->username }}</a> <small>( {{ $report->user->fullname }} )</small> </h5>
<h5>Reported: <abbr class="timeago" title="{{ date(DATE_ISO8601,strtotime($report->updated_at)) }}">{{ date(DATE_ISO8601,strtotime($report->updated_at)) }}</abbr> </h5>
<hr>
<h4>Description</h4>
<hr>
<p>{{ $report->description }}</p>

@stop