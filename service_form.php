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
			$service_data = get_service_data($_GET['id_servizio'], $mysqli);
			if ($service_data['exists'] == 0) {
				echo "<h4>Servizio inesistente.</h4>";
			} else {
				$show_form = 1;
				$page_title = "Modifica servizio";
			}
		} elseif ($_GET["action"]=="delete") {
			$service_data = get_service_data($_GET['id_servizio'], $mysqli);
			if ($service_data['exists'] == 0) {
				echo "<h4>Servizio inesistente.</h4>";
			} else {
				$show_delete = 1;
				$page_title = "Elimina servizio";
			}
		} else {
			$show_form = 1;
			$page_title = "Nuovo servizio";
		}
	?>
	<?php		
		if ($show_form==1 || $show_delete==1) {
	?>
	<div class="page-header">
		<h1><?php echo $page_title ?> <small>Anagrafiche</small></h1>
	</div>
	<?php		
		}
		if ($show_form==1) {
	?>
	<form data-toggle="validator" id="edit_service_form" role="form" action="process_service.php" method="post" name="edit_service_form" data-disable="true">
		<div class="form-group has-feedback">
			<div><label class="control-label" for="descrizione">Descrizione:</label></div>
			<div class="input-group">
				<span class="glyphicon glyphicon-user input-group-addon"></span>
				<input type="text" class="form-control" id="descrizione" name="descrizione" value="<?php echo htmlentities(!empty($service_data['descrizione'])?$service_data['descrizione']:'') ?>" maxlength="250" required>
				<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
			</div>
			<div class="help-block with-errors"></div>
		</div>
		<div class="form-inline">
			<div class="col-xs-6">
				<div><label class="control-label" for="prezzo">Prezzo:</label></div>
				<div class="form-group has-feedback">
					<div class="input-group">
						<span class="glyphicon glyphicon-euro input-group-addon"></span>
						<input type="number" step="any" class="form-control" id="prezzo" name="prezzo" value="<?php echo htmlentities(!empty($service_data['prezzo'])?$service_data['prezzo']:'') ?>" required>
						<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
					</div>
					<div class="help-block with-errors"></div>
				</div>
			</div>
			<div class="col-xs-6">
				<div><label class="control-label" for="categoria_list">Categoria:</label></div>
				<div class="form-group has-feedback">
					<div class="dropdown">
						<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo htmlentities(!empty($service_data['categoria'])?$service_data['categoria']:'Nessuna') ?> <span class="caret"></span></button>
						<ul class="dropdown-menu" id="categoria_list">
									<li value="0"><a href="#">Nessuna</a></li>
							<?php
								$sort_index = 'descrizione';
								$sort_dir = 'ASC';
								$items_coll = get_category_list('','=','','=',1,99999999,$sort_index,$sort_dir,$mysqli);
								unset($items_coll[sizeof($items_coll) - 1]);
								foreach ($items_coll as $item) {
							?>
									<li value="<?php echo $item["id_categoria"] ?>"><a href="#"><?php echo htmlentities($item["descrizione"]) ?></a></li>
							<?php
								}
							?>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<br><br><br><br>
		<input type="hidden" id="id_servizio" name="id_servizio" value="<?php echo !empty($service_data['id_servizio'])?$service_data['id_servizio']:'' ?>">
		<input type="hidden" id="id_categoria" name="id_categoria" value="<?php echo !empty($service_data['id_categoria'])?$service_data['id_categoria']:'0' ?>">
		<input type="hidden" id="action" name="action" value="<?php echo !empty($_GET['action'])?$_GET['action']:'' ?>">
		<button id="save-button" type="submit" class="btn btn-primary btn-lg" >Salva</button>
		<button id="cancel-button" type="button" class="btn btn-default btn-md" >Annulla</button>
	</form>
	<?php
		}
		
		if ($show_delete==1) {
	?>
	<form data-toggle="validator" id="delete_service_form" role="form" action="process_service.php" method="post" name="delete_service_form" data-disable="true">
		<fieldset disabled>
			<div class="form-group has-feedback">
				<div><label class="control-label" for="id_servizio">ID Servizio:</label></div>
				<div class="input-group">
					<span class="glyphicon glyphicon glyphicon-barcode input-group-addon"></span>
					<input type="text" class="form-control" value="<?php echo !empty($service_data['id_servizio'])?$service_data['id_servizio']:'' ?>">
					<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
				</div>
				<div class="help-block with-errors"></div>
			</div>
			<div class="form-group has-feedback">
				<div><label class="control-label" for="descrizione">Descrizione:</label></div>
				<div class="input-group">
					<span class="glyphicon glyphicon-user input-group-addon"></span>
					<input type="text" class="form-control" id="descrizione" name="descrizione" value="<?php echo htmlentities(!empty($service_data['descrizione'])?$service_data['descrizione']:'') ?>">
					<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
				</div>
				<div class="help-block with-errors"></div>
			</div>
		</fieldset>
		<br><br><br>
		<input type="hidden" id="id_servizio" name="id_servizio" value="<?php echo !empty($service_data['id_servizio'])?$service_data['id_servizio']:'' ?>">
		<input type="hidden" id="action" name="action" value="<?php echo !empty($_GET['action'])?$_GET['action']:'' ?>">
		<button id="delete-button" type="submit" class="btn btn-danger btn-lg" >Elimina</button>
		<button id="cancel-button" type="button" class="btn btn-default btn-md" >Annulla</button>
	</form>
	<?php
		}
	?>
	</div>

	<script type="text/javascript">
		$("#categoria_list li a").click(function(){
			$(this).parents(".dropdown").find(".dropdown-toggle").html($(this).html() + ' <span class="caret"></span>');
			$('input[name="id_categoria"]').val($(this).parents("li").attr("value"));
		});	

		$("#cancel-button").on('click', function(){
			window.close();    
		});
	</script>
</body>
</html>