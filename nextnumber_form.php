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
			$nextnumber_data = get_nextnumber_data($_GET['tipo_fiscale'], $_GET['anno'], $mysqli);
			if ($nextnumber_data['exists'] == 0) {
				echo "<h4>Numeratore inesistente.</h4>";
			} else {
				$show_form = 1;
				$page_title = "Modifica numeratore";
			}
		} elseif ($_GET["action"]=="delete") {
			$nextnumber_data = get_nextnumber_data($_GET['tipo_fiscale'], $_GET['anno'], $mysqli);
			if ($nextnumber_data['exists'] == 0) {
				echo "<h4>Numeratore inesistente.</h4>";
			} else {
				$show_delete = 1;
				$page_title = "Elimina numeratore";
			}
		} else {
			$show_form = 1;
			$page_title = "Nuovo numeratore";
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
	<form data-toggle="validator" id="edit_nextnumber_form" role="form" action="process_nextnumber.php" method="post" name="edit_nextnumber_form" data-disable="true">
		<?php echo $_GET['action']!="new"?"<fieldset disabled>":"" ?>
		<div class="form-group has-feedback">
			<div><label class="control-label" for="tipo_fiscale">Tipo documento:</label></div>
			<div class="input-group">
				<span class="glyphicon glyphicon-user input-group-addon"></span>
				<?php
					if ($_GET['action']=='new') {
				?>
					<input type="text" class="form-control" id="tipo_fiscale" name="tipo_fiscale" value="<?php echo htmlentities(!empty($nextnumber_data['tipo_fiscale'])?$nextnumber_data['tipo_fiscale']:'') ?>" maxlength="15" required>
				<?php
					} else {
				?>
					<input type="text" class="form-control" value="<?php echo htmlentities(!empty($nextnumber_data['tipo_fiscale'])?$nextnumber_data['tipo_fiscale']:'') ?>">
				<?php
					}
				?>
				<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
			</div>
			<div class="help-block with-errors"></div>
		</div>
		<?php echo $_GET['action']!="new"?"</fieldset>":"" ?>
		<?php if ($_GET['action']!="new") { ?>
			<input type="hidden" id="tipo_fiscale" name="tipo_fiscale" value="<?php echo htmlentities(!empty($nextnumber_data['tipo_fiscale'])?$nextnumber_data['tipo_fiscale']:'') ?>">
		<?php } ?>
		<div class="form-group has-feedback">
			<div><label class="control-label" for="prefisso">Prefisso:</label></div>
			<div class="input-group">
				<span class="glyphicon glyphicon-user input-group-addon"></span>
				<input type="text" class="form-control" id="prefisso" name="prefisso" value="<?php echo htmlentities(!empty($nextnumber_data['prefisso'])?$nextnumber_data['prefisso']:'') ?>" maxlength="15">
				<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
			</div>
			<div class="help-block with-errors"></div>
		</div>
		<div class="form-group has-feedback">
			<div><label class="control-label" for="numero_fiscale">Prossimo numero:</label></div>
			<div class="input-group">
				<span class="glyphicon glyphicon-sort-by-attributes input-group-addon"></span>
				<input type="number" class="form-control" id="numero_fiscale" name="numero_fiscale" value="<?php echo !empty($nextnumber_data['numero_fiscale'])?$nextnumber_data['numero_fiscale']:'1' ?>" required>
				<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
			</div>
			<div class="help-block with-errors"></div>
		</div>
		<div class="form-group has-feedback">
			<div><label class="control-label" for="suffisso">Suffisso:</label></div>
			<div class="input-group">
				<span class="glyphicon glyphicon-user input-group-addon"></span>
				<input type="text" class="form-control" id="suffisso" name="suffisso" value="<?php echo htmlentities(!empty($nextnumber_data['suffisso'])?$nextnumber_data['suffisso']:'') ?>" maxlength="15">
				<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
			</div>
			<div class="help-block with-errors"></div>
		</div>
		<?php echo $_GET['action']!="new"?"<fieldset disabled>":"" ?>
		<div class="form-group has-feedback">
			<div><label class="control-label" for="anno">Anno:</label></div>
			<div class="input-group">
				<span class="glyphicon glyphicon-sort-by-attributes input-group-addon"></span>
				<?php
					if ($_GET['action']=='new') {
				?>
					<input type="number" class="form-control" id="anno" name="anno" value="<?php echo !empty($nextnumber_data['anno'])?$nextnumber_data['anno']:date("Y") ?>" required>
				<?php
					} else {
				?>
					<input type="number" class="form-control" value="<?php echo !empty($nextnumber_data['anno'])?$nextnumber_data['anno']:date("Y") ?>">
				<?php
					}
				?>
				<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
			</div>
			<div class="help-block with-errors"></div>
		</div>
		<?php echo $_GET['action']!="new"?"</fieldset>":"" ?>
		<?php if ($_GET['action']!="new") { ?>
			<input type="hidden" id="anno" name="anno" value="<?php echo !empty($nextnumber_data['anno'])?$nextnumber_data['anno']:date("Y") ?>">
		<?php } ?>
		<br><br><br>
		<input type="hidden" id="action" name="action" value="<?php echo !empty($_GET['action'])?$_GET['action']:'' ?>">
		<button id="save-button" type="submit" class="btn btn-primary btn-lg" >Salva</button>
		<button id="cancel-button" type="button" class="btn btn-default btn-md" >Annulla</button>
	</form>
	<?php
		}
		
		if ($show_delete==1) {
	?>
	<form data-toggle="validator" id="delete_nextnumber_form" role="form" action="process_nextnumber.php" method="post" name="delete_nextnumber_form" data-disable="true">
		<fieldset disabled>
			<div class="form-group has-feedback">
				<div><label class="control-label" for="tipo_fiscale">Tipo documento:</label></div>
				<div class="input-group">
					<span class="glyphicon glyphicon glyphicon-barcode input-group-addon"></span>
					<input type="text" class="form-control" value="<?php echo htmlentities(!empty($nextnumber_data['tipo_fiscale'])?$nextnumber_data['tipo_fiscale']:'') ?>">
					<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
				</div>
				<div class="help-block with-errors"></div>
			</div>
			<div class="form-group has-feedback">
				<div><label class="control-label" for="anno">Anno:</label></div>
				<div class="input-group">
					<span class="glyphicon glyphicon-user input-group-addon"></span>
					<input type="number" class="form-control" value="<?php echo !empty($nextnumber_data['anno'])?$nextnumber_data['anno']:'' ?>">
					<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
				</div>
				<div class="help-block with-errors"></div>
			</div>
		</fieldset>
		<br><br><br>
		<input type="hidden" id="tipo_fiscale" name="tipo_fiscale" value="<?php echo !empty($nextnumber_data['tipo_fiscale'])?$nextnumber_data['tipo_fiscale']:'' ?>">
		<input type="hidden" id="anno" name="anno" value="<?php echo !empty($nextnumber_data['anno'])?$nextnumber_data['anno']:'' ?>">
		<input type="hidden" id="action" name="action" value="<?php echo !empty($_GET['action'])?$_GET['action']:'' ?>">
		<button id="delete-button" type="submit" class="btn btn-danger btn-lg" >Elimina</button>
		<button id="cancel-button" type="button" class="btn btn-default btn-md" >Annulla</button>
	</form>
	<?php
		}
	?>
	</div>

	<script type="text/javascript">
		$("#cancel-button").on('click', function(){
			window.close();    
		});
	</script>
</body>
</html>