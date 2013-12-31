@extends('admin/master')

@section('container')

<h2>Latest Comments</h2>
<hr>
<style type="text/css">
    td {
        vertical-align: middle!important;
    }
</style>
<table class="table table-bordered table-hover table-striped tablesorter">
    <thead>
    <tr>
        <th>Comment id  <i class="fa fa-sort"></i></th>
        <th>From User <i class="fa fa-sort"></i></th>
        <th>Comment <i class="fa fa-sort"></i></th>
        <th>On Image <i class="fa fa-sort"></i></th>
        <th>Created On <i class="fa fa-sort"></i></th>
    </tr>
    </thead>
    <tbody>
    @foreach($comments as $comment)
	@if($comment->image)
    <tr>
    <td>{{ $comment->id }}</td>
    <td><a href="{{ url('user/'.$comment->user->username) }}">{{ $comment->user->fullname }}</a></td>
    <td>{{{ Str::limit($comment->comment,30) }}}</td>
    <td><a href="{{ url('image/'.$comment->image->id.'/'.$comment->image->slug) }}">{{ Str::limit($comment->image->title,15) }}</a></td>
    <td><abbr class="timeago" title="{{ date(DATE_ISO8601,strtotime($comment->created_at)) }}">{{ date(DATE_ISO8601,strtotime($comment->created_at)) }}</abbr></td>
    </tr>
	@endif
    @endforeach
    </tbody>
</table>
{{ $comments->links() }}
@stop