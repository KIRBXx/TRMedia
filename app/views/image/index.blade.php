@extends('master/index')
@section('metaDescription')
@if(strlen($image->description) > 2)
<meta name="description" content="{{ $description }}">
@else
<meta name="description" content="{{ $image->title}} {{ siteSettings('description') }}">
@endif
<meta property="og:title" content="{{ ucfirst($image->title) }} - {{ siteSettings('siteName') }}"/>
<meta property="og:image" content="{{ asset(cropResize('uploads/'.$image->image_name. '.' . $image->type )) }}"/>
@stop


@section('content')

<h3 class="content-heading">{{{ ucfirst($image->title) }}}</h3>

<div class="main-image">
    <div class="controlArrow controlArrow-prev "><a href="{{ url('image/'.($image->id+1)) }}" class="fa fa-arrow-circle-left"></a></div>
    <div class="controlArrow controlArrow-next"><a href="{{ url('image/'.($image->id-1)) }}" class="fa fa-arrow-circle-right"></a></div>
    <p><img src="{{ asset(cropResize('uploads/'.$image->image_name. '.' . $image->type,1140,1140)) }}" alt="{{{ ucfirst($image->title) }}}" class="img-thumbnail"/></p>
</div> <!--.main-image-->

<div class="clearfix">
    <div class="image-details">
        <div class="col-md-8">
            <h3 class="block-heading">{{ t('Description') }} <span class="pull-right">
                            <div class="btn-group  btn-group-xs">
                                @if(checkFavorite($image->id) == true)
                                <button type="button" class="btn btn-danger favoritebtn" id="{{ $image->id }}"><i class="fa fa-heart"></i> {{ t('Un-Favorite') }}</button>
                                @else
                                <button type="button" class="btn  btn-success favoritebtn" id="{{ $image->id }}"><i class="fa fa-heart"></i> {{ t('Favorite') }}</button>
                                @endif

                                <button type="button" class="btn btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">
                                    {{ t('More') }}
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    @if(siteSettings('allowDownloadOriginal') == 1 || siteSettings('allowDownloadOriginal') == 'leaveToUser' && $image->allow_download == 1)
                                    <li>
                                        <a href="{{ url('download/'.Crypt::encrypt($image->id).'/'.Crypt::encrypt($image->slug)) }}">{{ t('Download Original') }}</a>
                                    </li>
                                    @endif
                                    <li><a href="{{ url('report/image/'.$image->id) }}">{{ t('Report') }}</a></li>
                                    @if(Auth::check() == true)
                                    @if(Auth::user()->id == $image->user_id)
                                    <li><a href="{{ url('delete/image/'.$image->id) }}">{{ t('Delete') }}</a></li>
                                    @endif
                                    @endif
                                </ul>
                                <!-- end of dropdown menu-->
                            </div>
                        </span></h3>

            <p>{{ nl2br(Smilies::parse(makeLinks(HTML::entities($image->image_description)))) }}</p>
        </div>
        <div class="col-md-4">
            <h3 class="block-heading">{{ t('Details') }}</h3>

            <div class="image-status">
                <ul class="list-inline">
                    <li><i class="fa fa-heart"></i> {{ $numberOfFavorites }}</li>
                    <li><i class="fa fa-comments"></i> {{ $numberOfComments }}</li>
                    <li><i class="fa fa-download"></i> {{ $image->downloads }}</li>
                </ul>
            </div>
        </div>
        <!-- .col-md-4 -->
    </div>
</div>
<!--.clearfix-->
@include('image/comment')
@stop

@section('sidebar')
@include('image/sidebar')
@stop

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-46572607-1', 'theracersmedia.com');
  ga('send', 'pageview');

</script>