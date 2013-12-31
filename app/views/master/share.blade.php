<ul class="action-bar clearfix">
    <li>
        <a href="https://twitter.com/intent/tweet?url={{ Request::url() }}" class="twitter" target="_blank">
            <span class="entypo-twitter"></span>
        </a>
    </li>
    <li>
        <a href="http://www.facebook.com/sharer/sharer.php?u={{ Request::url() }}" class="facebook" target="_blank">
            <span class="entypo-facebook"></span>
        </a>
    </li>
    <li>
        <a href="https://plusone.google.com/_/+1/confirm?hl=en&url={{ Request::url() }}" class="gplus" target="_blank">
            <span class="entypo-gplus"></span>
        </a>
    </li>
    <li>
        <a href="javascript:void(run_pinmarklet())" class="pintrest">
            <span class="entypo-pinterest"></span>
        </a>
    </li>
</ul>