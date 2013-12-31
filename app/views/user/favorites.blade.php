@extends('master/index')
@section('custom')

@include('user/rightsidebar')

<div class="col-md-9">
    <ul class="nav nav-tabs usernavbar">
        <li><a href="{{ url('user/'.$user->username) }}"><i class="glyphicon glyphicon-picture"></i> {{ t('Images Shared') }}</a></li>
        <li class="active"><a href="{{ url('user/'.$user->username.'/favorites') }}" class="active"><i class="glyphicon glyphicon-heart"></i> {{ t('Favorites') }}</a></li>
    </ul>
    <div class="gallery">
        @foreach($user->favorites as $image)
        <?php $image = $image->image; ?>
        @if($image->deleted_at == NULL AND $image->approved == 1)
        <div class="col-md-4 col-sm-4 gallery-display">
            <figure>
                <a href="{{ url('image/'.$image->id.'/'.$image->slug) }}"><img src="{{ asset(zoomCrop('uploads/'.$image->image_name. '.' . $image->type,350,263)) }}" alt="{{{ Str::limit(ucfirst($image->title),30) }}}"
                                                                               class="display-image"></a>
                <a href="{{ url('image/'.$image->id.'/'.$image->slug) }}" class="figcaption">
                    <h3>{{{ Str::limit(ucfirst($image->title),40) }}}</h3>
                    <span>{{{ Str::limit(ucfirst($image->image_description),80) }}}</span>
                </a>
            </figure>
            <div class="box-detail">
                <h5 class="heading"><a href="{{ url('image/'.$image->id.'/'.$image->slug) }}">{{{ Str::limit(ucfirst($image->title),20) }}}</a></h5>
                <ul class="list-inline gallery-details">
                    <li><a href="{{ url('user/'.$image->user->username) }}">{{{ ucfirst($image->user->username) }}}</a></li>
                    <li class="pull-right"><i class="fa fa-heart"></i> {{ $image->favorite()->count() }} <i class="fa fa-comments"></i> {{ $image->comments()->count() }}</li>
                </ul>
            </div>
        </div>
        @endif
        @endforeach
    </div>
</div>
@stop

@section('sidebar')
@stop