<?php
	include_once('includes/header.php');
	if(login_check($mysqli) != false) {
		header('Location: index.php');
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php
		include_once('includes/html_header.php');
	?>
</head>
<body>
	<div class="bg-outer">
		<div class="container gray-dark">
			<?php include_once('includes/navbar.php'); ?>
			<form data-toggle="validator" id="login_form" role="form" action="process_login.php" method="post" name="login_form" data-disable="true">
				<div class="form-group has-feedback">
					<label class="control-label" for="username">Username:</label>
					<div class="input-group">
						<span class="glyphicon glyphicon-user input-group-addon"></span>
						<input type="username" class="form-control" id="username" name="username" maxlength="15" required>
						<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
					</div>
					<div class="help-block with-errors"></div>
				</div>
				<div class="form-group has-feedback">
					<label for="password">Password:</label>
					<div class="input-group">
						<span class="glyphicon glyphicon-asterisk input-group-addon"></span>
						<input type="password" class="form-control" id="password" name="password" required>
						<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
					</div>
					<div class="help-block with-errors"></div>
				</div>
				<button id="login-button" type="submit" class="btn btn-primary btn-lg" >Login</button>
				<!--<button id="register-button" type="button" class="btn btn-default btn-md" >Register</button>-->
				<!--<button id="recover-button" type="button" class="btn btn-default btn-md" >Recover</button>-->
			</form>
		</div>
	</div>

	<script type="text/javascript">
		$('#login_form').submit( function (e) {
			e.preventDefault();  //prevent form from submitting
			formhash(this, this.password);
		});
		
		$("#register-button").on('click', function(){
			window.location = "register.php";    
		});

		$("#recover-button").on('click', function(){
			window.location = "recover.php";    
		});
	</script>
</body>
</html>