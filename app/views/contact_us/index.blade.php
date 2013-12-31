@extends('master/index')

@section('content')

<div class="col-md-9">
    <div class="page-header">
        <h1>{{{ $title }}}</h1>
    </div>
    <div id="mainwrapper">
      <?php 
        if(isset($status_message)){
          print "<div class='alert alert-success'>" . $status_message . "</div>";
        }
      ?>
      
      <form role='form' class='form-horizontal'  method='POST'>

        <div class='form-group'>
          <label for='email_input' class='control-label col-xs-3'>Email address:</label>
          <div class='col-xs-4'>
            <input name='email' type='email' class='form-control' id='email_input' placeholder='Enter your email'>
          </div>
        </div>

        <div class='form-group'>
          <label for='name_input' class='control-label col-xs-3'>Name:</label>
          <div class='col-xs-4'>
            <input name='name' type='text' class='form-control' id='name_input' placeholder='Enter your name'>
          </div>
        </div>

        <div class='form-group'>
          <label for='subject_input' class='control-label col-xs-3'>Subject:</label>
          <div class='col-xs-4'>
            <input name='subject' type='text' class='form-control' id='subject_input' placeholder='Subject'>
          </div>
        </div>

        <div class='form-group'>
          <label for='message_input' class='control-label col-xs-3'>Message:</label>
          <div class='col-xs-8'>
            <textarea name='message' rows=10 class='form-control' placeholder='Enter your message here'></textarea>
          </div>
        </div>
		
		<div class="form-group">
		<center>{{ Form::captcha() }}</center>
		</div>

        <div class='form-group'>
          <div class='col-sm-offset-2 col-xs-8'>
            <button type="submit" class="btn btn-primary">Submit</button>
          </div>
        </div>
		
      </form>

    <div class="clearfix">
    </div>

    <div class="clearfix">
    </div>
  </div>
</div>

@stop

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-46572607-1', 'theracersmedia.com');
  ga('send', 'pageview');

</script>