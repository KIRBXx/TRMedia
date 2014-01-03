<div class="navbar navbar-default navbar-static-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
			<a class="navbar-brand" HREF="/"><img src="/social/images/logo.png"></a>        
			</div>
        <div class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li><a href="{{ url('gallery') }}">{{ ('Home') }}</a></li>
                <li><a href="{{ url('users') }}">{{ ('Members') }}</a></li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">{{ t('Categories') }}<b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        @foreach(siteCategories() as $category)
                        <li><a href="{{ url( 'category/'.$category->slug) }}">{{ $category->category }}</a></li>
                        @endforeach
                    </ul>
                </li>
                 <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">{{ t('Popular') }}<b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li><a href="{{ url('featured') }}">{{ t('Featured') }}</a></li>
                        <li><a href="{{ url('most/commented') }}">{{ t('Most Commented') }}</a></li>
                        <li><a href="{{ url('most/favorites') }}">{{ t('Most Favorites') }}</a></li>
                        <li><a href="{{ url('most/downloads') }}">{{ t('Most Downloads') }}</a></li>
                    </ul>
                </li>			
            </ul>

            <div class="col-lg-3 col-md-3 col-sm-3">
                <form class="navbar-form" role="search" method="GET" action="{{ url('search') }}">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="{{ t('Search') }} " name="q" id="srch-term">
                        <div class="input-group-btn">
                            <button class="btn btn-default" type="submit"><i class="glyphicon glyphicon-search"></i></button>
                        </div>
                    </div>
                </form>
            </div>
            <ul class="nav navbar-nav navbar-right">
                @if(Auth::check() == false)
                <li><a href="{{ url('login') }}">{{ t('Login') }}</a></li>
                <li><a href="{{ url('registration') }}">{{ t('Register') }}</a></li>
                @else
                <li><a href="{{ url('upload') }}">{{ t('Upload') }}</a></li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        @if(numberOfNotifications() > 0)
                        <span class="badge badge-danger">{{ numberOfNotifications() }}</span>
                        @endif &nbsp;
                        {{{ Str::words(Auth::user()->username,1,'') }}} <b class="caret"></b></a>
                    <ul class="dropdown-menu">
					    <li><a href="{{ url('user/'.Auth::user()->username) }}">{{ t('My Profile') }}</a></li>
                        <li><a href="{{ url('settings') }}">{{ t('Profile Settings') }}</a></li>
                        <li><a href="{{ url('feeds') }}">{{ t('Feeds') }}</a></li>
                        <li><a href="{{ url('notifications') }}">{{ t('Notifications') }}
                                @if(numberOfNotifications() > 0)
                                <span class="badge badge-danger">{{ numberOfNotifications() }}</span>
                                @endif
                            </a></li>
                        <li><a href="{{ url('logout') }}">{{ t('Logout') }}</a></li>
                    </ul>
                </li>


                @endif
            </ul>
        </div>
        <!--/.nav-collapse -->
    </div>
</div>