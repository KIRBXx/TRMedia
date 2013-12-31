@extends('admin/master')

@section('container')
    <h3 class="content-heading">Bulk Upload</h3>
    <p>Note:- <strong>Image Name</strong> is taken as <strong>Title</strong> and all image will have same <strong>Tags</strong></p>

<form id="fileupload" action="" method="POST" enctype="multipart/form-data">

    <noscript><h4>Please enable JavaScript to upload image</h4></noscript>

    <div class="fileupload-buttonbar">
                <span class="btn btn-success fileinput-button">
                    <i class="glyphicon glyphicon-plus"></i>
                    <span>{{ t('Add files...') }}</span>
                    <input type="file" name="files[]" accept="image/*" multiple>
                </span>
        <button type="reset" class="btn btn-warning cancel">
            <i class="glyphicon glyphicon-ban-circle"></i>
            <span>{{ t('Cancel upload') }}</span>
        </button>
        <button type="submit" class="btn btn-primary start">
            <i class="glyphicon glyphicon-upload"></i>
            <span>Start upload</span>
        </button>

        <span class="fileupload-process"></span>

        <div class="form-group">
            <select name="category" class="form-control" required>
                <option>{{ t('Select Category') }}</option>
                @foreach(siteCategories() as $category)
                <option value="{{ $category->slug }}">{{ $category->category }}</option>
                @endforeach
            </select><br/>
        </div>

        <div class="form-group">
            <input type="text" autocomplete="off" name="tags" placeholder="Tags" class="form-control tm-input tm-input-success" data-original-title=""/>
        </div>

        <div class="col-md-12 fileupload-progress fade">
            <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar progress-bar-success" style="width:0%;"></div>
            </div>
            <div class="progress-extended">&nbsp;</div>
        </div>
    </div>

    <div role="presentation"><div class="row files"></div></div>
</form>

<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
<div class="clearfix template-upload fade">
<hr/>
        <div class="col-md-3">
         <p>
                   <span class="preview"> </span>
          </p>
         </div>

        <div class="col-md-5"> <p>

          </p>
        </div>

        <div class="col-md-3">
        <p>
            {% if (!i && !o.options.autoUpload) { %}
                <button class="btn btn-primary start" disabled>
                    <i class="glyphicon glyphicon-upload"></i>
                    <span>{{ t('Start') }}</span>
                </button>
            {% } %}
            {% if (!i) { %}
                <button class="btn btn-warning cancel">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <span>{{ t('Cancel') }}</span>
                </button>
            {% } %}
               <div class="size">{{ t('Processing') }}</div>
                <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>
            </p>

        </div>
</div>
{% } %}
</script>
<!-- The template to display files available for download -->
<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
<hr>
    <div class="clearfix template-download fade">

        <div class="col-md-12">
            <div class="col-md-3">
                {% if (file.title) { %}
                     <p><span class="label label-danger">{{ t('Rejected') }}</span></p>
                {% } %}
                {% if (file.tags) { %}
                     <p><span class="label label-danger">{{ t('Rejected') }}</span></p>
                {% } %}
                {% if (file.error) { %}
                    <p><span class="label label-danger">{{ t('Rejected') }}</span></p>
                {% } %}
                {% if (file.success) { %}
                    <p><img src="{%=file.thumbnail%}"/></p>
                {% } %}
               </div>
<div class="col-md-5">
                   {% if (file.title) { %}
                     <p>{%=file.title%}</p>
                 {% } %}
                 {% if (file.tags) { %}
                   <p>{%=file.tags%}</p>
                 {% } %}
                 {% if (file.error) { %}
                    <p>{%=file.error%}</p>
                {% } %}
                {% if (file.success) { %}
                    <p>{{ t('Your Image is uploaded successfully') }}</p>
                     <p><a href="{%=file.successSlug%}">{%=file.successTitle%}</a></p>
                {% } %}
</div>
<div class="col-md-3">
  {% if (file.success) { %}
                <a class="btn btn-success" href="{%=file.successSlug%}" target="_blank">
                    <i class="glyphicon glyphicon-new-window"></i>
                    <span>{{ t('Visit') }}</span>
                </a>
  {% } %}
</div>
        </div>
</div>
{% } %}
</script>


@stop

@section('extrafooter')
<script>
    $(function () {
        $("#fileupload").fileupload({type: "POST", previewMaxHeight: 210, previewMaxWidth: 210, limitMultiFileUploads: 1, acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i}).bind("click", function () {
            for (var a = 0; a <= 100; a++) {
                $(".tm-input:eq(" + a + ")").each(function () {
                    $(this).tagsManager({delimiters: [9, 13, 44, 32],maxTags:{{ (int)siteSettings('tagsLimit')}} })
        })
    }})});
</script>
@stop