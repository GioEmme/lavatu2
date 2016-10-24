<?php
	include_once('includes/header.php');
	if(login_check($mysqli) == false) {
		header('Location: login.php');
	}
?>
<!DOCTYPE html><!-- PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"-->
<!--<html xmlns="http://www.w3.org/1999/xhtml">-->
<head>
	<?php
		include_once('includes/html_header.php');
	?>
</head>
<body>
	<div class="bg-outer">
		<div class="container gray-dark">
			<?php include_once('includes/navbar.php'); ?>
			<div class="page-header">
				<h1>Lavorazioni <small>Ordini</small></h1>
			</div>
			<form data-toggle="validator" id="search_form" role="form" action="order_list.php" method="post" name="search_form" data-disable="true">
				<!-- ***************************************************** -->
				<!--                  FILTRI DI RICERCA                    -->
				<!-- ***************************************************** -->
				<div class="col-md-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#filter">
									<span class="glyphicon glyphicon-filter"></span>Filtri di ricerca
								</a>
							</h4>
						</div>
						<div id="filter" class="panel-collapse collapse">
							<div class="panel-body">
								<!-- ***************************************************** -->
								<!--                  ID LAVORAZIONE                       -->
								<!-- ***************************************************** -->
								<div class="form-group col-md-3">
									<label class="control-label" for="id_lavorazione">ID Lavorazione:</label>
									<div class="input-group">
										<div class="input-group-btn">
											<button type="button" class="btn btn-default dropdown-toggle input-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo !empty($_POST["id_lavorazione_searchop"])?$_POST["id_lavorazione_searchop"]:'=' ?> <span class="caret"></span></button>
											<ul class="dropdown-menu" id="id_lavorazione_searchop_list">
												<li><a href="#">=</a></li>
												<li><a href="#">>=</a></li>
												<li><a href="#"><=</a></li>
												<li><a href="#">diverso</a></li>
											</ul>
										</div>
										<input type="number" class="form-control input-sm" id="id_lavorazione" name="id_lavorazione" value="<?php echo !empty($_POST["id_lavorazione"])?$_POST["id_lavorazione"]:'' ?>">
										<input type="hidden" id="id_lavorazione_searchop" name="id_lavorazione_searchop" value="<?php echo !empty($_POST["id_lavorazione_searchop"])?$_POST["id_lavorazione_searchop"]:'=' ?>">
									</div>
								</div>
								<!-- ***************************************************** -->
								<!--                  COGNOME CLIENTE                      -->
								<!-- ***************************************************** -->
								<div class="form-group col-md-3">
									<label class="control-label" for="cognome_cliente">Cognome cliente:</label>
									<div class="input-group">
										<div class="input-group-btn">
											<button type="button" class="btn btn-default dropdown-toggle input-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo !empty($_POST["cognome_cliente_searchop"])?$_POST["cognome_cliente_searchop"]:'contiene' ?> <span class="caret"></span></button>
											<ul class="dropdown-menu" id="cognome_cliente_searchop_list">
												<li><a href="#">contiene</a></li>
												<li><a href="#">non contiene</a></li>
												<li><a href="#">=</a></li>
												<li><a href="#">>=</a></li>
												<li><a href="#"><=</a></li>
												<li><a href="#">diverso</a></li>
											</ul>
										</div>
										<input type="text" class="form-control input-sm" id="cognome_cliente" name="cognome_cliente" value="<?php echo !empty($_POST["cognome_cliente"])?$_POST["cognome_cliente"]:'' ?>">
										<input type="hidden" id="cognome_cliente_searchop" name="cognome_cliente_searchop" value="<?php echo !empty($_POST["cognome_cliente_searchop"])?$_POST["cognome_cliente_searchop"]:'contiene' ?>">
									</div>
								</div>
								<!-- ***************************************************** -->
								<!--                  DATA CONSEGNA DAL                    -->
								<!-- ***************************************************** -->
								<div class="form-group col-md-3">
									<label class="control-label" for="data_consegna_dal">Data consegna - dal:</label>
									<div class="input-group date" id="dtp_data_consegna_dal">
										<div class="input-group-btn">
											<button type="button" class="btn btn-default dropdown-toggle input-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo !empty($_POST["data_consegna_dal_searchop"])?$_POST["data_consegna_dal_searchop"]:'=' ?> <span class="caret"></span></button>
											<ul class="dropdown-menu" id="data_consegna_dal_searchop_list">
												<li><a href="#">=</a></li>
												<li><a href="#">>=</a></li>
												<li><a href="#"><=</a></li>
												<li><a href="#">diverso</a></li>
											</ul>
										</div>
										<input type="text" class="form-control input-sm" id="data_consegna_dal" name="data_consegna_dal" value="<?php echo !empty($_POST["data_consegna_dal"])?$_POST["data_consegna_dal"]:'' ?>">
										<div class="input-group-addon"><span class="glyphicon glyphicon-th"></span></div>
									</div>
								</div>
								<input type="hidden" id="data_consegna_dal_searchop" name="data_consegna_dal_searchop" value="<?php echo !empty($_POST["data_consegna_dal_searchop"])?$_POST["data_consegna_dal_searchop"]:'=' ?>">
								<!-- ***************************************************** -->
								<!--                  DATA CONSEGNA AL                    -->
								<!-- ***************************************************** -->
								<div class="form-group col-md-3">
									<label class="control-label" for="data_consegna_al">Data consegna - al:</label>
									<div class="input-group date" id="dtp_data_consegna_al">
										<div class="input-group-btn">
											<button type="button" class="btn btn-default dropdown-toggle input-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo !empty($_POST["data_consegna_al_searchop"])?$_POST["data_consegna_al_searchop"]:'=' ?> <span class="caret"></span></button>
											<ul class="dropdown-menu" id="data_consegna_al_searchop_list">
												<li><a href="#">=</a></li>
												<li><a href="#">>=</a></li>
												<li><a href="#"><=</a></li>
												<li><a href="#">diverso</a></li>
											</ul>
										</div>
										<input type="text" class="form-control input-sm" id="data_consegna_al" name="data_consegna_al" value="<?php echo !empty($_POST["data_consegna_al"])?$_POST["data_consegna_al"]:'' ?>">
										<div class="input-group-addon"><span class="glyphicon glyphicon-th"></span></div>
									</div>
									<input type="hidden" id="data_consegna_al_searchop" name="data_consegna_al_searchop" value="<?php echo !empty($_POST["data_consegna_al_searchop"])?$_POST["data_consegna_al_searchop"]:'=' ?>">
								</div>
								<!-- ***************************************************** -->
								<!--                  STATO                                -->
								<!-- ***************************************************** -->
								<div class="form-group col-md-3">
									<label class="control-label" for="stato">Stato:</label>
									<div class="btn-group btn-block" role="group">
										<div class="btn-group 1">
											<button type="button" class="btn btn-default dropdown-toggle input-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo !empty($_POST["stato_searchop"])?$_POST["stato_searchop"]:'=' ?> <span class="caret"></span></button>
											<ul class="dropdown-menu" id="stato_searchop_list">
												<li><a href="#">=</a></li>
											</ul>
										</div>
										<div class="btn-group 2">
											<?php
												if (!empty($_POST["stato"]) || !empty($_GET["stato"])) {
													$status = get_order_status_data('LAV', empty($_POST["stato"])?$_GET["stato"]:$_POST["stato"], $mysqli);
												}
											?>
											<button type="button" class="btn btn-default dropdown-toggle input-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo !empty($_POST["stato"])?(!empty($status[0]["descrizione"])?$status[0]["descrizione"]:'Qualsiasi'):(!empty($_GET["stato"])?(!empty($status[0]["descrizione"])?$status[0]["descrizione"]:'Qualsiasi'):'Qualsiasi') ?> <span class="caret"></span></button>
											<ul class="dropdown-menu" id="stato_list">
												<li value="*"><a href="#">Qualsiasi</a></li>
												<?php
													$status_coll = get_order_status_list('LAV', '0', $mysqli);
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
									<input type="hidden" class="form-control input-sm" id="stato" name="stato" value="<?php echo !empty($_POST["stato"])?$_POST["stato"]:(!empty($_GET["stato"])?$_GET["stato"]:'*') ?>">
									<input type="hidden" id="stato_searchop" name="stato_searchop" value="<?php echo !empty($_POST["stato_searchop"])?$_POST["stato_searchop"]:'=' ?>">
								</div>
								<!-- ***************************************************** -->
								<!--                     RICEVUTA                          -->
								<!-- ***************************************************** -->
								<div class="form-group col-md-3">
									<label class="control-label" for="numero_fiscale_ricevuta">N° Ricevuta:</label>
									<div class="input-group">
										<div class="input-group-btn">
											<button type="button" class="btn btn-default dropdown-toggle input-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo !empty($_POST["numero_fiscale_ricevuta_searchop"])?$_POST["numero_fiscale_ricevuta_searchop"]:'=' ?> <span class="caret"></span></button>
											<ul class="dropdown-menu" id="numero_fiscale_ricevuta_searchop_list">
												<li><a href="#">=</a></li>
												<li><a href="#">>=</a></li>
												<li><a href="#"><=</a></li>
												<li><a href="#">diverso</a></li>
											</ul>
										</div>
										<input type="number" class="form-control input-sm" id="numero_fiscale_ricevuta" name="numero_fiscale_ricevuta" value="<?php echo isset($_POST["numero_fiscale_ricevuta"])?$_POST["numero_fiscale_ricevuta"]:'' ?>">
										<input type="hidden" id="numero_fiscale_ricevuta_searchop" name="numero_fiscale_ricevuta_searchop" value="<?php echo !empty($_POST["numero_fiscale_ricevuta_searchop"])?$_POST["numero_fiscale_ricevuta_searchop"]:'=' ?>">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~ FINE FILTRI DI RICERCA -->
				<div class="col-md-12">
					<button id="search-button" type="submit" class="btn btn-primary btn-sm" title="Ricerca" data-toggle="tooltip"><span class="glyphicon glyphicon-search"></span></button>
					<button id="new-button" type="button" class="btn btn-primary btn-sm" onClick="javascript:windowpop('order_form.php?action=new',800,800,null,1)" title="Nuova lavorazione" data-toggle="tooltip"><span class="glyphicon glyphicon-plus tips"></span></button>
					<button id="move-status" type="button" class="btn btn-primary btn-sm" title="Avanza allo stato successivo" data-toggle="tooltip"><span class="glyphicon glyphicon-log-out"></span></button>
				</div>
				<br>
				<div class="table-responsive col-md-12">
					<input type="hidden" id="sort_index" name="sort_index" value="<?php echo !empty($_POST["sort_index"])?$_POST["sort_index"]:'id_lavorazione' ?>">
					<input type="hidden" id="sort_dir" name="sort_dir" value="<?php echo !empty($_POST["sort_dir"])?$_POST["sort_dir"]:'DESC' ?>">
					<table id="table_results" class="table table-hover table-striped">
						<thead>
							<tr>
								<th><span class="glyphicon glyphicon-log-out" id="check_all"></span></th>
								<th onClick="javascript:tableHeaderClick('id_lavorazione')">ID Lavorazione <?php echo !empty($_POST["sort_index"])?($_POST["sort_index"]=='id_lavorazione'?(!empty($_POST["sort_dir"])?($_POST["sort_dir"]=='ASC'?'<span class="glyphicon glyphicon-sort-by-attributes"></span>':'<span class="glyphicon glyphicon-sort-by-attributes-alt"></span>'):''):''):'' ?></th>
								<th onClick="javascript:tableHeaderClick('cognome_cliente')">Cliente <?php echo !empty($_POST["sort_index"])?($_POST["sort_index"]=='cognome_cliente'?(!empty($_POST["sort_dir"])?($_POST["sort_dir"]=='ASC'?'<span class="glyphicon glyphicon-sort-by-attributes"></span>':'<span class="glyphicon glyphicon-sort-by-attributes-alt"></span>'):''):''):'' ?></th>
								<th>Nome </th>
								<th onClick="javascript:tableHeaderClick('data_consegna')">Data consegna <?php echo !empty($_POST["sort_index"])?($_POST["sort_index"]=='data_consegna'?(!empty($_POST["sort_dir"])?($_POST["sort_dir"]=='ASC'?'<span class="glyphicon glyphicon-sort-by-attributes"></span>':'<span class="glyphicon glyphicon-sort-by-attributes-alt"></span>'):''):''):'' ?></th>
								<th onClick="javascript:tableHeaderClick('stato')">Stato <?php echo !empty($_POST["sort_index"])?($_POST["sort_index"]=='stato'?(!empty($_POST["sort_dir"])?($_POST["sort_dir"]=='ASC'?'<span class="glyphicon glyphicon-sort-by-attributes"></span>':'<span class="glyphicon glyphicon-sort-by-attributes-alt"></span>'):''):''):'' ?></th>
								<th>Invia SMS</th>
								<th onClick="javascript:tableHeaderClick('tot_lavorazione')">Totale <?php echo !empty($_POST["sort_index"])?($_POST["sort_index"]=='tot_lavorazione'?(!empty($_POST["sort_dir"])?($_POST["sort_dir"]=='ASC'?'<span class="glyphicon glyphicon-sort-by-attributes"></span>':'<span class="glyphicon glyphicon-sort-by-attributes-alt"></span>'):''):''):'' ?></th>
								<th>N° Ricevuta</th>
								<th>Azioni</th>
							</tr>
						</thead>
						<tbody>

							<?php
								$per_page = $Grids_PerPageRecords;
								$pages_gap = $Grids_PaginationPagesGap;
								$sort_index = !empty($_POST["sort_index"])?$_POST["sort_index"]:'id_lavorazione';
								$sort_dir = !empty($_POST["sort_dir"])?$_POST["sort_dir"]:'DESC';
								$items_coll = get_order_list(
																!empty($_POST["id_lavorazione"])?$_POST["id_lavorazione"]:'',
																!empty($_POST["id_lavorazione_searchop"])?$_POST["id_lavorazione_searchop"]:'=',
																!empty($_POST["cognome_cliente"])?$_POST["cognome_cliente"]:'',
																!empty($_POST["cognome_cliente_searchop"])?$_POST["cognome_cliente_searchop"]:'contiene',
																!empty($_POST["data_consegna_dal"])?$_POST["data_consegna_dal"]:'',
																!empty($_POST["data_consegna_dal_searchop"])?$_POST["data_consegna_dal_searchop"]:'=',
																!empty($_POST["data_consegna_al"])?$_POST["data_consegna_al"]:'',
																!empty($_POST["data_consegna_al_searchop"])?$_POST["data_consegna_al_searchop"]:'=',
																!empty($_POST["stato"])?$_POST["stato"]:(!empty($_GET["stato"])?$_GET["stato"]:''),
																!empty($_POST["stato_searchop"])?$_POST["stato_searchop"]:'=',
																isset($_POST["numero_fiscale_ricevuta"])?$_POST["numero_fiscale_ricevuta"]:'',
																!empty($_POST["numero_fiscale_ricevuta_searchop"])?$_POST["numero_fiscale_ricevuta_searchop"]:'=',
																!empty($_POST["search_page"])?$_POST["search_page"]:1,
																$per_page,
																$sort_index,
																$sort_dir,
																$mysqli);
								$current_search_page = !empty($_POST["search_page"])?$_POST["search_page"]:1;
								$page_count = ceil(!empty($items_coll[sizeof($items_coll) - 1])?$items_coll[sizeof($items_coll) - 1]["record_count"]/$per_page:1);
								unset($items_coll[sizeof($items_coll) - 1]);
								foreach ($items_coll as $item) {
									$status = get_order_next_status('LAV', $item["stato"], $mysqli);
							?>
									<tr id="tr_id_1" class="tr-class-1" <?php echo !empty($item["row_color"])?'style="background-color:'.$item["row_color"].' !important"':'' ?> >
										<td>
											<?php
												if (!empty($status[0])) {
											?>
													<input type="checkbox" name="chg_status_<?php echo $item["id_lavorazione"] . '_' . $status[0]['id_stato'] ?>" id="chg_status_<?php echo $item["id_lavorazione"] . '_' . $status[0]['id_stato'] ?>">
													<br>
													<h6><?php echo $status[0]['descrizione']; ?></h6>
											<?php
												}
											?>
										</td>
										<td><?php echo $item["id_lavorazione"] ?></td>
										<td><?php echo htmlentities($item["cognome_cliente"]) ?></td>
										<td><?php echo htmlentities($item["nome_cliente"]) ?></td>
										<td>
											<?php
												$fmtDate = DateTime::createFromFormat('Y-m-d', $item["data_consegna"]);
												$fmtDate = $fmtDate->format('d/m/Y');
												echo $fmtDate;
											?>
										</td>
										<td><?php echo htmlentities($item["descrizione_stato"]) ?></td>
										<td><?php echo $item["sms"]=='S'?'<span class="glyphicon glyphicon-ok"></span>':'' ?></td>
										<td><p class="text-right"><?php echo $curfmt->formatCurrency($item["tot_lavorazione"], "EUR") ?></p></td>
										<td>
											<?php
												$receipts_coll = get_order_receipts($item["id_lavorazione"], $mysqli);
												unset($receipts_coll[sizeof($receipts_coll) - 1]);
												foreach ($receipts_coll as $receipt) {
											?>
													<p class="text-right"><a onClick="reprintWarnMessage(<?php echo $item["id_lavorazione"] ?>,<?php echo $receipt["id_ricevuta"] ?>)"><?php echo $receipt["numero_fiscale_ricevuta"] ?></a></p>
											<?php
												}
											?>
										</td>
										<td>
											<button id="edit-button" type="button" class="btn btn-primary btn-sm" onClick="javascript:windowpop('order_form.php?action=edit&id_lavorazione=<?php echo $item["id_lavorazione"] ?>',800,800,null,1)" title="Modifica" data-toggle="tooltip"><span class="glyphicon glyphicon-pencil"></span></button>
											<button id="delete-button" type="button" class="btn btn-primary btn-sm" onClick="javascript:windowpop('order_form.php?action=delete&id_lavorazione=<?php echo $item["id_lavorazione"] ?>',800,500)" title="Elimina" data-toggle="tooltip"><span class="glyphicon glyphicon-trash"></span></button>
											<button id="print-button" type="button" class="btn btn-primary btn-sm" onClick="javascript:windowpop('process_order_print.php?id_lavorazione=<?php echo $item["id_lavorazione"] ?>',800,500)" title="Stampa buono" data-toggle="tooltip"><span class="glyphicon glyphicon-print"></span></button>
											<?php
												if ($item['print_fiscal_receipt']=='1') {
													if ((has_order_open_items($item["id_lavorazione"], $mysqli)==1) | (has_order_open_services($item["id_lavorazione"], $mysqli)==1)) {
											?>
														<span><button id="print-fiscal-button" type="button" class="btn btn-primary btn-sm" onClick="javascript:windowpop('order_form.php?action=fiscal&id_lavorazione=<?php echo $item["id_lavorazione"] ?>',800,800)" title="<?php echo empty($item["numero_fiscale_ricevuta"])?'Emetti e stampa Ricevuta Fiscale':'Ristampa Ricevuta Fiscale' ?>" data-toggle="tooltip"><span class="glyphicon glyphicon glyphicon-piggy-bank"></span></button></span>
											<?php
													}
												}
											?>
										</td>
									</tr>
									<?php
										if (!empty(trim($item["note_lavorazione"]))) {
									?>
											<tr class="tr-class-1" <?php echo !empty($item["row_color"])?'style="background-color:'.$item["row_color"].' !important"':'' ?> >
												<td colspan="10" style="border-top: none !important;"><?php echo /*htmlentities(*/$item["note_lavorazione"]/*)*/ ?></td>
											</tr>
									<?php
										}
									?>
							<?php
								}
							?>
						</tbody>
					</table>
				</div>
				<div class="col-md-12">
					<div class="btn-group" role="group">
						<?php
							if($page_count>($pages_gap*2)){
								$first_page = (!empty($_POST["search_page"])?$_POST["search_page"]:'1')-$pages_gap;
								$last_page = (!empty($_POST["search_page"])?$_POST["search_page"]:'1')+$pages_gap-1;
								if($last_page > $page_count){
									$last_page = $page_count;
									$first_page = $page_count - ($pages_gap*2) + 1;
								}
								if($first_page < 1){
									$last_page = ($pages_gap*2);
									$first_page = 1;
								}
							} else {
								$first_page = 1;
								$last_page = $page_count;
							}
							if($first_page>1){
						?>
								<button type="button" class="btn btn-default" onClick="document.getElementById('search_page').value=1;document.getElementById('search_form').submit();" title="Prima pagina" data-toggle="tooltip"><span class="glyphicon glyphicon-fast-backward btn-xs"></span></button>
						<?php
							}
							for ($i = $first_page; $i <= $last_page; $i++) {
						?>
								<button type="button" class="btn <?php echo $i==$current_search_page?'btn-info':'btn-default' ?>" onClick="document.getElementById('search_page').value=<?php echo $i ?>;document.getElementById('search_form').submit();"><?php echo $i ?></button>
						<?php
							}
							if($page_count > $last_page){
						?>
								<button type="button" class="btn btn-default" onClick="document.getElementById('search_page').value=<?php echo $page_count ?>;document.getElementById('search_form').submit();" title="Ultima pagina" data-toggle="tooltip"><span class="glyphicon glyphicon-fast-forward btn-xs"></span></button>
						<?php
							}
						?>
					</div>
				</div>
				<input type="hidden" id="search_page" name="search_page" value="<?php echo 1 //!empty($_POST["search_page"])?$_POST["search_page"]:'1' ?>">
			</form>
		</div>
	</div>
