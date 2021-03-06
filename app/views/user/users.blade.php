@extends('master/index')

@section('content')
<h3 class="content-heading">{{ ('Members')}} </h3>
@foreach($users as $user)
<div class="row">
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-4 col-sm-3 pull-left" style="margin-bottom:15px;min-wdth:100px">
                <a href="{{ url('user/'.$user->username) }}"><img class="thumbnail img-responsive" src="{{ avatar($user->avatar,114,114) }}"></a></div>
            <div class="col-md-8">
                <h3 style="margin-top:0px"><a href="{{ url('user/'.$user->username) }}">{{ ucfirst($user->username) }}</a>

                    <p>
                        <small><i class="glyphicon glyphicon-comment"></i> {{ $user->comments->count() }} comments &middot; <i class="glyphicon glyphicon-picture"></i> {{ $user->images->count() }} images</small>
                    </p>
                </h3>

                <p>{{ Str::limit($user->about_me,50) }}</p></div>
        </div>
    </div>
    @foreach($user->latestImages->take(3) as $image)
    <div class="col-md-2 col-sm-3 col-xs-3 hidden-xs">
        <a href="{{ url('image/'.$image->id.'/'.$image->slug) }}"><img src="{{ asset(zoomCrop('uploads/'.$image->image_name. '.' . $image->type,100,100)) }}" class="thumbnail"></a>
    </div>
    @endforeach
</div>
@endforeach

@stop

@section('sidebar')
@include('gallery/sidebar')
@stop

@section('pagination')
<div class="row">
    <div class="container">
        <div class="col-md-12">      {{ $users->links() }}</div>
    </div>
</div>
@stop

