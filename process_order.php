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
				if(!empty($_POST['id_lavorazione']) && !empty($_POST['id_cliente']) && !empty($_POST['data_consegna'])) {
					$articoli = array();
					$servizi = array();
					$prezzi_servizi = array();
					$id_cliente = $_POST['id_cliente'];
					$data_consegna = $_POST['data_consegna'];
					$stato = $_POST['stato'];
					$note = $_POST['note'];
					$presa_visione = $_POST['presa_visione'];
					$id_lavorazione = $_POST['id_lavorazione'];
					$sms = $_POST['sms'];
					/* COSTRUZIONE ARRAY ARTICOLI */
					foreach ($_POST as $name => $val) {
						if(substr($name, 0, 8) == "articolo") {
							$articolo =  array(
								substr($name, 9, 8) => $val
							);
							array_push($articoli, $articolo);
						}
					}
					/* COSTRUZIONE ARRAY SERVIZI */
					foreach ($_POST as $name => $val) {
						if(substr($name, 0, 8) == "servizio") {
							$servizio =  array(
								substr($name, 9, 8) => $val
							);
							array_push($servizi, $servizio);
						}
					}
					/* COSTRUZIONE ARRAY SERVIZI-PREZZI */
					foreach ($_POST as $name => $val) {
						if(substr($name, 0, 15) == "prezzo_servizio") {
							$prezzo_servizio =  array(
								substr($name, 16, 8) => $val
							);
							array_push($prezzi_servizi, $prezzo_servizio);
						}
					}
					$result = edit_order_data($id_lavorazione, $id_cliente, $data_consegna, $stato, $note, $presa_visione, $articoli, $servizi, $prezzi_servizi, $sms, $mysqli);
					if ($result == 1) {
						// Edit eseguito
						echo '<script>gAlert("OK!", "La lavorazione &egrave; stata modificata correttamente.", "img/gSuccess.png", "", ' . $gASuccessTimeOut . ', 1);</script>';
					} else {
						echo '<script>gAlert("Errore!", "Errore in fase di modifica della lavorazione. Contattare il supporto.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
					}
				} else { 
					// Le variabili corrette non sono state inviate a questa pagina dal metodo POST.
					echo '<script>gAlert("Error!", "Invalid request.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
				}
			}
			if ($_POST["action"]=="new") {
				if(!empty($_POST['id_cliente']) && !empty($_POST['data_consegna'])) {
					$articoli = array();
					$servizi = array();
					$prezzi_servizi = array();
					$id_cliente = $_POST['id_cliente'];
					$data_consegna = $_POST['data_consegna'];
					$stato = $_POST['stato'];
					$note = $_POST['note'];
					$presa_visione = $_POST['presa_visione'];
					$sms = $_POST['sms'];
					/* COSTRUZIONE ARRAY ARTICOLI */
					foreach ($_POST as $name => $val) {
						if(substr($name, 0, 8) == "articolo") {
							$articolo =  array(
								substr($name, 9, 8) => $val
							);
							array_push($articoli, $articolo);
						}
					}
					/* COSTRUZIONE ARRAY SERVIZI */
					foreach ($_POST as $name => $val) {
						if(substr($name, 0, 8) == "servizio") {
							$servizio =  array(
								substr($name, 9, 8) => $val
							);
							array_push($servizi, $servizio);
						}
					}
					/* COSTRUZIONE ARRAY SERVIZI-PREZZI */
					foreach ($_POST as $name => $val) {
						if(substr($name, 0, 15) == "prezzo_servizio") {
							$prezzo_servizio =  array(
								substr($name, 16, 8) => $val
							);
							array_push($prezzi_servizi, $prezzo_servizio);
						}
					}
					$result = add_order_data($id_cliente, $data_consegna, $stato, $note, $presa_visione, $articoli, $servizi, $prezzi_servizi, $sms, $mysqli);
					if ($result == 1) {
						// Add eseguito
						echo '<script>gAlert("OK!", "La nuova lavorazione &egrave; stata acquisita.", "img/gSuccess.png", "", ' . $gASuccessTimeOut . ', 1);</script>';
					} else {
						echo '<script>gAlert("Errore!", "Errore in fase di acquisizione della lavorazione. Contattare il supporto.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
					}
				} else { 
					// Le variabili corrette non sono state inviate a questa pagina dal metodo POST.
					echo '<script>gAlert("Error!", "Invalid request.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
				}
			}
			if ($_POST["action"]=="delete") {
				if(!empty($_POST['id_lavorazione'])) {
					$id_lavorazione = $_POST['id_lavorazione'];
					$result = delete_order_data($id_lavorazione, $mysqli);
					if ($result == 1) {
						// Delete eseguito
						echo '<script>gAlert("OK!", "La lavorazione &egrave; stata eliminata correttamente.", "img/gSuccess.png", "", ' . $gASuccessTimeOut . ', 1);</script>';
					} else {
						echo '<script>gAlert("Errore!", "Errore in fase di eliminazione della lavorazione. Contattare il supporto.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
					}
				} else { 
					// Le variabili corrette non sono state inviate a questa pagina dal metodo POST.
					echo '<script>gAlert("Error!", "Invalid request.", "img/gError.png", "", 3000, 1);</script>';
				}
			}
			if ($_POST["action"]=="fiscal") {
				if(!empty($_POST['id_lavorazione'])) {
					$id_lavorazione = $_POST['id_lavorazione'];
					if ($_POST['reprint_fiscal']=='0') {
						$articoli = array();
						$servizi = array();
						$prezzi_servizi = array();
						$tipo_fiscale_ricevuta = $_POST['tipo_fiscale_ricevuta'];
						$numero_fiscale_ricevuta = $_POST['numero_fiscale_ricevuta'];
						$anno_fiscale_ricevuta = $_POST['anno_fiscale_ricevuta'];
						/* COSTRUZIONE ARRAY ARTICOLI */
						foreach ($_POST as $name => $val) {
							if(substr($name, 0, 8) == "articolo") {
								$articolo =  array(
									substr($name, 9, 8) => $val
								);
								array_push($articoli, $articolo);
							}
						}
						/* COSTRUZIONE ARRAY SERVIZI */
						foreach ($_POST as $name => $val) {
							if(substr($name, 0, 8) == "servizio") {
								$servizio =  array(
									substr($name, 9, 8) => $val
								);
								array_push($servizi, $servizio);
							}
						}
						$result = bill_order($id_lavorazione, $tipo_fiscale_ricevuta, $numero_fiscale_ricevuta, $anno_fiscale_ricevuta, $articoli, $servizi, $mysqli);
						if ($result > 0) {
							// Bill OK
							echo '<script>gAlert("OK!", "Ricevuta emessa correttamente.", "img/gSuccess.png", "process_order_fiscal_print.php?id_lavorazione=' . $id_lavorazione . '&id_ricevuta=' . $result . '", ' . $gASuccessTimeOut . ', 0);</script>';
						} elseif ($result == -1) {
							// Numerazione presente
							echo '<script>gAlert("Errore!", "La numerazione della ricevuta non &egrave corretta. Assicurarsi di aver inserito una numerazione valida. Tornare indietro per correggere il problema.", "img/gError.png", "", ' . $gAExtErrorTimeOut . ', 0);</script>';
						} elseif ($result <= -2) {
							echo '<script>gAlert("Errore!", "Errore in fase di emissione della ricevuta. Contattare il supporto.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
						}
					} else {
						// Reprint BILL
						echo '<script>gAlert("Attendere", "Ristampa della ricevuta in corso...", "img/gSuccess.png", "process_order_fiscal_print.php?id_lavorazione=' . $id_lavorazione . '", ' . $gASuccessTimeOut . ', 0);</script>';
					}
				} else { 
					// Le variabili corrette non sono state inviate a questa pagina dal metodo POST.
					echo '<script>gAlert("Error!", "Invalid request.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
				}
			}
		} else {
			echo '<script>gAlert("Error!", "Invalid request.", "img/gError.png");</script>';
		}
	?>
</body>
</html>