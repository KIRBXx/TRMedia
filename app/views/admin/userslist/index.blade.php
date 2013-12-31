@extends('admin/master')

@section('container')

<h2>{{ $title }}</h2>
<hr>
<style type="text/css">
    td {
        vertical-align: middle!important;
    }
</style>
<table class="table table-bordered table-hover table-striped tablesorter">
    <thead>
    <tr>
        <th>Avatar </th>
        <th>username <i class="fa fa-sort"></i></th>
        <th>Full Name <i class="fa fa-sort"></i></th>
        <th>Email <i class="fa fa-sort"></i></th>
        <th>Registered On <i class="fa fa-sort"></i></th>
        <th>Last Login <i class="fa fa-sort"></i></th>
        <th>Status <i class="fa fa-sort"></i></th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    @foreach($users as $user)
    @if($user->deleted_at != NULL)
    <tr class="danger">
        @else
    <tr>
        @endif
        <td><img src="{{ avatar($user->avatar,40,40) }}" alt=""/></td>
        <td>{{ Str::limit($user->username,15) }}</td>
        <td>{{ Str::limit($user->fullname,15) }}</td>
        <td>{{ $user->email }}</td>
        <td><abbr class="timeago" title="{{ date(DATE_ISO8601,strtotime($user->created_at)) }}">{{ date(DATE_ISO8601,strtotime($user->created_at)) }}</abbr></td>
        <td><abbr class="timeago" title="{{ date(DATE_ISO8601,strtotime($user->updated_at)) }}">{{ date(DATE_ISO8601,strtotime($user->updated_at)) }}</abbr></td>
        @if($user->confirmed == 1)
        <td>@if(strtotime($user->updated_at) < strtotime('-30 days'))
            <span class="label label-default">Inactive</span>
            @else
            <span class="label label-success">Active</span>
            @endif
        </td>
        @else
        <td><span class="label label-danger">email activation required</span></td>
        @endif
        <td>
            <a class="btn btn-success" href="{{ url('user/'.$user->username) }}">
                <i class="glyphicon glyphicon-zoom-in"></i>
            </a>
            <a class="btn btn-info" href="{{ url('admin/edituser/'.$user->username) }}">
                <i class="glyphicon glyphicon-edit"></i>
            </a>
            </td>
    </tr>
    @endforeach

    </tbody>
</table>
{{ $users->links() }}
@stop