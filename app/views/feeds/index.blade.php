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

