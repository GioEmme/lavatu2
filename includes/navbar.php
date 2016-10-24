<style type="text/css">
.navbar-brand>img {
	margin-left:-5px !important;
	margin-top:-5px !important;
	max-height: 130%;
}
</style>

<nav class="navbar navbar-default">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="index.php"><img src="<?php echo $App_BrandImage; ?>"></a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div id="navbar" class="navbar-collapse collapse">
		<ul class="nav navbar-nav list-inline">
			<li class="dropdown" id="menu_amministrazione_button">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-wrench"></span>&nbsp;<span class="caret"></span></a>
				<ul class="dropdown-menu" id="menu_amministrazione_list">
					<li><a href="account_list.php">Utenti</a></li>
					<li role="separator" class="divider"></li>
					<li><a href="nextnumber_list.php">Numeratori documenti</a></li>
					<li><a href="status_list.php">Stati ordini/documenti</a></li>
				</ul>
			</li>
			<li class="dropdown" id="menu_anagrafiche_button">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-book"></span>&nbsp;<span class="caret"></span></a>
				<ul class="dropdown-menu" id="menu_anagrafiche_list">
					<li><a href="address_book_list.php">Clienti</a></li>
					<li role="separator" class="divider"></li>
					<li><a href="category_list.php">Categorie Art./Ser.</a></li>
					<li><a href="item_list.php">Articoli</a></li>
					<li><a href="service_list.php">Servizi</a></li>
				</ul>
			</li>
			<li class="dropdown" id="menu_ordini_button">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-shopping-cart"></span>&nbsp;<span class="caret"></span></a>
				<ul class="dropdown-menu" id="menu_ordini_list">
					<li><a href="order_list.php">Lavorazioni</a></li>
					<li role="separator" class="divider"></li>
					<li><a href="order_list.php?stato=M">Acquisite</a></li>
					<li><a href="order_list.php?stato=A">In Lavorazione</a></li>
					<li><a href="order_list.php?stato=C">Chiuse</a></li>
					<li><a href="order_list.php?stato=D">Cancellate</a></li>
					<li role="separator" class="divider"></li>
					<li><a href="receipt_list.php">Elenco ricevute per data</a></li>
				</ul>
			</li>			
		</ul>

		<ul class="nav navbar-nav navbar-right list-inline">
			<!--<li><a href="#">Link</a></li>-->
			<li class="dropdown">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
				<?php
					echo isset($_SESSION['username'])?$_SESSION['username']:'Utente';
				?>
				<span class="caret"></span></a>
				<ul class="dropdown-menu multi-level">
					<?php
						if(!isset($_SESSION['username'])) {
					?>
							<li><a href="login.php">Accesso</a></li>
					<?php
						} else {
					?>
							<li class="disabled"><a href="account.php">Modifica profilo</a></li>
							<li role="separator" class="divider"></li>
							<li class="dropdown-submenu">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
									Tema grafico</a>
								<ul class="dropdown-menu">
									<?php
										$themeslist=simplexml_load_file("css/ux-themes/themes-list.xml");
										foreach($themeslist->children() as $theme) {
									?>
											<li><a href="#" onClick="update_user_theme('<?php echo $_SESSION['username'] ?>','<?php echo htmlentities($theme->htmltag) ?>');"><?php echo $_SESSION['ux_theme']==html_entity_decode($theme->htmltag)?'<i class="glyphicon glyphicon-ok"></i>&nbsp;':'' ?> <?php echo htmlentities($theme->title) ?></a></li>
									<?php
										}
									?>
								</ul>
							</li>
							<li role="separator" class="divider"></li>
							<li><a href="logout.php">Disconnetti</a></li>
					<?php
						}
					?>
					<!--<li><a href="#">Something else here</a></li>
					<li role="separator" class="divider"></li>
					<li><a href="#">Separated link</a></li>-->
				</ul>
			</li>
		</ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>
<script language="javascript">
	function update_user_theme(user_id, htmltag){
		var request = $.ajax({
			url: "ac_update_user_theme.php",
			method: "POST",
			data: {
					pref_userid : user_id,
					pref_htmltag : htmltag
			},
			dataType: "json"
		});
		 
		request.done(function(msg) {
			if (typeof msg.error !== 'undefined') {
				switch (parseInt(msg.error)) {
					case 0:
						gAlert("Successo", "E' stato aggiornato il tema grafico di default. Eseguire nuovamente l'accesso.", "img/gSuccess.png", "", <?php echo $gASuccessTimeOut ?>, 0);
						break;
					case -1:
						gAlert("Errore", "Errore nella procedura di aggiornamento della preferenza. Contattare il supporto.", "img/gError.png", "", <?php echo $gAErrorTimeOut ?>, 0);
						break;
					case -999:
						gAlert("Critical", "Attack!", "img/gError.png", "", <?php echo $gAErrorTimeOut ?>, 0);
						break;
					default:
						gAlert("Errore", "Errore durante il richiamo della procedura di aggiornamento della preferenza. Contattare il supporto.", "img/gError.png", "", <?php echo $gAErrorTimeOut ?>, 0);
				}
			}
		});
		 
		request.fail(function( jqXHR, textStatus ) {
			gAlert("Errore", "Errore durante il richiamo della procedura di aggiornamento della preferenza (AJAX Fail). Contattare il supporto.", "img/gError.png", "", <?php echo $gAErrorTimeOut ?>, 0);
		});
	};
</script>