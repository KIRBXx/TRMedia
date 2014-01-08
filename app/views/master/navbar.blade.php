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
						<li><a href="{{ url('/contact_us') }}">{{ ('Contact Us') }}</a></li>
						<li><a href="#donate" data-toggle="modal">{{ ('Donate') }}</a></li>
					</li>
						</ul>
					</ul>
					
            <ul class="nav navbar-nav navbar-right">
                @if(Auth::check() == false)
                <li><a href="{{ url('login') }}">{{ t('Login') }}</a></li>
                <li><a href="{{ url('registration') }}">{{ t('Register') }}</a></li>
                @else
                <li><a href="#" onClick="drawUploadModal()">{{ t('Upload') }}</a></li>
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
								  <!-- ABOUT US Modal -------------------------------------------------------------------------------->
								  <div class="modal fade" id="about" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
									<div class="modal-dialog">
									  <div class="modal-content">
										<div class="modal-header navbar-default">
										  <button type="button" style="color: #fff" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
										  <h4 class="modal-title" style="color: #fff">About The Racers Media</h4>
										</div>
										<div class="modal-body">
										  <p>The Racers Media is a Motorsport Media Sharing Website!<br> At The Racers Media, you can share Motorsport Images from the past or present!</p>
										  <ul>
											<li><strong>Founder - </strong>The Stigs 16th Cousin, Jimbo Stiggels</li>
											<li><strong>Contact Email - </strong>main@theracersmedia.com</li>
											<li><strong>Launch Date - </strong>November 23rd, 2013</li>
										</div>
										<div class="modal-footer">
										  <a href="http://twitter.com/theracersmedia"><img src="/social/images/twitter.png"></a>										  
										  <a href="http://facebook.com/theracersmedia"><img src="/social/images/facebook.png"></a>
										</div>
									  </div><!-- /.modal-content -->
									</div><!-- /.modal-dialog -->
								  </div><!-- ABOUT US/.modal ---------------------------------------------------------------------------->
							
								
								<!-- DONATE Modal -------------------------------------------------------------------------------->
								  <div class="modal fade" id="donate" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
									<div class="modal-dialog">
									  <div class="modal-content">
										<div class="modal-header navbar-default">
										  <button type="button" style="color: #fff" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
										  <h4 class="modal-title" style="color: #fff">Donate to The Racers Media</h4>
										</div>
										<div class="modal-body">
										<div class="udb-box" data-id="1"></div>
										</div>
										<div class="modal-footer">
										  <a href="http://twitter.com/theracersmedia"><img src="/social/images/twitter.png"></a>										  
										  <a href="http://facebook.com/theracersmedia"><img src="/social/images/facebook.png"></a>
										</div>
									  </div><!-- /.modal-content -->
									</div><!-- /.modal-dialog -->
								  </div><!-- DONATE/.modal ---------------------------------------------------------------------------->
