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
				if(!empty($_POST['tipo_fiscale']) && !empty($_POST['numero_fiscale']) && !empty($_POST['anno'])) {
					$tipo_fiscale = $_POST['tipo_fiscale'];
					$prefisso = $_POST['prefisso'];
					$numero_fiscale = $_POST['numero_fiscale'];
					$suffisso = $_POST['suffisso'];
					$anno = $_POST['anno'];
					$result = edit_nextnumber_data($tipo_fiscale, $prefisso, $numero_fiscale, $suffisso, $anno, $mysqli);
					if ($result == 1) {
						// Edit eseguito
						echo '<script>gAlert("OK!", "Il numeratore &egrave; stato modificato correttamente.", "img/gSuccess.png", "", ' . $gASuccessTimeOut . ', 1);</script>';
					} else {
						if ($result == -1) {
							echo '<script>gAlert("Errore!", "Controllare che la coppia TIPO DOCUMENTO-ANNO non esista gi&agrave;. Tornare indietro per correggere il problema.", "img/gError.png", "", ' . $gAExtErrorTimeOut . ', 0);</script>';
						} else {
							echo '<script>gAlert("Errore!", "Errore in fase di modifica del numeratore. Contattare il supporto.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
						}
					}
				} else { 
					// Le variabili corrette non sono state inviate a questa pagina dal metodo POST.
					echo '<script>gAlert("Error!", "Invalid request.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
				}
			}
			if ($_POST["action"]=="new") {
				if(!empty($_POST['tipo_fiscale']) && !empty($_POST['numero_fiscale']) && !empty($_POST['anno'])) {
					$tipo_fiscale = $_POST['tipo_fiscale'];
					$prefisso = $_POST['prefisso'];
					$numero_fiscale = $_POST['numero_fiscale'];
					$suffisso = $_POST['suffisso'];
					$anno = $_POST['anno'];
					$result = add_nextnumber_data($tipo_fiscale, $prefisso, $numero_fiscale, $suffisso, $anno, $mysqli);
					if ($result == 1) {
						// Add eseguito
						echo '<script>gAlert("OK!", "Il nuovo numeratore &egrave; stato creato.", "img/gSuccess.png", "", ' . $gASuccessTimeOut . ', 1);</script>';
					} else {
						if ($result == -1) {
							echo '<script>gAlert("Errore!", "Controllare che la coppia TIPO DOCUMENTO-ANNO non esista gi&agrave;. Tornare indietro per correggere il problema.", "img/gError.png", "", ' . $gAExtErrorTimeOut . ', 0);</script>';
						} else {
							echo '<script>gAlert("Errore!", "Errore in fase di creazione del numeratore. Contattare il supporto.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
						}
					}
				} else { 
					// Le variabili corrette non sono state inviate a questa pagina dal metodo POST.
					echo '<script>gAlert("Error!", "Invalid request.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
				}
			}
			if ($_POST["action"]=="delete") {
				if(!empty($_POST['tipo_fiscale']) && !empty($_POST['anno'])) {
					$tipo_fiscale = $_POST['tipo_fiscale'];
					$anno = $_POST['anno'];
					$result = delete_nextnumber_data($tipo_fiscale, $anno, $mysqli);
					if ($result == 1) {
						// Delete eseguito
						echo '<script>gAlert("OK!", "Il numeratore &egrave; stato eliminato correttamente.", "img/gSuccess.png", "", ' . $gASuccessTimeOut . ', 1);</script>';
					} else {
						echo '<script>gAlert("Errore!", "Errore in fase di eliminazione del numeratore. Contattare il supporto.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
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