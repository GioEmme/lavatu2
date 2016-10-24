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
			$address_book_data = get_address_book_data($_GET['id_cliente'], $mysqli);
			if ($address_book_data['exists'] == 0) {
				echo "<h4>Cliente inesistente.</h4>";
			} else {
				$show_form = 1;
				$page_title = "Modifica cliente";
			}
		} elseif ($_GET["action"]=="delete") {
			$address_book_data = get_address_book_data($_GET['id_cliente'], $mysqli);
			if ($address_book_data['exists'] == 0) {
				echo "<h4>Cliente inesistente.</h4>";
			} else {
				$show_delete = 1;
				$page_title = "Elimina cliente";
			}
		} elseif ($_GET["action"]=="interconnect") {
			$show_form = 1;
			$page_title = "Nuovo cliente";
		} elseif ($_GET["action"]=="interconnect_edit") {
			$address_book_data = get_address_book_data($_GET['id_cliente'], $mysqli);
			if ($address_book_data['exists'] == 0) {
				echo "<h4>Cliente inesistente.</h4>";
			} else {
				$show_form = 1;
				$page_title = "Modifica cliente";
			}
		} else {
			$show_form = 1;
			$page_title = "Nuovo cliente";
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
	<form data-toggle="validator" id="edit_address_book_form" role="form" action="process_address_book.php" method="post" name="edit_address_book_form" data-disable="true">
		<div class="form-group has-feedback">
			<div><label class="control-label" for="nome">Nome:</label></div>
			<div class="input-group">
				<span class="glyphicon glyphicon-user input-group-addon"></span>
				<input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlentities(!empty($address_book_data['nome'])?$address_book_data['nome']:'') ?>" maxlength="50" required>
				<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
			</div>
			<div class="help-block with-errors"></div>
		</div>
		<div class="form-group has-feedback">
			<div><label class="control-label" for="cognome">Cognome:</label></div>
			<div class="input-group">
				<span class="glyphicon glyphicon-user input-group-addon"></span>
				<input type="text" class="form-control" id="cognome" name="cognome" value="<?php echo htmlentities(!empty($address_book_data['cognome'])?$address_book_data['cognome']:'') ?>" maxlength="50" required>
				<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
			</div>
			<div class="help-block with-errors"></div>
		</div>
		<div class="form-group has-feedback">
			<div><label class="control-label" for="indirizzo">Indirizzo:</label></div>
			<div class="input-group">
				<span class="glyphicon glyphicon-home input-group-addon"></span>
				<input type="text" class="form-control" id="indirizzo" name="indirizzo" value="<?php echo htmlentities(!empty($address_book_data['indirizzo'])?$address_book_data['indirizzo']:'') ?>" maxlength="150">
				<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
			</div>
			<div class="help-block with-errors"></div>
		</div>
		<div class="form-group has-feedback">
			<div><label class="control-label" for="telefono">Telefono:</label></div>
			<div class="input-group">
				<span class="glyphicon glyphicon-phone-alt input-group-addon"></span>
				<input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo htmlentities(!empty($address_book_data['telefono'])?$address_book_data['telefono']:'') ?>" maxlength="50">
				<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
			</div>
			<div class="help-block with-errors"></div>
		</div>
		<div class="form-group has-feedback">
			<div><label class="control-label" for="cellulare">Cellulare:</label></div>
			<div class="input-group">
				<span class="glyphicon glyphicon-earphone input-group-addon"></span>
				<input type="text" class="form-control" id="cellulare" name="cellulare" value="<?php echo htmlentities(!empty($address_book_data['cellulare'])?$address_book_data['cellulare']:'') ?>" maxlength="50">
				<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
			</div>
			<div class="help-block with-errors"></div>
		</div>
		<div class="form-group has-feedback">
			<div><label class="control-label" for="cellulare">Note:</label></div>
			<div class="input-group">
				<span class="glyphicon glyphicon-align-justify input-group-addon"></span>
				<textarea class="form-control" id="note" name="note" rows="5" maxlength="250"><?php echo htmlentities(!empty($address_book_data['note'])?$address_book_data['note']:'') ?></textarea>
				<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
			</div>
			<div class="help-block with-errors"></div>
		</div>
		<div class="form-group has-feedback">
			<div><label>Invia SMS:</label></div>
			<div class="btn-group" role="group">
				<button type="button" name="invia_sms_yes" id="invia_sms_yes" class="btn btn-default <?php echo isset($address_book_data['invia_sms'])?($address_book_data['invia_sms']=='S'?'btn-info':''):'' ?>">Si</button>
				<button type="button" name="invia_sms_no" id="invia_sms_no" class="btn btn-default <?php echo isset($address_book_data['invia_sms'])?($address_book_data['invia_sms']=='N'?'btn-info':''):'btn-info' ?>">No</button>
				<input type="hidden" name="invia_sms" id="invia_sms" value="<?php echo isset($address_book_data['invia_sms'])?$address_book_data['invia_sms']:'N' ?>">
			</div>
		</div>
		<br><br><br>
		<input type="hidden" id="id_cliente" name="id_cliente" value="<?php echo !empty($address_book_data['id_cliente'])?$address_book_data['id_cliente']:'' ?>">
		<input type="hidden" id="action" name="action" value="<?php echo !empty($_GET['action'])?$_GET['action']:'' ?>">
		<button id="save-button" type="submit" class="btn btn-primary btn-lg" >Salva</button>
		<button id="cancel-button" type="button" class="btn btn-default btn-md" >Annulla</button>
	</form>
	<?php
		}
		
		if ($show_delete==1) {
	?>
	<form data-toggle="validator" id="delete_address_book_form" role="form" action="process_address_book.php" method="post" name="delete_address_book_form" data-disable="true">
		<fieldset disabled>
			<div class="form-group has-feedback">
				<div><label class="control-label" for="id_cliente">ID Cliente:</label></div>
				<div class="input-group">
					<span class="glyphicon glyphicon glyphicon-barcode input-group-addon"></span>
					<input type="text" class="form-control" value="<?php echo !empty($address_book_data['id_cliente'])?$address_book_data['id_cliente']:'' ?>">
					<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
				</div>
				<div class="help-block with-errors"></div>
			</div>
			<div class="form-group has-feedback">
				<div><label class="control-label" for="nome">Nome:</label></div>
				<div class="input-group">
					<span class="glyphicon glyphicon-user input-group-addon"></span>
					<input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlentities(!empty($address_book_data['nome'])?$address_book_data['nome']:'') ?>">
					<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
				</div>
				<div class="help-block with-errors"></div>
			</div>
			<div class="form-group has-feedback">
				<div><label class="control-label" for="cognome">Cognome:</label></div>
				<div class="input-group">
					<span class="glyphicon glyphicon-user input-group-addon"></span>
					<input type="text" class="form-control" id="cognome" name="cognome" value="<?php echo htmlentities(!empty($address_book_data['cognome'])?$address_book_data['cognome']:'') ?>">
					<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
				</div>
				<div class="help-block with-errors"></div>
			</div>
		</fieldset>
		<br><br><br>
		<input type="hidden" id="id_cliente" name="id_cliente" value="<?php echo !empty($address_book_data['id_cliente'])?$address_book_data['id_cliente']:'' ?>">
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
		$("#invia_sms_yes").click(function() {
		  $(this).toggleClass("btn-info");
		  $("#invia_sms_no").toggleClass("btn-info");
		  $("#invia_sms").val($("#invia_sms").val()=="S"?"N":"S");
		});
		$("#invia_sms_no").click(function() {
		  $(this).toggleClass("btn-info");
		  $("#invia_sms_yes").toggleClass("btn-info");
		  $("#invia_sms").val($("#invia_sms").val()=="S"?"N":"S");
		});
	</script>
</body>
</html>