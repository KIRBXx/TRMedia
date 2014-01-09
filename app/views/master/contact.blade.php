	<div class="modal fade" id="contact" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header navbar-default">
					<button type="button" style="color: #fff" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title" style="color: #fff">Contact The Racers Media</h4>
				</div>
						
						<div class="modal-body">
						
						<div class="alert alert-danger">The Contact Form is currently undergoing Maintenance</div>
						      <?php 
									if(isset($status_message)){
									print "<div class='alert alert-success'>" . $status_message . "</div>";
									}
								?>

							<form action='/contact_us' role='form' class='form-horizontal'  method='POST'>

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

							</div>
      
							

						<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
								<button type="submit" class="btn btn-primary">Submit</button>
						</div>
						
			</form>
			</div>
		</div>
	</div>