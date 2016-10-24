<?php
	include_once('includes/header.php');
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
		if(!empty($_POST['username']) && !empty($_POST['p'])) { 
			$username = $_POST['username'];
			$password = $_POST['p']; // Recupero la password criptata.
			$login_result = login($username, $password, $mysqli);
			if($login_result == -1) {
				// Brute attack
				echo '<script>gAlert("Accesso sospeso!", "Si &egrave; cercato di accedere troppe volte con username e/o password sbagliati. L\'accesso &egrave; temporaneamente sospeso.", "img/gError.png", "login.php", ' . $gAExtErrorTimeOut . ');</script>';
			} elseif ($login_result == 0) {
				// Login fallito
				echo '<script>gAlert("Errore!", "Username e/o password sbagliati.", "img/gError.png", "login.php", ' . $gAErrorTimeOut . ');</script>';
			} elseif ($login_result == -2) {
				// Login fallito
				echo '<script>gAlert("Attenzione!", "L\' utente &egrave; disabilitato. Contattare l\'amministratore del sistema.", "img/gWarning.png", "login.php", ' . $gAExtWarningTimeOut . ');</script>';
			} else {
				// Login success
				echo '<script>gAlert("Benvenuto!", "Accesso effettuato correttamente.", "img/gSuccess.png", "index.php", ' . $gASuccessTimeOut . ');</script>';
			}
		} else { 
			// Le variabili corrette non sono state inviate a questa pagina dal metodo POST.
			echo '<script>gAlert("Error!", "Invalid request.", "img/gError.png", "login.php", ' . $gAErrorTimeOut . ');</script>';
		}
	?>
</body>
</html>