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
		// Elimina tutti i valori della sessione.
		$_SESSION = array();
		// Recupera i parametri di sessione.
		$params = session_get_cookie_params();
		// Cancella i cookie attuali.
		setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
		// Cancella la sessione.
		session_destroy();
		echo '<script>gAlert("Disconnessione.", "Chiusura sessione avvenuta con successo.", "img/gSuccess.png", "index.php", 3000);</script>';
		//header('Location: ./');
	?>
</body>
</html>