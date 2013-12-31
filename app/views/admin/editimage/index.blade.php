@extends('admin/master')

@section('container')
@foreach($image as $img)
<h3 class="content-heading">Editing Image</h3>
<div class="row">
    <div class="col-md-3">
        <img src="{{ asset(cropResize('uploads/'.$img->image_name. '.' . $img->type,200,200 )) }}" class="thumbnail"/>
    </div>
    <div class="col-md-7">
        <h4>Image Details</h4>
        <hr>
        <p><strong>Number Of Favorites </strong>{{ $img->favorite()->count() }}</p>
        <p><strong>Number Of Comments </strong>{{ numberOfComments($img->id) }}</p>
        <p><strong>Created By </strong>{{ $img->user->fullname }} <small><a href="{{ url('user/'.$img->user->username) }}">{{ $img->user->username}}</a></small></p>
        <p><strong>Created On </strong><abbr class="timeago" title="{{ date(DATE_ISO8601,strtotime($img->created_at)) }}">{{ date(DATE_ISO8601,strtotime($img->created_at)) }}</abbr></p>
        <p><strong>Updated At </strong><abbr class="timeago" title="{{ date(DATE_ISO8601,strtotime($img->updated_at)) }}">{{ date(DATE_ISO8601,strtotime($img->updated_at)) }}</abbr></p>

        @if($img->is_featured != null)
            <p><strong>THIS IS A FEATURED IMAGE</strong></p>
        @else
            <p><strong>This not a featured image</strong></p>
        @endif
        <hr>
        <h4>Editing Image</h4>
        <hr>
        <p>
            {{ Form::open() }}
            <p>Image Title</p>
            {{ Form::text('title',$img->title,array('class'=>'form-control')) }}
            <p>Image Description</p>
            {{ Form::textarea('description',$img->image_description,array('class'=>'form-control')) }}
            @if($img->is_featured == 1)
             <p>Remove From Featured {{ Form::radio('remove-featured', 'value') }}</p>
            @else
            <p>Make Featured {{ Form::radio('make-featured', 'value') }}</p>
             @endif
            Delete {{ Form::radio('delete', 'value') }} <br>
            {{ Form::submit('update image',array('class'=>'btn btn-default')) }}
            {{ Form::close()}}
        </p>

    </div>
</div>

@endforeach
@stop