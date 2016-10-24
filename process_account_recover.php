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
		if(!empty($_POST['username'])) {
			$username = $_POST['username'];
			$result = recover($username, $mysqli);
			if ($result == 1) {
				// Recover eseguito
				echo '<script>gAlert("Password reset!", "Riceverete una mail con la nuova password al vostro indirizzo email.", "img/gSuccess.png", "", ' . $gASuccessTimeOut . ', 1);</script>';
			} elseif ($result == -1) {
				// Recover fallito
				echo '<script>gAlert("Errore!", "Lo Username specificato non esiste. E\' possibile creare in alternativa un nuovo utente.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
			} else {
				echo '<script>gAlert("Errore!", "Errore sul processo di recupero password. Contattare il supporto.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
			}
		} else { 
			// Le variabili corrette non sono state inviate a questa pagina dal metodo POST.
			echo '<script>gAlert("Error!", "Invalid request.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
		}
	?>
</body>
</html>