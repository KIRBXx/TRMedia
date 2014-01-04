@extends('master/index')

@section('content')
<h3 class="content-heading">{{ t('Privacy Policy') }}</h3>
<p>
    {{ siteSettings('privacy') }}
</p>
@stop

