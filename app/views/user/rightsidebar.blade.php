<div class="col-md-3">
<font size="5"><center><strong>{{{ $user->username }}}</strong></center></font>    
<a href="{{ url('user/'.$user->username) }}" class="thumbnail">
    <img src="{{ avatar($user->avatar,263,236) }}" alt="...">
</a>
<h2 class="profile-social">
    <a href="{{ url('user/'.$user->username.'/rss') }}" class="black entypo-rss" target="_blank"></a>
    @if(strlen($user->fb_link) > 2)
    <a href="{{ addhttp($user->fb_link) }}" class="black entypo-facebook" target="_blank"></a>
    @endif
    @if(strlen($user->tw_link) > 2)
    <a href="{{ addhttp($user->tw_link) }}" class="black entypo-twitter" target="_blank"></a>
    @endif
    @if(strlen($user->blogurl) > 2)
    <a href="{{ addhttp($user->blogurl) }}" class="black entypo-link" target="_blank"></a>
    @endif
</h2>
<hr>
@if(Auth::check() == true)
@if(Auth::user()->id == $user->id)
<a href="{{ url('settings') }}" type="button" class="btn btn-default btn-lg btn-block">{{ t('Edit My Profile') }}</a>
<a href="{{ url('user/'.Auth::user()->username.'/following') }}" type="button" class="btn btn-default btn-lg btn-block">{{ t("I'm following") }}</a>
@else
@if(checkFollow($user->id))
<a type="button" class="btn btn-default btn-lg btn-block follow" id="{{ $user->id }}">{{ t('Un Follow') }}</a>
@else
<a type="button" class="btn btn-default btn-lg btn-block follow" id="{{ $user->id }}">{{ t('Follow Me') }}</a>
@endif
@endif
<hr>
@endif

<div class="userdetails">
    <h3 class="content-heading">{{ $user->followers->count() }}&nbsp;&nbsp; {{ t('Followers') }}  <small class="pull-right"><a href="{{ url('user/'. $user->username. '/followers') }}">{{ t('See all') }}</a></small></h3>

    <div class="clearfix">
        <div class="imagesFromUser">
        <?php $i = 0; ?>
        @foreach($user->followers as $follower)
            <a href="{{ url('user/'.$follower->user->username) }}" class="pull-left userimage">
                <img src="{{ avatar($follower->user->avatar,69,69) }}" alt="{{ $follower->user->fullname }}" class="thumbnail">
            </a>
        <?php
        if ($i == 9)
            break;
        $i++;
        ?>
        @endforeach
        </div>
    </div>

    <h3 class="content-heading">{{ ('Stats') }}</h3>
    <p>{{ $user->numberOfImages }} {{ t('Images Shared') }}</p>
    <p>{{ $user->numberOfComments }} {{ t('Comments') }}</p>

    <h3 class="content-heading">{{ t('Most Used Tags') }}</h3>
    @foreach($mostUsedTags as $tag => $key)
    <a href="{{ url('tag/'.$tag) }}" class="tag"><span class="label label-info">{{{ $tag }}}</span></a>
    @endforeach

    @if(strlen($user->about_me) > 2)
    <h3 class="content-heading">{{ t('About Me') }}</h3>
    <p>{{{ $user->about_me }}}</p>
    @endif

    @if(strlen($user->country) == 2)
    <h3 class="content-heading">{{ t('Country') }}</h3>
    <p>{{ countryResolver($user->country) }}</p>
    @endif

</div>

@if(Auth::check())
@if(Auth::user()->id != $user->id)
<small><a href="{{ url('report/user/'.$user->username) }}">{{ t('Report') }}</a></small>
@endif
@endif
</div>