@if(Session::has('flashSuccess'))
<div class="alert alert-success fade in">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <strong>{{ Session::get('flashSuccess') }}</strong>
</div>
@endif

@if(Session::has('flashError'))
<div class="alert alert-danger fade in">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <strong>{{ Session::get('flashError') }}</strong>
</div>
@endif

@if(Session::has('errors'))
<div class="alert alert-danger fade in">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <strong>{{ Session::get('errors')->first() }}</strong>
</div>
@endif