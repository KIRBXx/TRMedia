@extends('master/index')

@section('content')
<h3 class="content-heading">{{ t('FAQ') }}</h3>
<p>
    {{ siteSettings('faq') }}
</p>
@stop

