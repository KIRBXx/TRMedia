<hr>
<footer>
    <p class="pull-right"><A HREF="https://www.facebook.com/theracersmedia"><img src="/social/images/facebook.jpg"></A></p>
	<p class="pull-right"><A HREF="http://twitter.com/theracersmedia"><img src="/social/images/twitter.jpg"></A></p>

    <p>&copy; {{ date("Y") }} {{ siteSettings('siteName') }}&nbsp;&middot;&nbsp;<a href="{{ url('privacy') }}">{{ t('Privacy Policy') }}</a> · <a
            href="{{ url('tos') }}">{{ ('Friends') }}</a>&nbsp;&middot;&nbsp;<a
            href="{{ url('faq') }}">{{ t('FAQ') }}</a>&nbsp;&middot;&nbsp;<a
            href="{{ url('about') }}">{{ t('About Us') }}</a>&nbsp;&middot;&nbsp;<a
			href="{{ url('contact_us') }}">{{ 'Contact Us' }}</a>&nbsp;&middot;&nbsp;<a

        @include('master/language')
    </p>
</footer>