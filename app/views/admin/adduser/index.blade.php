@extends('admin/master')

@section('container')

<h3 class="content-heading">Add Real/Fake User</h3>
{{ Form::open() }}

<div class="form-group">
    <label for="username">Username</label>
    {{ Form::text('username','',array('class'=>'form-control','placeholder'=>'Choose unique username')) }}
</div>

<div class="form-group">
    <label for="username">Fullname</label>
    {{ Form::text('fullname','',array('class'=>'form-control','placeholder'=>'New user fullname')) }}
</div>

<div class="form-group">
    <label for="username">Email</label>
    {{ Form::text('email','',array('class'=>'form-control','placeholder'=>'New user email')) }}
</div>

<div class="form-group">
    <label for="username">Password</label>
    {{ Form::password('password',array('class'=>'form-control','placeholder'=>'New user password')) }}
</div>


<div class="form-group">
    {{ Form::submit('Add User',array('class'=>'btn btn-default')) }}
</div>


{{ Form::close() }}
@stop