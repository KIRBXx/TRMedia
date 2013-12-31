@extends('admin/master')

@section('container')
<h2>{{ $title }}</h2>
<hr>
<style type="text/css">
    td {
        vertical-align: middle!important;
    }
</style>
<table class="table table-striped">
    <thead>
    <tr>
        <th>#</th>
        <th>Original Name</th>
        <th>Title</th>
        <th>Uploaded By</th>
        <th>Favorites</th>
        <th>Downloads</th>
        <th>Created At</th>
        <th>Approve</th>
        <th>Action</th>
    </tr>
    </thead>
    <tbody>
    @foreach($images as $image)
    <tr>
        <td><a href="{{ url('image/'.$image->id.'/'.$image->slug) }}"><img src="{{ asset(zoomCrop('uploads/'.$image->image_name. '.' . $image->type,69,69)) }}"/></a></td>
        <td>{{ $image->image_name }}.{{ $image->type }}</td>
        <td>{{ $image->title }}</td>
        <td><a href="{{ url('user/'.$image->user->username) }}">{{ $image->user->fullname }}</a></td>
        <td>{{ $image->favorite->count() }}</td>
        <td>{{ $image->downloads }}</td>
        <td><abbr class="timeago" title="{{ date(DATE_ISO8601,strtotime($image->created_at)) }}">{{ date(DATE_ISO8601,strtotime($image->created_at)) }}</abbr></td>
        <td>
            <button class="btn btn-xs btn-success adminImageApprove" id="{{ $image->id }}">Approve This</button>
        </td>
        <td>
            <a class="btn btn-success" href="{{ url('image/'.$image->id.'/'.$image->slug) }}">
                <i class="glyphicon glyphicon-zoom-in"></i>
            </a>
            <a class="btn btn-info" href="{{ url('admin/editimage/'.$image->id) }}">
                <i class="glyphicon glyphicon-edit"></i>
            </a></td>
    </tr>
    @endforeach

    </tbody>
</table>
{{ $images->links() }}
@stop