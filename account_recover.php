<?php
	include_once('includes/header.php');
	if(login_check($mysqli) == false) {
		header('Location: login.php');
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
	<div class="container">
	<div class="page-header">
		<h1>Recupera password utente <small>Amministrazione</small></h1>
	</div>
	<form data-toggle="validator" id="recover_form" role="form" action="process_account_recover.php" method="post" name="recover_form" data-disable="true">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Procedura per il recupero della password</h3>
			</div>
			<div class="panel-body">
				Specificare lo USERNAME. Verrà inviata una nuova password all'indirizzo email di registrazione.
			</div>
		</div>
		<div class="form-group has-feedback">
			<div><label class="control-label" for="email">Username:</label></div>
			<div class="input-group">
				<span class="glyphicon glyphicon-user input-group-addon"></span>
				<input type="username" class="form-control" id="username" name="username" maxlength="15" required>
				<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
			</div>
			<div class="help-block with-errors"></div>
		</div>
		<button id="signup-button" type="submit" class="btn btn-primary btn-lg" >Recupera</button>
		<button id="cancel-button" type="button" class="btn btn-default btn-md" >Annulla</button>
	</form>
	</div>

	<script type="text/javascript">		
		$("#cancel-button").on('click', function(){
			close();    
		});
	</script>
</body>
</html>