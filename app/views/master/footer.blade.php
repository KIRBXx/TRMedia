<hr>
<footer>


    <p>&copy; {{ date("Y") }} {{ siteSettings('siteName') }}&nbsp;&middot;&nbsp;<a href="#policy" data-toggle="modal">{{ t('Privacy Policy') }}</a> · 						
	<a href="#faq" data-toggle="modal">{{ ('FAQ') }}</a>
&nbsp;&middot;&nbsp;<a

        @include('master/language')
		@include('master/faq')
		@include('master/policy')
    </p>
</footer>