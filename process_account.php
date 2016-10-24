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
				if(!empty($_POST['user_id']) && !empty($_POST['username']) && !empty($_POST['nome']) && !empty($_POST['email']) && !empty($_POST['p']) && isset($_POST['is_active']) && isset($_POST['role_administrator'])) {
					$user_id = $_POST['user_id'];
					$username = $_POST['username'];
					$email = $_POST['email'];
					$name = $_POST['nome'];
					$is_active = $_POST['is_active'];
					$role_administrator = $_POST['role_administrator'];
					$password = $_POST['p']; // Recupero la password criptata.
					if (!empty($_POST['p_new'])) {
						$password_new = $_POST['p_new'];
					} else {
						$password_new = ''; //$password;
					}
					$result = edit_account_data($user_id, $username, $name, $email, $password, $password_new, $is_active, $role_administrator, $mysqli);
					if ($result == 1) {
						// Edit eseguito
						echo '<script>gAlert("OK!", "L\'utente &egrave; stato modificato correttamente.", "img/gSuccess.png", "", ' . $gASuccessTimeOut . ', 1);</script>';
						if (($password != $password_new) && (!empty($password_new))) {
							echo '<script>gAlert("Collegarsi nuovamente!", "E\' stata modificata la password. Si prega di eseguire nuovamente l\'accesso.", "img/gWarning.png", "", ' . $gAWarningTimeOut . ', 1);</script>';
						}
					} elseif ($result == -1) {
						// Old password non coincidente
						echo '<script>gAlert("Errore!", "La password corrente non &egrave; corretta. Si prega di riprovare.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
					} elseif ($result == -2) {
						// Nuova email già esistente
						echo '<script>gAlert("Errore!", "Il nuovo Username esiste gi&agrave;. Si prega di sceglierne un altro o confermare il precedente.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
					} else {
						echo '<script>gAlert("Errore!", "Errore in fase di modifica dell\'utente. Contattare il supporto.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
					}
				} else { 
					// Le variabili corrette non sono state inviate a questa pagina dal metodo POST.
					echo '<script>gAlert("Error!", "Invalid request.", "img/gError.png", "", ' . $gAErrorTimeOut . ');</script>';
				}
			}
			if ($_POST["action"]=="new") {
				if(!empty($_POST['username']) && !empty($_POST['nome']) && !empty($_POST['email']) && !empty($_POST['p_new']) && isset($_POST['is_active']) && isset($_POST['role_administrator'])) {
					$username = $_POST['username'];
					$email = $_POST['email'];
					$name = $_POST['nome'];
					$is_active = $_POST['is_active'];
					$role_administrator = $_POST['role_administrator'];
					$password = $_POST['p_new']; // Recupero la password criptata.
					$result = add_account_data($username, $name, $email, $password, $is_active, $role_administrator, $mysqli);
					if ($result == 1) {
						// Add eseguito
						echo '<script>gAlert("OK!", "Il nuovo utente &egrave; stato creato.", "img/gSuccess.png", "", ' . $gASuccessTimeOut . ', 1);</script>';
					} elseif ($result == -2) {
						// Nuovo username già esistente
						echo '<script>gAlert("Errore!", "L\'utente (Username) che si sta cercando di creare esiste gi&agrave;.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
					} else {
						echo '<script>gAlert("Errore!", "Errore in fase di creazione del nuovo utente. Contattare il supporto.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
					}
				} else { 
					// Le variabili corrette non sono state inviate a questa pagina dal metodo POST.
					echo '<script>gAlert("Error!", "Invalid request.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
				}
			}
			if ($_POST["action"]=="delete") {
				if(!empty($_POST['user_id'])) {
					if($_POST["user_id"] != $_SESSION['user_id']){
						$user_id = $_POST['user_id'];
						$result = delete_account_data($user_id, $mysqli);
						if ($result == 1) {
							// Delete eseguito
							echo '<script>gAlert("OK!", "L\'utente &egrave; stato eliminato correttamente.", "img/gSuccess.png", "", ' . $gASuccessTimeOut . ', 1);</script>';
						} else {
							echo '<script>gAlert("Errore!", "Errore in fase di eliminazione dell\'utente. Contattare il supporto.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
						}
					} else {
						echo '<script>gAlert("Errore!", "L\'utente selezionato non pu&ograve; essere rimosso perch&egrave; attualmente connesso.", "img/gError.png", "", ' . $gAErrorTimeOut . ', 1);</script>';
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