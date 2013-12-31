@extends('master/index')
@section('custom')

@include('user/rightsidebar')

<div class="col-md-9">
    <span id="links"></span>
    <ul class="nav nav-tabs usernavbar">
        <li class="active"><a href="{{ url('user/'.$user->username) }}"><i class="glyphicon glyphicon-picture"></i> {{ t('Images Shared') }}</a></li>
        <li><a href="{{ url('user/'.$user->username.'/favorites') }}" class="active"><i class="glyphicon glyphicon-heart"></i> {{ t('Favorites') }}</a></li>
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

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-46572607-1', 'theracersmedia.com');
  ga('send', 'pageview');

</script>