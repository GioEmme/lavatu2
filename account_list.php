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
				<h1>Utenti <small>Amministrazione</small></h1>
			</div>
			<form data-toggle="validator" id="search_form" role="form" action="account_list.php" method="post" name="search_form" data-disable="true">
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
								<!--                  USER ID                              -->
								<!-- ***************************************************** -->
								<div class="form-group col-md-3">
									<label class="control-label" for="nome">ID:</label>
									<div class="input-group">
										<div class="input-group-btn">
											<button type="button" class="btn btn-default dropdown-toggle input-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo !empty($_POST["userid-searchop"])?$_POST["userid-searchop"]:'=' ?> <span class="caret"></span></button>
											<ul class="dropdown-menu" id="userid-searchop-list">
												<li><a href="#">=</a></li>
												<li><a href="#">>=</a></li>
												<li><a href="#"><=</a></li>
												<li><a href="#">diverso</a></li>
											</ul>
										</div>
										<input type="number" class="form-control input-sm" id="userid" name="userid" value="<?php echo !empty($_POST["userid"])?$_POST["userid"]:'' ?>">
										<input type="hidden" id="userid-searchop" name="userid-searchop" value="<?php echo !empty($_POST["userid-searchop"])?$_POST["userid-searchop"]:'=' ?>">
									</div>
								</div>
								<!-- ***************************************************** -->
								<!--                  NOME                                 -->
								<!-- ***************************************************** -->
								<div class="form-group col-md-3">
									<label class="control-label" for="nome">Nome:</label>
									<div class="input-group">
										<div class="input-group-btn">
											<button type="button" class="btn btn-default dropdown-toggle input-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo !empty($_POST["nome-searchop"])?$_POST["nome-searchop"]:'contiene' ?> <span class="caret"></span></button>
											<ul class="dropdown-menu" id="nome-searchop-list">
												<li><a href="#">contiene</a></li>
												<li><a href="#">non contiene</a></li>
												<li><a href="#">=</a></li>
												<li><a href="#">>=</a></li>
												<li><a href="#"><=</a></li>
												<li><a href="#">diverso</a></li>
											</ul>
										</div>
										<input type="text" class="form-control input-sm" id="nome" name="nome" value="<?php echo !empty($_POST["nome"])?$_POST["nome"]:'' ?>">
										<input type="hidden" id="nome-searchop" name="nome-searchop" value="<?php echo !empty($_POST["nome-searchop"])?$_POST["nome-searchop"]:'contiene' ?>">
									</div>
								</div>
								<!-- ***************************************************** -->
								<!--                  USERNAME                             -->
								<!-- ***************************************************** -->
								<div class="form-group col-md-3">
									<label class="control-label" for="username">Username:</label>
									<div class="input-group">
										<div class="input-group-btn">
											<button type="button" class="btn btn-default dropdown-toggle input-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo !empty($_POST["username-searchop"])?$_POST["username-searchop"]:'contiene' ?> <span class="caret"></span></button>
											<ul class="dropdown-menu" id="username-searchop-list">
												<li><a href="#">contiene</a></li>
												<li><a href="#">non contiene</a></li>
												<li><a href="#">=</a></li>
												<li><a href="#">>=</a></li>
												<li><a href="#"><=</a></li>
												<li><a href="#">diverso</a></li>
											</ul>
										</div><!-- /btn-group -->
										<input type="text" class="form-control input-sm" id="username" name="username" value="<?php echo !empty($_POST["username"])?$_POST["username"]:'' ?>">
										<input type="hidden" id="username-searchop" name="username-searchop" value="<?php echo !empty($_POST["username-searchop"])?$_POST["username-searchop"]:'contiene' ?>">
									</div>
								</div>
								<!-- ***************************************************** -->
								<!--                  EMAIL                                -->
								<!-- ***************************************************** -->
								<div class="form-group col-md-3">
									<label class="control-label" for="username">e-mail:</label>
									<div class="input-group">
										<div class="input-group-btn">
											<button type="button" class="btn btn-default dropdown-toggle input-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo !empty($_POST["email-searchop"])?$_POST["email-searchop"]:'contiene' ?> <span class="caret"></span></button>
											<ul class="dropdown-menu" id="email-searchop-list">
												<li><a href="#">contiene</a></li>
												<li><a href="#">non contiene</a></li>
												<li><a href="#">=</a></li>
												<li><a href="#">>=</a></li>
												<li><a href="#"><=</a></li>
												<li><a href="#">diverso</a></li>
											</ul>
										</div><!-- /btn-group -->
										<input type="text" class="form-control input-sm" id="email" name="email" value="<?php echo !empty($_POST["email"])?$_POST["email"]:'' ?>">
										<input type="hidden" id="email-searchop" name="email-searchop" value="<?php echo !empty($_POST["email-searchop"])?$_POST["email-searchop"]:'contiene' ?>">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~ FINE FILTRI DI RICERCA -->
				<div class="col-md-12">
					<button id="search-button" type="submit" class="btn btn-primary btn-sm" title="Ricerca" data-toggle="tooltip"><span class="glyphicon glyphicon-search"></span></button>
					<button id="new-button" type="button" class="btn btn-primary btn-sm" onClick="javascript:windowpop('account_form.php?action=new',800,800)" title="Nuovo utente" data-toggle="tooltip"><span class="glyphicon glyphicon-plus"></span></button>
					<button id="recover-button" type="button" class="btn btn-primary btn-sm" onClick="javascript:windowpop('account_recover.php',800,500)">Reset password</button>
				</div>
				<br>
				<div class="table-responsive col-md-12">
					<input type="hidden" id="sort_index" name="sort_index" value="<?php echo !empty($_POST["sort_index"])?$_POST["sort_index"]:'user_id' ?>">
					<input type="hidden" id="sort_dir" name="sort_dir" value="<?php echo !empty($_POST["sort_dir"])?$_POST["sort_dir"]:'ASC' ?>">
					<table id="table_results" class="table table-hover table-striped">
						<thead>
							<tr>
								<th class="hidden-sm hidden-xs" onClick="javascript:tableHeaderClick('user_id')">User ID <?php echo !empty($_POST["sort_index"])?($_POST["sort_index"]=='user_id'?(!empty($_POST["sort_dir"])?($_POST["sort_dir"]=='ASC'?'<span class="glyphicon glyphicon-sort-by-attributes"></span>':'<span class="glyphicon glyphicon-sort-by-attributes-alt"></span>'):''):''):'' ?></th>
								<th class="hidden-xs" onClick="javascript:tableHeaderClick('name')">Nome <?php echo !empty($_POST["sort_index"])?($_POST["sort_index"]=='name'?(!empty($_POST["sort_dir"])?($_POST["sort_dir"]=='ASC'?'<span class="glyphicon glyphicon-sort-by-attributes"></span>':'<span class="glyphicon glyphicon-sort-by-attributes-alt"></span>'):''):''):'' ?></th>
								<th onClick="javascript:tableHeaderClick('username')">Username <?php echo !empty($_POST["sort_index"])?($_POST["sort_index"]=='username'?(!empty($_POST["sort_dir"])?($_POST["sort_dir"]=='ASC'?'<span class="glyphicon glyphicon-sort-by-attributes"></span>':'<span class="glyphicon glyphicon-sort-by-attributes-alt"></span>'):''):''):'' ?></th>
								<th class="hidden-xs" onClick="javascript:tableHeaderClick('email')">e-mail <?php echo !empty($_POST["sort_index"])?($_POST["sort_index"]=='email'?(!empty($_POST["sort_dir"])?($_POST["sort_dir"]=='ASC'?'<span class="glyphicon glyphicon-sort-by-attributes"></span>':'<span class="glyphicon glyphicon-sort-by-attributes-alt"></span>'):''):''):'' ?></th>
								<th onClick="javascript:tableHeaderClick('is_active')">Attivo <?php echo !empty($_POST["sort_index"])?($_POST["sort_index"]=='is_active'?(!empty($_POST["sort_dir"])?($_POST["sort_dir"]=='ASC'?'<span class="glyphicon glyphicon-sort-by-attributes"></span>':'<span class="glyphicon glyphicon-sort-by-attributes-alt"></span>'):''):''):'' ?></th>
								<th onClick="javascript:tableHeaderClick('role_administrator')">Amministratore <?php echo !empty($_POST["sort_index"])?($_POST["sort_index"]=='role_administrator'?(!empty($_POST["sort_dir"])?($_POST["sort_dir"]=='ASC'?'<span class="glyphicon glyphicon-sort-by-attributes"></span>':'<span class="glyphicon glyphicon-sort-by-attributes-alt"></span>'):''):''):'' ?></th>
								<th>Azioni</th>
							</tr>
						</thead>
						<tbody>

							<?php
								$per_page = $Grids_PerPageRecords;
								$pages_gap = $Grids_PaginationPagesGap;
								$sort_index = !empty($_POST["sort_index"])?$_POST["sort_index"]:'user_id';
								$sort_dir = !empty($_POST["sort_dir"])?$_POST["sort_dir"]:'ASC';
								$items_coll = get_user_list(	!empty($_POST["userid"])?$_POST["userid"]:'',
																!empty($_POST["userid-searchop"])?$_POST["userid-searchop"]:'=',
																!empty($_POST["nome"])?$_POST["nome"]:'',
																!empty($_POST["nome-searchop"])?$_POST["nome-searchop"]:'contiene',
																!empty($_POST["username"])?$_POST["username"]:'',
																!empty($_POST["username-searchop"])?$_POST["username-searchop"]:'contiene',
																!empty($_POST["email"])?$_POST["email"]:'',
																!empty($_POST["email-searchop"])?$_POST["email-searchop"]:'contiene',
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
										<td class="hidden-sm hidden-xs"><?php echo $item["user_id"] ?></td>
										<td class="hidden-xs"><?php echo htmlentities($item["name"]) ?></td>
										<td><?php echo htmlentities($item["username"]) ?></td>
										<td class="hidden-xs"><?php echo htmlentities($item["email"]) ?></td>
										<td><?php echo $item["is_active"]==1?'<span class="glyphicon glyphicon-ok"></span>':'' ?></td>
										<td><?php echo $item["role_administrator"]==1?'<span class="glyphicon glyphicon-ok"></span>':'' ?></td>
										<td>
											<button id="edit-button" type="button" class="btn btn-primary btn-sm" onClick="javascript:windowpop('account_form.php?action=edit&user_id=<?php echo $item["user_id"] ?>',800,800)" title="Modifica" data-toggle="tooltip"><span class="glyphicon glyphicon-pencil"></span></button>
											<button id="delete-button" type="button" class="btn btn-primary btn-sm" onClick="javascript:windowpop('account_form.php?action=delete&user_id=<?php echo $item["user_id"] ?>',800,500)" title="Elimina" data-toggle="tooltip"><span class="glyphicon glyphicon-trash"></span></button>
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
	$("#userid-searchop-list li a").click(function(){
		$(this).parents(".input-group-btn").find(".dropdown-toggle").html($(this).html() + ' <span class="caret"></span>');
		$('input[name="userid-searchop"]').val($(this).text());
	});	

	$("#nome-searchop-list li a").click(function(){
		$(this).parents(".input-group-btn").find(".dropdown-toggle").html($(this).html() + ' <span class="caret"></span>');
		$('input[name="nome-searchop"]').val($(this).text());
	});	

	$("#username-searchop-list li a").click(function(){
		$(this).parents(".input-group-btn").find(".dropdown-toggle").html($(this).html() + ' <span class="caret"></span>');
		$('input[name="username-searchop"]').val($(this).text());
	});	

	$("#email-searchop-list li a").click(function(){
		$(this).parents(".input-group-btn").find(".dropdown-toggle").html($(this).html() + ' <span class="caret"></span>');
		$('input[name="email-searchop"]').val($(this).text());
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