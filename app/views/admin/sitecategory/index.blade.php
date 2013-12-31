@extends('admin/master')

@section('container')
<h3 class="content-heading">Site Category Settings</h3>

{{ Form::open() }}
<div class="form-group">
    <label for="addnew">Add New Category</label>
    {{ Form::text('addnew','',array('class'=>'form-control','placeholder'=>'Name of category')) }}
</div>
<div class="form-group">
    {{ Form::submit('Add new category',array('class'=>'btn btn-default')) }}
</div>
{{ Form::close() }}


<div class="page-header">
    <h3 class="content-heading">Current Categories</h3>
</div>
<ul class="list-group">
    @foreach(siteCategories() as $category)
    <li class="list-group-item">
        {{ $category->category }}
    </li>
    @endforeach
</ul>

@stop