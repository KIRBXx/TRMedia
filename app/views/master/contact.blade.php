	<div class="modal fade" id="contact" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header navbar-default">
					<button type="button" style="color: #fff" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title" style="color: #fff">Contact The Racers Media</h4>
				</div>
						
			<div id='contact-modal' class="modal-body">
						
						      <?php 
									if(isset($status_message)){
									print "<div class='alert alert-success'>" . $status_message . "</div>";
									}
								?>

      <div id='formErrors'></div>
      <form action='/contact_us' role='form' class='form-horizontal' id='contactForm'  method='POST'>

				<div class='form-group'>
					<label for='email_input' class='control-label col-xs-3'>Email address:</label>
				  <div class='col-xs-4'>
				    <input name='email' type='email' class='form-control' id='email' placeholder='Enter your email'>
				  </div>
				</div>

				<div class='form-group'>
					<label for='name_input' class='control-label col-xs-3'>Name:</label>
				  <div class='col-xs-4'>
					<input name='name' type='text' class='form-control' id='name' placeholder='Enter your name'>
				  </div>
				</div>

				<div class='form-group'>
						<label for='subject_input' class='control-label col-xs-3'>Subject:</label>
					<div class='col-xs-4'>
						<input name='subject' id='subject' type='text' class='form-control' placeholder='Subject'>
					</div>
				</div>

				<div class='form-group'>
						<label for='message_input' class='control-label col-xs-3'>Message:</label>
					<div class='col-xs-8'>
						<textarea name='message' id='message' rows=10 class='form-control' placeholder='Enter your message here'></textarea>
					</div>
				</div>
		
				</div>

				<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						<button id='contactSubmit' type="submit" class="btn btn-primary">Submit</button>
				</div>
				
			</form>
      <script type='text/javascript'>
        $("#contactSubmit").click(function(){
          $.ajax({
            type: "POST",
            url: "/contact_us",
            data: $("#contactForm").serialize(),
            success: function(data, responseText, xhr){
              for(error in data){
                $("#formErrors").append("<div class='alert alert-danger'>" + data[error] + "</div>");
                $("#" + error).parent().parent().addClass('has-error')
              };

              //if(responseText){
              //  $("#contactForm").html(responseText);
              //}
            }
          })
          return false;
        });
      </script>
			</div>
		</div>
	</div>
