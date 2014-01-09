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

    </form>

    <div class="clearfix">
    </div>

    <div class="clearfix">
    </div>
