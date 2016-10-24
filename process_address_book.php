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
			if ($_POST["action"]=="edit" || $_POST["action"]=="interconnect_edit") {
				if(!empty($_POST['id_cliente']) && !empty($_POST['nome']) && !empty($_POST['cognome'])) {
					$id_cliente = $_POST['id_cliente'];
					$nome = $_POST['nome'];
					$cognome = $_POST['cognome'];
					$indirizzo = $_POST['indirizzo'];
					$telefono = $_POST['telefono'];
					$cellulare = $_POST['cellulare'];
					$note = $_POST['note'];
					$invia_sms = $_POST['invia_sms'];
					$result = edit_address_book_data($id_cliente, $nome, $cognome, $indirizzo, $telefono, $cellulare, $note, $invia_sms, $mysqli);
					$results = explode("|", $result);
					if ($result[0] == 1) {
						// Edit eseguito
						if ($_POST['action']=='interconnect_edit'){
							echo '<script>gAlert("OK!", "L\'anagrafica cliente &egrave; stata modificata correttamente.", "img/gSuccess.png", "", ' . $gASuccessTimeOut . ', 1, "id_cliente_visible", ' . $results[1] . ');</script>';							
						} else {
							echo '<script>gAlert("OK!", "L\'anagrafica cliente &egrave; stata modificata correttamente.", "img/gSuccess.png", "", ' . $gASuccessTimeOut . ', 1);</script>';
						}
					} else {
						echo '<script>gAlert("Errore!", "Errore in fase di modifica dell\'anagrafica del cliente. Contattare il supporto.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
					}
				} else { 
					// Le variabili corrette non sono state inviate a questa pagina dal metodo POST.
					echo '<script>gAlert("Error!", "Invalid request.", "img/gError.png", "", ' . $gAErrorTimeOut . ');</script>';
				}
			}
			if ($_POST["action"]=="new" || $_POST["action"]=="interconnect") {
				if(!empty($_POST['nome']) && !empty($_POST['cognome'])) {
					$nome = $_POST['nome'];
					$cognome = $_POST['cognome'];
					$indirizzo = $_POST['indirizzo'];
					$telefono = $_POST['telefono'];
					$cellulare = $_POST['cellulare'];
					$note = $_POST['note']; // Recupero la password criptata.
					$invia_sms = $_POST['invia_sms'];
					$result = add_address_book_data($nome, $cognome, $indirizzo, $telefono, $cellulare, $note, $invia_sms, $mysqli);
					$results = explode("|", $result);
					if ($results[0] == '1') {
						// Add eseguito
						if ($_POST['action']=='interconnect'){
							echo '<script>gAlert("OK!", "L\'anagrafica del nuovo cliente &egrave; stata creata.", "img/gSuccess.png", "", ' . $gASuccessTimeOut . ', 1, "id_cliente_visible", ' . $results[1] . ');</script>';							
						} else {
							echo '<script>gAlert("OK!", "L\'anagrafica del nuovo cliente &egrave; stata creata.", "img/gSuccess.png", "", ' . $gASuccessTimeOut . ', 1);</script>';
						}
					} else {
						echo '<script>gAlert("Errore!", "Errore in fase di creazione dell\'anagrafica cliente. Contattare il supporto.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
					}
				} else { 
					// Le variabili corrette non sono state inviate a questa pagina dal metodo POST.
					echo '<script>gAlert("Error!", "Invalid request.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
				}
			}
			if ($_POST["action"]=="delete") {
				if(!empty($_POST['id_cliente'])) {
					$id_cliente = $_POST['id_cliente'];
					$result = delete_address_book_data($id_cliente, $mysqli);
					if ($result == 1) {
						// Delete eseguito
						echo '<script>gAlert("OK!", "L\'anagrafica del cliente &egrave; stata eliminata correttamente.", "img/gSuccess.png", "", ' . $gASuccessTimeOut . ', 1);</script>';
					} else {
						echo '<script>gAlert("Errore!", "Errore in fase di eliminazione dell\'anagrafica cliente. Contattare il supporto.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
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