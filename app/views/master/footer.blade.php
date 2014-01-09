<hr>
<footer>


    <p>&copy; {{ date("Y") }} {{ siteSettings('siteName') }}&nbsp;&middot;&nbsp;<a href="#policy" data-toggle="modal">{{ t('Privacy Policy') }}</a> · 						
	<a href="#rules" data-toggle="modal">{{ ('Rules') }}</a>
&nbsp;&middot;&nbsp;<a

        @include('master/language')
		@include('master/rules')
		@include('master/policy')
    </p>
</footer>