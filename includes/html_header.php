<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
<title><?php echo $App_Name . ' ' . $App_Version ?></title>

<!-- jQuery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.3/jquery.min.js"></script>
<!-- jQuery UI -->
<script src="https://code.jquery.com/ui/1.12.0-rc.2/jquery-ui.min.js"></script>
<link href="https://code.jquery.com/ui/1.12.0-rc.2/themes/smoothness/jquery-ui.css" rel="stylesheet">

<!-- workaround per incompatibilità buttons/tooltips tra bootstrap e jquery -->
<script>
	// handle jQuery plugin naming conflict between jQuery UI and Bootstrap
	$.widget.bridge('uibutton', $.ui.button);
	$.widget.bridge('uitooltip', $.ui.tooltip);
</script>

<!-- Bootstrap -->
<!--<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">-->
<?php
	if (!empty($_SESSION['ux_theme'])) {
		echo html_entity_decode($_SESSION['ux_theme']);
	} else {
?>
		<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
<?php
	}
?>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>

<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->

<!-- Password crypt client-side -->
<script type="text/javascript" src="js/sha512.js"></script>

<!-- Validator 1000Hz -->
<script type="text/javascript" src="js/validator.min.js"></script>

<!-- Funzioni varie client-side -->
<script type="text/javascript" src="js/forms.js"></script>

<!-- jQuery Gritter - Alert che compaiono in alto a destra -->
<link rel="stylesheet" type="text/css" href="css/jquery.gritter.css" />
<script type="text/javascript" src="js/jquery.gritter.js"></script>

<!-- CSS Custom -->
<link href="css/app_custom.css" rel="stylesheet">

<!-- Bootstrap Datepicker -->
<link id="bsdp-css" href="includes/datepicker/css/bootstrap-datepicker3.css" rel="stylesheet">
<script src="includes/datepicker/js/bootstrap-datepicker.js"></script>
<script src="includes/datepicker/locales/bootstrap-datepicker.it.min.js" charset="UTF-8"></script>

<!-- Bootbox (Alerts) -->
<script type="text/javascript" src="js/bootbox.min.js"></script>

<!-- Color picker -->
<link href="css/bootstrap-colorpicker.min.css" rel="stylesheet">
<script type="text/javascript" src="js/bootstrap-colorpicker.min.js"></script>