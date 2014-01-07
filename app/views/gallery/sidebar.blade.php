            <div class="col-md-3">
			<h3 class="content-heading">{{ ('Search') }}</h3></div>
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

<div class="col-md-3">
    @if(getFeaturedUser()->count() >= 1)
    <div class="clearfix">
        <h3 class="content-heading">{{ ('Featured Member') }}</h3>

        <div class="imagesFromUser">
            @foreach(getFeaturedUser() as $featuredUser)
            <div class="col-md-12">
                <div class="row">

                    <a href="{{ url('user/'.$featuredUser->username) }}" class="thumbnail pull-left">
                        <img src="{{ avatar($featuredUser->avatar,69,69) }}" alt="{{ $featuredUser->fullname }}">
                    </a>

                    <div class="col-md-6 col-sm-6 col-xs-6">
                        <p><strong><a href="{{ url('user/'.$featuredUser->username) }}">{{ $featuredUser->username }}</a></strong></p>
                        @if(Auth::check())
                        @if(checkFollow($featuredUser->id))
                        <button class="btn btn-default btn-xs replyfollow follow" id="{{ $featuredUser->id }}">{{ ('UnFollow') }}</button>
                        @else
                        <button class="btn btn-default btn-xs replyfollow follow" id="{{ $featuredUser->id }}">{{ ('Follow') }}</button>
                        @endif
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
<iframe src="//www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2FTheRacersMedia&amp;width=250&amp;height=290&amp;colorscheme=light&amp;show_faces=true&amp;header=true&amp;stream=false&amp;show_border=false&amp;appId=504325102999321" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:250px; height:290px;" allowTransparency="true"></iframe>

    <div class="clearfix">
        <h3 class="block-heading">{{ t('More From') }} {{ siteSettings('siteName') }}</h3>
        <div class="more-from-site">
            @foreach(moreFromSite() as $sidebarImage)
            <a href="{{ url('image/'.$sidebarImage->id.'/'.$sidebarImage->slug) }}"><img src="{{ asset(zoomCrop('uploads/'.$sidebarImage->image_name.'.' . $sidebarImage->type ,70,70)) }}" alt="{{ $sidebarImage->title }}"/></a>
            @endforeach
        </div>
    </div>
</div>