<script>
	function reprintWarnMessage(id_lavorazione_val, id_ricevuta_val) {
		bootbox.confirm("Attenzione! Assicurarsi di aver rimosso dalla stampante i fogli numerati delle ricevute fiscali. Questa &egrave; una <b>RISTAMPA</b>. Procedere?", function(result) {
			if (result===true) {
				windowpop('process_order_fiscal_print.php?id_lavorazione=' + id_lavorazione_val + '&id_ricevuta=' + id_ricevuta_val,800,500);
			}
		});
	}

	$("#id_lavorazione_searchop_list li a").click(function(){
		$(this).parents(".input-group-btn").find(".dropdown-toggle").html($(this).html() + ' <span class="caret"></span>');
		$('input[name="id_lavorazione_searchop"]').val($(this).text());
	});	
	$("#cognome_cliente_searchop_list li a").click(function(){
		$(this).parents(".input-group-btn").find(".dropdown-toggle").html($(this).html() + ' <span class="caret"></span>');
		$('input[name="cognome_cliente_searchop"]').val($(this).text());
	});	
	$("#data_consegna_dal_searchop_list li a").click(function(){
		$(this).parents(".input-group-btn").find(".dropdown-toggle").html($(this).html() + ' <span class="caret"></span>');
		$('input[name="data_consegna_dal_searchop"]').val($(this).text());
	});	
	$("#data_consegna_al_searchop_list li a").click(function(){
		$(this).parents(".input-group-btn").find(".dropdown-toggle").html($(this).html() + ' <span class="caret"></span>');
		$('input[name="data_consegna_al_searchop"]').val($(this).text());
	});	
	$("#stato_searchop_list li a").click(function(){
		$(this).parents(".btn-group.1").find(".dropdown-toggle").html($(this).html() + ' <span class="caret"></span>');
		$('input[name="stato_searchop"]').val($(this).text());
	});	
	$("#stato_list li a").click(function(){
		$(this).parents(".btn-group.2").find(".dropdown-toggle").html($(this).html() + ' <span class="caret"></span>');
		$('input[name="stato"]').val($(this).parents("li").attr("value"));
	});
	$("#numero_fiscale_ricevuta_searchop_list li a").click(function(){
		$(this).parents(".input-group-btn").find(".dropdown-toggle").html($(this).html() + ' <span class="caret"></span>');
		$('input[name="numero_fiscale_ricevuta_searchop"]').val($(this).text());
	});
	
	function tableHeaderClick(field) {
		document.getElementById('sort_index').value=field;
		document.getElementById('sort_dir').value = document.getElementById('sort_dir').value=='ASC'?'DESC':'ASC';
		document.getElementById('search_form').submit();		
	}
	
    $('#dtp_data_consegna_al').datepicker({
		language: 'it'
    });
    $('#dtp_data_consegna_dal').datepicker({
		language: 'it'
    });
	
	$(document).ready(function(){
		$('#check_all').click(function(){
			$('input:checkbox').prop('checked',!$('input:checkbox').prop('checked'));
		});
		
		$("[data-toggle=tooltip]").tooltip({
			placement: 'auto top'
		});
	});
	
	$("#move-status").click(function(){
		var changes_count = 0;
		var elems_count = parseInt(document.querySelectorAll('input[type="checkbox"]:checked').length);
		elems_count = elems_count==0?-1:elems_count;
		$('input[type=checkbox]').each(function (){
			if (this.checked) {
				changes_count++;
				var chgs = $(this).attr('name').toString().split("_");
				if (chgs[0]=="chg" && chgs[1]=="status") {
					var request = $.ajax({
						url: "ac_set_order_status.php",
						method: "POST",
						data: {
								id_lavorazione : chgs[2],
								id_stato : chgs[3],
								tipo_documento : 'LAV'
						},
						dataType: "json"
					});
					 
					request.done(function(msg) {
						switch (parseInt(msg.error)) {
							case 0:
								gAlert("Successo", "Lo stato della lavorazione " + chgs[2].toString() + " e' stato modificato correttamente.", "img/gSuccess.png", "", <?php echo $gASuccessTimeOut ?>, 0);
								break;
							case -1:
								gAlert("Errore", "Errore nella procedura di avanzamento stato. Contattare il supporto.", "img/gError.png", "", <?php echo $gAErrorTimeOut ?>, 0);
								break;
							case -999:
								gAlert("Critical", "Attack!", "img/gError.png", "", <?php echo $gAErrorTimeOut ?>, 0);
								break;
							case -888:
								gAlert("Errore", "Errore nell'invio dell'SMS al cliente. Contattare il supporto.", "img/gError.png", "", <?php echo $gAErrorTimeOut ?>, 0);
								break;
							default:
								gAlert("Errore", "Errore durante il richiamo della procedura di avanzamento stato. Contattare il supporto.", "img/gError.png", "", <?php echo $gAErrorTimeOut ?>, 0);
						}
					});
					 
					request.fail(function( jqXHR, textStatus ) {
						gAlert("Errore", "Errore durante il richiamo della procedura di avanzamento stato. Contattare il supporto.", "img/gError.png", "", <?php echo $gAErrorTimeOut ?>, 0);
					});
				}
			}
			if (changes_count == elems_count) {
				setTimeout(function(){
					location.reload();
				},3000);
			}
		});
	});
</script>
</body>
</html>