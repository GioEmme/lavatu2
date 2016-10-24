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
	<?php
		if (!empty($_GET["id_lavorazione"]) && !empty($_GET["id_ricevuta"])) {
			$xml_file_name = get_order_xml_for_print($_GET['id_lavorazione'], $mysqli, $App_BaseServerPath, $_GET['id_ricevuta']);
			if ($xml_file_name!='-1') {
				$xml_file_extension = '.xml';
				$pdf_file_extension = '.pdf';
				exec($fop_folder . '/' . $fop_command . ' -xml ' . $App_BaseServerPath . '/print_output/' . $xml_file_name . $xml_file_extension . ' -xsl ' . $App_BaseServerPath . '/print_template/order_fiscal_print.xslt -pdf ' . $App_BaseServerPath . '/print_output/' . $xml_file_name . $pdf_file_extension);
				if (file_exists($App_BaseServerPath . '/print_output/' . $xml_file_name . $pdf_file_extension)) {
					header('Location: ' . $App_BaseURL . '/print_output/' . $xml_file_name . $pdf_file_extension); 
				} else {
					echo '<script>gAlert("Errore", "Errore in fase di generazione del PDF per la stampa. Contattare il supporto.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
				}
			} else {
				echo '<script>gAlert("Errore", "Errore in fase di recupero dei dati da stampare. Contattare il supporto.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
			}
		} else {
			echo '<script>gAlert("Error!", "Invalid request.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
		}
	?>
</body>
</html>