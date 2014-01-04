@extends('master/index')

@section('content')

    <h3 class="content-heading">{{ t('Notifications') }}</h3>

    @foreach($notifications as $notice)
	@if($notice->user)
    <div class="media">
        <a class="pull-left" href="#">
            <img class="media-object" alt="{{{ $notice->user->fullname }}}" src="{{ avatar($notice->user->avatar,64,64) }}">
        </a>

        <div class="media-body">
            <h4 class="media-heading black"><a href="{{ url('user/'.$notice->user->username) }}">{{{ $notice->user->username }}}</a>
                <span class="msg-time pull-right">
				<i class="glyphicon glyphicon-time"></i>
				<span><small><abbr class="timeago comment-time" title="{{ date(DATE_ISO8601,strtotime($notice->created_at)) }}">{{ date(DATE_ISO8601,strtotime($notice->created_at)) }}</abbr>&nbsp;</small></span>
				</span>
            </h4>
            @if($notice->type == 'follow')
            <p>Started Following you</p>
            @elseif($notice->type == 'comment')
            <p>Commented on your image <a href="{{ url('image/'.$notice->image->id.'/'.$notice->image->slug) }}">{{{ ucfirst($notice->image->title) }}}</a></p>
            @elseif($notice->type == 'like')
            <p>Liked your image <a href="{{ url('image/'.$notice->image->id.'/'.$notice->image->slug) }}">{{{ ucfirst($notice->image->title) }}}</a></p>
            @elseif($notice->type == 'reply')
            <p>Replied on your comment <a href="{{ url('image/'.$notice->image->id.'/'.$notice->image->slug) }}">{{{ ucfirst($notice->image->title) }}}</a></p>
            @elseif($notice->type == 'follow')
            <p>Started Following Your</p>
            @endif

        </div>
    </div>
    <hr>
	@endif
    @endforeach
    {{ $notifications->links() }}
@stop

