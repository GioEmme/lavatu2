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
			$status_data = get_status_data($_GET['id_stato'], $_GET['tipo_documento'], $mysqli);
			if ($status_data['exists'] == 0) {
				echo "<h4>Stato inesistente.</h4>";
			} else {
				$show_form = 1;
				$page_title = "Modifica stato";
			}
		} elseif ($_GET["action"]=="delete") {
			$status_data = get_status_data($_GET['id_stato'], $_GET['tipo_documento'], $mysqli);
			if ($status_data['exists'] == 0) {
				echo "<h4>Stato inesistente.</h4>";
			} else {
				$show_delete = 1;
				$page_title = "Elimina stato";
			}
		} else {
			$show_form = 1;
			$page_title = "Nuovo stato";
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
	<form data-toggle="validator" id="edit_status_form" role="form" action="process_status.php" method="post" name="edit_status_form" data-disable="true">
		<div class="form-group has-feedback">
			<div><label class="control-label" for="id_stato">ID Stato:</label></div>
			<div class="input-group">
				<span class="glyphicon glyphicon-user input-group-addon"></span>
				<input type="text" class="form-control" id="id_stato" name="id_stato" value="<?php echo htmlentities(!empty($status_data['id_stato'])?$status_data['id_stato']:'') ?>" maxlength="3" required <?php echo $_GET['action']=='new'?'':'disabled' ?>>
				<?php if ($_GET['action']!='new') { ?>
					<input type="hidden" id="id_stato" name="id_stato" value="<?php echo htmlentities(!empty($status_data['id_stato'])?$status_data['id_stato']:'') ?>">
				<?php } ?>
				<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
			</div>
			<div class="help-block with-errors"></div>
		</div>
		<div class="form-group has-feedback">
			<div><label class="control-label" for="descrizione">Descrizione:</label></div>
			<div class="input-group">
				<span class="glyphicon glyphicon-user input-group-addon"></span>
				<input type="text" class="form-control" id="descrizione" name="descrizione" value="<?php echo htmlentities(!empty($status_data['descrizione'])?$status_data['descrizione']:'') ?>" maxlength="30" required>
				<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
			</div>
			<div class="help-block with-errors"></div>
		</div>
		<div class="form-group has-feedback">
			<div><label class="control-label" for="tipo_documento">Tipo documento:</label></div>
			<div class="input-group">
				<span class="glyphicon glyphicon-user input-group-addon"></span>
				<input type="text" class="form-control" id="tipo_documento" name="tipo_documento" value="<?php echo htmlentities(!empty($status_data['tipo_documento'])?$status_data['tipo_documento']:'') ?>" maxlength="3" required <?php echo $_GET['action']=='new'?'':'disabled' ?>>
				<?php if ($_GET['action']!='new') { ?>
					<input type="hidden" id="tipo_documento" name="tipo_documento" value="<?php echo htmlentities(!empty($status_data['tipo_documento'])?$status_data['tipo_documento']:'') ?>">
				<?php } ?>
				<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
			</div>
			<div class="help-block with-errors"></div>
		</div>
		<div class="form-inline">
			<div class="form-group has-feedback col-xs-6">
				<div><label class="control-label" for="show_change">Consenti avanzamento:</label></div>
				<div class="btn-group" role="group">
					<button type="button" name="allownext_yes" id="allownext_yes" class="btn btn-default <?php echo isset($status_data['show_change'])?($status_data['show_change']==1?'btn-info':''):'btn-info' ?>">Si</button>
					<button type="button" name="allownext_no" id="allownext_no" class="btn btn-default <?php echo isset($status_data['show_change'])?($status_data['show_change']==0?'btn-info':''):'' ?>">No</button>
					<input type="hidden" name="show_change" id="show_change" value="<?php echo isset($status_data['show_change'])?$status_data['show_change']:'1' ?>">
				</div>
			</div>
			<div class="form-group has-feedback col-xs-6">
				<div><label class="control-label" for="ultimo_stato_1">Stato precedente:</label></div>
				<div class="input-group">
					<span class="glyphicon glyphicon-user input-group-addon"></span>
					<input type="text" class="form-control" id="ultimo_stato_1" name="ultimo_stato_1" value="<?php echo htmlentities(!empty($status_data['ultimo_stato_1'])?$status_data['ultimo_stato_1']:'') ?>" maxlength="3" required>
					<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
				</div>
				<div class="help-block with-errors"></div>
			</div>
		</div>
		<div class="form-inline">
			<div class="form-group has-feedback col-xs-3">
				<div><label class="control-label" for="ordinamento">Ordinamento:</label></div>
				<div class="input-group">
					<span class="glyphicon glyphicon-sort-by-attributes input-group-addon"></span>
					<input type="number" class="form-control" id="ordinamento" name="ordinamento" value="<?php echo htmlentities(!empty($status_data['ordine_stato'])?$status_data['ordine_stato']:'0') ?>" required>
					<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
				</div>
				<div class="help-block with-errors"></div>
			</div>
			<div class="form-group has-feedback col-xs-3">
				<div><label class="control-label" for="print_fiscal_receipt">Emetti ricevuta:</label></div>
				<div class="btn-group" role="group">
					<button type="button" name="printreceipt_yes" id="printreceipt_yes" class="btn btn-default <?php echo isset($status_data['print_fiscal_receipt'])?($status_data['print_fiscal_receipt']==1?'btn-info':''):'btn-info' ?>">Si</button>
					<button type="button" name="printreceipt_no" id="printreceipt_no" class="btn btn-default <?php echo isset($status_data['print_fiscal_receipt'])?($status_data['print_fiscal_receipt']==0?'btn-info':''):'' ?>">No</button>
					<input type="hidden" name="print_fiscal_receipt" id="print_fiscal_receipt" value="<?php echo isset($status_data['print_fiscal_receipt'])?$status_data['print_fiscal_receipt']:'1' ?>">
				</div>
			</div>
			<div class="form-group has-feedback col-xs-3">
				<div><label class="control-label" for="invio_sms">Invio SMS:</label></div>
				<div class="btn-group" role="group">
					<button type="button" name="inviosms_yes" id="inviosms_yes" class="btn btn-default <?php echo isset($status_data['invio_sms'])?($status_data['invio_sms']==1?'btn-info':''):'btn-info' ?>">Si</button>
					<button type="button" name="inviosms_no" id="inviosms_no" class="btn btn-default <?php echo isset($status_data['invio_sms'])?($status_data['invio_sms']==0?'btn-info':''):'' ?>">No</button>
					<input type="hidden" name="invio_sms" id="invio_sms" value="<?php echo isset($status_data['invio_sms'])?$status_data['invio_sms']:'1' ?>">
				</div>
			</div>
			<div class="form-group has-feedback col-xs-3">
				</div><label class="control-label" for="row_color">Colore riga griglia:</label></div>
				<div id="cp_row_color" class="input-group colorpicker-component">
					<input type="text" id="row_color" name="row_color" value="<?php echo htmlentities(!empty($status_data['row_color'])?$status_data['row_color']:'') ?>" class="form-control" />
					<span class="input-group-addon"><i></i></span>
				</div>
				<script> $(function() { $('#cp_row_color').colorpicker(); }); </script>
			</div>
		</div>
		<br><br><br>
		<input type="hidden" id="action" name="action" value="<?php echo !empty($_GET['action'])?$_GET['action']:'' ?>">
		<div class="col-xs-12">
			<button id="save-button" type="submit" class="btn btn-primary btn-lg" >Salva</button>
			<button id="cancel-button" type="button" class="btn btn-default btn-md" >Annulla</button>
		</div>
	</form>
	<?php
		}
		
		if ($show_delete==1) {
	?>
	<form data-toggle="validator" id="delete_status_form" role="form" action="process_status.php" method="post" name="delete_status_form" data-disable="true">
		<fieldset disabled>
			<div class="form-group has-feedback">
				<div><label class="control-label" for="id_stato">ID Stato:</label></div>
				<div class="input-group">
					<span class="glyphicon glyphicon glyphicon-barcode input-group-addon"></span>
					<input type="text" class="form-control" value="<?php echo !empty($status_data['id_stato'])?$status_data['id_stato']:'' ?>">
					<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
				</div>
				<div class="help-block with-errors"></div>
			</div>
			<div class="form-group has-feedback">
				<div><label class="control-label" for="descrizione">Descrizione:</label></div>
				<div class="input-group">
					<span class="glyphicon glyphicon-user input-group-addon"></span>
					<input type="text" class="form-control" id="descrizione" name="descrizione" value="<?php echo htmlentities(!empty($status_data['descrizione'])?$status_data['descrizione']:'') ?>">
					<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
				</div>
				<div class="help-block with-errors"></div>
			</div>
			<div class="form-group has-feedback">
				<div><label class="control-label" for="descrizione">Tipo documento:</label></div>
				<div class="input-group">
					<span class="glyphicon glyphicon-user input-group-addon"></span>
					<input type="text" class="form-control" value="<?php echo htmlentities(!empty($status_data['tipo_documento'])?$status_data['tipo_documento']:'') ?>">
					<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
				</div>
				<div class="help-block with-errors"></div>
			</div>
		</fieldset>
		<br><br><br>
		<input type="hidden" id="id_stato" name="id_stato" value="<?php echo !empty($status_data['id_stato'])?$status_data['id_stato']:'' ?>">
		<input type="hidden" id="tipo_documento" name="tipo_documento" value="<?php echo !empty($status_data['tipo_documento'])?$status_data['tipo_documento']:'' ?>">
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

		$("#allownext_yes").click(function() {
		  $(this).toggleClass("btn-info");
		  $("#allownext_no").toggleClass("btn-info");
		  $("#show_change").val($("#show_change").val()=="1"?"0":"1");
		});
		$("#allownext_no").click(function() {
		  $(this).toggleClass("btn-info");
		  $("#allownext_yes").toggleClass("btn-info");
		  $("#show_change").val($("#show_change").val()=="1"?"0":"1");
		});
		
		$("#printreceipt_yes").click(function() {
		  $(this).toggleClass("btn-info");
		  $("#printreceipt_no").toggleClass("btn-info");
		  $("#print_fiscal_receipt").val($("#print_fiscal_receipt").val()=="1"?"0":"1");
		});
		$("#printreceipt_no").click(function() {
		  $(this).toggleClass("btn-info");
		  $("#printreceipt_yes").toggleClass("btn-info");
		  $("#print_fiscal_receipt").val($("#print_fiscal_receipt").val()=="1"?"0":"1");
		});
		
		$("#inviosms_yes").click(function() {
		  $(this).toggleClass("btn-info");
		  $("#inviosms_no").toggleClass("btn-info");
		  $("#invio_sms").val($("#invio_sms").val()=="1"?"0":"1");
		});
		$("#inviosms_no").click(function() {
		  $(this).toggleClass("btn-info");
		  $("#inviosms_yes").toggleClass("btn-info");
		  $("#invio_sms").val($("#invio_sms").val()=="1"?"0":"1");
		});
	</script>
</body>
</html>