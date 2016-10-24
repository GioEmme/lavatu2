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
	<script src="//cdn.tinymce.com/4/tinymce.min.js"></script>
	<script>	
		tinymce.init({
			  selector: 'textarea',
			  height: 300,
			  toolbar: 'undo redo | copy paste | bold italic underline | forecolor',
			  menubar: false,
			  maxLength: 250,
			  plugins: 'textcolor',
			  textcolor_cols: '10'
		});
	</script>

  <script type="text/javascript">
		var current_service_total = 0;
		var current_service_total = 0;
	</script>
</head>
<body>
	<div class="container">
	<?php
		$show_form = 0;
		$show_delete = 0;
		$show_fiscal = 0;
		if (empty($_GET["action"])) {
			echo "<h4>Richiesta non valida.</h4>";
		} elseif ($_GET["action"]=="edit") {
			$order_data = get_order_data($_GET['id_lavorazione'], $mysqli);
			if ($order_data['exists'] == 0) {
				echo "<h4>Lavorazione inesistente.</h4>";
			} else {
				$show_form = 1;
				$page_title = "Modifica lavorazione";
			}
		} elseif ($_GET["action"]=="delete") {
			$order_data = get_order_data($_GET['id_lavorazione'], $mysqli);
			if ($order_data['exists'] == 0) {
				echo "<h4>Lavorazione inesistente.</h4>";
			} else {
				$show_delete = 1;
				$page_title = "Elimina lavorazione";
			}
		} elseif ($_GET["action"]=="fiscal") {
			$order_data = get_order_data($_GET['id_lavorazione'], $mysqli);
			if ($order_data['exists'] == 0) {
				echo "<h4>Lavorazione inesistente.</h4>";
			} else {
				$show_fiscal = 1;
				$page_title = "Ricevuta fiscale";
			}
		} else {
			$show_form = 1;
			$page_title = "Nuova lavorazione";
		}
	?>
	<?php		
		if ($show_form==1 || $show_delete==1) {
	?>
	<div class="page-header">
		<div class="row">
			<div class="col-xs-6">
				<h1><?php echo $page_title ?> <small>Ordini</small></h1>
			</div>
			<div class="col-xs-6 text-right">
				<label class="control-label" >Totale lavorazione:</label>
				<h3><span class="label label-default" id="totale_prezzo">0</span></h3>
			</div>
		</div>
	</div>
	<?php		
		}
		if ($show_form==1) {
	?>
	<form data-toggle="validator" id="edit_order_form" role="form" action="process_order.php" method="post" name="edit_order_form" data-disable="true">
		<div class="col-xs-12">
			<div class="well well-lg col-xs-6">
				<div class="col-xs-3">
					<div class="input-group">
						<input type="text" class="form-control form-fixer" id="id_cliente_visible" name="id_cliente_visible" value="<?php echo !empty($order_data["id_cliente"])?$order_data["id_cliente"]:'' ?>" disabled>
						<div class="input-group-addon"><span class="glyphicon glyphicon-barcode"></span></div>
					</div>
					<input type="hidden" class="form-control" id="id_cliente" name="id_cliente" value="<?php echo !empty($order_data["id_cliente"])?$order_data["id_cliente"]:'' ?>">
				</div>
				<div class="col-xs-9">
					<div class="form-group has-feedback">
						<div class="input-group">
							<input type="text" class="form-control ui-autocomplete-input" id="cliente" name="cliente" placeholder="Cliente" value="<?php echo trim(htmlentities(!empty($order_data["nome"])?$order_data["nome"]:'') . ' ' . htmlentities(!empty($order_data["cognome"])?$order_data["cognome"]:'')) ?>" required>
							<div class="input-group-addon"><span class="glyphicon glyphicon-user"></span></div>
						</div>
						<span class="glyphicon form-control-feedback"></span>
						<div class="help-block with-errors"></div>
					</div>
				</div>
				<div class="input-group">
					<input type="text" class="form-control" id="indirizzo" name="indirizzo" value="<?php echo htmlentities(!empty($order_data["indirizzo"])?$order_data["indirizzo"]:'') ?>" disabled>
					<div class="input-group-addon"><span class="glyphicon glyphicon-home"></span></div>
				</div>
				<div class="input-group">
					<input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo htmlentities(!empty($order_data["telefono"])?$order_data["telefono"]:'') ?>" disabled>
					<div class="input-group-addon"><span class="glyphicon glyphicon-earphone"></span></div>
				</div>	
				<div class="input-group">
					<input type="text" class="form-control" id="cellulare" name="cellulare" value="<?php echo htmlentities(!empty($order_data["cellulare"])?$order_data["cellulare"]:'') ?>" disabled>
					<div class="input-group-addon"><span class="glyphicon glyphicon-phone"></span></div>
				</div>
				<br/>
				<button id="newaddress-button" type="button" class="btn btn-primary btn-sm" onClick="javascript:windowpop('address_book_form.php?action=interconnect',800,800,'newaddress')" title="Crea nuovo cliente" data-toggle="tooltip"><span class="glyphicon glyphicon-plus"></span></button>
				<button id="editaddress-button" type="button" class="btn btn-primary btn-sm" onClick="javascript:windowpop('address_book_form.php?action=interconnect_edit&id_cliente=' + document.getElementById('id_cliente').value,800,800,'editaddress')" title="Modifica cliente" data-toggle="tooltip"><span class="glyphicon glyphicon-pencil"></span></button>
			</div>
			<div class="well well-lg col-xs-3">
				<div class="form-group">
					<div><label class="control-label" for="data_consegna">Data consegna:</label></div>
					<div class="input-group date">
						<input type="text" class="form-control input-sm" id="data_consegna" name="data_consegna" value="<?php echo !empty($order_data["data_consegna"])?$order_data["data_consegna"]:date('d/m/Y') ?>
						" required>
						<div class="input-group-addon"><span class="glyphicon glyphicon-th"></span></div>
					</div>
				</div>
				<div class="form-group">
					<div><label class="control-label" for="data_ritiro">Data ritiro:</label></div>
					<div class="input-group date">
						<input type="text" class="form-control input-sm" id="data_ritiro" name="data_ritiro" value="<?php echo !empty($order_data["data_ritiro"])?$order_data["data_ritiro"]:'' ?>" disabled>
						<div class="input-group-addon"><span class="glyphicon glyphicon-th"></span></div>
					</div>
				</div>
				<div class="form-group">
					<div><label class="control-label" for="data_sms">Data SMS:</label></div>
					<div class="input-group date">
						<input type="text" class="form-control input-sm" id="data_sms" name="data_sms" value="<?php echo !empty($order_data["data_sms"])?$order_data["data_sms"]:'' ?>" disabled>
						<div class="input-group-addon"><span class="glyphicon glyphicon-th"></span></div>
					</div>
				</div>
			</div>
			<div class="well well-lg col-xs-3">
				<div class="form-group">
					<div><label class="control-label">Invia SMS:</label></div>
					<div class="btn-group" role="group">
						<button type="button" name="sms_yes" id="sms_yes" class="btn btn-default btn-sm <?php echo isset($order_data['sms'])?($order_data['sms']=='S'?'btn-info':''):'' ?>">Si</button>
						<button type="button" name="sms_no" id="sms_no" class="btn btn-default btn-sm <?php echo isset($order_data['sms'])?($order_data['sms']=='N'?'btn-info':''):'btn-info' ?>">No</button>
						<input type="hidden" name="sms" id="sms" value="<?php echo isset($order_data['sms'])?$order_data['sms']:'N' ?>">
					</div>
				</div>
				<div class="form-group">
					<div><label class="control-label" for="stato">Stato:</label></div>
					<div class="btn-group btn-block" role="group">
						<div class="btn-group">
							<?php
								$first_status = get_order_first_status('LAV', $mysqli);
							?>
							<button type="button" class="btn btn-default dropdown-toggle input-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" <?php echo $_GET["action"]!="new"?'':'disabled' ?>><?php echo $_GET["action"]!="new"?$order_data["descrizione_stato"]:$first_status[0]["descrizione"] ?> <span class="caret"></span></button>
							<ul class="dropdown-menu" id="stato_list">
								<?php
									$status_coll = get_order_status_list('LAV', '1', $mysqli);
									unset($status_coll[sizeof($status_coll) - 1]);
									foreach ($status_coll as $status) {
								?>
										<li value="<?php echo $status["id_stato"] ?>"><a href="#"><?php echo htmlentities($status["descrizione"]) ?></a></li>
								<?php
									}
								?>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
		<input type="hidden" id="stato" name="stato" value="<?php echo !empty($order_data["stato"])?$order_data["stato"]:$first_status[0]["id_stato"] ?>">
		<!--#############################################################################################################-->
		<!--TABS                                                                                                         -->
		<!--#############################################################################################################-->
		<ul class="nav nav-tabs" id="ArtSerTab">
			<li class="active"><a href="#articoli" data-toggle="tab"><span class="glyphicon glyphicon-bed"></span>&nbsp;Articoli</a></li>
			<li><a href="#servizi" data-toggle="tab"><span class="glyphicon glyphicon-thumbs-up"></span>&nbsp;Servizi</a></li>
			<li><a href="#presa_visione_tab" data-toggle="tab"><span class="glyphicon glyphicon-eye-open"></span>&nbsp;Presa visione</a></li>
			<li><a href="#note_tab" data-toggle="tab"><span class="glyphicon glyphicon-pencil"></span>&nbsp;Note</a></li>
		</ul>
		<br>
		<!--#############################################################################################################-->
		<!--TAB - ARTICOLI                                                                                               -->
		<!--#############################################################################################################-->
		<div class="tab-content">
			<div class="tab-pane active" id="articoli">
		<?php
			$cats_coll = get_order_category_list('A', $mysqli);
			unset($cats_coll[sizeof($cats_coll) - 1]);
			foreach ($cats_coll as $cat) {
		?>
				<div class="panel-group col-xs-12">
					<div class="panel panel-default">
						<div class="panel-heading">
								<h4 class="panel-title">
									<a data-toggle="collapse" href="#articoli_collapse<?php echo $cat['id_categoria'] ?>"><?php echo htmlentities($cat['descrizione']) ?></a>
								</h4>
							</div>
							<div id="articoli_collapse<?php echo $cat['id_categoria'] ?>" class="panel-collapse collapse <?php echo $cat['collapsed']==1?'':'in' ?>">
							<div class="panel-body">
							<?php
								if($_GET["action"]=="new"){
									$items_coll = get_order_item_list($cat['id_categoria'], $mysqli);
								} else {
									$items_coll = get_order_item_data($order_data["id_lavorazione"], $cat['id_categoria'], $mysqli);
								}
								unset($items_coll[sizeof($items_coll) - 1]);
								$counter = 0;
								$last_id_articolo = -1;
								foreach ($items_coll as $item) {
									$quantita_residua = (!empty($item['quantita'])?$item['quantita']:'0') - (!empty($item['quantita_evasa'])?$item['quantita_evasa']:'0');
									if ($last_id_articolo != $item['id_articolo']) {
										$counter += 1;
										if ($counter > 1) { echo "</div>"; }
							?>
											<div class="form-inline col-xs-12 <?php echo $counter%2==0?'bg-info':'' ?>">
												<label class="control-label col-xs-6"><?php echo $item['descrizione'] ?></label>
												<div class="form-group col-xs-6">
													<input type="number" id="articolo_<?php echo $item['id_articolo'] ?>" name="articolo_<?php echo $item['id_articolo'] ?>" class="form-control" placeholder="0" value="<?php echo !empty($item['quantita'])?$item['quantita']:'0' ?>" />
												</div>
							<?php
										$last_id_articolo = $item['id_articolo'];
									}
									if ((!empty($item['quantita_evasa'])?$item['quantita_evasa']:'0')>0) {
							?>
												<div class="form-group col-xs-4">
													Quantità evasa: <b><?php echo !empty($item['quantita_evasa_ricevuta'])?$item['quantita_evasa_ricevuta']:'0' ?></b> / <?php echo !empty($item['quantita'])?$item['quantita']:'0' ?><br/>
													Numero ricevuta fiscale: <?php echo !empty($item['numero_ricevuta'])?$item['numero_ricevuta']:'' ?><br/>
													del: <?php echo !empty($item['data_ricevuta'])?$item['data_ricevuta']:'' ?><br/>
												</div>
							<?php			
									}
								}
								if ($counter >= 1) { echo "</div>"; }
							?>
							</div>
						</div>
					</div>
				</div>
		<?php
			}
		?>
				<div class="panel-group col-xs-12">
					<div class="panel panel-default">
						<div class="panel-heading">
								<h4 class="panel-title">
									<a data-toggle="collapse" href="#articoli_collapse_last">Altro</a>
								</h4>
							</div>
							<div id="articoli_collapse_last" class="panel-collapse collapse">
							<div class="panel-body">
							<?php
								if($_GET["action"]=="new"){
									$items_coll = get_order_item_list(0, $mysqli);
								} else {
									$items_coll = get_order_item_data($order_data["id_lavorazione"], 0, $mysqli);
								}
								unset($items_coll[sizeof($items_coll) - 1]);
								$counter = 0;
								$last_id_articolo = -1;
								foreach ($items_coll as $item) {
									$quantita_residua = (!empty($item['quantita'])?$item['quantita']:'0') - (!empty($item['quantita_evasa'])?$item['quantita_evasa']:'0');
									if ($last_id_articolo != $item['id_articolo']) {
										$counter += 1;
										if ($counter > 1) { echo "</div>"; }
							?>
											<div class="form-inline col-xs-12 <?php echo $counter%2==0?'bg-info':'' ?>">
												<label class="control-label col-xs-6"><?php echo $item['descrizione'] ?></label>
												<div class="form-group col-xs-6">
													<input type="number" id="articolo_<?php echo $item['id_articolo'] ?>" name="articolo_<?php echo $item['id_articolo'] ?>" class="form-control" placeholder="0" value="<?php echo !empty($item['quantita'])?$item['quantita']:'0' ?>" />
												</div>
							<?php
										$last_id_articolo = $item['id_articolo'];
									}
									if ((!empty($item['quantita_evasa'])?$item['quantita_evasa']:'0')>0) {
							?>
												<div class="form-group col-xs-4">
													Quantità evasa: <b><?php echo !empty($item['quantita_evasa_ricevuta'])?$item['quantita_evasa_ricevuta']:'0' ?></b> / <?php echo !empty($item['quantita'])?$item['quantita']:'0' ?><br/>
													Numero ricevuta fiscale: <?php echo !empty($item['numero_ricevuta'])?$item['numero_ricevuta']:'' ?><br/>
													del: <?php echo !empty($item['data_ricevuta'])?$item['data_ricevuta']:'' ?><br/>
												</div>
							<?php			
									}
								}
								if ($counter >= 1) { echo "</div>"; }
							?>
							</div>
						</div>
					</div>
				</div>
			</div>
		<!--#############################################################################################################-->
		<!--TAB - SERVIZI                                                                                                -->
		<!--#############################################################################################################-->
			<div class="tab-pane" id="servizi">
		<?php
			$cats_coll = get_order_category_list('S', $mysqli);
			unset($cats_coll[sizeof($cats_coll) - 1]);
			foreach ($cats_coll as $cat) {
		?>
				<div class="panel-group col-xs-12">
					<div class="panel panel-default">
						<div class="panel-heading">
								<h4 class="panel-title">
									<a data-toggle="collapse" href="#servizi_collapse<?php echo $cat['id_categoria'] ?>"><?php echo $cat['descrizione'] ?></a>
								</h4>
							</div>
							<div id="servizi_collapse<?php echo $cat['id_categoria'] ?>" class="panel-collapse collapse <?php echo $cat['collapsed']==1?'':'in' ?>">
							<div class="panel-body">
							<?php
								if($_GET["action"]=="new"){
									$services_coll = get_order_service_list($cat['id_categoria'], $mysqli);
								} else {
									$services_coll = get_order_service_data($order_data["id_lavorazione"], $cat['id_categoria'], $mysqli);
								}
								unset($services_coll[sizeof($services_coll) - 1]);
								$counter = 0;
								$last_id_servizio = -1;
								foreach ($services_coll as $service) {
									$quantita_residua = (!empty($service['quantita'])?$service['quantita']:'0') - (!empty($service['quantita_evasa'])?$service['quantita_evasa']:'0');
									if ($last_id_servizio != $service['id_servizio']) {
										$counter += 1;
										if ($counter > 1) { echo "</div>"; }
							?>
											<div class="form-inline col-xs-12 <?php echo $counter%2==0?'bg-info':'' ?>">
												<label class="control-label col-xs-4"><?php echo $service['descrizione'] ?></label>
												<label class="control-label col-xs-3 service_price_label" prezzo="<?php echo $_GET["action"]=="new"?$service['prezzo']:(isset($service['prezzo_lavorazione'])?$service['prezzo_lavorazione']:$service['prezzo']) ?>"><?php echo $curfmt->formatCurrency($_GET["action"]=="new"?$service['prezzo']:(isset($service['prezzo_lavorazione'])?$service['prezzo_lavorazione']:$service['prezzo']), "EUR") ?></label>
												<?php
													if($_GET["action"]!="new" && isset($service['prezzo_lavorazione'])){
														echo "<script>current_service_total = parseFloat(current_service_total) + ((parseFloat('" . $service['prezzo_lavorazione'] . "')||0) * (parseFloat('" . $service['quantita'] . "')||0));$('#totale_prezzo').html(current_service_total.toFixed(2));</script>";
													}
													if($_GET["action"]!="new" && isset($service['prezzo_lavorazione']) && $service['prezzo_lavorazione']!=$service['prezzo']){
												?>
														<span class="glyphicon glyphicon-refresh col-xs-1"></span>
												<?php
													} else {
												?>
														<span class="col-xs-1"></span>
												<?php
													}
												?>
												<div class="form-group col-xs-4">
													<input type="number" id="servizio_<?php echo $service['id_servizio'] ?>" name="servizio_<?php echo $service['id_servizio'] ?>" class="form-control" placeholder="0" value="<?php echo !empty($service['quantita'])?$service['quantita']:'0' ?>">
													<input type="hidden" id="prezzo_servizio_<?php echo $service['id_servizio'] ?>" name="prezzo_servizio_<?php echo $service['id_servizio'] ?>" value="<?php echo $_GET["action"]=="new"?$service['prezzo']:(isset($service['prezzo_lavorazione'])?$service['prezzo_lavorazione']:$service['prezzo']) ?>">
												</div>
											<!--</div>-->
							<?php
										$last_id_servizio = $service['id_servizio'];
									}
									if ((!empty($service['quantita_evasa'])?$service['quantita_evasa']:'0')>0) {
							?>
												<div class="form-group col-xs-4">
													Quantità evasa: <b><?php echo !empty($service['quantita_evasa_ricevuta'])?$service['quantita_evasa_ricevuta']:'0' ?></b> / <?php echo !empty($service['quantita'])?$service['quantita']:'0' ?><br/>
													Numero ricevuta fiscale: <?php echo !empty($service['numero_ricevuta'])?$service['numero_ricevuta']:'' ?><br/>
													del: <?php echo !empty($service['data_ricevuta'])?$service['data_ricevuta']:'' ?><br/>
												</div>
							<?php			
									}
								}
								if ($counter >= 1) { echo "</div>"; }
							?>
							</div>
						</div>
					</div>
				</div>
		<?php
			}
		?>
				<div class="panel-group col-xs-12">
					<div class="panel panel-default">
						<div class="panel-heading">
								<h4 class="panel-title">
									<a data-toggle="collapse" href="#servizi_collapse_last">Altro</a>
								</h4>
							</div>
							<div id="servizi_collapse_last" class="panel-collapse collapse">
							<div class="panel-body">
							<?php
								if($_GET["action"]=="new"){
									$services_coll = get_order_service_list(0, $mysqli);
								} else {
									$services_coll = get_order_service_data($order_data["id_lavorazione"], 0, $mysqli);
								}
								unset($services_coll[sizeof($services_coll) - 1]);
								$counter = 0;
								$last_id_servizio = -1;
								foreach ($services_coll as $service) {
									$quantita_residua = (!empty($service['quantita'])?$service['quantita']:'0') - (!empty($service['quantita_evasa'])?$service['quantita_evasa']:'0');
									if ($last_id_servizio != $service['id_servizio']) {
										$counter += 1;
										if ($counter > 1) { echo "</div>"; }
							?>
											<div class="form-inline col-xs-12 <?php echo $counter%2==0?'bg-info':'' ?>">
												<label class="control-label col-xs-4"><?php echo $service['descrizione'] ?></label>
												<label class="control-label col-xs-3 service_price_label" prezzo="<?php echo $_GET["action"]=="new"?$service['prezzo']:(isset($service['prezzo_lavorazione'])?$service['prezzo_lavorazione']:$service['prezzo']) ?>"><?php echo $curfmt->formatCurrency($_GET["action"]=="new"?$service['prezzo']:(isset($service['prezzo_lavorazione'])?$service['prezzo_lavorazione']:$service['prezzo']), "EUR") ?></label>
												<?php
													if($_GET["action"]!="new" && isset($service['prezzo_lavorazione'])){
														echo "<script>current_service_total = parseFloat(current_service_total) + ((parseFloat('" . $service['prezzo_lavorazione'] . "')||0) * (parseFloat('" . $service['quantita'] . "')||0));$('#totale_prezzo').html(current_service_total.toFixed(2));</script>";
													}
													if($_GET["action"]!="new" && isset($service['prezzo_lavorazione']) && $service['prezzo_lavorazione']!=$service['prezzo']){
												?>
														<span class="glyphicon glyphicon-refresh col-xs-1"></span>
												<?php
													} else {
												?>
														<span class="col-xs-1"></span>
												<?php
													}
												?>
												<div class="form-group col-xs-4">
													<input type="number" id="servizio_<?php echo $service['id_servizio'] ?>" name="servizio_<?php echo $service['id_servizio'] ?>" class="form-control" placeholder="0" value="<?php echo !empty($service['quantita'])?$service['quantita']:'0' ?>">
													<input type="hidden" id="prezzo_servizio_<?php echo $service['id_servizio'] ?>" name="prezzo_servizio_<?php echo $service['id_servizio'] ?>" value="<?php echo $_GET["action"]=="new"?$service['prezzo']:(isset($service['prezzo_lavorazione'])?$service['prezzo_lavorazione']:$service['prezzo']) ?>">
												</div>
											<!--</div>-->
							<?php
										$last_id_servizio = $service['id_servizio'];
									}
									if ((!empty($service['quantita_evasa'])?$service['quantita_evasa']:'0')>0) {
							?>
												<div class="form-group col-xs-4">
													Quantità evasa: <b><?php echo !empty($service['quantita_evasa_ricevuta'])?$service['quantita_evasa_ricevuta']:'0' ?></b> / <?php echo !empty($service['quantita'])?$service['quantita']:'0' ?><br/>
													Numero ricevuta fiscale: <?php echo !empty($service['numero_ricevuta'])?$service['numero_ricevuta']:'' ?><br/>
													del: <?php echo !empty($service['data_ricevuta'])?$service['data_ricevuta']:'' ?><br/>
												</div>
							<?php			
									}
								}
								if ($counter >= 1) { echo "</div>"; }
							?>
							</div>
						</div>
					</div>
				</div>
			</div>
		<!--#############################################################################################################-->
		<!--TAB - PRESA VISIONE                                                                                          -->
		<!--#############################################################################################################-->
			<div class="tab-pane" id="presa_visione_tab">
				<div class="form-group has-feedback">
					<div><label class="control-label" for="presa_visione">Presa visione:</label></div>
					<div class="input-group">
						<span class="glyphicon glyphicon-align-justify input-group-addon"></span>
						<textarea class="form-control" id="presa_visione" name="presa_visione" rows="10" maxlength="250"><?php echo htmlentities(!empty($order_data['presa_visione'])?$order_data['presa_visione']:'') ?></textarea>
					</div>
					<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
					<div class="help-block with-errors"></div>
				</div>
			</div>
		<!--#############################################################################################################-->
		<!--TAB - NOTE                                                                                                   -->
		<!--#############################################################################################################-->
			<div class="tab-pane" id="note_tab">
				<div class="form-group has-feedback">
					<div><label class="control-label" for="note">Note:</label></div>
					<div class="input-group">
						<span class="glyphicon glyphicon-align-justify input-group-addon"></span>
						<textarea class="form-control" id="note" name="note" rows="10" maxlength="250"><?php echo htmlentities(!empty($order_data['note_lavorazione'])?$order_data['note_lavorazione']:'') ?></textarea>
					</div>
					<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
					<div class="help-block with-errors"></div>
				</div>
			</div>
		</div>
		<!--#############################################################################################################-->
		<!--FINE TABS                                                                                                    -->
		<!--#############################################################################################################-->
		<br><br><br><br>
		<!--<input type="hidden" id="id_servizio" name="id_servizio" value="<?php echo !empty($service_data['id_servizio'])?$service_data['id_servizio']:'' ?>">-->
		<!--<input type="hidden" id="id_categoria" name="id_categoria" value="<?php echo !empty($service_data['id_categoria'])?$service_data['id_categoria']:'0' ?>">-->
		<input type="hidden" id="action" name="action" value="<?php echo !empty($_GET['action'])?$_GET['action']:'' ?>">
		<input type="hidden" id="id_lavorazione" name="id_lavorazione" value="<?php echo !empty($_GET['id_lavorazione'])?$_GET['id_lavorazione']:'0' ?>">
		<div class="col-xs-12">
			<button id="save-button" type="submit" class="btn btn-primary btn-lg" >Salva</button>
			<button id="cancel-button" type="button" class="btn btn-default btn-md" >Annulla</button>
		</div>
	</form>
	<?php
		}
		
		if ($show_delete==1) {
	?>
	<form data-toggle="validator" id="delete_order_form" role="form" action="process_order.php" method="post" name="delete_order_form" data-disable="true">
		<fieldset disabled>
			<div class="form-group has-feedback">
				<div><label class="control-label" for="id_lavorazione"><?php echo htmlentities('N° Lavorazione:') ?></label></div>
				<div class="input-group">
					<span class="glyphicon glyphicon glyphicon-barcode input-group-addon"></span>
					<input type="text" class="form-control" value="<?php echo !empty($order_data['id_lavorazione'])?$order_data['id_lavorazione']:'' ?>">
					<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
				</div>
				<div class="help-block with-errors"></div>
			</div>
			<div class="form-group has-feedback">
				<div><label class="control-label" for="cliente">Cliente:</label></div>
				<div class="input-group">
					<span class="glyphicon glyphicon-user input-group-addon"></span>
					<input type="text" class="form-control" id="cliente" name="cliente" value="<?php echo trim(htmlentities(!empty($order_data["nome"])?$order_data["nome"]:'') . ' ' . htmlentities(!empty($order_data["cognome"])?$order_data["cognome"]:'')) ?>">
					<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
				</div>
				<div class="help-block with-errors"></div>
			</div>
		</fieldset>
		<br><br><br>
		<?php echo "<script>current_service_total = parseFloat(current_service_total) + (parseFloat('" . $order_data['tot_lavorazione'] . "')||0);$('#totale_prezzo').html(current_service_total.toFixed(2));</script>"; ?>
		<input type="hidden" id="id_lavorazione" name="id_lavorazione" value="<?php echo !empty($order_data['id_lavorazione'])?$order_data['id_lavorazione']:'' ?>">
		<input type="hidden" id="action" name="action" value="<?php echo !empty($_GET['action'])?$_GET['action']:'' ?>">
		<button id="delete-button" type="submit" class="btn btn-danger btn-lg" >Elimina</button>
		<button id="cancel-button" type="button" class="btn btn-default btn-md" >Annulla</button>
	</form>
	<?php
		}

		if ($show_fiscal==1) {
	?>
	<form data-toggle="validator" id="fiscal_order_form" role="form" action="process_order.php" method="post" name="fiscal_order_form" data-disable="true">
		<fieldset disabled>
			<div class="form-group has-feedback">
				<div><label class="control-label" for="id_lavorazione"><?php echo htmlentities('N° Lavorazione:') ?></label></div>
				<div class="input-group">
					<span class="glyphicon glyphicon glyphicon-barcode input-group-addon"></span>
					<input type="text" class="form-control" value="<?php echo !empty($order_data['id_lavorazione'])?$order_data['id_lavorazione']:'' ?>">
					<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
				</div>
				<div class="help-block with-errors"></div>
			</div>
			<div class="form-group has-feedback">
				<div><label class="control-label" for="cliente">Cliente:</label></div>
				<div class="input-group">
					<span class="glyphicon glyphicon-user input-group-addon"></span>
					<input type="text" class="form-control" id="cliente" name="cliente" value="<?php echo trim(htmlentities(!empty($order_data["nome"])?$order_data["nome"]:'') . ' ' . htmlentities(!empty($order_data["cognome"])?$order_data["cognome"]:'')) ?>">
					<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
				</div>
				<div class="help-block with-errors"></div>
			</div>
			<div class="row">
				<div class="form-group has-feedback col-xs-6">
					<div><label class="control-label" for="cliente">Totale lavorazione:</label></div>
					<div class="input-group">
						<span class="glyphicon glyphicon-euro input-group-addon"></span>
						<input type="text" class="form-control" id="totale_prezzo" name="totale_prezzo" value="<?php echo $curfmt->formatCurrency(!empty($order_data["tot_lavorazione"])?$order_data["tot_lavorazione"]:0, "EUR") ?>" />
					</div>
				</div>
				<div class="form-group has-feedback col-xs-6">
					<div><label class="control-label" for="cliente">Totale a pagare:</label></div>
					<div class="input-group">
						<span class="glyphicon glyphicon-euro input-group-addon"></span>
						<input type="text" class="form-control" id="totale_parziale_prezzo" name="totale_parziale_prezzo" value="<?php echo $curfmt->formatCurrency(!empty($order_data["tot_lavorazione"])?$order_data["tot_lavorazione"]:0, "EUR") ?>" disabled />
					</div>
				</div>
			</div>
		</fieldset>
		<div class="form-group">
			<div><label class="control-label" for="cliente">Protocollo ricevuta:</label></div>
			<div class="form-group row">
				<div class="col-xs-4">
					<div class="input-group">
						<input type="text" class="form-control" id="tipo_fiscale_display" name="tipo_fiscale_display" value="<?php echo !empty($order_data["tipo_fiscale_ricevuta"])?$order_data["tipo_fiscale_ricevuta"]:$lavorazioni_tipo_fiscale ?>" disabled />
						<div class="input-group-addon"><span class="glyphicon glyphicon-file"></span></div>
					</div>
				</div>
				<input type="hidden" id="tipo_fiscale_ricevuta" name="tipo_fiscale_ricevuta" value="<?php echo $lavorazioni_tipo_fiscale ?>" />
				<div class="col-xs-4">
					<!--<input type="number" class="form-control" id="numero_fiscale_ricevuta" name="numero_fiscale_ricevuta" value="<?php echo $order_data["numero_fiscale_ricevuta"] ?>" <?php echo empty($order_data["numero_fiscale_ricevuta"])?'':'disabled' ?>/>-->
					<div class="input-group">
						<input type="number" class="form-control" id="numero_fiscale_ricevuta" name="numero_fiscale_ricevuta" value="" />
						<div class="input-group-addon"><span class="glyphicon glyphicon-tags"></span></div>
					</div>
				</div>
				<div class="col-xs-4">
					<!--<input type="number" class="form-control" id="anno_fiscale_ricevuta" name="anno_fiscale_ricevuta" value="<?php echo $order_data["anno_fiscale_ricevuta"] ?>" <?php echo empty($order_data["anno_fiscale_ricevuta"])?'':'disabled' ?>/>-->
					<div class="input-group">
						<input type="number" class="form-control" id="anno_fiscale_ricevuta" name="anno_fiscale_ricevuta" value="" />
						<div class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></div>
					</div>
				</div>
				<!--<div class="col-xs-3">
					<input type="text" class="form-control" id="data_fiscale_ricevuta" name="data_fiscale_ricevuta" value="" disabled />
				</div>-->
			</div>
		</div>
		<!--#############################################################################################################-->
		<!--TABS                                                                                                         -->
		<!--#############################################################################################################-->
		<ul class="nav nav-tabs" id="ArtSerTab">
			<li class="active"><a href="#articoli" data-toggle="tab"><span class="glyphicon glyphicon-bed"></span>&nbsp;Articoli</a></li>
			<li><a href="#servizi" data-toggle="tab"><span class="glyphicon glyphicon-thumbs-up"></span>&nbsp;Servizi</a></li>
		</ul>
		<br>
		<!--#############################################################################################################-->
		<!--TAB - ARTICOLI                                                                                               -->
		<!--#############################################################################################################-->
		<div class="tab-content">
			<div class="tab-pane active" id="articoli">
		<?php
			$cats_coll = get_order_category_list('A', $mysqli);
			unset($cats_coll[sizeof($cats_coll) - 1]);
			foreach ($cats_coll as $cat) {
		?>
				<div class="panel-group col-xs-12">
					<div class="panel panel-default">
						<div class="panel-heading">
								<h4 class="panel-title">
									<a data-toggle="collapse" href="#articoli_collapse<?php echo $cat['id_categoria'] ?>"><?php echo htmlentities($cat['descrizione']) ?></a>
								</h4>
							</div>
							<div id="articoli_collapse<?php echo $cat['id_categoria'] ?>" class="panel-collapse collapse <?php echo $cat['collapsed']==1?'':'in' ?>">
							<div class="panel-body">
							<?php
								if($_GET["action"]=="new"){
									$items_coll = get_order_item_list($cat['id_categoria'], $mysqli);
								} else {
									$items_coll = get_order_item_data($order_data["id_lavorazione"], $cat['id_categoria'], $mysqli);
								}
								unset($items_coll[sizeof($items_coll) - 1]);
								$counter = 0;
								$last_id_articolo = -1;
								foreach ($items_coll as $item) {
									if ((!empty($item['quantita'])?$item['quantita']:'0')>0) {
										$quantita_residua = (!empty($item['quantita'])?$item['quantita']:'0') - (!empty($item['quantita_evasa'])?$item['quantita_evasa']:'0');
										if ($last_id_articolo != $item['id_articolo']) {
											$counter += 1;
											if ($counter > 1) { echo "</div>"; }
							?>
												<div class="form-inline col-xs-12 <?php echo $counter%2==0?'bg-info':'' ?>">
													<label class="control-label col-xs-6"><?php echo $item['descrizione'] ?></label>
													<div class="form-group has-feedback col-xs-6">
														<input type="number" id="articolo_<?php echo $item['id_articolo'] ?>" name="articolo_<?php echo $item['id_articolo'] ?>" class="form-control" placeholder="0" value="<?php echo $quantita_residua ?>" min="0" max="<?php echo $quantita_residua ?>" required/>
														<span class="glyphicon form-control-feedback"></span>
														<div class="help-block with-errors"></div>
													</div>
							<?php
											$last_id_articolo = $item['id_articolo'];
										}
										if ((!empty($item['quantita_evasa'])?$item['quantita_evasa']:'0')>0) {
							?>
													<div class="form-group col-xs-4">
														Quantità evasa: <b><?php echo !empty($item['quantita_evasa_ricevuta'])?$item['quantita_evasa_ricevuta']:'0' ?></b> / <?php echo !empty($item['quantita'])?$item['quantita']:'0' ?><br/>
														Numero ricevuta fiscale: <?php echo !empty($item['numero_ricevuta'])?$item['numero_ricevuta']:'' ?><br/>
														del: <?php echo !empty($item['data_ricevuta'])?$item['data_ricevuta']:'' ?><br/>
													</div>
							<?php			
										}
									}
								}
								if ($counter >= 1) { echo "</div>"; }
								if ($counter <= 0) {
									echo "<script>$('#articoli_collapse" . $cat['id_categoria'] . "').closest('div[class^=\"panel-group\"]').remove();</script>";
								}
							?>
							</div>
						</div>
					</div>
				</div>
		<?php
			}
		?>
				<div class="panel-group col-xs-12">
					<div class="panel panel-default">
						<div class="panel-heading">
								<h4 class="panel-title">
									<a data-toggle="collapse" href="#articoli_collapse_last">Altro</a>
								</h4>
							</div>
							<div id="articoli_collapse_last" class="panel-collapse collapse">
							<div class="panel-body">
							<?php
								if($_GET["action"]=="new"){
									$items_coll = get_order_item_list(0, $mysqli);
								} else {
									$items_coll = get_order_item_data($order_data["id_lavorazione"], 0, $mysqli);
								}
								unset($items_coll[sizeof($items_coll) - 1]);
								$counter = 0;
								$last_id_articolo = -1;
								foreach ($items_coll as $item) {
									if ((!empty($item['quantita'])?$item['quantita']:'0')>0) {
										$quantita_residua = (!empty($item['quantita'])?$item['quantita']:'0') - (!empty($item['quantita_evasa'])?$item['quantita_evasa']:'0');
										if ($last_id_articolo != $item['id_articolo']) {
											$counter += 1;
											if ($counter > 1) { echo "</div>"; }
							?>
												<div class="form-inline col-xs-12 <?php echo $counter%2==0?'bg-info':'' ?>">
													<label class="control-label col-xs-6"><?php echo $item['descrizione'] ?></label>
													<div class="form-group has-feedback col-xs-6">
														<input type="number" id="articolo_<?php echo $item['id_articolo'] ?>" name="articolo_<?php echo $item['id_articolo'] ?>" class="form-control" placeholder="0" value="<?php echo $quantita_residua ?>" min="0" max="<?php echo $quantita_residua ?>" required/>
														<span class="glyphicon form-control-feedback"></span>
														<div class="help-block with-errors"></div>
													</div>
							<?php
											$last_id_articolo = $item['id_articolo'];
										}
										if ((!empty($item['quantita_evasa'])?$item['quantita_evasa']:'0')>0) {
							?>
													<div class="form-group col-xs-4">
														Quantità evasa: <b><?php echo !empty($item['quantita_evasa_ricevuta'])?$item['quantita_evasa_ricevuta']:'0' ?></b> / <?php echo !empty($item['quantita'])?$item['quantita']:'0' ?><br/>
														Numero ricevuta fiscale: <?php echo !empty($item['numero_ricevuta'])?$item['numero_ricevuta']:'' ?><br/>
														del: <?php echo !empty($item['data_ricevuta'])?$item['data_ricevuta']:'' ?><br/>
													</div>
							<?php			
										}
									}
								}
								if ($counter >= 1) { echo "</div>"; }
								if ($counter <= 0) {
									echo "<script>$('#articoli_collapse_last').closest('div[class^=\"panel-group\"]').remove();</script>";
								}
							?>
							</div>
						</div>
					</div>
				</div>
			</div>
		<!--#############################################################################################################-->
		<!--TAB - SERVIZI                                                                                                -->
		<!--#############################################################################################################-->
			<div class="tab-pane" id="servizi">
		<?php
			$cats_coll = get_order_category_list('S', $mysqli);
			unset($cats_coll[sizeof($cats_coll) - 1]);
			foreach ($cats_coll as $cat) {
		?>
				<div class="panel-group col-xs-12">
					<div class="panel panel-default">
						<div class="panel-heading">
								<h4 class="panel-title">
									<a data-toggle="collapse" href="#servizi_collapse<?php echo $cat['id_categoria'] ?>"><?php echo $cat['descrizione'] ?></a>
								</h4>
							</div>
							<div id="servizi_collapse<?php echo $cat['id_categoria'] ?>" class="panel-collapse collapse <?php echo $cat['collapsed']==1?'':'in' ?>">
							<div class="panel-body">
							<?php
								if($_GET["action"]=="new"){
									$services_coll = get_order_service_list($cat['id_categoria'], $mysqli);
								} else {
									$services_coll = get_order_service_data($order_data["id_lavorazione"], $cat['id_categoria'], $mysqli);
								}
								unset($services_coll[sizeof($services_coll) - 1]);
								$counter = 0;
								$last_id_servizio = -1;
								foreach ($services_coll as $service) {
									if ((!empty($service['quantita'])?$service['quantita']:'0')>0) {
										$quantita_residua = (!empty($service['quantita'])?$service['quantita']:'0') - (!empty($service['quantita_evasa'])?$service['quantita_evasa']:'0');
										if ($last_id_servizio != $service['id_servizio']) {
											$counter += 1;
											if ($counter > 1) { echo "</div>"; }
							?>
												<div class="form-inline col-xs-12 <?php echo $counter%2==0?'bg-info':'' ?>">
													<label class="control-label col-xs-4"><?php echo $service['descrizione'] ?></label>
													<label class="control-label col-xs-3 service_price_label" prezzo="<?php echo $_GET["action"]=="new"?$service['prezzo']:(isset($service['prezzo_lavorazione'])?$service['prezzo_lavorazione']:$service['prezzo']) ?>"><?php echo $curfmt->formatCurrency($_GET["action"]=="new"?$service['prezzo']:(isset($service['prezzo_lavorazione'])?$service['prezzo_lavorazione']:$service['prezzo']), "EUR") ?></label>
													<?php
														if($_GET["action"]!="new" && isset($service['prezzo_lavorazione'])){
															echo "<script>current_service_total = parseFloat(current_service_total) + ((parseFloat('" . $service['prezzo_lavorazione'] . "')||0) * (parseFloat('" . $quantita_residua . "')||0));</script>";
														}
														if($_GET["action"]!="new" && isset($service['prezzo_lavorazione']) && $service['prezzo_lavorazione']!=$service['prezzo']){
													?>
															<span class="glyphicon glyphicon-refresh col-xs-1"></span>
													<?php
														} else {
													?>
															<span class="col-xs-1"></span>
													<?php
														}
													?>
													<div class="form-group has-feedback col-xs-4">
														<input type="number" id="servizio_<?php echo $service['id_servizio'] ?>" name="servizio_<?php echo $service['id_servizio'] ?>" class="form-control" placeholder="0" value="<?php echo $quantita_residua ?>" min="0" max="<?php echo $quantita_residua ?>" required/>
														<input type="hidden" id="prezzo_servizio_<?php echo $service['id_servizio'] ?>" name="prezzo_servizio_<?php echo $service['id_servizio'] ?>" value="<?php echo $_GET["action"]=="new"?$service['prezzo']:(isset($service['prezzo_lavorazione'])?$service['prezzo_lavorazione']:$service['prezzo']) ?>">
														<span class="glyphicon form-control-feedback"></span>
														<div class="help-block with-errors"></div>
													</div>
												<!--</div>-->
							<?php
											$last_id_servizio = $service['id_servizio'];
										}
										if ((!empty($service['quantita_evasa'])?$service['quantita_evasa']:'0')>0) {
							?>
													<div class="form-group col-xs-4">
														Quantità evasa: <b><?php echo !empty($service['quantita_evasa_ricevuta'])?$service['quantita_evasa_ricevuta']:'0' ?></b> / <?php echo !empty($service['quantita'])?$service['quantita']:'0' ?><br/>
														Numero ricevuta fiscale: <?php echo !empty($service['numero_ricevuta'])?$service['numero_ricevuta']:'' ?><br/>
														del: <?php echo !empty($service['data_ricevuta'])?$service['data_ricevuta']:'' ?><br/>
													</div>
							<?php			
										}
									}
								}
								if ($counter >= 1) { echo "</div>"; }
								if ($counter <= 0) {
									echo "<script>$('#servizi_collapse" . $cat['id_categoria'] . "').closest('div[class^=\"panel-group\"]').remove();</script>";
								}
							?>
							</div>
						</div>
					</div>
				</div>
		<?php
			}
		?>
				<div class="panel-group col-xs-12">
					<div class="panel panel-default">
						<div class="panel-heading">
								<h4 class="panel-title">
									<a data-toggle="collapse" href="#servizi_collapse_last">Altro</a>
								</h4>
							</div>
							<div id="servizi_collapse_last" class="panel-collapse collapse">
							<div class="panel-body">
							<?php
								if($_GET["action"]=="new"){
									$services_coll = get_order_service_list(0, $mysqli);
								} else {
									$services_coll = get_order_service_data($order_data["id_lavorazione"], 0, $mysqli);
								}
								unset($services_coll[sizeof($services_coll) - 1]);
								$counter = 0;
								$last_id_servizio = -1;
								foreach ($services_coll as $service) {
									if ((!empty($service['quantita'])?$service['quantita']:'0')>0) {
										$quantita_residua = (!empty($service['quantita'])?$service['quantita']:'0') - (!empty($service['quantita_evasa'])?$service['quantita_evasa']:'0');
										if ($last_id_servizio != $service['id_servizio']) {
											$counter += 1;
											if ($counter > 1) { echo "</div>"; }
							?>
												<div class="form-inline col-xs-12 <?php echo $counter%2==0?'bg-info':'' ?>">
													<label class="control-label col-xs-4"><?php echo $service['descrizione'] ?></label>
													<label class="control-label col-xs-3 service_price_label" prezzo="<?php echo $_GET["action"]=="new"?$service['prezzo']:(isset($service['prezzo_lavorazione'])?$service['prezzo_lavorazione']:$service['prezzo']) ?>"><?php echo $curfmt->formatCurrency($_GET["action"]=="new"?$service['prezzo']:(isset($service['prezzo_lavorazione'])?$service['prezzo_lavorazione']:$service['prezzo']), "EUR") ?></label>
													<?php
														if($_GET["action"]!="new" && isset($service['prezzo_lavorazione'])){
															echo "<script>current_service_total = parseFloat(current_service_total) + ((parseFloat('" . $service['prezzo_lavorazione'] . "')||0) * (parseFloat('" . $quantita_residua . "')||0));</script>";
														}
														if($_GET["action"]!="new" && isset($service['prezzo_lavorazione']) && $service['prezzo_lavorazione']!=$service['prezzo']){
													?>
															<span class="glyphicon glyphicon-refresh col-xs-1"></span>
													<?php
														} else {
													?>
															<span class="col-xs-1"></span>
													<?php
														}
													?>
													<div class="form-group has-feedback col-xs-4">
														<input type="number" id="servizio_<?php echo $service['id_servizio'] ?>" name="servizio_<?php echo $service['id_servizio'] ?>" class="form-control" placeholder="0" value="<?php echo $quantita_residua ?>" min="0" max="<?php echo $quantita_residua ?>" required/>
														<input type="hidden" id="prezzo_servizio_<?php echo $service['id_servizio'] ?>" name="prezzo_servizio_<?php echo $service['id_servizio'] ?>" value="<?php echo $_GET["action"]=="new"?$service['prezzo']:(isset($service['prezzo_lavorazione'])?$service['prezzo_lavorazione']:$service['prezzo']) ?>">
														<span class="glyphicon form-control-feedback"></span>
														<div class="help-block with-errors"></div>
													</div>
												<!--</div>-->
							<?php
											$last_id_servizio = $service['id_servizio'];
										}
										if ((!empty($service['quantita_evasa'])?$service['quantita_evasa']:'0')>0) {
							?>
													<div class="form-group col-xs-4">
														Quantità evasa: <b><?php echo !empty($service['quantita_evasa_ricevuta'])?$service['quantita_evasa_ricevuta']:'0' ?></b> / <?php echo !empty($service['quantita'])?$service['quantita']:'0' ?><br/>
														Numero ricevuta fiscale: <?php echo !empty($service['numero_ricevuta'])?$service['numero_ricevuta']:'' ?><br/>
														del: <?php echo !empty($service['data_ricevuta'])?$service['data_ricevuta']:'' ?><br/>
													</div>
							<?php			
										}
									}
								}
								if ($counter >= 1) { echo "</div>"; }
								if ($counter <= 0) {
									echo "<script>$('#servizi_collapse_last').closest('div[class^=\"panel-group\"]').remove();</script>";
								}
							?>
							</div>
						</div>
					</div>
				</div>
			</div>
		<!--#############################################################################################################-->
		<!--FINE TABS                                                                                                    -->
		<!--#############################################################################################################-->
		
		<br/><br/><br/>
		<input type="hidden" id="id_lavorazione" name="id_lavorazione" value="<?php echo !empty($order_data['id_lavorazione'])?$order_data['id_lavorazione']:'' ?>">
		<input type="hidden" id="action" name="action" value="<?php echo !empty($_GET['action'])?$_GET['action']:'' ?>">
		<!--<input type="hidden" id="reprint_fiscal" name="reprint_fiscal" value="<?php echo !empty($order_data["numero_fiscale_ricevuta"])?'1':'0' ?>">-->
		<input type="hidden" id="reprint_fiscal" name="reprint_fiscal" value="0">
		<!--<button id="fiscal-button" type="button" class="btn btn-primary btn-lg" action-type="<?php echo empty($order_data["numero_fiscale_ricevuta"])?'E':'R' ?>"><?php echo empty($order_data["numero_fiscale_ricevuta"])?'Emetti e Stampa':'Ristampa' ?></button>-->
		<button id="fiscal-button" type="button" class="btn btn-primary btn-lg" action-type="E">Emetti e Stampa</button>
		<button id="cancel-button" type="button" class="btn btn-default btn-md" >Annulla</button>
	</form>
	<?php
		}
	?>
	</div>

	<script type="text/javascript">
		$("#fiscal-button").on('click', function() {
			if($(this).attr('action-type')=='E') { //PRIMA EMISSIONE
				$("#fiscal_order_form").submit();
			} else { //RISTAMPA
				bootbox.confirm("Attenzione! Assicurarsi di aver rimosso dalla stampante i fogli numerati delle ricevute fiscali. Questa &egrave; una <b>RISTAMPA</b>. Procedere?", function(result) {
					if(result===true){
						$("#fiscal_order_form").submit();
					}
				}); 
			}
		});
		
		var current_service_quantity = 0;
		$("#categoria_list li a").click(function(){
			$(this).parents(".dropdown").find(".dropdown-toggle").html($(this).html() + ' <span class="caret"></span>');
			$('input[name="id_categoria"]').val($(this).parents("li").attr("value"));
		});	

		$("#cancel-button").on('click', function(){
			window.close();    
		});

		$('#cliente').autocomplete({
			source: function( request, response ) {
				$.ajax({
					url : 'ac_get_customers.php',
					dataType: "json",
					method: "POST",
					data: {
					   name_startsWith: request.term,
					},
					success: function( data ) {
						if (typeof data.error === 'undefined') {
							response( $.map( data, function( item ) {
								var code = item.split("|");
								return {
									label: code[1],
									value: code[2],
									data : item
								}
							}));
						}
					}
				});
			},
			autoFocus: true,	      	
			minLength: 0,
			select: function( event, ui ) {
				var customers = ui.item.data.split("|");						
				$('#id_cliente').val(customers[0]);
				$('#id_cliente_visible').val(customers[0]);
				$('#indirizzo').val(customers[5]);
				$('#telefono').val(customers[3]);
				$('#cellulare').val(customers[4]);
				
				$("#sms").val()!=customers[6]?$("#sms_yes").trigger('click'):false;
			}		
		});

		$('#data_consegna').datepicker({
			language: 'it'
		});
		$('#data_ritiro').datepicker({
			language: 'it'
		});
		$('#data_sms').datepicker({
			language: 'it'
		});
		$("#sms_yes").click(function() {
		  $(this).toggleClass("btn-info");
		  $("#sms_no").toggleClass("btn-info");
		  $("#sms").val($("#sms").val()=="S"?"N":"S");
		});
		$("#sms_no").click(function() {
		  $(this).toggleClass("btn-info");
		  $("#sms_yes").toggleClass("btn-info");
		  $("#sms").val($("#sms").val()=="S"?"N":"S");
		});

		$("#stato_list li a").click(function(){
			$(this).parents(".btn-group").find(".dropdown-toggle").html($(this).html() + ' <span class="caret"></span>');
			$('input[name="stato"]').val($(this).parents("li").attr('value'));
		});	

		$('input[id^="servizio_"]').focus(
			function(){
				current_service_quantity = parseFloat($(this).val());
			}
		);
		
		$('input[id^="servizio_"]').change(
			function(){
				current_service_quantity = current_service_quantity || 0;
				new_service_quantity = parseFloat($(this).val()) || 0;
				if (getParameterByName('action')=='fiscal') {
					current_service_total = parseFloat($('#totale_parziale_prezzo').attr('value')) || 0;
					current_service_total = parseFloat(current_service_total) + ((new_service_quantity-current_service_quantity)*parseFloat($(this).parent().parent().find('.service_price_label').attr("prezzo")));
					$('#totale_parziale_prezzo').attr('value', current_service_total.toFixed(2));
				} else {
					current_service_total = parseFloat($('#totale_prezzo').html()) || 0;
					current_service_total = parseFloat(current_service_total) + ((new_service_quantity-current_service_quantity)*parseFloat($(this).parent().parent().find('.service_price_label').attr("prezzo")));
					$('#totale_prezzo').html(current_service_total.toFixed(2));
				}
			}
		);
		
		$('#id_cliente_visible').change(function(){
			var request = $.ajax({
				url: "ac_get_customers.php",
				method: "POST",
				data: { id_cliente : $('#id_cliente_visible').val() },
				dataType: "json"
			});
			 
			request.done(function(msg) {
				if (typeof msg.error === 'undefined') {
					var customers = msg.toString().split('|');
					//alert(customers[5]);
					$('#id_cliente').val(customers[0]);
					$('#id_cliente_visible').val(customers[0]);
					$('#cliente').val(customers[2]);
					$('#indirizzo').val(customers[5]);
					$('#telefono').val(customers[3]);
					$('#cellulare').val(customers[4]);

					$("#sms").val()!=customers[6]?$("#sms_yes").trigger('click'):false;
				}
			});
			 
			request.fail(function( jqXHR, textStatus ) {
				alert( "Errore in fase di recupero del nuovo cliente. Contattare il supporto. [" + textStatus + "]");
			});
		});

		function getFiscalNumber(tipo_fiscale_val, anno_fiscale_val){
			$('#fiscal-button').attr('disabled',true);
			
			var request = $.ajax({
				url: "ac_get_next_number.php",
				method: "POST",
				data: { tipo_fiscale : tipo_fiscale_val, anno_fiscale : anno_fiscale_val },
				dataType: "json"
			});
			 
			request.done(function(msg) {
				if (typeof msg.error === 'undefined') {
					var numbers = msg.toString().split('|');
					$('#numero_fiscale_ricevuta').attr('value', numbers[1]);
					$('#anno_fiscale_ricevuta').attr('value', numbers[4]);
					$('#fiscal-button').attr('disabled',false);
				}
			});
			 
			request.fail(function( jqXHR, textStatus ) {
				alert( "Errore in fase di recupero del protocollo fiscale. Contattare il supporto. [" + textStatus + "]");
			});
		}
		
		$(document).ready(function(){
			if ((!$('#numero_fiscale_ricevuta').attr('value')) && (getParameterByName('action')=='fiscal')) {
				var currentTime = new Date();
				var year = currentTime.getFullYear();
				getFiscalNumber($('#tipo_fiscale_ricevuta').attr('value'), year);
			}

			$("[data-toggle=tooltip]").tooltip({
				placement: 'auto top'
			});
			
			$('#totale_parziale_prezzo').attr('value', current_service_total.toFixed(2));
		});
		
		function getParameterByName(name, url) {
			if (!url) url = window.location.href;
			name = name.replace(/[\[\]]/g, "\\$&");
			var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
			results = regex.exec(url);
			if (!results) return null;
			if (!results[2]) return '';
			return decodeURIComponent(results[2].replace(/\+/g, " "));
		}
	</script>
</body>
</html>