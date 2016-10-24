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
	<div class="bg-outer">
		<div class="container gray-dark">
			<?php include_once('includes/navbar.php'); ?>
			<div class="page-header">
				<h1>Elenco per data <small>Ricevute</small></h1>
			</div>
			<form data-toggle="validator" id="search_form" role="form" action="receipt_list.php" method="post" name="search_form" data-disable="true">
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
								<!--                  DATA RICEVUTA DAL                    -->
								<!-- ***************************************************** -->
								<div class="form-group col-md-3">
									<label class="control-label" for="data_ricevuta_dal">Data ricevuta - dal:</label>
									<div class="input-group date" id="dtp_data_ricevuta_dal">
										<div class="input-group-btn">
											<button type="button" class="btn btn-default dropdown-toggle input-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo !empty($_POST["data_ricevuta_dal_searchop"])?$_POST["data_ricevuta_dal_searchop"]:'=' ?> <span class="caret"></span></button>
											<ul class="dropdown-menu" id="data_ricevuta_dal_searchop_list">
												<li><a href="#">=</a></li>
												<li><a href="#">>=</a></li>
												<li><a href="#"><=</a></li>
												<li><a href="#">diverso</a></li>
											</ul>
										</div>
										<input type="text" class="form-control input-sm" id="data_ricevuta_dal" name="data_ricevuta_dal" value="<?php echo !empty($_POST["data_ricevuta_dal"])?$_POST["data_ricevuta_dal"]:'' ?>">
										<div class="input-group-addon"><span class="glyphicon glyphicon-th"></span></div>
									</div>
								</div>
								<input type="hidden" id="data_ricevuta_dal_searchop" name="data_ricevuta_dal_searchop" value="<?php echo !empty($_POST["data_ricevuta_dal_searchop"])?$_POST["data_ricevuta_dal_searchop"]:'=' ?>">
								<!-- ***************************************************** -->
								<!--                  DATA RICEVUTA AL                    -->
								<!-- ***************************************************** -->
								<div class="form-group col-md-3">
									<label class="control-label" for="data_ricevuta_al">Data ricevuta - al:</label>
									<div class="input-group date" id="dtp_data_ricevuta_al">
										<div class="input-group-btn">
											<button type="button" class="btn btn-default dropdown-toggle input-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo !empty($_POST["data_ricevuta_al_searchop"])?$_POST["data_ricevuta_al_searchop"]:'=' ?> <span class="caret"></span></button>
											<ul class="dropdown-menu" id="data_ricevuta_al_searchop_list">
												<li><a href="#">=</a></li>
												<li><a href="#">>=</a></li>
												<li><a href="#"><=</a></li>
												<li><a href="#">diverso</a></li>
											</ul>
										</div>
										<input type="text" class="form-control input-sm" id="data_ricevuta_al" name="data_ricevuta_al" value="<?php echo !empty($_POST["data_ricevuta_al"])?$_POST["data_ricevuta_al"]:'' ?>">
										<div class="input-group-addon"><span class="glyphicon glyphicon-th"></span></div>
									</div>
									<input type="hidden" id="data_ricevuta_al_searchop" name="data_ricevuta_al_searchop" value="<?php echo !empty($_POST["data_ricevuta_al_searchop"])?$_POST["data_ricevuta_al_searchop"]:'=' ?>">
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~ FINE FILTRI DI RICERCA -->
				<div class="col-md-12">
					<button id="search-button" type="submit" class="btn btn-primary btn-sm" title="Ricerca" data-toggle="tooltip"><span class="glyphicon glyphicon-search"></span></button>
				</div>
				<br>
				<div class="table-responsive col-md-12">
					<input type="hidden" id="sort_index" name="sort_index" value="<?php echo !empty($_POST["sort_index"])?$_POST["sort_index"]:'data_ricevuta' ?>">
					<input type="hidden" id="sort_dir" name="sort_dir" value="<?php echo !empty($_POST["sort_dir"])?$_POST["sort_dir"]:'ASC' ?>">
					<table id="table_results" class="table table-hover table-striped">
						<thead>
							<tr>
								<th onClick="javascript:tableHeaderClick('data_ricevuta')">Data ricevuta <?php echo !empty($_POST["sort_index"])?($_POST["sort_index"]=='data_ricevuta'?(!empty($_POST["sort_dir"])?($_POST["sort_dir"]=='ASC'?'<span class="glyphicon glyphicon-sort-by-attributes"></span>':'<span class="glyphicon glyphicon-sort-by-attributes-alt"></span>'):''):''):'' ?></th>
								<th>Da N°</th>
								<th>A N°</th>
								<th>Importo Totale</th>
							</tr>
						</thead>
						<tbody>

							<?php
								$per_page = $Grids_PerPageRecords;
								$pages_gap = $Grids_PaginationPagesGap;
								$page_total = 0;
								$grand_total = 0;
								$sort_index = !empty($_POST["sort_index"])?$_POST["sort_index"]:'data_ricevuta';
								$sort_dir = !empty($_POST["sort_dir"])?$_POST["sort_dir"]:'ASC';
								$items_coll = get_receipt_list(
																!empty($_POST["data_ricevuta_dal"])?$_POST["data_ricevuta_dal"]:'',
																!empty($_POST["data_ricevuta_dal_searchop"])?$_POST["data_ricevuta_dal_searchop"]:'=',
																!empty($_POST["data_ricevuta_al"])?$_POST["data_ricevuta_al"]:'',
																!empty($_POST["data_ricevuta_al_searchop"])?$_POST["data_ricevuta_al_searchop"]:'=',
																!empty($_POST["search_page"])?$_POST["search_page"]:1,
																$per_page,
																$sort_index,
																$sort_dir,
																$mysqli);
								$current_search_page = !empty($_POST["search_page"])?$_POST["search_page"]:1;
								$page_count = ceil(!empty($items_coll[sizeof($items_coll) - 1])?$items_coll[sizeof($items_coll) - 1]["record_count"]/$per_page:1);
								unset($items_coll[sizeof($items_coll) - 1]);
								foreach ($items_coll as $item) {
							?>
									<tr id="tr_id_1" class="tr-class-1">
										<td>
											<?php
												$fmtDate = DateTime::createFromFormat('Y-m-d', $item["data_ricevuta"]);
												$fmtDate = $fmtDate->format('d/m/Y');
												echo $fmtDate;
											?>
										</td>
										<td><?php echo $item["da_ricevuta_n"] ?></td>
										<td><?php echo $item["a_ricevuta_n"] ?></td>
										<td><?php echo $curfmt->formatCurrency($item["importo_totale"], "EUR") ?></td>
										<?php $page_total += $item["importo_totale"]; ?>
									</tr>
							<?php
								}
							?>
						</tbody>
					</table>
					Totale pagina: <b><?php echo $curfmt->formatCurrency($page_total, "EUR") ?></b><br/>
					Totale generale:
						<b>
						<?php
							$grand_total = get_receipt_list_grand_total(
																!empty($_POST["data_ricevuta_dal"])?$_POST["data_ricevuta_dal"]:'',
																!empty($_POST["data_ricevuta_dal_searchop"])?$_POST["data_ricevuta_dal_searchop"]:'=',
																!empty($_POST["data_ricevuta_al"])?$_POST["data_ricevuta_al"]:'',
																!empty($_POST["data_ricevuta_al_searchop"])?$_POST["data_ricevuta_al_searchop"]:'=',
																$mysqli);
							echo $curfmt->formatCurrency($grand_total, "EUR");
						?>
						</b>
					<br/><br/>
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
    $('#data_ricevuta_al').datepicker({
		language: 'it'
    });
    $('#data_ricevuta_dal').datepicker({
		language: 'it'
    });
	
	$("#data_ricevuta_dal_searchop_list li a").click(function(){
		$(this).parents(".input-group-btn").find(".dropdown-toggle").html($(this).html() + ' <span class="caret"></span>');
		$('input[name="data_ricevuta_dal_searchop"]').val($(this).text());
	});	
	$("#data_ricevuta_al_searchop_list li a").click(function(){
		$(this).parents(".input-group-btn").find(".dropdown-toggle").html($(this).html() + ' <span class="caret"></span>');
		$('input[name="data_ricevuta_al_searchop"]').val($(this).text());
	});	
	
	function tableHeaderClick(field) {
		document.getElementById('sort_index').value=field;
		document.getElementById('sort_dir').value = document.getElementById('sort_dir').value=='ASC'?'DESC':'ASC';
		document.getElementById('search_form').submit();		
	}
	
	$(document).ready(function(){
		$("[data-toggle=tooltip]").tooltip({
			placement: 'auto top'
		});
	});
</script>
</body>
</html>