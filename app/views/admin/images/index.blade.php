@extends('admin/master')

@section('container')
<h2>{{ $title }}</h2>
<hr>
<style type="text/css">
    td {
        vertical-align: middle!important;
    }
</style>
<div class="table-responsive">
    <table class="table table-bordered table-hover table-striped tablesorter">
        <thead>
        <tr>
            <th>#</th>
            <th>Original Name <i class="fa fa-sort"></i></th>
            <th>Title <i class="fa fa-sort"></i></th>
            <th>Uploaded By <i class="fa fa-sort"></i></th>
            <th>Favorites <i class="fa fa-sort"></i></th>
            <th>Downloads <i class="fa fa-sort"></i></th>
            <th>Created At <i class="fa fa-sort"></i></th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        @foreach($images as $image)
        <tr>
            <td><a href="{{ url('image/'.$image->id.'/'.$image->slug) }}"><img src="{{ asset(zoomCrop('uploads/'.$image->image_name. '.' . $image->type,69,69)) }}"/></a></td>
            <td>{{ $image->image_name }}.{{ $image->type }}</td>
            <td>{{ Str::limit($image->title,10) }}</td>
            <td><a href="{{ url('user/'.$image->user->username) }}">{{ Str::limit($image->user->fullname,15) }}</a></td>
            <td>{{ $image->favorite->count() }}</td>
            <td>{{ $image->downloads }}</td>
            <td><abbr class="timeago" title="{{ date(DATE_ISO8601,strtotime($image->created_at)) }}">{{ date(DATE_ISO8601,strtotime($image->created_at)) }}</abbr></td>
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
</div>
{{ $images->links() }}
@stop