<?php
	$App_Name = 'LavaTu 2';
	$App_Version = '1.0';
	$App_BrandImage = 'img/brand_image.png';
	
	$App_BaseServerPath = 'c:\xampp\htdocs\lavatu2';
	$App_BaseURL = '/lavatu2';
	
	$Grids_PerPageRecords = 15;
	$Grids_PaginationPagesGap = 5;
	
	$fop_folder = 'c:\fop';
	$fop_command = 'fop.bat';
	
	$Clickatell_API_username = 'giovanni.mauramati';
	$Clickatell_API_password = 'APbGfNDFScNWgQ';
	$Clickatell_API_id = '3601041';
	
	$SMSDebugMode = 1;
	$SMSDebugNumber = '393488743455';
	
	$lavorazioni_tipo_fiscale = 'RFL';
	
	$gAErrorTimeOut = 3000;
	$gAWarningTimeOut = 3000;
	$gASuccessTimeOut = 1500;

	/* extended timeout, da usare dove c'è necessità di visualizzare messaggi lunghi */
	$gAExtErrorTimeOut = 5000;
	$gAExtWarningTimeOut = 5000;
	$gAExtSuccessTimeOut = 3000;
	
	setlocale(LC_CTYPE, 'it_IT');
	$curfmt = new NumberFormatter('it_IT', NumberFormatter::CURRENCY);
	$GLOBALS['curfmt'] = new NumberFormatter('it_IT', NumberFormatter::CURRENCY);

	// *** Log Global Variable ***
	$GLOBALS['logPrefixString'] = 'return sprintf("Page: %s | IP: %s | User: %s | ", $_SERVER["PHP_SELF"], $_SERVER["REMOTE_ADDR"], isset($_SESSION["username"])?$_SESSION["username"]:"-");';
	$GLOBALS['log'] = new KLogger("logs/log_" . date('Ymd') . ".txt" , KLogger::DEBUG);
	//$GLOBALS['log'] = new KLogger("logs/log.txt" , KLogger::WARN);
?>