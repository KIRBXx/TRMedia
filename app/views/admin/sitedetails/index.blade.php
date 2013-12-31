@extends('admin/master')

@section('container')
<h3 class="content-heading">Site Details In Quick Look</h3>


<div class="row section">
    <div class="col-md-12">
        <h4 class="title">Monthy Sigup Chart <small>Monthly number of registered users</small></h4>
    </div>
    <div class="col-md-12 chart">
        <div id="hero-graph" style="height: 230px;"></div>
    </div>
</div>

<hr>


<div class="row section">
    <div class="col-md-12">
        <h4 class="title">Monthy Image Uploads <small>Number of images uploaded monthly</small></h4>
    </div>
    <div class="col-md-12 chart">
        <div id="image-upload" style="height: 230px;"></div>
    </div>
</div>

<hr>



<div class="page-header">
    <h3 class="content-heading">Other Stats</h3>
</div>
@if(siteSettings('autoApprove') == 0)
<p class="text-danger"><h4>{{ numberOfImagesApprovalRequired() }} Images Required Approval  <a href="{{ url('admin/images/approval') }}">Manage</a></h4></p>
<hr>
@else
<p><h4> Your site is auto approval.</h4></p>
<hr>
@endif

<ul class="inline-block-list">
    <li class="panel panel-default provider-panel">
        <div class="panel-body">{{ numberOfUsers() }}</div>
        <div class="panel-footer"><a href="{{ url('admin/users') }}">Users </a></div>
    </li>
    <li class="panel panel-default provider-panel">
        <div class="panel-body">{{ numberOfImages() }}</div>
        <div class="panel-footer"><a href="{{ url('admin/images') }}">Images </a></div>
    </li>
    <li class="panel panel-default provider-panel">
        <div class="panel-body">{{ numberOfComments() }}</div>
        <div class="panel-footer"><a href="{{ url('admin/comments') }}">Comments</a></div>
    </li>
    <li class="panel panel-default provider-panel">
        <div class="panel-body">{{ numberOfFeaturedImages() }}</div>
        <div class="panel-footer"><a href="{{ url('admin/images/featured') }}">Featured Images </a></div>

    </li>
    <li class="panel panel-default provider-panel">
        <div class="panel-body">{{ numberOfFeaturedUsers() }}</div>
        <div class="panel-footer"><a href="{{ url('admin/users/featured') }}">Featured Users </a></div>
    </li>
    <li class="panel panel-default provider-panel">
        <div class="panel-body">{{ numberOfBanedUsers() }}</div>
        <div class="panel-footer"><a href="{{ url('admin/users/banned') }}">Baned Users </a></div>
    </li>
    <li class="panel panel-default provider-panel">
        <div class="panel-body">{{ numberOfReports() }}</div>
        <div class="panel-footer"><a href="{{ url('admin/reports') }}">Reports </a></div>
    </li>
</ul>

<hr>
<div class="page-header">
    <h3 class="content-heading">Site Other Settings</h3>
</div>

<ul class="inline-block-list">
    <li class="panel panel-default provider-panel">
        <div class="panel-body"><i class="glyphicon glyphicon-edit"></i></div>
        <div class="panel-footer"><a href="{{ url('admin/sitesettings') }}">Edit Site Settings </a></div>
    </li>
    <li class="panel panel-default provider-panel">
        <div class="panel-body"><i class="glyphicon glyphicon-wrench"></i></div>
        <div class="panel-footer"><a href="{{ url('admin/limitsettings') }}">Limit Settings </a></div>
    </li>
    <li class="panel panel-default provider-panel">
        <div class="panel-body"><i class="glyphicon glyphicon-align-center"></i></div>
        <div class="panel-footer"><a href="{{ url('admin/sitecategory') }}">Sites Category</a></div>
    </li>
    <li class="panel panel-default provider-panel">
        <div class="panel-body"><i class="glyphicon glyphicon-floppy-remove"></i></div>
        <div class="panel-footer"><a href="{{ url('admin/removecache') }}">Delete Cache</a></div>
    </li>
    <li class="panel panel-default provider-panel">
        <div class="panel-body"><i class="glyphicon glyphicon-time"></i></div>
        <div class="panel-footer"><a href="{{ url('admin/updatesitemap') }}">Update Sitemap</a></div>
    </li>
    <li class="panel panel-default provider-panel">
        <div class="panel-body"><i class="fa fa-plus-square-o"></i></div>
        <div class="panel-footer"><a href="{{ url('admin/adduser') }}">Add real/fake user</a></div>
    </li>
    <li class="panel panel-default provider-panel">
        <div class="panel-body"><i class="fa fa-cloud-upload"></i></div>
        <div class="panel-footer"><a href="{{ url('admin/bulkupload') }}">Bulk Upload</a></div>
    </li>
</ul>
@stop

@section('chart-js')
<script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
<script src="http://cdn.oesmith.co.uk/morris-0.4.3.min.js"></script>

<script type="text/javascript">

    var tax_data = [

    @for($i = date('n'); $i >= 0; $i--)
            @if(isset($signUpDetails[$i]))
                {"period": "{{ date('Y') }}-{{ $signUpDetails[$i]->month }}", "signups": {{ $signUpDetails[$i]->number }} },
            @else
                 {"period": "{{ date('Y') }}-{{ $i }}", "signups": 0 },
            @endif
    @endfor
    ];
    Morris.Line({
        element: 'hero-graph',
        data: tax_data,
        xkey: 'period',
        xLabels: "month",
        ykeys: ['signups'],
        labels: ['Signups']
    });


    var tax_data = [

    @for($i = date('n'); $i >= 0; $i--)
        @if(isset($imageDetails[$i]))
    {"period": "{{ date('Y') }}-{{ $imageDetails[$i]->month }}", "signups": {{ $imageDetails[$i]->number }} },
    @else
    {"period": "{{ date('Y') }}-{{ $i }}", "signups": 0 },
    @endif
    @endfor
    ];
    Morris.Line({
        element: 'image-upload',
        data: tax_data,
        xkey: 'period',
        xLabels: "month",
        ykeys: ['signups'],
        labels: ['Image Uploads']
    });
</script>
@stop