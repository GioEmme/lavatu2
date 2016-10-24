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
			$category_data = get_category_data($_GET['id_categoria'], $mysqli);
			if ($category_data['exists'] == 0) {
				echo "<h4>Categoria inesistente.</h4>";
			} else {
				$show_form = 1;
				$page_title = "Modifica categoria";
			}
		} elseif ($_GET["action"]=="delete") {
			$category_data = get_category_data($_GET['id_categoria'], $mysqli);
			if ($category_data['exists'] == 0) {
				echo "<h4>Categoria inesistente.</h4>";
			} else {
				$show_delete = 1;
				$page_title = "Elimina categoria";
			}
		} else {
			$show_form = 1;
			$page_title = "Nuova categoria";
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
	<form data-toggle="validator" id="edit_category_form" role="form" action="process_category.php" method="post" name="edit_category_form" data-disable="true">
		<div class="form-group has-feedback">
			<div><label class="control-label" for="descrizione">Descrizione:</label></div>
			<div class="input-group">
				<span class="glyphicon glyphicon-user input-group-addon"></span>
				<input type="text" class="form-control" id="descrizione" name="descrizione" value="<?php echo htmlentities(!empty($category_data['descrizione'])?$category_data['descrizione']:'') ?>" maxlength="30" required>
				<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
			</div>
			<div class="help-block with-errors"></div>
		</div>
		<div class="form-inline">
			<div class="col-xs-6">
				<div><label>Collapsed:</label></div>
				<div class="btn-group" role="group">
					<button type="button" name="collapsed_yes" id="collapsed_yes" class="btn btn-default <?php echo isset($category_data['collapsed'])?($category_data['collapsed']==1?'btn-info':''):'btn-info' ?>">Si</button>
					<button type="button" name="collapsed_no" id="collapsed_no" class="btn btn-default <?php echo isset($category_data['collapsed'])?($category_data['collapsed']==0?'btn-info':''):'' ?>">No</button>
					<input type="hidden" name="collapsed" id="collapsed" value="<?php echo isset($category_data['collapsed'])?$category_data['collapsed']:'1' ?>">
				</div>
			</div>
			<div class="form-group has-feedback">
				<div><label class="control-label" for="ordinamento">Ordinamento:</label></div>
				<div class="input-group">
					<span class="glyphicon glyphicon-sort-by-attributes input-group-addon"></span>
					<input type="number" class="form-control" id="ordinamento" name="ordinamento" value="<?php echo htmlentities(!empty($category_data['ordinamento'])?$category_data['ordinamento']:'0') ?>" required>
					<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
				</div>
				<div class="help-block with-errors"></div>
			</div>
		</div>
		<br><br><br>
		<input type="hidden" id="id_categoria" name="id_categoria" value="<?php echo !empty($category_data['id_categoria'])?$category_data['id_categoria']:'' ?>">
		<input type="hidden" id="action" name="action" value="<?php echo !empty($_GET['action'])?$_GET['action']:'' ?>">
		<button id="save-button" type="submit" class="btn btn-primary btn-lg" >Salva</button>
		<button id="cancel-button" type="button" class="btn btn-default btn-md" >Annulla</button>
	</form>
	<?php
		}
		
		if ($show_delete==1) {
	?>
	<form data-toggle="validator" id="delete_category_form" role="form" action="process_category.php" method="post" name="delete_category_form" data-disable="true">
		<fieldset disabled>
			<div class="form-group has-feedback">
				<div><label class="control-label" for="id_categoria">ID Categoria:</label></div>
				<div class="input-group">
					<span class="glyphicon glyphicon glyphicon-barcode input-group-addon"></span>
					<input type="text" class="form-control" value="<?php echo !empty($category_data['id_categoria'])?$category_data['id_categoria']:'' ?>">
					<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
				</div>
				<div class="help-block with-errors"></div>
			</div>
			<div class="form-group has-feedback">
				<div><label class="control-label" for="descrizione">Descrizione:</label></div>
				<div class="input-group">
					<span class="glyphicon glyphicon-user input-group-addon"></span>
					<input type="text" class="form-control" id="descrizione" name="descrizione" value="<?php echo htmlentities(!empty($category_data['descrizione'])?$category_data['descrizione']:'') ?>">
					<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
				</div>
				<div class="help-block with-errors"></div>
			</div>
		</fieldset>
		<br><br><br>
		<input type="hidden" id="id_categoria" name="id_categoria" value="<?php echo !empty($category_data['id_categoria'])?$category_data['id_categoria']:'' ?>">
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

		$("#collapsed_yes").click(function() {
		  $(this).toggleClass("btn-info");
		  $("#collapsed_no").toggleClass("btn-info");
		  $("#collapsed").val($("#collapsed").val()=="1"?"0":"1");
		});
		$("#collapsed_no").click(function() {
		  $(this).toggleClass("btn-info");
		  $("#collapsed_yes").toggleClass("btn-info");
		  $("#collapsed").val($("#collapsed").val()=="1"?"0":"1");
		});
	</script>
</body>
</html>