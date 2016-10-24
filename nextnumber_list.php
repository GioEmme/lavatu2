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
				<h1>Numeratori documenti <small>Amministrazione</small></h1>
			</div>
			<form data-toggle="validator" id="search_form" role="form" action="nextnumber_list.php" method="post" name="search_form" data-disable="true">
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
								<!--                  TIPO FISCALE                         -->
								<!-- ***************************************************** -->
								<div class="form-group col-md-3">
									<label class="control-label" for="tipo_fiscale">Tipo documento:</label>
									<div class="input-group">
										<div class="input-group-btn">
											<button type="button" class="btn btn-default dropdown-toggle input-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo !empty($_POST["tipo_fiscale_searchop"])?$_POST["tipo_fiscale_searchop"]:'=' ?> <span class="caret"></span></button>
											<ul class="dropdown-menu" id="tipo_fiscale_searchop_list">
												<li><a href="#">=</a></li>
												<li><a href="#">>=</a></li>
												<li><a href="#"><=</a></li>
												<li><a href="#">diverso</a></li>
											</ul>
										</div>
										<input type="text" class="form-control input-sm" id="tipo_fiscale" name="tipo_fiscale" value="<?php echo !empty($_POST["tipo_fiscale"])?$_POST["tipo_fiscale"]:'' ?>">
										<input type="hidden" id="tipo_fiscale_searchop" name="tipo_fiscale_searchop" value="<?php echo !empty($_POST["tipo_fiscale_searchop"])?$_POST["tipo_fiscale_searchop"]:'=' ?>">
									</div>
								</div>
								<!-- ***************************************************** -->
								<!--                         ANNO                          -->
								<!-- ***************************************************** -->
								<div class="form-group col-md-3">
									<label class="control-label" for="anno">Anno:</label>
									<div class="input-group">
										<div class="input-group-btn">
											<button type="button" class="btn btn-default dropdown-toggle input-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo !empty($_POST["anno_searchop"])?$_POST["anno_searchop"]:'=' ?> <span class="caret"></span></button>
											<ul class="dropdown-menu" id="anno_searchop_list">
												<li><a href="#">=</a></li>
												<li><a href="#">>=</a></li>
												<li><a href="#"><=</a></li>
												<li><a href="#">diverso</a></li>
											</ul>
										</div>
										<input type="number" class="form-control input-sm" id="anno" name="anno" value="<?php echo !empty($_POST["anno"])?$_POST["anno"]:'' ?>">
										<input type="hidden" id="anno_searchop" name="anno_searchop" value="<?php echo !empty($_POST["anno_searchop"])?$_POST["anno_searchop"]:'=' ?>">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~ FINE FILTRI DI RICERCA -->
				<div class="col-md-12">
					<button id="search-button" type="submit" class="btn btn-primary btn-sm" title="Ricerca" data-toggle="tooltip"><span class="glyphicon glyphicon-search"></span></button>
					<button id="new-button" type="button" class="btn btn-primary btn-sm" onClick="javascript:windowpop('nextnumber_form.php?action=new',800,600)" title="Nuovo numeratore" data-toggle="tooltip"><span class="glyphicon glyphicon-plus"></span></button>
				</div>
				<br>
				<div class="table-responsive col-md-12">
					<input type="hidden" id="sort_index" name="sort_index" value="<?php echo !empty($_POST["sort_index"])?$_POST["sort_index"]:'tipo_fiscale' ?>">
					<input type="hidden" id="sort_dir" name="sort_dir" value="<?php echo !empty($_POST["sort_dir"])?$_POST["sort_dir"]:'ASC' ?>">
					<table id="table_results" class="table table-hover table-striped">
						<thead>
							<tr>
								<th onClick="javascript:tableHeaderClick('tipo_fiscale')">Tipo documento <?php echo !empty($_POST["sort_index"])?($_POST["sort_index"]=='tipo_fiscale'?(!empty($_POST["sort_dir"])?($_POST["sort_dir"]=='ASC'?'<span class="glyphicon glyphicon-sort-by-attributes"></span>':'<span class="glyphicon glyphicon-sort-by-attributes-alt"></span>'):''):''):'' ?></th>
								<th onClick="javascript:tableHeaderClick('prefisso')">Prefisso <?php echo !empty($_POST["sort_index"])?($_POST["sort_index"]=='prefisso'?(!empty($_POST["sort_dir"])?($_POST["sort_dir"]=='ASC'?'<span class="glyphicon glyphicon-sort-by-attributes"></span>':'<span class="glyphicon glyphicon-sort-by-attributes-alt"></span>'):''):''):'' ?></th>
								<th onClick="javascript:tableHeaderClick('numero_fiscale')">Prossimo Numero <?php echo !empty($_POST["sort_index"])?($_POST["sort_index"]=='numero_fiscale'?(!empty($_POST["sort_dir"])?($_POST["sort_dir"]=='ASC'?'<span class="glyphicon glyphicon-sort-by-attributes"></span>':'<span class="glyphicon glyphicon-sort-by-attributes-alt"></span>'):''):''):'' ?></th>
								<th onClick="javascript:tableHeaderClick('suffisso')">Suffisso <?php echo !empty($_POST["sort_index"])?($_POST["sort_index"]=='suffisso'?(!empty($_POST["sort_dir"])?($_POST["sort_dir"]=='ASC'?'<span class="glyphicon glyphicon-sort-by-attributes"></span>':'<span class="glyphicon glyphicon-sort-by-attributes-alt"></span>'):''):''):'' ?></th>
								<th onClick="javascript:tableHeaderClick('anno')">Anno <?php echo !empty($_POST["sort_index"])?($_POST["sort_index"]=='anno'?(!empty($_POST["sort_dir"])?($_POST["sort_dir"]=='ASC'?'<span class="glyphicon glyphicon-sort-by-attributes"></span>':'<span class="glyphicon glyphicon-sort-by-attributes-alt"></span>'):''):''):'' ?></th>
								<th>Azioni</th>
							</tr>
						</thead>
						<tbody>

							<?php
								$per_page = $Grids_PerPageRecords;
								$pages_gap = $Grids_PaginationPagesGap;
								$sort_index = !empty($_POST["sort_index"])?$_POST["sort_index"]:'tipo_fiscale';
								$sort_dir = !empty($_POST["sort_dir"])?$_POST["sort_dir"]:'ASC';
								$items_coll = get_nextnumber_list(
																!empty($_POST["tipo_fiscale"])?$_POST["tipo_fiscale"]:'',
																!empty($_POST["tipo_fiscale_searchop"])?$_POST["tipo_fiscale_searchop"]:'=',
																!empty($_POST["anno"])?$_POST["anno"]:'',
																!empty($_POST["anno_searchop"])?$_POST["anno_searchop"]:'=',
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
										<td><?php echo htmlentities($item["tipo_fiscale"]) ?></td>
										<td><?php echo htmlentities($item["prefisso"]) ?></td>
										<td><?php echo $item["numero_fiscale"] ?></td>
										<td><?php echo htmlentities($item["suffisso"]) ?></td>
										<td><?php echo $item["anno"] ?></td>
										<td>
											<button id="edit-button" type="button" class="btn btn-primary btn-sm" onClick="javascript:windowpop('nextnumber_form.php?action=edit&tipo_fiscale=<?php echo $item["tipo_fiscale"] ?>&anno=<?php echo $item["anno"] ?>',800,600)" title="Modifica" data-toggle="tooltip"><span class="glyphicon glyphicon-pencil"></span></button>
											<button id="delete-button" type="button" class="btn btn-primary btn-sm" onClick="javascript:windowpop('nextnumber_form.php?action=delete&tipo_fiscale=<?php echo $item["tipo_fiscale"] ?>&anno=<?php echo $item["anno"] ?>',800,500)" title="Elimina" data-toggle="tooltip"><span class="glyphicon glyphicon-trash"></span></button>
										</td>
									</tr>
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
	$("#tipo_fiscale_searchop_list li a").click(function(){
		$(this).parents(".input-group-btn").find(".dropdown-toggle").html($(this).html() + ' <span class="caret"></span>');
		$('input[name="tipo_fiscale_searchop"]').val($(this).text());
	});	
	$("#anno_searchop_list li a").click(function(){
		$(this).parents(".input-group-btn").find(".dropdown-toggle").html($(this).html() + ' <span class="caret"></span>');
		$('input[name="anno_searchop"]').val($(this).text());
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