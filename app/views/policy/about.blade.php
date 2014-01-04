@extends('master/index')

@section('content')
<h3 class="content-heading">{{ t('About Us') }}</h3>
<p>
    {{ siteSettings('about') }}
</p>

<div class="udb-box" data-id="1"></div>
@stop

