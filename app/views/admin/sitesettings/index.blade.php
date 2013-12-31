@extends('admin/master')

@section('container')
<h3 class="content-heading">Site Settings</h3>
{{ Form::open() }}
@foreach($settings as $setting)
@if($setting->option != 'autoApprove' && $setting->option != 'numberOfImagesInGallery' && $setting->option != 'limitPerDay' && $setting->option != 'tagsLimit' && $setting->option != 'allowDownloadOriginal' && $setting->option != 'maxImageSize')
@if($setting->option != 'privacy' && $setting->option != 'faq' && $setting->option != 'tos' && $setting->option != 'about')
<div class="form-group">
    <label for="{{ $setting->option }}">{{ $setting->option }}</label>
    {{ Form::text($setting->option,$setting->value,array('class'=>'form-control')) }}
</div>
@endif
@if($setting->option == 'privacy' || $setting->option == 'faq' || $setting->option == 'tos' || $setting->option == 'about')
<div class="form-group">
    <label for="{{ $setting->option }}">{{ $setting->option }}</label>
    {{ Form::textarea($setting->option,htmlspecialchars($setting->value),array('class'=>'form-control ckeditor')) }}
</div>
@endif
@endif
@endforeach
{{ Form::submit('update',array('class'=>'btn btn-default')) }}


{{ Form::close() }}

@stop