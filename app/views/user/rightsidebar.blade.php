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
  @endif
@endif

@if(Auth::check())
@if(Auth::user()->id != $user->id)
<i class='glyphicon glyphicon-warning-sign'></i><a href="{{ url('report/user/'.$user->username) }}"> Report This User</a>
@endif
@endif
</div>
