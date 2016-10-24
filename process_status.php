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
		if (!empty($_POST["action"])) {
			if ($_POST["action"]=="edit") {
				if(!empty($_POST['id_stato']) && !empty($_POST['descrizione']) && !empty($_POST['tipo_documento']) && isset($_POST['ordinamento']) && !empty($_POST['ultimo_stato_1'])) {
					$id_stato = $_POST['id_stato'];
					$descrizione = $_POST['descrizione'];
					$ordine_stato = $_POST['ordinamento'];
					$tipo_documento = $_POST['tipo_documento'];
					$print_fiscal_receipt = $_POST['print_fiscal_receipt'];
					$show_change = $_POST['show_change'];
					$ultimo_stato_1 = $_POST['ultimo_stato_1'];
					$invio_sms = $_POST['invio_sms'];
					$row_color = $_POST['row_color'];
					$result = edit_status_data($id_stato, $descrizione, $tipo_documento, $ordine_stato, $print_fiscal_receipt, $show_change, $ultimo_stato_1, $invio_sms, $row_color, $mysqli);
					if ($result == 1) {
						// Edit eseguito
						echo '<script>gAlert("OK!", "Lo stato &egrave; stato modificato correttamente.", "img/gSuccess.png", "", ' . $gASuccessTimeOut . ', 1);</script>';
					} else {
						if ($result == -1) {
							echo '<script>gAlert("Errore!", "Controllare che la coppia STATO-TIPO DOCUMENTO non esista gi&agrave;. Tornare indietro per correggere il problema.", "img/gError.png", "", ' . $gAExtErrorTimeOut . ', 0);</script>';
						} else {
							echo '<script>gAlert("Errore!", "Errore in fase di modifica dello stato. Contattare il supporto.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
						}
					}
				} else { 
					// Le variabili corrette non sono state inviate a questa pagina dal metodo POST.
					echo '<script>gAlert("Error!", "Invalid request.", "img/gError.png", , 30000);</script>';
				}
			}
			if ($_POST["action"]=="new") {
				if(!empty($_POST['id_stato']) && !empty($_POST['descrizione']) && !empty($_POST['tipo_documento']) && isset($_POST['ordinamento']) && !empty($_POST['ultimo_stato_1'])) {
					$id_stato = $_POST['id_stato'];
					$descrizione = $_POST['descrizione'];
					$ordine_stato = $_POST['ordinamento'];
					$tipo_documento = $_POST['tipo_documento'];
					$print_fiscal_receipt = $_POST['print_fiscal_receipt'];
					$show_change = $_POST['show_change'];
					$ultimo_stato_1 = $_POST['ultimo_stato_1'];
					$invio_sms = $_POST['invio_sms'];
					$row_color = $_POST['row_color'];
					$result = add_status_data($id_stato, $descrizione, $tipo_documento, $ordine_stato, $print_fiscal_receipt, $show_change, $ultimo_stato_1, $invio_sms, $row_color, $mysqli);
					if ($result == 1) {
						// Add eseguito
						echo '<script>gAlert("OK!", "Il nuovo stato &egrave; stato creato.", "img/gSuccess.png", "", ' . $gASuccessTimeOut . ', 1);</script>';
					} else {
						if ($result == -1) {
							echo '<script>gAlert("Errore!", "Controllare che la coppia STATO-TIPO DOCUMENTO non esista gi&agrave;.", "img/gError.png", "", ' . $gAExtErrorTimeOut . ', 1);</script>';
						} else {
							echo '<script>gAlert("Errore!", "Errore in fase di creazione del nuovo stato. Contattare il supporto.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
						}
					}
				} else { 
					// Le variabili corrette non sono state inviate a questa pagina dal metodo POST.
					echo '<script>gAlert("Error!", "Invalid request.", "img/gError.png", "", 3000, 1);</script>';
				}
			}
			if ($_POST["action"]=="delete") {
				if(!empty($_POST['id_stato']) && !empty($_POST['tipo_documento'])) {
					$id_stato = $_POST['id_stato'];
					$tipo_documento = $_POST['tipo_documento'];
					$result = delete_status_data($id_stato, $tipo_documento, $mysqli);
					if ($result == 1) {
						// Delete eseguito
						echo '<script>gAlert("OK!", "Lo stato &egrave; stato eliminato correttamente.", "img/gSuccess.png", "", ' . $gASuccessTimeOut . ', 1);</script>';
					} else {
						echo '<script>gAlert("Errore!", "Errore in fase di eliminazione dello stato. Contattare il supporto.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
					}
				} else { 
					// Le variabili corrette non sono state inviate a questa pagina dal metodo POST.
					echo '<script>gAlert("Error!", "Invalid request.", "img/gError.png", "", ' . $gAErrorTimeOut . ');</script>';
				}
			}
		} else {
			echo '<script>gAlert("Error!", "Invalid request.", "img/gError.png");</script>';
		}
	?>
</body>
</html>