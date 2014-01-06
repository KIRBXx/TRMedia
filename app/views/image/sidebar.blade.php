<div class="col-md-3">

    <h3 class="block-heading">{{ t('Author') }}</h3>

    <div class="image-author">
        <img src="{{ avatar($image->user->avatar,80,80) }}" alt=""/>
        <a href="{{ url('user/'.$image->user->username) }}">{{ ucfirst($image->user->username) }}</a>

        <p>
            <small>-</small>
        </p>
        @if(Auth::check() == false)
        <button class="btn btn-info btn-xs replyfollow follow" id="{{ $image->user->id }}">Follow Me
        </button>
        @else
        @if(Auth::user()->id == $image->user->id)
        <a class="btn btn-success btn-xs" href="{{ url('settings') }}">Edit Profile</a>
        @else
        <button class="btn btn-default btn-xs replyfollow follow" id="{{ $image->user->id }}">Follow Me
        </button>
        @endif
        @endif
    </div>
    
    <h3 class="block-heading">Image Source</h3>
      <div class="image-source">
        @if($image->source)
          {{ $image->source }}
        @else
          No Source Provided
        @endif
      </div>

    <h3 class="block-heading">Tags</h3>
    <ul class="list-inline taglist">
        @foreach(explode(',',$image->tags) as $tag)
        <li><a href="{{ url('tag/'.$tag) }}" class="tag"><span
                    class="label label-info">{{{ $tag }}}</span></a></li>
        @endforeach
    </ul>


    <h3 class="block-heading">{{ t('More From') }} {{ siteSettings('siteName') }}</h3>

    <div class="clearfix">
        <div class="more-from-site">
            @foreach(moreFromSite() as $sidebarImage)
            <a href="{{ url('image/'.$sidebarImage->id.'/'.$sidebarImage->slug) }}"><img src="{{ asset(zoomCrop('uploads/'.$sidebarImage->image_name.'.' . $sidebarImage->type ,70,70)) }}"
                                                                                         alt="{{ $sidebarImage->title }}"/></a>
            @endforeach
        </div>
    </div>

    @if($image->favorite->count() >= 1)
    <!-- DIMPLY USERS WHO FAVORITE THIS IMAGE -->
    <h3 class="block-heading">{{ ('Favorited By') }}
        <small class="pull-right">{{ $image->favorite->count() }}</small>
    </h3>
    <div class="clearfix">
        <div class="more-from-site">
            @foreach($image->favorite()->take(16)->get() as $sidebarImage)
            <a href="{{ url('user/'.$sidebarImage->user->username) }}"><img src="{{ avatar($sidebarImage->user->avatar,70,70) }}" alt="{{ $sidebarImage->user->fullname }}"/></a>
            @endforeach
        </div>
    </div>
    @endif

</div>
