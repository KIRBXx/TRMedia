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

@foreach($user->following as $follower)
<div class="col-md-4 br-right follwing">
    <a href="{{ url('user/'.$follower->followingUser->username) }}" class="pull-left user-profile-avatar">
        <img src="{{ avatar($follower->followingUser->avatar,120,120) }}" alt="...">
    </a>
    <h4>{{{ $follower->followingUser->username }}}<br>
    </h4>
    @if(Auth::check() == true)
    @if(checkFollow($follower->followingUser->id))
    <a type="button" class="btn btn-info btn-xs  follow" id="{{ $follower->followingUser->id }}">{{ ('Un Follow') }}</a>
    @else
    <a type="button" class="btn btn-info btn-xs  follow" id="{{ $follower->followingUser->id }}">{{ ('Follow') }}</a>
    @endif
    @endif
</div>

@endforeach
</div>
@stop
@section('sidebar')
@stop
