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
				if(!empty($_POST['id_categoria']) && !empty($_POST['descrizione']) && isset($_POST['collapsed']) && isset($_POST['ordinamento'])) {
					$id_categoria = $_POST['id_categoria'];
					$descrizione = $_POST['descrizione'];
					$collapsed = $_POST['collapsed'];
					$ordinamento = $_POST['ordinamento'];
					$result = edit_category_data($id_categoria, $descrizione, $collapsed, $ordinamento, $mysqli);
					if ($result == 1) {
						// Edit eseguito
						echo '<script>gAlert("OK!", "L\'anagrafica categoria &egrave; stata modificata correttamente.", "img/gSuccess.png", "", ' . $gASuccessTimeOut . ', 1);</script>';
					} else {
						echo '<script>gAlert("Errore!", "Errore in fase di modifica dell\'anagrafica categoria. Contattare il supporto.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
					}
				} else { 
					// Le variabili corrette non sono state inviate a questa pagina dal metodo POST.
					echo '<script>gAlert("Error!", "Invalid request.", "img/gError.png", "", ' . $gAErrorTimeOut . ');</script>';
				}
			}
			if ($_POST["action"]=="new") {
				if(!empty($_POST['descrizione']) && isset($_POST['collapsed']) && isset($_POST['ordinamento'])) {
					$descrizione = $_POST['descrizione'];
					$collapsed = $_POST['collapsed'];
					$ordinamento = $_POST['ordinamento'];
					$result = add_category_data($descrizione, $collapsed, $ordinamento, $mysqli);
					if ($result == 1) {
						// Add eseguito
						echo '<script>gAlert("OK!", "L\'anagrafica della nuova categoria &egrave; stata creata.", "img/gSuccess.png", "", ' . $gASuccessTimeOut . ', 1);</script>';
					} else {
						echo '<script>gAlert("Errore!", "Errore in fase di creazione dell\'anagrafica categoria. Contattare il supporto.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
					}
				} else { 
					// Le variabili corrette non sono state inviate a questa pagina dal metodo POST.
					echo '<script>gAlert("Error!", "Invalid request.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
				}
			}
			if ($_POST["action"]=="delete") {
				if(!empty($_POST['id_categoria'])) {
					$id_categoria = $_POST['id_categoria'];
					$result = delete_category_data($id_categoria, $mysqli);
					if ($result == 1) {
						// Delete eseguito
						echo '<script>gAlert("OK!", "L\'anagrafica del categoria &egrave; stata eliminata correttamente.", "img/gSuccess.png", "", ' . $gASuccessTimeOut . ', 1);</script>';
					} else {
						echo '<script>gAlert("Errore!", "Errore in fase di eliminazione dell\'anagrafica categoria. Contattare il supporto.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
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