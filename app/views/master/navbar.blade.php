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
            
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">{{ ('Other') }}<b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li><a href="#about" data-toggle="modal">{{ ('About TRM') }}</a></li>
						<li><a href="#donate" data-toggle="modal">{{ ('Donate') }}</a></li>
						<li><a href="#contact" data-toggle="modal">{{ ('Contact') }}</a></li>
					</li>
						</ul>
					</ul>
					
            <ul class="nav navbar-nav navbar-right">
                <li><a href="#" onClick="drawUploadModal()">{{ t('Upload') }}</a></li>
                @if(Auth::check() == false)
                <li><a href="{{ url('login') }}">{{ t('Login') }}</a></li>
                <li><a href="{{ url('registration') }}">{{ t('Register') }}</a></li>
                @else
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
@include('master/contact')
@include('master/donate')
@include('master/about')