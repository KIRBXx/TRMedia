<div class="modal fade" id='upload-modal'>
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header navbar-default" >
        <button type="button"  style='color: #FFF' class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" style='color: #FFF'>Upload</h4>
      </div>

      <div class="modal-body" id='uploadModalBody'>

        <script id="template-upload" type="text/x-tmpl">
          {% $(".fileupload-buttonbar").hide(); %}
          {% for (var i=0, file; file=o.files[i]; i++) { %}
          <div class="clearfix template-upload fade">
            <!--<hr/>-->
            <div class="col-md-3">
              <p>
                <span class="preview upload-preview"> </span>
              </p>
            </div>
            <div class="size">{{ t('Processing') }}</div>
            <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
              <div class="progress-bar progress-bar-success" style="width:0%;"></div>
            </div>

            <div class="col-md-12"> <p>
              <input name="title" type="text" class="form-control" placeholder="{{ t('Title') }}" required/><br/>
              <textarea name="description" type="text" class="form-control" placeholder="{{ t('Description') }}" ></textarea><br/>
              <select name="category" class="form-control" required>
                <option>{{ t('Select Category') }}</option>
                @foreach(siteCategories() as $category)
                  <option value="{{ $category->slug }}">{{ $category->category }}</option>
                @endforeach
              </select><br/>
              @if(siteSettings('allowDownloadOriginal') == 'leaveToUser')
                <select name="allowDownloadOriginal" class="form-control">
                  <option>{{ t('Allow download of original image') }}</option>
                  <option value="1">Yes</option>
                  <option value="0">No</option>
                </select><br/>
              @endif
              <input type="text" autocomplete="off" name="tags" placeholder="Tags" class="form-control tm-input tm-input-success" data-original-title=""/>
              <input name="source" type="text" class="form-control" placeholder="Image Source"/><br/>
            </p></div>

            <div class="modal-footer col-md-12">
              <p>
                {% if (!i) { %}
                  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                  <!--<button class="btn btn-warning cancel">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <span>{{ t('Cancel') }}</span>
                  </button>-->
                {% } %}
                {% if (!i && !o.options.autoUpload) { %}
                  <button class="btn btn-primary start" disabled>
                    <i class="glyphicon glyphicon-upload"></i>
                    <span>{{ t('Start') }}</span>
                  </button>
                {% } %}
              </p>
            </div>
          </div>
          {% } %}
        </script>

        <!-- The template to display files available for download -->
        <script id="template-download" type="text/x-tmpl">
        {% for (var i=0, file; file=o.files[i]; i++) { %}
          <div class="clearfix template-download fade">
            <div class="col-md-12">
              <div class="col-md-12">
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
              <div class="col-md-12">
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
              <div class="col-md-12">
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


      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
@section('extrafooter')
<script>
  var modalBodyHtml = $("#modalBodyHtml").html();
  console.log(modalBodyHtml);
  console.log("END");

  function drawUploadModal(){
    $("#uploadModalBody").html(modalBodyHtml);
    $('#upload-modal').modal('show')
  }

  $(function () {
      $("#fileupload").fileupload({
        type: "POST", 
        previewMaxHeight: 210,
        previewMaxWidth: 210,
        limitMultiFileUploads: 1, 
        acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i}).bind("click", function () {
          for (var a = 0; a <= 100; a++) {
            $(".tm-input:eq(" + a + ")").each(function () {
              $(this).tagsManager({delimiters: [9, 13, 44, 32],maxTags:{{ (int)siteSettings('tagsLimit')}} })
            })
          }} 
        )
  });

</script>
@stop

<div class='row' id='modalBodyHtml' style="display:none">
  <div class='row'>
    <form id="fileupload" action="/upload" method="POST" enctype="multipart/form-data">
      <!-- Redirect browsers with JavaScript disabled to the origin page -->
      <noscript><h4>Please enable JavaScript to upload image</h4></noscript>
      <!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
      <div class="fileupload-buttonbar">
        <div class="col-md-7">
          <!-- The fileinput-button span is used to style the file input field as button -->
          <span class="btn btn-success fileinput-button">
            <i class="glyphicon glyphicon-plus"></i>
            <span>Add file</span>
            <input type="file" name="files[]" accept="image/*" multiple>
          </span>

          <!-- The global file processing state -->
          <span class="fileupload-process"></span>
        </div>
            <!-- The global progress state -->
        <div class="col-md-5 fileupload-progress fade">
          <!-- The global progress bar -->
          <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
            <div class="progress-bar progress-bar-success" style="width:0%;"></div>
          </div>
          <!-- The extended global progress state -->
          <div class="progress-extended">&nbsp;</div>
        </div>
      </div>
      <!-- The table listing the files available for upload/download -->
      <div role="presentation"><div class="files"></div>
    <!--</div>-->
    </form>
  </div>
</div>
