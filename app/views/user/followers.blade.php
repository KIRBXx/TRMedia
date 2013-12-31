@extends('master/index')
@section('custom')
@include('user/rightsidebar')
<div class="col-md-9">
    <style>
        .follwing {
            margin-bottom: 10px;
        }
    </style>
    <h3 class="content-heading">{{ t('Users')}} I am following</h3>

    @foreach($user->followers as $follower)
    <div class="col-md-4 br-right follwing">
        <a href="{{ url('user/'.$follower->user->username) }}" class="pull-left user-profile-avatar">
            <img src="{{ avatar($follower->user->avatar,120,120) }}" alt="...">
        </a>
        <h4><a href="{{ url('user/'.$follower->user->username) }}">{{{ $follower->user->username }}}</a>
        </h4>
        @if(Auth::check() == true)
        @if(checkFollow($follower->user->id))
        <a type="button" class="btn btn-info btn-xs  follow" id="{{ $follower->user->id }}">{{ t('Un Follow') }}</a>
        @else
        <a type="button" class="btn btn-info btn-xs  follow" id="{{ $follower->user->id }}">{{ t('Follow Me') }}</a>
        @endif
        @endif
    </div>

    @endforeach
</div>
@stop
@section('sidebar')
@stop