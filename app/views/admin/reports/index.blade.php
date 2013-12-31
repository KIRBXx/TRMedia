@extends('admin/master')

@section('container')
<h2>{{ $title }}</h2>
<hr>
<table class="table table-bordered table-hover table-striped tablesorter">
    <thead>
    <tr>
        <th>On id <i class="fa fa-sort"></i></th>
        <th>Reported <i class="fa fa-sort"></i></th>
        <th>Reported By <i class="fa fa-sort"></i></th>
        <th>Details <i class="fa fa-sort"></i></th>
        <th>Created <i class="fa fa-sort"></i></th>
        <th>Checked <i class="fa fa-sort"></i></th>
        <th>Read Full Report <i class="fa fa-sort"></i></th>
    </tr>
    </thead>
    <tbody>
    @foreach($reports as $report)
    <tr>
        @if($report->type == 'user')
        <td><a href="{{ url('user/'.$report->report)  }}">{{ Str::limit(url('user/'.$report->report),15)  }}</a></td>
        @endif
        @if($report->type == 'image')
        <td><a href="{{ url('image/'.$report->report) }}">{{ Str::limit(url('image/'.$report->report),15) }}</a></td>
        @endif
        <td>{{ $report->type }}</td>
        <td><a href="{{ url('user/'.$report->user->username) }}">{{ $report->user->username }}</a></td>
        <td>{{ $report->description }}</td>
        <td><abbr class="timeago" title="{{ date(DATE_ISO8601,strtotime($report->created_at)) }}">{{ date(DATE_ISO8601,strtotime($report->created_at)) }}</abbr></td>
        @if($report->created_at == $report->updated_at)
        <td>Unchecked</td>
        @else
        <td><abbr class="timeago" title="{{ date(DATE_ISO8601,strtotime($report->updated_at)) }}">{{ date(DATE_ISO8601,strtotime($report->updated_at)) }}</abbr></td>
        @endif
        <td><a href="{{ url('admin/report/'.$report->id) }}">Read Full</a></td>
    </tr>
    @endforeach

    </tbody>
</table>
@stop