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
	<?php
		$show_form = 0;
		$show_delete = 0;
		if (empty($_GET["action"])) {
			echo "<h4>Richiesta non valida.</h4>";
		} elseif ($_GET["action"]=="edit") {
			$account_data = get_account_data($_GET['user_id'], $mysqli);
			if ($account_data['exists'] == 0) {
				echo "<h4>Utente inesistente.</h4>";
			} else {
				$show_form = 1;
				$page_title = "Modifica utente";
			}
		} elseif ($_GET["action"]=="delete") {
			$account_data = get_account_data($_GET['user_id'], $mysqli);
			if ($account_data['exists'] == 0) {
				echo "<h4>Utente inesistente.</h4>";
			} else {
				$show_delete = 1;
				$page_title = "Elimina utente";
			}
		} else {
			$show_form = 1;
			$page_title = "Nuovo utente";
		}
	?>
	<?php
		if ($show_form==1 || $show_delete==1) {
	?>
	<div class="page-header">
		<h1><?php echo $page_title ?> <small>Amministrazione</small></h1>
	</div>
	<?php
		}		
		if ($show_form==1) {
	?>
	<form data-toggle="validator" id="edit_account_form" role="form" action="process_account.php" method="post" name="edit_account_form" data-disable="true">
		<div class="form-group has-feedback">
			<div><label class="control-label" for="name">Username:</label></div>
			<div class="input-group">
				<span class="glyphicon glyphicon-user input-group-addon"></span>
				<input type="username" class="form-control" id="username" name="username" value="<?php echo htmlentities(!empty($account_data['username'])?$account_data['username']:'') ?>" maxlength="15" required>
				<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
			</div>
			<div class="help-block with-errors"></div>
		</div>
		<div class="form-group has-feedback">
			<div><label class="control-label" for="name">Nome:</label></div>
			<div class="input-group">
				<span class="glyphicon glyphicon-user input-group-addon"></span>
				<input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlentities(!empty($account_data['name'])?$account_data['name']:'') ?>" maxlength="50" required>
				<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
			</div>
			<div class="help-block with-errors"></div>
		</div>
		<div class="form-group has-feedback">
			<div><label class="control-label" for="email">Email:</label></div>
			<div class="input-group">
				<span class="input-group-addon">@</span>
				<input type="email" class="form-control" id="email" name="email" value="<?php echo htmlentities(!empty($account_data['email'])?$account_data['email']:'') ?>" maxlength="255" required>
				<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
			</div>
			<div class="help-block with-errors"></div>
		</div>
		<?php if ($_GET["action"]!="new") { ?>
		<div class="form-group has-feedback">
			<div><label for="password_current">Password attuale:</label></div>
			<div class="input-group">
				<span class="glyphicon glyphicon-asterisk input-group-addon"></span>
				<input type="password" class="form-control" id="password_current" name="password_current">
				<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
			</div>
			<div class="help-block with-errors"></div>
		</div>
		<?php } ?>
		<div class="form-group has-feedback">
			<div><label for="password_new">Nuova password:</label></div>
			<div class="input-group">
				<span class="glyphicon glyphicon-asterisk input-group-addon"></span>
				<input type="password" class="form-control" id="password_new" name="password_new">
				<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
			</div>
			<div class="help-block with-errors"></div>
		</div>
		<div class="form-group has-feedback">
			<div><label for="password_new_confirm">Conferma password:</label></div>
			<div class="input-group">
				<span class="glyphicon glyphicon-asterisk input-group-addon"></span>
				<input type="password" class="form-control" id="password_new_confirm" name="password_new_confirm" data-match="#password_new" data-match-error="Ops, these don't match">
				<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
			</div>
			<div class="help-block with-errors"></div>
		</div>
		<div class="form-inline">
			<div class="col-xs-6">
				<div><label>Attivo:</label></div>
				<div class="btn-group" role="group">
					<button type="button" name="is_active_yes" id="is_active_yes" class="btn btn-default <?php echo isset($account_data['is_active'])?($account_data['is_active']==1?'btn-info':''):'btn-info' ?>">Si</button>
					<button type="button" name="is_active_no" id="is_active_no" class="btn btn-default <?php echo isset($account_data['is_active'])?($account_data['is_active']==0?'btn-info':''):'' ?>">No</button>
					<input type="hidden" name="is_active" id="is_active" value="<?php echo isset($account_data['is_active'])?$account_data['is_active']:'1' ?>">
				</div>
			</div>
			<div class="col-xs-6">
				<div><label>Amministratore:</label></div>
				<div class="btn-group" role="group">
					<button type="button" name="role_administrator_yes" id="role_administrator_yes" class="btn btn-default <?php echo isset($account_data['role_administrator'])?($account_data['role_administrator']==1?'btn-info':''):'btn-info' ?>">Si</button>
					<button type="button" name="role_administrator_no" id="role_administrator_no" class="btn btn-default <?php echo isset($account_data['role_administrator'])?($account_data['role_administrator']==0?'btn-info':''):'' ?>">No</button>
					<input type="hidden" name="role_administrator" id="role_administrator" value="<?php echo isset($account_data['role_administrator'])?$account_data['role_administrator']:'1' ?>">
				</div>
			</div>
		</div>
		<br/><br/><br/><br/>
		<input type="hidden" id="user_id" name="user_id" value="<?php echo !empty($account_data['user_id'])?$account_data['user_id']:'' ?>">
		<input type="hidden" id="action" name="action" value="<?php echo !empty($_GET['action'])?$_GET['action']:'' ?>">
		<button id="save-button" type="submit" class="btn btn-primary btn-lg" >Salva</button>
		<button id="cancel-button" type="button" class="btn btn-default btn-md" >Annulla</button>
	</form>
	<?php
		}
		
		if ($show_delete==1) {
	?>
	<form data-toggle="validator" id="delete_account_form" role="form" action="process_account.php" method="post" name="delete_account_form" data-disable="true">
		<fieldset disabled>
			<div class="form-group has-feedback">
				<div><label class="control-label" for="name">User ID:</label></div>
				<div class="input-group">
					<span class="glyphicon glyphicon glyphicon-barcode input-group-addon"></span>
					<input type="text" class="form-control" value="<?php echo !empty($account_data['user_id'])?$account_data['user_id']:'' ?>">
					<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
				</div>
				<div class="help-block with-errors"></div>
			</div>
			<div class="form-group has-feedback">
				<div><label class="control-label" for="name">Username:</label></div>
				<div class="input-group">
					<span class="glyphicon glyphicon-user input-group-addon"></span>
					<input type="username" class="form-control" id="username" name="username" value="<?php echo htmlentities(!empty($account_data['username'])?$account_data['username']:'') ?>">
					<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
				</div>
				<div class="help-block with-errors"></div>
			</div>
		</fieldset>
		<br><br><br>
		<input type="hidden" id="user_id" name="user_id" value="<?php echo !empty($account_data['user_id'])?$account_data['user_id']:'' ?>">
		<input type="hidden" id="action" name="action" value="<?php echo !empty($_GET['action'])?$_GET['action']:'' ?>">
		<button id="delete-button" type="submit" class="btn btn-danger btn-lg" >Elimina</button>
		<button id="cancel-button" type="button" class="btn btn-default btn-md" >Annulla</button>
	</form>
	<?php
		}
	?>
	</div>

	<script type="text/javascript">
		$('#edit_account_form').submit( function (e) {
			e.preventDefault();
			$("input[name=password_new_confirm]").val('');
			<?php
				if ($_GET["action"]!="new") {
			?>
			formhash(this, this.password_current);
			<?php
				}
			?>
			if (this.password_new.value.trim() != '') {
				formhash(this, this.password_new, 'p_new');
			}
		});
		
		$("#cancel-button").on('click', function(){
			window.close();    
		});

		$("#is_active_yes").click(function() {
		  $(this).toggleClass("btn-info");
		  $("#is_active_no").toggleClass("btn-info");
		  $("#is_active").val($("#is_active").val()=="1"?"0":"1");
		});
		$("#is_active_no").click(function() {
		  $(this).toggleClass("btn-info");
		  $("#is_active_yes").toggleClass("btn-info");
		  $("#is_active").val($("#is_active").val()=="1"?"0":"1");
		});
		$("#role_administrator_yes").click(function() {
		  $(this).toggleClass("btn-info");
		  $("#role_administrator_no").toggleClass("btn-info");
		  $("#role_administrator").val($("#role_administrator").val()=="1"?"0":"1");
		});
		$("#role_administrator_no").click(function() {
		  $(this).toggleClass("btn-info");
		  $("#role_administrator_yes").toggleClass("btn-info");
		  $("#role_administrator").val($("#role_administrator").val()=="1"?"0":"1");
		});
	</script>
</body>
</html>