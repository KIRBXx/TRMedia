@extends('master/index')
@section('custom')

@include('user/rightsidebar')

<div class="col-md-9">
    <span id="links"></span>
<ul class="nav nav-tabs usernavbar">
          <li><a href="{{ url('user/'.$user->username) }}" class="active"><i class="glyphicon glyphicon-user"></i> User Info</a></li>
          <li class="active"><a href="{{ url('user/'.$user->username.'/shared') }}"><i class="glyphicon glyphicon-picture"></i> {{ t('Images Shared') }}</a></li>
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
    <div class="gallery">
        @foreach(array_chunk($images->getCollection()->all(),3) as $img)
        <div class="row">
            @foreach($img as $image)
            @if($image->deleted_at == NULL AND $image->approved == 1)
            <div class="col-md-4 col-sm-4 gallery-display">
                <figure>
                    <a href="{{ url('image/'.$image->id.'/'.$image->slug) }}"><img src="{{ asset(zoomCrop('uploads/'.$image->image_name. '.' . $image->type,350,263)) }}"
                                                                                   alt="{{{ Str::limit(ucfirst($image->title),30) }}}"
                                                                                   class="display-image"></a>
                    <a href="{{ url('image/'.$image->id.'/'.$image->slug) }}" class="figcaption">
                        <h3>{{{ Str::limit(ucfirst($image->title),40) }}}</h3>
                        <span>{{{ Str::limit(ucfirst($image->image_description),80) }}}</span>
                    </a>
                </figure>
                <div class="box-detail">
                    <h5 class="heading"><a href="{{ url('image/'.$image->id.'/'.$image->slug) }}">{{{ Str::limit(ucfirst($image->title),15) }}}</a></h5>
                    <ul class="list-inline gallery-details">
                        <li><a href="{{ url('user/'.$image->user->username) }}">{{{ ucfirst($image->user->username) }}}</a></li>
                        <li class="pull-right"><i class="fa fa-heart"></i> {{ $image->favorite()->count() }} <i class="fa fa-comments"></i> {{ $image->comments()->count() }}
                            <span id="links"><a href="{{ asset(cropResize('uploads/'.$image->image_name. '.' . $image->type,1140,1140)) }}" title="{{{ ucfirst($image->title) }}}" data-gallery data-description="{{{ $image->image_description }}}"><i class="fa fa-external-link"></i></a></span>
                        </li>
                    </ul>
                </div>
            </div>
            @endif
            @endforeach
        </div>
        @endforeach
        <!-- Gallery navigation buttons -->
        <div id="blueimp-gallery" class="blueimp-gallery">
            <div class="slides"></div>
            <h3 class="title"></h3>
            <p class="description"></p>
            <a class="prev">‹</a>
            <a class="next">›</a>
            <a class="close">×</a>
            <a class="play-pause"></a>
            <ol class="indicator"></ol>
        </div> <!--.blueimp-gallery-->
    </div>

    <div class="row">
        <div class="container">
            <div class="col-md-12"> {{ $images->links() }}</div>
        </div>
    </div>
</div>

@stop

@section('sidebar')
@stop

@section('pagination')
@stop

