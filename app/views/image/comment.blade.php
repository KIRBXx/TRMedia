<div class="comments-block">
    <div class="col-md-12">
        <h3 class="block-heading">{{ t('Comments') }}</h3>

        @if(Auth::check() == true)
        {{ Form::open(array('files'=> TRUE,'role'=>'form')) }}
        <div class="form-group">
            {{ Form::textarea('comment','',array('class'=>"form-control",'rows'=>2,'placeholder'=>t('Comment'))) }}
        </div>
        <div class="form-group">
            {{ Form::submit(t('Comment'),array('class'=>'btn btn-info')) }}
        </div>
        {{ Form::close() }}
        <!--end of comment form-->
        @endif

        @foreach($comments as $comment)
        <div class="media">
            <a class="pull-left" href="{{ url('user/'.$comment->user->username) }}">
                <img class="media-object" src="{{ avatar($comment->user->avatar,75,75) }}" alt="{{ $comment->user->fullname }}" style="width: 64px; height: 64px;" src="">
            </a>
            <div class="media-body">
                <h4 class="media-heading"><a href="{{ url('user/'.$comment->user->username) }}">{{{ ucfirst($comment->user->username) }}}</a> <span class="pull-right"><i class="comment-time fa fa-clock-o"></i> <abbr class="timeago comment-time" title="{{ date(DATE_ISO8601,strtotime($comment->created_at)) }}">{{ date(DATE_ISO8601,strtotime($comment->created_at)) }}</abbr> </span></h4>
                <p>{{ Smilies::parse(e($comment->comment)) }}</p>

                @if(Auth::check() == TRUE)
                <a class="replybutton" id="box-{{ $comment->id }}">{{ t('Reply') }}</a>

                <div class="commentReplyBox" id="openbox-{{ $comment->id }}">
                    <input type="hidden" name="pid" value="19">
                    {{ Form::textarea('comment','',array('id'=>'textboxcontent'.$comment->id,'class'=>"form-control",'rows'=>2,'placeholder'=>t('Comment'))) }}
                    </br>
                    <button class="btn btn-info replyMainButton" id="{{ $comment->id }}">{{ t('Reply') }}</button>
                    <a class="closebutton" id="box-{{ $comment->id }}">{{ t('Cancel') }}</a>
                </div>
                @endif
                <!-- reply block stats here -->
                @foreach($comment->reply as $reply)
                <hr/>
                <div class="media" id="reply-{{ $reply->id }}">
                    <a class="pull-left" href="{{ url('user/'.$reply->user->username) }}">
                        <img class="media-object" src="{{ avatar($reply->user->avatar,64,64) }}" alt="{{ $reply->user->fullname }}" style="width: 64px; height: 64px;" src="">
                    </a>
                    <div class="media-body">
                        <h4 class="media-heading"><a href="{{ url('user/'.$reply->user->username) }}">{{{ ucfirst($reply->user->fullname) }}}</a> <span class="pull-right"><i class="comment-time fa fa-clock-o"></i> <abbr class="timeago comment-time" title="{{ date(DATE_ISO8601,strtotime($comment->created_at)) }}">{{ date(DATE_ISO8601,strtotime($reply->created_at)) }}</abbr> </span></h4>
                        <p>{{ Smilies::parse(e($reply->reply)) }}</p>
                    </div>
                </div>
                @endforeach
                <!-- reply block ends here -->
            </div>
            <hr/>
        </div>
        @endforeach

        <div class="row">
            {{ $comments->links(array('class'=>'pagination')) }}
        </div>
    </div>

    <!--.col-md-8-->
</div>
