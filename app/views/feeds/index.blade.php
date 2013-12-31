@extends('master/index')
@section('content')
<h3 class="content-heading">{{{ $title }}}</h3>
<div class="gallery">
    @foreach(array_chunk($images->getCollection()->all(),3) as $img)
    <div class="row">
        @foreach($img as $image)
        <div class="col-md-4 col-sm-4 gallery-display">
            <figure>
                <a href="{{ url('image/'.$image->imgid.'/'.$image->slug) }}"><img src="{{ asset(zoomCrop('uploads/'.$image->image_name. '.' . $image->type,350,263)) }}" alt="{{{ Str::limit(ucfirst($image->title),30) }}}"
                                                                                  class="display-image"></a>
                <a href="{{ url('image/'.$image->imgid.'/'.$image->slug) }}" class="figcaption">
                    <h3>{{{ Str::limit(ucfirst($image->title),30) }}}</h3>
                    <span>{{{ Str::limit(ucfirst($image->image_description),50) }}}</span>
                </a>
            </figure>
            <div class="box-detail">
                <h5 class="heading"><a href="{{ url('image/'.$image->imgid.'/'.$image->slug) }}">{{{ Str::limit(ucfirst($image->title),15) }}}</a></h5>
                <ul class="list-inline gallery-details">
                    <li><a href="{{ url('user/'.$image->username) }}">{{{ ucfirst($image->username) }}}</a></li>
                </ul>
            </div>
        </div>
        @endforeach
    </div>
    @endforeach
</div>
@stop
@section('pagination')
<div class="row">
    <div class="container">
        <div class="col-md-12"> {{ $images->links() }}</div>
    </div>
</div>
@stop

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-46572607-1', 'theracersmedia.com');
  ga('send', 'pageview');

</script>