<div class="col-md-3">

    <div class="clearfix">
        <h4>
            <span>Featured Image</span>
        </h4>
        <hr>
        <div class="imagesFromUser">
            @foreach(getFeaturedImage() as $userimage)
            <div class="col-sm-11 col-md-12 userimage">
                <a href="{{ url('image/'.$userimage->id.'/'.$userimage->slug) }}" class="thumbnail">
                    <img src="{{ asset(zoomCrop('uploads/'.$userimage->image_name.'.' . $userimage->type,300,300)) }}"
                         alt="{{ $userimage->title }}">
                </a>
            </div>
            @endforeach
        </div>
    </div>


    <div class="clearfix">
        <h4>
            <span>More From {{ siteSettings('siteName') }}</span>
        </h4>
        <hr>
        <div class="imagesFromUser">
            @foreach(moreFromSite() as $userimage)
            <div class="col-xs-2 col-sm-2 col-md-5 userimage">
                <a href="{{ url('image/'.$userimage->id.'/'.$userimage->slug) }}" class="thumbnail">
                    <img src="{{ asset(zoomCrop('uploads/'.$userimage->image_name. '.' . $userimage->type,100,100)) }}"
                         alt="{{ $userimage->title }}">
                </a>
            </div>
            @endforeach
        </div>
    </div>
</div>

