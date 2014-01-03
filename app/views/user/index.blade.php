@extends('master/index')
@section('custom')

@include('user/rightsidebar')

<div class="col-md-9">
    <span id="links"></span>
      <ul class="nav nav-tabs usernavbar">
          <li class="active"><a href="{{ url('user/'.$user->username) }}" class="active"><i class="glyphicon glyphicon-user"></i> User Info</a></li>
          <li><a href="{{ url('user/'.$user->username.'/shared') }}"><i class="glyphicon glyphicon-picture"></i> {{ t('Images Shared') }}</a></li>
          <li><a href="{{ url('user/'.$user->username.'/favorites') }}" class="active"><i class="glyphicon glyphicon-heart"></i> {{ t('Favorites') }}</a></li>
@if(Auth::check() == true)
  @if(Auth::user()->id == $user->id)
      <li class='pull-right'><a href="{{ url('user/'.Auth::user()->username.'/following') }}" class='btn btn-danger'>{{ t("I'm following") }}</a></li>
  @else
    @if(checkFollow($user->id))
      <li class='pull-right'><a href="#" class="follow btn btn-success" id="{{ $user->id }}">Unfollow Me</a>
    @else
      <li class='pull-right'><a href="#" class="follow btn btn-danger" id="{{ $user->id }}">Follow Me</a>
    @endif
  @endif
@endif
      </ul>
    <div class="row">
        <div class="container col-md-12">
          <div class="col-left col-md-6">
            <h3 class='content-heading'><a href="{{ url('settings') }}">Details</a></h3>
            <table class='table table-striped'>
            <tr><td><strong>User Name:</strong><td>{{$user->username}}</td></tr>
            @if(strlen($user->country) == 2) 
            <tr><td><strong>Country:</strong><td>{{ countryResolver($user->country) }}</td></tr>
            @else
            <tr><td><strong>Country:</strong><td></td></tr>
            @endif
            <tr><td><strong>Website:</strong><td>{{$user->blogurl}}</td></tr>
            </table>

            <h3 class='content-heading'>Stats</h3>
            <table class='table table-striped'>
            <tr><td><strong>Images Shared:</strong><td>{{ $user->numberOfImages }}</td></tr>
            <tr><td><strong>Comments:</strong><td>{{ $user->numberOfComments }}</td></tr>
            </table>
          </div>
          <div class="col-right col-md-6">
            <h3 class='content-heading'><a href="{{ url('settings') }}">About Me</a></h3>
            @if(strlen($user->about_me) > 2)
              <p>{{{ $user->about_me }}}</p>
            @else
              <p><i><center>(none)</center></i></p>
            @endif

            <h3 class="content-heading">{{ $user->followers->count() }}&nbsp;&nbsp; {{ t('Followers') }}  <small class="pull-right"><a href="{{ url('user/'. $user->username. '/followers') }}">{{ t('See all') }}</a></small></h3> 

            <h3 class="content-heading">{{ t('Most Used Tags') }}</h3>
            @foreach($mostUsedTags as $tag => $key)
              <a href="{{ url('tag/'.$tag) }}" class="tag"><span class="label label-info">{{{ $tag }}}</span></a>
            @endforeach

          </div>
        </div>
    </div>
</div>

@stop

@section('sidebar')
@stop

@section('pagination')
@stop

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-46572607-1', 'theracersmedia.com');
  ga('send', 'pageview');

</script>
