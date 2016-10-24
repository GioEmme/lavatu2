<?php
	//################################################################################################
	//FUNZIONI GENERICHE
	//################################################################################################

	function getWhereCondition($searchop_string, $search_value, $field_type, &$param_array, &$type_array) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		switch ($searchop_string) {
			case 'contiene':
				//return " like " . (($field_type=='t')?"'":"") . "%" . $search_value . "%" . (($field_type=='t')?"'":"") . " ";
				$param_array[] = "%" . $search_value . "%";
				$type_array[] = $field_type=='t'?"s":"i";
				return " like ? ";
				break;
			case 'non contiene':
				//return " not like " . (($field_type=='t')?"'":"") . "%" . $search_value . "%" . (($field_type=='t')?"'":"") . " ";
				$param_array[] = "%" . $search_value . "%";
				$type_array[] = $field_type=='t'?"s":"i";
				return " not like ? ";
				break;
			case '=':
				//return " = " . (($field_type=='t')?"'":"") . $search_value . (($field_type=='t')?"'":"") . " ";
				$param_array[] = $search_value;
				$type_array[] = $field_type=='t'?"s":"i";
				return " = ? ";
				break;
			case '>=':
				//return " >= " . (($field_type=='t')?"'":"") . $search_value . (($field_type=='t')?"'":"") . " ";
				$param_array[] = $search_value;
				$type_array[] = $field_type=='t'?"s":"i";
				return " >= ? ";
				break;
			case '<=':
				//return " <= " . (($field_type=='t')?"'":"") . $search_value . (($field_type=='t')?"'":"") . " ";
				$param_array[] = $search_value;
				$type_array[] = $field_type=='t'?"s":"i";
				return " <= ? ";
				break;
			case 'diverso':
				//return " <> " . (($field_type=='t')?"'":"") . $search_value . (($field_type=='t')?"'":"") . " ";
				$param_array[] = $search_value;
				$type_array[] = $field_type=='t'?"s":"i";
				return " <> ? ";
				break;
		}
	}

	function refValues($arr){
		if (strnatcmp(phpversion(),'5.3') >= 0) //Reference is required for PHP 5.3+
		{
			$refs = array();
			foreach($arr as $key => $value)
				$refs[$key] = &$arr[$key];
			return $refs;
		}
		return $arr;
	}
	
	//################################################################################################
	//FINE FUNZIONI GENERICHE
	//################################################################################################

	//################################################################################################
	//FUNZIONI GESTIONE UTENZA
	//################################################################################################

	function sec_session_start() {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$session_name = 'sec_session_id'; // Imposta un nome di sessione
		$secure = false; // Imposta il parametro a true se vuoi usare il protocollo 'https'.
		$httponly = true; // Questo impedirà ad un javascript di essere in grado di accedere all'id di sessione.
		ini_set('session.use_only_cookies', 1); // Forza la sessione ad utilizzare solo i cookie.
		$cookieParams = session_get_cookie_params(); // Legge i parametri correnti relativi ai cookie.
		session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"], $secure, $httponly); 
		session_name($session_name); // Imposta il nome di sessione con quello prescelto all'inizio della funzione.
		session_start(); // Avvia la sessione php.
		session_regenerate_id(); // Rigenera la sessione e cancella quella creata in precedenza.
	}
	
	function login_check($mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		if(isset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['login_string'])) {
			$user_id = $_SESSION['user_id'];
			$login_string = $_SESSION['login_string'];
			$username = $_SESSION['username'];     
			$user_browser = $_SERVER['HTTP_USER_AGENT'];
			$query = 'SELECT password FROM users WHERE user_id = ? LIMIT 1';
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
			if ($stmt = $mysqli->prepare($query)) { 
				#*** Query parameters ***
				$params = Array('i', $user_id);
				$tmp = array();
				foreach($params as $key => $value) $tmp[$key] = &$params[$key];
				call_user_func_array(array($stmt, 'bind_param'), $tmp);
				#*** Query parameters ***
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
				
				$stmt->execute();
				if($stmt->errno!=0){
					$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
					return false;
				} else {
					$stmt->store_result();
					$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));

					if($stmt->num_rows == 1) {
						$stmt->bind_result($password);
						$stmt->fetch();
						$login_check = hash('sha512', $password.$user_browser);
						if($login_check == $login_string) {
							$GLOBALS['log']->LogInfo(sprintf(eval($GLOBALS['logPrefixString']) . "Check login utente OK"));
							return true;
						} else {
							$GLOBALS['log']->LogWarn(sprintf(eval($GLOBALS['logPrefixString']) . "Check login utente fallito (password errata)"));
							return false;
						}
					} else {
						$GLOBALS['log']->LogWarn(sprintf(eval($GLOBALS['logPrefixString']) . "Check login utente fallito (username non trovato)"));
						return false;
					}
				}
			} else {
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return false;
			}
		} else {
			$GLOBALS['log']->LogWarn(sprintf(eval($GLOBALS['logPrefixString']) . "Login utente fallito (variabili di sessione non impostate)"));
			return false;
		}
	}
	
	function login($username, $password, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$query = "SELECT user_id, username, password, salt, is_active, ux_theme FROM users WHERE username = ? LIMIT 1";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) { 
			#*** Query parameters ***
			$params = Array('s', $username);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			$stmt->execute();
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return false;
			} else {
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($user_id, $db_username, $db_password, $salt, $is_active, $ux_theme);
				$stmt->fetch();
				$password = hash('sha512', $password.$salt);
				if($stmt->num_rows == 1) {
					if(checkbrute($user_id, $mysqli) == true) { 
						$GLOBALS['log']->LogWarn(sprintf(eval($GLOBALS['logPrefixString']) . "L'utente risulta temporaneamente disabilitato per eccessivi tentativi di login"));
						return -1;
					} else {
						if($db_password == $password) {
							if($is_active==1){
								$user_browser = $_SERVER['HTTP_USER_AGENT']; // Recupero il parametro 'user-agent' relativo all'utente corrente.

								$user_id = preg_replace("/[^0-9]+/", "", $user_id); // ci proteggiamo da un attacco XSS
								$_SESSION['user_id'] = $user_id; 
								$db_username = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $db_username); // ci proteggiamo da un attacco XSS
								$_SESSION['username'] = $db_username;
								$_SESSION['login_string'] = hash('sha512', $password.$user_browser);
								$_SESSION['ux_theme'] = $ux_theme;
								$GLOBALS['log']->LogInfo(sprintf(eval($GLOBALS['logPrefixString']) . "Login eseguito con successo"));
								return 1;    
							} else {
								$GLOBALS['log']->LogWarn(sprintf(eval($GLOBALS['logPrefixString']) . "L'utente risulta disabilitato"));
								return -2;
							}
						} else {
							$GLOBALS['log']->LogWarn(sprintf(eval($GLOBALS['logPrefixString']) . "Login fallito (password errata)"));
							$now = time();
							$query = "INSERT INTO login_attempts (user_id, time) VALUES (?, ?)";
							$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
							if ($stmt = $mysqli->prepare($query)) { 
								#*** Query parameters ***
								$params = Array('ii', $user_id, $now);
								$tmp = array();
								foreach($params as $key => $value) $tmp[$key] = &$params[$key];
								call_user_func_array(array($stmt, 'bind_param'), $tmp);
								#*** Query parameters ***
								$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
								$stmt->execute();
								if($mysqli->errno!=0){
									$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
								}
								return 0;
							} else {
								$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
								return 0;
							}
						}
					}
				} else {
					$GLOBALS['log']->LogWarn(sprintf(eval($GLOBALS['logPrefixString']) . "Login fallito (username inesistente)"));
					return 0;
				}
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return 0;
		}
	}
	
	function checkbrute($user_id, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$now = time();
		$valid_attempts = $now - (2 * 60 * 60); 
		$query = "SELECT time FROM login_attempts WHERE user_id = ? AND time > ?";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) {
			#*** Query parameters ***
			$params = Array('ii', $user_id, $valid_attempts);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			$stmt->execute();
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return false;
			} else {
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				if($stmt->num_rows > 5) {
					return true;
				} else {
					return false;
				}
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return false;
		}
	}
	
	function recover($username, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$query = "SELECT count(1) as username_count FROM users WHERE username = ?";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) { 
			#*** Query parameters ***
			$params = Array('s', $username);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			$stmt->execute();
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return false;
			} else {
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($username_count);
				$stmt->fetch();
				if($username_count <= 0) {
					$GLOBALS['log']->LogWarn(sprintf(eval($GLOBALS['logPrefixString']) . "User recover fallito (username inesistente)"));
					return -1;
				} else {
					$query = "SELECT email FROM users WHERE username = ?";
					$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
					if ($stmt = $mysqli->prepare($query)) {
						#*** Query parameters ***
						$params = Array('s', $username);
						$tmp = array();
						foreach($params as $key => $value) $tmp[$key] = &$params[$key];
						call_user_func_array(array($stmt, 'bind_param'), $tmp);
						#*** Query parameters ***
						$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
						$stmt->execute();
						if($stmt->errno!=0){
							$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
							return false;
						} else {
							$stmt->store_result();
							$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
							$stmt->bind_result($email);
							$stmt->fetch();
							$random_salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
							$password = randomPassword();
							$email_password = $password;
							$password = hash('sha512', $password);
							$password = hash('sha512', $password.$random_salt);
							$query = "UPDATE users SET password = ?, salt = ? WHERE username = ?";
							$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
							if ($update_stmt = $mysqli->prepare($query)) {    
								#*** Query parameters ***
								$params = Array('sss', $password, $random_salt, $username);
								$tmp = array();
								foreach($params as $key => $value) $tmp[$key] = &$params[$key];
								call_user_func_array(array($update_stmt, 'bind_param'), $tmp);
								#*** Query parameters ***
								$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
								$update_stmt->execute();
								if($update_stmt->errno!=0){
									$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
									return 0;
								} else {
									$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $update_stmt->affected_rows));
									$message = "La tua password è stata resettata.\r\nLa nuova password è: " . $email_password;
									$message = wordwrap($message, 70, "\r\n");
									mail($email, 'Password recover', $message);

									return 1;
								}
							} else {
								$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
								return 0;
							}			
						}
					} else {
						$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
						return 0;
					}
				}
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return 0;
		}
	}
	
	function randomPassword() {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
		$pass = array();
		$alphaLength = strlen($alphabet) - 1;
		for ($i = 0; $i < 8; $i++) {
			$n = rand(0, $alphaLength);
			$pass[] = $alphabet[$n];
		}
		return implode($pass);
	}
	
	//################################################################################################
	//FINE FUNZIONI GESTIONE UTENZA
	//################################################################################################
	
	//################################################################################################
	//GESTIONE ANAGRAFICA UTENTI
	//################################################################################################

	function get_user_list($userid, $userid_searchop, $nome, $nome_searchop, $username, $username_searchop, $email, $email_searchop, $search_page, $per_page, $sort_index, $sort_dir, $mysqli) {   
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		
		$param = array();
		$type = array();
		$params = array();
		
		$push = array();
		$where_added = false;
		$boolean_op = ' and ';
		$query = "SELECT SQL_CALC_FOUND_ROWS user_id, name, email, username, is_active, role_administrator FROM users ";
		if(!empty($userid)){
			if (!$where_added) {
				$query = $query . 'where ';
				$where_added = true;
			}
			$query = $query . "user_id " . getWhereCondition($userid_searchop, $userid, 'n', $param, $type) . $boolean_op;
		}
		if(!empty($nome)){
			if (!$where_added) {
				$query = $query . 'where ';
				$where_added = true;
			}
			$query = $query . "name " . getWhereCondition($nome_searchop, $nome, 't', $param, $type) . $boolean_op;
		}
		if(!empty($username)){
			if (!$where_added) {
				$query = $query . 'where ';
				$where_added = true;
			}
			$query = $query . "username " . getWhereCondition($username_searchop, $username, 't', $param, $type) . $boolean_op;
		}
		if(!empty($email)){
			if (!$where_added) {
				$query = $query . 'where ';
				$where_added = true;
			}
			$query = $query . "email " . getWhereCondition($email_searchop, $email, 't', $param, $type) . $boolean_op;
		}
		$query = $where_added?substr($query, 0, strlen($query)-strlen($boolean_op)):$query;
		$query = $query . " ORDER BY " . $sort_index . " " . $sort_dir;
		$query = $query . " LIMIT " . $per_page . " OFFSET " . strval(($per_page * $search_page)-$per_page) . " ";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) {
			if (count($param)>0) {
				$params[] = implode("", $type);
				$params = array_merge($params, $param);
				call_user_func_array(array(&$stmt, 'bind_param'), refValues($params));
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($params)));
			}
			$stmt->execute();
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return false;
			} else {
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($user_id, $name, $email, $username, $is_active, $role_administrator);
				while($stmt->fetch()) {
					$row["user_id"] = $user_id;
					$row["name"] = $name;
					$row["email"] = $email;
					$row["username"] = $username;
					$row["is_active"] = $is_active;
					$row["role_administrator"] = $role_administrator;
					$row["record_count"] = mysqli_stmt_num_rows($stmt);
					$push[] = $row;
				}
				
				$query = "SELECT FOUND_ROWS()";
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
				if ($stmt = $mysqli->prepare($query)) {
					$stmt->execute();
					if($stmt->errno!=0){
						$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
					} else {
						$stmt->store_result();
						$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
						$stmt->bind_result($record_count);
						$stmt->fetch();
						$row["record_count"] = $record_count;
						$push[] = $row;
					}
				} else {
					$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				}
				return $push;
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return $push;
		}
	}
	
	function add_account_data($username, $name, $email, $new_password, $is_active, $role_administrator, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$query = "SELECT count(1) as username_count FROM users WHERE username = ?";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) { 
			#*** Query parameters ***
			$params = Array('s', $username);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			$stmt->execute(); 
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return 0;
			} else {
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($username_count); 
				$stmt->fetch();
				if($username_count >= 1) { 
					return -2;
				} else {
					$query = "INSERT INTO users (username, name, email, password, salt, is_active, role_administrator, ux_theme) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
					$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
					if ($insert_stmt = $mysqli->prepare($query)) {    
						$salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
						$proc_password = hash('sha512', $new_password.$salt);
						#*** Query parameters ***
						$params = Array('sssssiis', $username, $name, $email, $proc_password, $salt, $is_active, $role_administrator, '');
						$tmp = array();
						foreach($params as $key => $value) $tmp[$key] = &$params[$key];
						call_user_func_array(array($insert_stmt, 'bind_param'), $tmp);
						#*** Query parameters ***
						$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
						$insert_stmt->execute();
						if($insert_stmt->errno!=0){
							$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
							return 0;
						} else {
							return 1;
						}
					} else {
						$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
						return 0;
					}
				}
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return 0;
		}
	}
	
	function edit_account_data($user_id, $username, $name, $email, $password, $new_password, $is_active, $role_administrator, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$password_error = 0;
		$query = "SELECT count(1) as username_count FROM users WHERE username = ? and user_id <> ?";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) { 
			#*** Query parameters ***
			$params = Array('si', $username, $user_id);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			$stmt->execute(); 
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return 0;
			} else {
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($username_count); 
				$stmt->fetch();
				if($username_count >= 1) {
					return -2;
				} else {
					$query = "SELECT password, salt FROM users WHERE user_id = ?";
					$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
					if ($stmt = $mysqli->prepare($query)) { 
						#*** Query parameters ***
						$params = Array('i', $user_id);
						$tmp = array();
						foreach($params as $key => $value) $tmp[$key] = &$params[$key];
						call_user_func_array(array($stmt, 'bind_param'), $tmp);
						#*** Query parameters ***
						$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
						$stmt->execute(); 
						if($stmt->errno!=0){
							$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
							return 0;
						} else {
							$stmt->store_result();
							$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
							$stmt->bind_result($old_password, $old_salt); 
							$stmt->fetch();
							
							$proc_password = hash('sha512', $password.$old_salt);
							
							if (!empty($new_password)) {
								if ($old_password != $proc_password) {
									$password_error = 1;
								} else {
									$salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
									$proc_password = hash('sha512', $new_password.$salt);							
								}
							} else {
								$proc_password = $old_password;
								$salt = $old_salt;
							}
							
							if (!$password_error) {
								$query = "UPDATE users SET username = ?, name = ?, email = ?, password = ?, salt = ?, is_active = ?, role_administrator = ? WHERE user_id = ?";
								$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
								if ($update_stmt = $mysqli->prepare($query)) {    
									#*** Query parameters ***
									$params = Array('sssssiii', $username, $name, $email, $proc_password, $salt, $is_active, $role_administrator, $user_id);
									$tmp = array();
									foreach($params as $key => $value) $tmp[$key] = &$params[$key];
									call_user_func_array(array($update_stmt, 'bind_param'), $tmp);
									#*** Query parameters ***
									$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
									$update_stmt->execute(); 
									if($update_stmt->errno!=0){
										$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
										return 0;
									} else {
										return 1;
									}
								} else {
									$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
									return 0;
								}
							} else {
								return -1;
							}
						}
					} else {
						$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
						return 0;
					}
				}
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return 0;
		}
	}
	
	function get_account_data($user_id, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$query = "SELECT username, name, email, is_active, role_administrator FROM users WHERE user_id = ? LIMIT 1";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) { 
			#*** Query parameters ***
			$params = Array('i', $user_id);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			$stmt->execute(); 
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return 0;
			} else {
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($username, $name, $email, $is_active, $role_administrator); 
				$stmt->fetch();
				if($stmt->num_rows == 1) { 
					$account_data_array["exists"] = "1";
					$account_data_array["username"] = $username;
					$account_data_array["name"] = $name;
					$account_data_array["email"] = $email;
					$account_data_array["is_active"] = $is_active;
					$account_data_array["role_administrator"] = $role_administrator;
					$account_data_array["user_id"] = $user_id;
					return $account_data_array;
				} else {
					// L'utente inserito non esiste.
					$account_data_array["exists"] = "0";
					return $account_data_array;
				}
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return 0;
		}
	}
	
	function delete_account_data($user_id, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$query = "DELETE FROM users WHERE user_id = ?";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($delete_stmt = $mysqli->prepare($query)) {
			#*** Query parameters ***
			$params = Array('i', $user_id);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($delete_stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			$delete_stmt->execute();
			if($delete_stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return 0;
			} else {
				return 1;
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return 0;
		}
	}
	
	//################################################################################################
	//FINE GESTIONE ANAGRAFICA UTENTI
	//################################################################################################

	//################################################################################################
	//GESTIONE ANAGRAFICA SOGGETTI
	//################################################################################################

	function get_address_book_list($id_cliente, $id_cliente_searchop, $cognome, $cognome_searchop, $indirizzo, $indirizzo_searchop, $telefono, $telefono_searchop, $cellulare, $cellulare_searchop, $search_page, $per_page, $sort_index, $sort_dir, $mysqli) {   
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));

		$param = array();
		$type = array();
		$params = array();

		$push = array();
		$where_added = false;
		$boolean_op = ' and ';
		$query = "SELECT SQL_CALC_FOUND_ROWS id_cliente, nome, cognome, indirizzo, telefono, cellulare, note, invia_sms FROM clienti ";
		if(!empty($id_cliente)){
			if (!$where_added) {
				$query = $query . 'where ';
				$where_added = true;
			}
			$query = $query . "id_cliente " . getWhereCondition($id_cliente_searchop, $id_cliente, 'n', $param, $type) . $boolean_op;
		}
		if(!empty($cognome)){
			if (!$where_added) {
				$query = $query . 'where ';
				$where_added = true;
			}
			$query = $query . "cognome " . getWhereCondition($cognome_searchop, $cognome, 't', $param, $type) . $boolean_op;
		}
		if(!empty($indirizzo)){
			if (!$where_added) {
				$query = $query . 'where ';
				$where_added = true;
			}
			$query = $query . "indirizzo " . getWhereCondition($indirizzo_searchop, $indirizzo, 't', $param, $type) . $boolean_op;
		}
		if(!empty($cellulare)){
			if (!$where_added) {
				$query = $query . 'where ';
				$where_added = true;
			}
			$query = $query . "cellulare " . getWhereCondition($cellulare_searchop, $cellulare, 't', $param, $type) . $boolean_op;
		}
		if(!empty($telefono)){
			if (!$where_added) {
				$query = $query . 'where ';
				$where_added = true;
			}
			$query = $query . "telefono " . getWhereCondition($telefono_searchop, $telefono, 't', $param, $type) . $boolean_op;
		}
		$query = $where_added?substr($query, 0, strlen($query)-strlen($boolean_op)):$query;
		$query = $query . " ORDER BY " . $sort_index . " " . $sort_dir;
		$query = $query . " LIMIT " . $per_page . " OFFSET " . strval(($per_page * $search_page)-$per_page) . " ";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) {
			if (count($param)>0) {
				$params[] = implode("", $type);
				$params = array_merge($params, $param);
				call_user_func_array(array(&$stmt, 'bind_param'), refValues($params));
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($params)));
			}
			$stmt->execute(); 
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return $push;
			} else {
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($id_cliente, $nome, $cognome, $indirizzo, $telefono, $cellulare, $note, $invia_sms);
				while($stmt->fetch()) {
					$row["id_cliente"] = $id_cliente;
					$row["nome"] = $nome;
					$row["cognome"] = $cognome;
					$row["indirizzo"] = $indirizzo;
					$row["telefono"] = $telefono;
					$row["cellulare"] = $cellulare;
					$row["note"] = $note;
					$row["invia_sms"] = $invia_sms;
					$row["record_count"] = mysqli_stmt_num_rows($stmt);
					$push[] = $row;
				}
				
				$query = "SELECT FOUND_ROWS()";
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
				if ($stmt = $mysqli->prepare($query)) {
					$stmt->execute(); 
					if($stmt->errno!=0){
						$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
					} else {
						$stmt->store_result();
						$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
						$stmt->bind_result($record_count);
						$stmt->fetch();
						$row["record_count"] = $record_count;
						$push[] = $row;
					}
				} else {
					$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				}

				return $push;
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return $push;
		}
	}
	
	function get_address_book_data($id_cliente, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$query = "SELECT id_cliente, nome, cognome, indirizzo, telefono, cellulare, note, invia_sms FROM clienti WHERE id_cliente = ? LIMIT 1";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) { 
			#*** Query parameters ***
			$params = Array('i', $id_cliente);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			$stmt->execute(); 
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				$address_book_data_array["exists"] = "0";
				return $address_book_data_array;
			} else {
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($id_cliente, $nome, $cognome, $indirizzo, $telefono, $cellulare, $note, $invia_sms); 
				$stmt->fetch();
				if($stmt->num_rows == 1) { 
					$address_book_data_array["exists"] = "1";
					$address_book_data_array["nome"] = $nome;
					$address_book_data_array["cognome"] = $cognome;
					$address_book_data_array["indirizzo"] = $indirizzo;
					$address_book_data_array["telefono"] = $telefono;
					$address_book_data_array["cellulare"] = $cellulare;
					$address_book_data_array["id_cliente"] = $id_cliente;
					$address_book_data_array["note"] = $note;
					$address_book_data_array["invia_sms"] = $invia_sms;
					return $address_book_data_array;
				} else {
					$address_book_data_array["exists"] = "0";
					return $address_book_data_array;
				}
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			$address_book_data_array["exists"] = "0";
			return $address_book_data_array;
		}
	}
	
	function edit_address_book_data($id_cliente, $nome, $cognome, $indirizzo, $telefono, $cellulare, $note, $invia_sms, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$query = "UPDATE clienti SET nome = ?, cognome = ?, indirizzo = ?, telefono = ?, cellulare = ?, note = ?, invia_sms = ? WHERE id_cliente = ?";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($update_stmt = $mysqli->prepare($query)) {    
			#*** Query parameters ***
			$params = Array('sssssssi', $nome, $cognome, $indirizzo, $telefono, $cellulare, $note, $invia_sms, $id_cliente);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($update_stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			$update_stmt->execute();
			if($update_stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return '0|';
			} else {
				return '1|' . $id_cliente;
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return '0|';
		}
	}
	
	function delete_address_book_data($id_cliente, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$query = "DELETE FROM clienti WHERE id_cliente = ?";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($delete_stmt = $mysqli->prepare($query)) {
			#*** Query parameters ***
			$params = Array('i', $id_cliente);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($delete_stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			$delete_stmt->execute();
			if($delete_stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return 0;
			} else {
				return 1;
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return 0;
		}
	}
	
	function add_address_book_data($nome, $cognome, $indirizzo, $telefono, $cellulare, $note, $invia_sms, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$query = "INSERT INTO clienti (nome, cognome, indirizzo, telefono, cellulare, note, invia_sms) VALUES (?, ?, ?, ?, ?, ?, ?)";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($insert_stmt = $mysqli->prepare($query)) {
			#*** Query parameters ***
			$params = Array('sssssss', $nome, $cognome, $indirizzo, $telefono, $cellulare, $note, $invia_sms);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($insert_stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			$insert_stmt->execute();
			if($insert_stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return '0|';
			} else {
				$id_cliente = $mysqli->insert_id;
				return '1|' . $id_cliente;
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return '0|';
		}
	}
	
	//################################################################################################
	//FINE GESTIONE ANAGRAFICA SOGGETTI
	//################################################################################################

	//################################################################################################
	//GESTIONE ANAGRAFICA ARTICOLI
	//################################################################################################

	function get_item_list($id_articolo, $id_articolo_searchop, $descrizione, $descrizione_searchop, $search_page, $per_page, $sort_index, $sort_dir, $mysqli) {   
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));

		$param = array();
		$type = array();
		$params = array();

		$push = array();
		$where_added = false;
		$boolean_op = ' and ';
		$query = "SELECT SQL_CALC_FOUND_ROWS A.id_articolo, A.descrizione, C.descrizione FROM articoli A LEFT JOIN categorie C ON A.id_categoria = C.id_categoria ";
		if(!empty($id_articolo)){
			if (!$where_added) {
				$query = $query . 'where ';
				$where_added = true;
			}
			$query = $query . "A.id_articolo " . getWhereCondition($id_articolo_searchop, $id_articolo, 'n', $param, $type) . $boolean_op;
		}
		if(!empty($descrizione)){
			if (!$where_added) {
				$query = $query . 'where ';
				$where_added = true;
			}
			$query = $query . "A.descrizione " . getWhereCondition($descrizione_searchop, $descrizione, 't', $param, $type) . $boolean_op;
		}
		$query = $where_added?substr($query, 0, strlen($query)-strlen($boolean_op)):$query;
		$query = $query . " ORDER BY " . $sort_index . " " . $sort_dir;
		$query = $query . " LIMIT " . $per_page . " OFFSET " . strval(($per_page * $search_page)-$per_page) . " ";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) {
			if (count($param)>0) {
				$params[] = implode("", $type);
				$params = array_merge($params, $param);
				call_user_func_array(array(&$stmt, 'bind_param'), refValues($params));
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($params)));
			}
			$stmt->execute(); 
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return $push;
			} else {
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($id_articolo, $descrizione, $categoria);
				while($stmt->fetch()) {
					$row["id_articolo"] = $id_articolo;
					$row["descrizione"] = $descrizione;
					$row["categoria"] = $categoria;
					$row["record_count"] = mysqli_stmt_num_rows($stmt);
					$push[] = $row;
				}
				
				$query = "SELECT FOUND_ROWS()";
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
				if ($stmt = $mysqli->prepare($query)) {
					$stmt->execute(); 
					if($stmt->errno!=0){
						$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
						return $push;
					} else {
						$stmt->store_result();
						$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
						$stmt->bind_result($record_count);
						$stmt->fetch();
						$row["record_count"] = $record_count;
						$push[] = $row;

						return $push;
					}
				} else {
					$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
					return $push;
				}
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return $push;
		}
	}
	
	function get_item_data($id_articolo, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$query = "SELECT A.id_articolo, A.descrizione, C.descrizione, C.id_categoria FROM articoli A LEFT JOIN categorie C ON A.id_categoria = C.id_categoria WHERE id_articolo = ? LIMIT 1";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) { 
			#*** Query parameters ***
			$params = Array('i', $id_articolo);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			$stmt->execute(); 
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				$item_data_array["exists"] = "0";
				return $item_data_array;
			} else {
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($id_articolo, $descrizione, $categoria, $id_categoria); 
				$stmt->fetch();
				if($stmt->num_rows == 1) { 
					$item_data_array["exists"] = "1";
					$item_data_array["id_articolo"] = $id_articolo;
					$item_data_array["descrizione"] = $descrizione;
					$item_data_array["categoria"] = $categoria;
					$item_data_array["id_categoria"] = $id_categoria;
					return $item_data_array;
				} else {
					$item_data_array["exists"] = "0";
					return $item_data_array;
				}
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			$item_data_array["exists"] = "0";
			return $item_data_array;
		}
	}

	function edit_item_data($id_articolo, $descrizione, $id_categoria, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$query = "UPDATE articoli SET descrizione = ?, id_categoria = ? WHERE id_articolo = ?";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($update_stmt = $mysqli->prepare($query)) {
			#*** Query parameters ***
			$params = Array('sii', $descrizione, $id_categoria, $id_articolo);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($update_stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			$update_stmt->execute();
			if($update_stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return 0;
			} else {
				return 1;
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return 0;
		}
	}
	
	function delete_item_data($id_articolo, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$query = "DELETE FROM articoli WHERE id_articolo = ?";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($delete_stmt = $mysqli->prepare($query)) {
			#*** Query parameters ***
			$params = Array('i', $id_articolo);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($delete_stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			$delete_stmt->execute();
			if($delete_stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return 0;
			} else {
				return 1;
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return 0;
		}
	}
	
	function add_item_data($descrizione, $id_categoria, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$query = "INSERT INTO articoli (descrizione, id_categoria) VALUES (?, ?)";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($insert_stmt = $mysqli->prepare($query)) {
			#*** Query parameters ***
			$params = Array('si', $descrizione, $id_categoria);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($insert_stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			$insert_stmt->execute();
			if($insert_stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return 0;
			} else {
				return 1;
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return 0;
		}
	}

	//################################################################################################
	//FINE GESTIONE ANAGRAFICA ARTICOLI
	//################################################################################################

	//################################################################################################
	//GESTIONE ANAGRAFICA SERVIZI
	//################################################################################################

	function get_service_list($id_servizio, $id_servizio_searchop, $descrizione, $descrizione_searchop, $prezzo, $prezzo_searchop, $search_page, $per_page, $sort_index, $sort_dir, $mysqli) {   
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));

		$param = array();
		$type = array();
		$params = array();

		$push = array();
		$where_added = false;
		$boolean_op = ' and ';
		$query = "SELECT SQL_CALC_FOUND_ROWS S.id_servizio, S.descrizione, S.prezzo, C.id_categoria, C.descrizione FROM servizi S LEFT JOIN categorie C ON S.id_categoria = C.id_categoria ";
		if(!empty($id_servizio)){
			if (!$where_added) {
				$query = $query . 'where ';
				$where_added = true;
			}
			$query = $query . "S.id_servizio " . getWhereCondition($id_servizio_searchop, $id_servizio, 'n', $param, $type) . $boolean_op;
		}
		if(!empty($descrizione)){
			if (!$where_added) {
				$query = $query . 'where ';
				$where_added = true;
			}
			$query = $query . "S.descrizione " . getWhereCondition($descrizione_searchop, $descrizione, 't', $param, $type) . $boolean_op;
		}
		if(!empty($prezzo)){
			if (!$where_added) {
				$query = $query . 'where ';
				$where_added = true;
			}
			$query = $query . "prezzo " . getWhereCondition($prezzo_searchop, $prezzo, 'n', $param, $type) . $boolean_op;
		}
		$query = $where_added?substr($query, 0, strlen($query)-strlen($boolean_op)):$query;
		$query = $query . " ORDER BY " . $sort_index . " " . $sort_dir;
		$query = $query . " LIMIT " . $per_page . " OFFSET " . strval(($per_page * $search_page)-$per_page) . " ";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) {
			if (count($param)>0) {
				$params[] = implode("", $type);
				$params = array_merge($params, $param);
				call_user_func_array(array(&$stmt, 'bind_param'), refValues($params));
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($params)));
			}
			$stmt->execute(); 
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return $push;
			} else {
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($id_servizio, $descrizione, $prezzo, $id_categoria, $categoria);
				while($stmt->fetch()) {
					$row["id_servizio"] = $id_servizio;
					$row["descrizione"] = $descrizione;
					$row["prezzo"] = $prezzo;
					$row["id_categoria"] = $id_categoria;
					$row["categoria"] = $categoria;
					$row["record_count"] = mysqli_stmt_num_rows($stmt);
					$push[] = $row;
				}
				
				$query = "SELECT FOUND_ROWS()";
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
				if ($stmt = $mysqli->prepare($query)) {
					$stmt->execute(); 
					if($stmt->errno!=0){
						$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
						return $push;
					} else {					
						$stmt->store_result();
						$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
						$stmt->bind_result($record_count);
						$stmt->fetch();
						$row["record_count"] = $record_count;
						$push[] = $row;

						return $push;
					}
				} else {
					$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
					return $push;
				}
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return $push;
		}
	}
	
	function get_service_data($id_servizio, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$query = "SELECT S.id_servizio, S.descrizione, S.prezzo, C.id_categoria, C.descrizione FROM servizi S LEFT JOIN categorie C ON S.id_categoria = C.id_categoria WHERE id_servizio = ? LIMIT 1";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) {
			#*** Query parameters ***
			$params = Array('i', $id_servizio);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			$stmt->execute(); 
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				$service_data_array["exists"] = "0";
				return $service_data_array;
			} else {								
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($id_servizio, $descrizione, $prezzo, $id_categoria, $categoria); 
				$stmt->fetch();
				if($stmt->num_rows == 1) { 
					$service_data_array["exists"] = "1";
					$service_data_array["id_servizio"] = $id_servizio;
					$service_data_array["descrizione"] = $descrizione;
					$service_data_array["prezzo"] = $prezzo;
					$service_data_array["id_categoria"] = $id_categoria;
					$service_data_array["categoria"] = $categoria;
					return $service_data_array;
				} else {
					$service_data_array["exists"] = "0";
					return $service_data_array;
				}
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			$service_data_array["exists"] = "0";
			return $service_data_array;
		}
	}
	
	function edit_service_data($id_servizio, $descrizione, $prezzo, $id_categoria, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$query = "UPDATE servizi SET descrizione = ?, prezzo = ?, id_categoria = ? WHERE id_servizio = ?";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($update_stmt = $mysqli->prepare($query)) {
			#*** Query parameters ***
			$params = Array('sdii', $descrizione, $prezzo, $id_categoria, $id_servizio);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($update_stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			$update_stmt->execute();
			if($update_stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return 0;
			} else {
				return 1;
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return 0;
		}
	}
	
	function delete_service_data($id_servizio, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$query = "DELETE FROM servizi WHERE id_servizio = ?";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($delete_stmt = $mysqli->prepare($query)) {
			#*** Query parameters ***
			$params = Array('i', $id_servizio);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($delete_stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			$delete_stmt->execute();
			if($delete_stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return 0;
			} else {
				return 1;
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return 0;
		}
	}
	
	function add_service_data($descrizione, $prezzo, $id_categoria, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$query = "INSERT INTO servizi (descrizione, prezzo, id_categoria) VALUES (?, ?, ?)";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($insert_stmt = $mysqli->prepare($query)) {
			#*** Query parameters ***
			$params = Array('sdi', $descrizione, $prezzo, $id_categoria);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($insert_stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			$insert_stmt->execute();
			if($insert_stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return 0;
			} else {
				return 1;
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return 0;
		}
	}

	//################################################################################################
	//FINE GESTIONE ANAGRAFICA SERVIZI
	//################################################################################################

	//################################################################################################
	//GESTIONE CATEGORIE ARTICOLI / SERVIZI
	//################################################################################################

	function get_category_list($id_categoria, $id_categoria_searchop, $descrizione, $descrizione_searchop, $search_page, $per_page, $sort_index, $sort_dir, $mysqli) {   
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));

		$param = array();
		$type = array();
		$params = array();

		$push = array();
		$where_added = false;
		$boolean_op = ' and ';
		$query = "SELECT SQL_CALC_FOUND_ROWS id_categoria, descrizione, collapsed, ordinamento FROM categorie ";
		if(!empty($id_categoria)){
			if (!$where_added) {
				$query = $query . 'where ';
				$where_added = true;
			}
			$query = $query . "id_categoria " . getWhereCondition($id_categoria_searchop, $id_categoria, 'n', $param, $type) . $boolean_op;
		}
		if(!empty($descrizione)){
			if (!$where_added) {
				$query = $query . 'where ';
				$where_added = true;
			}
			$query = $query . "descrizione " . getWhereCondition($descrizione_searchop, $descrizione, 't', $param, $type) . $boolean_op;
		}
		$query = $where_added?substr($query, 0, strlen($query)-strlen($boolean_op)):$query;
		$query = $query . " ORDER BY " . $sort_index . " " . $sort_dir;
		$query = $query . " LIMIT " . $per_page . " OFFSET " . strval(($per_page * $search_page)-$per_page) . " ";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) {
			if (count($param)>0) {
				$params[] = implode("", $type);
				$params = array_merge($params, $param);
				call_user_func_array(array(&$stmt, 'bind_param'), refValues($params));
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($params)));
			}
			$stmt->execute(); 
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return $push;
			} else {			
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($id_categoria, $descrizione, $collapsed, $ordinamento);
				while($stmt->fetch()) {
					$row["id_categoria"] = $id_categoria;
					$row["descrizione"] = $descrizione;
					$row["collapsed"] = $collapsed;
					$row["ordinamento"] = $ordinamento;
					$row["record_count"] = mysqli_stmt_num_rows($stmt);
					$push[] = $row;
				}
				
				$query = "SELECT FOUND_ROWS()";
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
				if ($stmt = $mysqli->prepare($query)) {
					$stmt->execute(); 
					if($stmt->errno!=0){
						$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
						return $push;
					} else {								
						$stmt->store_result();
						$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
						$stmt->bind_result($record_count);
						$stmt->fetch();
						$row["record_count"] = $record_count;
						$push[] = $row;
					}
				} else {
					$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
					return $push;
				}
				return $push;
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return $push;
		}
	}
	
	function get_category_data($id_categoria, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$query = "SELECT id_categoria, descrizione, collapsed, ordinamento FROM categorie WHERE id_categoria = ? LIMIT 1";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) { 
			#*** Query parameters ***
			$params = Array('i', $id_categoria);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			$stmt->execute(); 
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				$category_data_array["exists"] = "0";
				return $category_data_array;
			} else {								
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($id_categoria, $descrizione, $collapsed, $ordinamento); 
				$stmt->fetch();
				if($stmt->num_rows == 1) { 
					$category_data_array["exists"] = "1";
					$category_data_array["id_categoria"] = $id_categoria;
					$category_data_array["descrizione"] = $descrizione;
					$category_data_array["collapsed"] = $collapsed;
					$category_data_array["ordinamento"] = $ordinamento;
					return $category_data_array;
				} else {
					$category_data_array["exists"] = "0";
					return $category_data_array;
				}
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			$category_data_array["exists"] = "0";
			return $category_data_array;
		}
	}
	
	function edit_category_data($id_categoria, $descrizione, $collapsed, $ordinamento, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$query = "UPDATE categorie SET descrizione = ?, collapsed = ?, ordinamento = ? WHERE id_categoria = ?";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));		
		if ($update_stmt = $mysqli->prepare($query)) {    
			#*** Query parameters ***
			$params = Array('siii', $descrizione, $collapsed, $ordinamento, $id_categoria);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($update_stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			
			$update_stmt->execute();
			if($update_stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return 0;
			} else {								
				return 1;
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return 0;
		}
	}
	
	function delete_category_data($id_categoria, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$query = "DELETE FROM categorie WHERE id_categoria = ?";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));		
		if ($delete_stmt = $mysqli->prepare($query)) {    
			#*** Query parameters ***
			$params = Array('i', $id_categoria);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($delete_stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			
			$delete_stmt->execute();
			if($delete_stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return 0;
			} else {								
				return 1;
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return 0;
		}
	}
	
	function add_category_data($descrizione, $collapsed, $ordinamento, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$query = "INSERT INTO categorie (descrizione, collapsed, ordinamento) VALUES (?, ?, ?)";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));		
		if ($insert_stmt = $mysqli->prepare($query)) {    
			#*** Query parameters ***
			$params = Array('sii', $descrizione, $collapsed, $ordinamento);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($insert_stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			
			$insert_stmt->execute();
			if($insert_stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return 0;
			} else {								
				return 1;
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return 0;
		}
	}

	//################################################################################################
	//FINE GESTIONE CATEGORIE ARTICOLI / SERVIZI
	//################################################################################################
	
	//################################################################################################
	//GESTIONE ORDINI
	//################################################################################################

	function get_order_list($id_lavorazione, $id_lavorazione_searchop, $cognome_cliente, $cognome_cliente_searchop, $data_consegna_dal, $data_consegna_dal_searchop, $data_consegna_al, $data_consegna_al_searchop, $stato, $stato_searchop, $numero_fiscale_ricevuta, $numero_fiscale_ricevuta_searchop, $search_page, $per_page, $sort_index, $sort_dir, $mysqli) {   
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));

		$param = array();
		$type = array();
		$params = array();

		$push = array();
		$where_added = false;
		$boolean_op = ' and ';
		$query =			"SELECT SQL_CALC_FOUND_ROWS L.id_lavorazione, C.cognome as cognome_cliente, C.nome as nome_cliente, L.data_consegna, L.stato, ";
		$query = $query . 	"(SELECT SUM(prezzo_lavorazione*quantita) FROM lavorazioni_servizi LS WHERE LS.id_lavorazione = L.id_lavorazione) AS tot_lavorazione, ";
		$query = $query . 	"L.note_lavorazione, L.sms, ST.descrizione, ST.print_fiscal_receipt, ST.row_color ";
		$query = $query . 	"FROM lavorazioni L ";
		$query = $query . 	"INNER JOIN clienti C ON L.id_cliente = C.id_cliente ";
		$query = $query . 	"INNER JOIN stati ST ON L.stato = ST.id_stato ";
		if(!empty($id_lavorazione)){
			if (!$where_added) {
				$query = $query . 'where ';
				$where_added = true;
			}
			$query = $query . "L.id_lavorazione " . getWhereCondition($id_lavorazione_searchop, $id_lavorazione, 'n', $param, $type) . $boolean_op;
		}
		if(!empty($cognome_cliente)){
			if (!$where_added) {
				$query = $query . 'where ';
				$where_added = true;
			}
			$query = $query . "C.cognome " . getWhereCondition($cognome_cliente_searchop, $cognome_cliente, 't', $param, $type) . $boolean_op;
		}
		if(!empty($data_consegna_dal)){
			if (!$where_added) {
				$query = $query . 'where ';
				$where_added = true;
			}
			$fmtDate = DateTime::createFromFormat('d/m/Y', trim($data_consegna_dal));
			$fmtDate = $fmtDate->format('Y-m-d');
			$query = $query . "L.data_consegna " . getWhereCondition($data_consegna_dal_searchop, $fmtDate, 't', $param, $type) . $boolean_op;
		}
		if(!empty($data_consegna_al)){
			if (!$where_added) {
				$query = $query . 'where ';
				$where_added = true;
			}
			$fmtDate = DateTime::createFromFormat('d/m/Y', trim($data_consegna_al));
			$fmtDate = $fmtDate->format('Y-m-d');
			$query = $query . "L.data_consegna " . getWhereCondition($data_consegna_al_searchop, $fmtDate, 't', $param, $type) . $boolean_op;
		}
		if(!empty($stato)){
			if($stato!='*'){
				if (!$where_added) {
					$query = $query . 'where ';
					$where_added = true;
				}
				$query = $query . "L.stato " . getWhereCondition($stato_searchop, $stato, 't', $param, $type) . $boolean_op;
			}
		}
		if(!empty($numero_fiscale_ricevuta)){
			if (!$where_added) {
				$query = $query . 'where ';
				$where_added = true;
			}
			$query = $query . "EXISTS (SELECT 1 FROM lavorazioni_ricevute LR WHERE LR.id_lavorazione = L.id_lavorazione AND LR.numero_fiscale_ricevuta " . getWhereCondition($numero_fiscale_ricevuta_searchop, $numero_fiscale_ricevuta, 'n', $param, $type) . ") " . $boolean_op;
		}
		$query = $where_added?substr($query, 0, strlen($query)-strlen($boolean_op)):$query;		
		if($stato!="D"){
			$query = $query . ($where_added?" AND stato <> 'D' ":" WHERE stato <> 'D' "); 
		}
		$query = $query . " ORDER BY " . $sort_index . " " . $sort_dir;
		$query = $query . " LIMIT " . $per_page . " OFFSET " . strval(($per_page * $search_page)-$per_page) . " ";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) {
			if (count($param)>0) {
				$params[] = implode("", $type);
				$params = array_merge($params, $param);
				call_user_func_array(array(&$stmt, 'bind_param'), refValues($params));
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($params)));
			}
			$stmt->execute();
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return $push;
			} else {
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($id_lavorazione, $cognome_cliente, $nome_cliente, $data_consegna, $stato, $tot_lavorazione, $note_lavorazione, $sms, $descrizione_stato, $print_fiscal_receipt, $row_color);
				while($stmt->fetch()) {
					$row["id_lavorazione"] = $id_lavorazione;
					$row["cognome_cliente"] = $cognome_cliente;
					$row["nome_cliente"] = $nome_cliente;
					$row["data_consegna"] = $data_consegna;
					$row["stato"] = $stato;
					$row["tot_lavorazione"] = $tot_lavorazione;
					$row["note_lavorazione"] = $note_lavorazione;
					$row["sms"] = $sms;
					$row["descrizione_stato"] = $descrizione_stato;
					$row["print_fiscal_receipt"] = $print_fiscal_receipt;
					$row["row_color"] = $row_color;
					$row["record_count"] = mysqli_stmt_num_rows($stmt);
					$push[] = $row;
				}
				
				$query = "SELECT FOUND_ROWS()";
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
				if ($stmt = $mysqli->prepare($query)) {
					$stmt->execute(); 
					if($stmt->errno!=0){
						$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
						return $push;
					} else {					
						$stmt->store_result();
						$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
						$stmt->bind_result($record_count);
						$stmt->fetch();
						$row["record_count"] = $record_count;
						$push[] = $row;
					}
				} else {
					$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
					return $push;
				}
				
				return $push;
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return $push;
		}
	}
	
	function get_order_category_list($type, $mysqli) {   
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$push = array();
		$query = "SELECT SQL_CALC_FOUND_ROWS DISTINCT C.id_categoria, C.descrizione, C.collapsed FROM categorie C ";
		if($type=="S") {
			$query = $query . "INNER JOIN servizi S ON S.id_categoria = C.id_categoria ";
		} elseif ($type=="A") {
			$query = $query . "INNER JOIN articoli A ON A.id_categoria = C.id_categoria ";
		}
		$query = $query . " ORDER BY C.collapsed ASC, C.ordinamento ASC ";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) {
			$stmt->execute(); 
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return $push;
			} else {
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($id_categoria, $descrizione, $collapsed);
				while($stmt->fetch()) {
					$row["id_categoria"] = $id_categoria;
					$row["descrizione"] = $descrizione;
					$row["collapsed"] = $collapsed;
					$row["record_count"] = mysqli_stmt_num_rows($stmt);
					$push[] = $row;
				}
				
				$query = "SELECT FOUND_ROWS()";
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
				if ($stmt = $mysqli->prepare($query)) {
					$stmt->execute(); 
					if($stmt->errno!=0){
						$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
						return $push;
					} else {					
						$stmt->store_result();
						$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
						$stmt->bind_result($record_count);
						$stmt->fetch();
						$row["record_count"] = $record_count;
						$push[] = $row;
					}
				} else {
					$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
					return $push;
				}

				return $push;
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return $push;
		}
	}
	
	function get_order_item_list($id_categoria, $mysqli) {   
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$push = array();
		$query = "SELECT SQL_CALC_FOUND_ROWS DISTINCT A.id_articolo, A.descrizione FROM articoli A WHERE id_categoria = ? ";
		$query = $query . " ORDER BY A.descrizione ASC ";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) {
			#*** Query parameters ***
			$params = Array('i', $id_categoria);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			$stmt->execute(); 
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return $push;
			} else {					
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($id_articolo, $descrizione);
				while($stmt->fetch()) {
					$row["id_articolo"] = $id_articolo;
					$row["descrizione"] = $descrizione;
					$row["record_count"] = mysqli_stmt_num_rows($stmt);
					$push[] = $row;
				}
			
				$query = "SELECT FOUND_ROWS()";
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
				if ($stmt = $mysqli->prepare($query)) {
					$stmt->execute(); 
					if($stmt->errno!=0){
						$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
						return $push;
					} else {										
						$stmt->store_result();
						$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
						$stmt->bind_result($record_count);
						$stmt->fetch();
						$row["record_count"] = $record_count;
						$push[] = $row;
					}
				} else {
					$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
					return $push;
				}
			}
			
			return $push;
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return $push;
		}
	}

	function get_order_service_list($id_categoria, $mysqli) {   
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$push = array();
		$query = "SELECT SQL_CALC_FOUND_ROWS DISTINCT S.id_servizio, S.descrizione, S.prezzo FROM servizi S WHERE id_categoria = ? ";
		$query = $query . " ORDER BY S.descrizione ASC ";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) {
			#*** Query parameters ***
			$params = Array('i', $id_categoria);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			$stmt->execute(); 
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return $push;
			} else {													
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($id_servizio, $descrizione, $prezzo);
				while($stmt->fetch()) {
					$row["id_servizio"] = $id_servizio;
					$row["descrizione"] = $descrizione;
					$row["prezzo"] = $prezzo;
					$row["record_count"] = mysqli_stmt_num_rows($stmt);
					$push[] = $row;
				}
				
				$query = "SELECT FOUND_ROWS()";
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
				if ($stmt = $mysqli->prepare($query)) {
					$stmt->execute(); 
					if($stmt->errno!=0){
						$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
						return $push;
					} else {																		
						$stmt->store_result();
						$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
						$stmt->bind_result($record_count);
						$stmt->fetch();
						$row["record_count"] = $record_count;
						$push[] = $row;
					}
				} else {
					$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
					return $push;					
				}
			}
			
			return $push;
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return $push;
		}
	}

	function add_order_data($id_cliente, $data_consegna, $stato, $note, $presa_visione, $articoli, $servizi, $prezzi_servizi, $sms, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$mysqli->autocommit(FALSE);
		$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Avvio transazione DB");
		try {
			$query = "INSERT INTO lavorazioni (id_cliente, data_consegna, stato, note_lavorazione, presa_visione, sms) VALUES (?, ?, ?, ?, ?, ?)";
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
			if ($insert_stmt = $mysqli->prepare($query)) {
				$fmtDate = DateTime::createFromFormat('d/m/Y', trim($data_consegna));
				$fmtDate = $fmtDate->format('Y-m-d');
				#*** Query parameters ***
				$params = Array('isssss', $id_cliente, $fmtDate, $stato, $note, $presa_visione, $sms);
				$tmp = array();
				foreach($params as $key => $value) $tmp[$key] = &$params[$key];
				call_user_func_array(array($insert_stmt, 'bind_param'), $tmp);
				#*** Query parameters ***
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
				
				$insert_stmt->execute();
				if($insert_stmt->errno!=0){
					$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
					$mysqli->rollback();
					$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
					$mysqli->autocommit(TRUE);
					$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
					return 0;
				} else {																		
					$id_lavorazione = $mysqli->insert_id;

					for($i=0; $i < count($articoli); $i++) {
						$id_articolo = array_keys($articoli[$i])[0];
						$quantita = array_values($articoli[$i])[0];				
						if(!empty($quantita)){
							$query = "INSERT INTO lavorazioni_articoli (id_lavorazione, id_articolo, quantita) VALUES (?, ?, ?)";
							$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
							if ($insert_stmt = $mysqli->prepare($query)) {
								#*** Query parameters ***
								$params = Array('iii', $id_lavorazione, $id_articolo, $quantita);
								$tmp = array();
								foreach($params as $key => $value) $tmp[$key] = &$params[$key];
								call_user_func_array(array($insert_stmt, 'bind_param'), $tmp);
								#*** Query parameters ***
								$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
								
								$insert_stmt->execute();
								if($insert_stmt->errno!=0){
									$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
									$mysqli->rollback();
									$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
									$mysqli->autocommit(TRUE);
									$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
									return 0;
								}
							} else {
								$mysqli->rollback();
								$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
								$mysqli->autocommit(TRUE);
								$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
								return 0;
							}
						}
					}

					for($i=0; $i < count($servizi); $i++) {
						$id_servizio = array_keys($servizi[$i])[0];
						$quantita = array_values($servizi[$i])[0];
						$prezzo_servizio = array_values($prezzi_servizi[$i])[0];				
						if(!empty($quantita)){
							$query = "INSERT INTO lavorazioni_servizi (id_lavorazione, id_servizio, quantita, prezzo_lavorazione) VALUES (?, ?, ?, ?)";
							$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
							if ($insert_stmt = $mysqli->prepare($query)) {
								#*** Query parameters ***
								$params = Array('iiid', $id_lavorazione, $id_servizio, $quantita, $prezzo_servizio);
								$tmp = array();
								foreach($params as $key => $value) $tmp[$key] = &$params[$key];
								call_user_func_array(array($insert_stmt, 'bind_param'), $tmp);
								#*** Query parameters ***
								$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
								$insert_stmt->execute();
								if($insert_stmt->errno!=0){
									$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
									$mysqli->rollback();
									$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
									$mysqli->autocommit(TRUE);
									$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
									return 0;
								}
							} else {
								$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
								$mysqli->rollback();
								$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
								$mysqli->autocommit(TRUE);
								$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
								return 0;
							}
						}
					}
				}
			} else {
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				$mysqli->rollback();
				$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
				$mysqli->autocommit(TRUE);
				$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
				return 0;
			}
		} catch (Exception $e) {
			//echo $e->getMessage();
			$mysqli->rollback();
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Exception catched (%s): Rollback transazione DB", $e->getMessage()));
			$mysqli->autocommit(TRUE);
			$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
			return 0;
		}
		$mysqli->autocommit(TRUE);
		$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
		return 1;
	}
	
	function get_order_data($id_lavorazione, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$query = "SELECT L.id_lavorazione, L.id_cliente, L.data_consegna, L.data_sms, L.data_ritiro, L.stato, L.sms, L.note_lavorazione, L.presa_visione, C.nome, C.cognome, C.indirizzo, C.telefono, C.cellulare, ";
		$query = $query . " (SELECT SUM(prezzo_lavorazione*quantita) FROM lavorazioni_servizi LS WHERE LS.id_lavorazione = L.id_lavorazione) AS tot_lavorazione, ST.descrizione AS descrizione_stato, LR.tipo_fiscale_ricevuta, LR.numero_fiscale_ricevuta, LR.data_ricevuta, LR.anno_fiscale_ricevuta FROM lavorazioni L  ";
		$query = $query . " INNER JOIN clienti C ON C.id_cliente = L.id_cliente ";
		$query = $query . " INNER JOIN stati ST ON L.stato = ST.id_stato ";
		$query = $query . " LEFT JOIN lavorazioni_ricevute LR ON LR.id_lavorazione = L.id_lavorazione ";
		$query = $query . " WHERE L.id_lavorazione = ? LIMIT 1";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) { 
			#*** Query parameters ***
			$params = Array('i', $id_lavorazione);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
								
			$stmt->execute(); 
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				$order_data_array["exists"] = "0";
				return $order_data_array;
			} else {
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($id_lavorazione, $id_cliente, $data_consegna, $data_sms, $data_ritiro, $stato, $sms, $note_lavorazione, $presa_visione, $nome, $cognome, $indirizzo, $telefono, $cellulare, $tot_lavorazione, $descrizione_stato, $tipo_fiscale_ricevuta, $numero_fiscale_ricevuta, $data_ricevuta, $anno_fiscale_ricevuta);
				$stmt->fetch();
				if($stmt->num_rows == 1) {
					$order_data_array["exists"] = "1";
					$order_data_array["id_lavorazione"] = $id_lavorazione;
					$order_data_array["id_cliente"] = $id_cliente;
					
					$fmtDate = $data_consegna;
					if(!empty($data_consegna) && ($data_consegna!='0000-00-00')){
						$fmtDate = DateTime::createFromFormat('Y-m-d', trim($data_consegna));
						$fmtDate = $fmtDate->format('d/m/Y');
						$order_data_array["data_consegna"] = $fmtDate;
					} else {
						$order_data_array["data_consegna"] = "";
					}

					$fmtDate = $data_ritiro;
					if(!empty($data_ritiro) && ($data_ritiro!='0000-00-00')){
						$fmtDate = DateTime::createFromFormat('Y-m-d', trim($data_ritiro));
						$fmtDate = $fmtDate->format('d/m/Y');
						$order_data_array["data_ritiro"] = $fmtDate;
					} else {
						$order_data_array["data_ritiro"] = "";
					}
					
					$fmtDate = $data_sms;
					if(!empty($data_sms)){
						$fmtDate = DateTime::createFromFormat('Y-m-d', trim($data_sms));
						$fmtDate = $fmtDate->format('d/m/Y');
						$order_data_array["data_sms"] = $fmtDate;
					} else {
						$order_data_array["data_sms"] = "";
					}

					$fmtDate = $data_ricevuta;
					if(!empty($data_ricevuta)){
						$fmtDate = DateTime::createFromFormat('Y-m-d H:i:s', trim($data_ricevuta));
						$fmtDate = $fmtDate->format('d/m/Y H:i:s');
						$order_data_array["data_ricevuta"] = $fmtDate;
					} else {
						$order_data_array["data_ricevuta"] = "";
					}

					$order_data_array["stato"] = $stato;
					$order_data_array["descrizione_stato"] = $descrizione_stato;
					$order_data_array["sms"] = $sms;
					$order_data_array["note_lavorazione"] = $note_lavorazione;
					$order_data_array["presa_visione"] = $presa_visione;
					$order_data_array["nome"] = $nome;
					$order_data_array["cognome"] = $cognome;
					$order_data_array["indirizzo"] = $indirizzo;
					$order_data_array["telefono"] = $telefono;
					$order_data_array["cellulare"] = $cellulare;
					$order_data_array["tot_lavorazione"] = $tot_lavorazione;
					$order_data_array["tipo_fiscale_ricevuta"] = $tipo_fiscale_ricevuta;
					$order_data_array["numero_fiscale_ricevuta"] = $numero_fiscale_ricevuta;
					$order_data_array["anno_fiscale_ricevuta"] = $anno_fiscale_ricevuta;
					return $order_data_array;
				} else {
					$order_data_array["exists"] = "0";
					return $order_data_array;
				}
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			$order_data_array["exists"] = "0";
			return $order_data_array;
		}
	}
	
	function get_order_item_data($id_lavorazione, $id_categoria, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$push = array();
		
		/*$query = "	SELECT A.id_articolo, A.descrizione, ";
		$query .= "	(SELECT quantita FROM lavorazioni_articoli LA WHERE LA.id_articolo = A.id_articolo AND LA.id_lavorazione=? LIMIT 1) AS quantita, ";
		$query .= "	COALESCE((SELECT SUM(quantita) FROM lavorazioni_ricevute_articoli LRA WHERE LRA.id_articolo = A.id_articolo AND LRA.id_lavorazione=?), 0) AS quantita_evasa ";
		$query .= "	FROM articoli A ";*/
		
		$query = "	SELECT A.id_articolo, A.descrizione, ";
		$query .= "	(SELECT quantita FROM lavorazioni_articoli LA WHERE LA.id_articolo = A.id_articolo AND LA.id_lavorazione=? LIMIT 1) AS quantita, ";
		$query .= "	COALESCE((SELECT SUM(quantita) FROM lavorazioni_ricevute_articoli LRA WHERE LRA.id_articolo = A.id_articolo AND LRA.id_lavorazione=?), 0) AS quantita_evasa, ";
		$query .= "	LRA.quantita AS quantita_evasa_ricevuta, ";
		$query .= "	LR.numero_fiscale_ricevuta AS numero_ricevuta, ";
		$query .= "	LR.data_ricevuta AS data_ricevuta ";
		$query .= "	FROM articoli A ";
		$query .= "	LEFT JOIN lavorazioni_ricevute_articoli LRA ";
		$query .= "	ON LRA.id_articolo = A.id_articolo ";
		$query .= "	AND LRA.id_lavorazione=? ";
		$query .= "	LEFT JOIN lavorazioni_ricevute LR ";
		$query .= "	ON LRA.id_ricevuta = LR.id_ricevuta ";
		$query .= "	WHERE A.id_categoria=? ";
		$query .= "	ORDER BY A.descrizione, A.id_articolo ";
		
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) { 
			#*** Query parameters ***
			$params = Array('iiii', $id_lavorazione, $id_lavorazione, $id_lavorazione, $id_categoria);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			
			$stmt->execute(); 
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return $push;
			} else {
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($id_articolo, $descrizione, $quantita, $quantita_evasa, $quantita_evasa_ricevuta, $numero_ricevuta, $data_ricevuta); 
				while($stmt->fetch()) {
					$order_item_data_array["exists"] = "1";
					$order_item_data_array["id_articolo"] = $id_articolo;
					$order_item_data_array["descrizione"] = $descrizione;
					$order_item_data_array["quantita"] = $quantita;
					$order_item_data_array["quantita_evasa"] = $quantita_evasa;
					$order_item_data_array["quantita_evasa_ricevuta"] = $quantita_evasa_ricevuta;
					$order_item_data_array["numero_ricevuta"] = $numero_ricevuta;
					
					$fmtDate = $data_ricevuta;
					if(!empty($data_ricevuta)){
						$fmtDate = DateTime::createFromFormat('Y-m-d H:i:s', trim($data_ricevuta));
						$fmtDate = $fmtDate->format('d/m/Y H:i:s');
						$order_item_data_array["data_ricevuta"] = $fmtDate;
					} else {
						$order_item_data_array["data_ricevuta"] = "";
					}

					$push[] = $order_item_data_array;
				}
				
				$query = "SELECT FOUND_ROWS()";
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
				if ($stmt = $mysqli->prepare($query)) {
					$stmt->execute(); 
					if($stmt->errno!=0){
						$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
						return $push;
					} else {					
						$stmt->store_result();
						$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
						$stmt->bind_result($record_count);
						$stmt->fetch();
						$order_item_data_array["record_count"] = $record_count;
						$push[] = $order_item_data_array;
					}
				} else {
					$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
					return $push;
				}

				return $push;
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return $push;
		}
	}

	function get_order_service_data($id_lavorazione, $id_categoria, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$push = array();
		/*$query = "SELECT S.id_servizio, S.descrizione, S.prezzo, (SELECT quantita FROM lavorazioni_servizi LS WHERE LS.id_servizio = S.id_servizio AND LS.id_lavorazione=? LIMIT 1) AS quantita, ";
		$query = $query . " (SELECT prezzo_lavorazione FROM lavorazioni_servizi LS WHERE LS.id_servizio = S.id_servizio AND LS.id_lavorazione=? LIMIT 1) AS prezzo_lavorazione FROM servizi S WHERE S.id_categoria=? ";*/

		$query = "	SELECT S.id_servizio, S.descrizione, S.prezzo, ";
		$query .= "	(SELECT quantita FROM lavorazioni_servizi LS WHERE LS.id_servizio = S.id_servizio AND LS.id_lavorazione=? LIMIT 1) AS quantita, ";
		$query .= "	(SELECT prezzo_lavorazione FROM lavorazioni_servizi LS WHERE LS.id_servizio = S.id_servizio AND LS.id_lavorazione=? LIMIT 1) AS prezzo_lavorazione, ";
		$query .= "	COALESCE((SELECT SUM(quantita) FROM lavorazioni_ricevute_servizi LRS WHERE LRS.id_servizio = S.id_servizio AND LRS.id_lavorazione=?), 0) AS quantita_evasa, ";
		$query .= "	LRS.quantita AS quantita_evasa_ricevuta, ";
		$query .= "	LR.numero_fiscale_ricevuta AS numero_ricevuta, ";
		$query .= "	LR.data_ricevuta AS data_ricevuta ";
		$query .= "	FROM servizi S ";
		$query .= "	LEFT JOIN lavorazioni_ricevute_servizi LRS ";
		$query .= "	ON LRS.id_servizio = S.id_servizio ";
		$query .= "	AND LRS.id_lavorazione=? ";
		$query .= "	LEFT JOIN lavorazioni_ricevute LR ";
		$query .= "	ON LRS.id_ricevuta = LR.id_ricevuta ";
		$query .= "	WHERE S.id_categoria=? ";
		$query .= "	ORDER BY S.descrizione, S.id_servizio ";

		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) { 
			#*** Query parameters ***
			$params = Array('iiiii', $id_lavorazione, $id_lavorazione, $id_lavorazione, $id_lavorazione, $id_categoria);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			$stmt->execute(); 
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return $push;
			} else {					
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($id_servizio, $descrizione, $prezzo, $quantita, $prezzo_lavorazione, $quantita_evasa, $quantita_evasa_ricevuta, $numero_ricevuta, $data_ricevuta); 
				while($stmt->fetch()) {
					$order_service_data_array["exists"] = "1";
					$order_service_data_array["id_servizio"] = $id_servizio;
					$order_service_data_array["descrizione"] = $descrizione;
					$order_service_data_array["quantita"] = $quantita;
					$order_service_data_array["quantita_evasa"] = $quantita_evasa;
					$order_service_data_array["prezzo"] = $prezzo;
					$order_service_data_array["prezzo_lavorazione"] = $prezzo_lavorazione;
					$order_service_data_array["quantita_evasa_ricevuta"] = $quantita_evasa_ricevuta;
					$order_service_data_array["numero_ricevuta"] = $numero_ricevuta;
					
					$fmtDate = $data_ricevuta;
					if(!empty($data_ricevuta)){
						$fmtDate = DateTime::createFromFormat('Y-m-d H:i:s', trim($data_ricevuta));
						$fmtDate = $fmtDate->format('d/m/Y H:i:s');
						$order_service_data_array["data_ricevuta"] = $fmtDate;
					} else {
						$order_service_data_array["data_ricevuta"] = "";
					}
					
					$push[] = $order_service_data_array;
				}
				
				$query = "SELECT FOUND_ROWS()";
				if ($stmt = $mysqli->prepare($query)) {
					$stmt->execute(); 
					if($stmt->errno!=0){
						$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
						return $push;
					} else {				
						$stmt->store_result();
						$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
						$stmt->bind_result($record_count);
						$stmt->fetch();
						$order_service_data_array["record_count"] = $record_count;
						$push[] = $order_service_data_array;
					}
				} else {
					$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
					return $push;
				}

				return $push;
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return $push;
		}
	}

	function get_order_receipts($id_lavorazione, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$push = array();
		$query = "	SELECT id_ricevuta, numero_fiscale_ricevuta ";
		$query .= "	FROM lavorazioni_ricevute ";
		$query .= "	WHERE id_lavorazione = ? ";
 		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) { 
			#*** Query parameters ***
			$params = Array('i', $id_lavorazione);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			$stmt->execute(); 
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return $push;
			} else {					
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($id_ricevuta, $numero_fiscale_ricevuta); 
				while($stmt->fetch()) {
					$order_receipts_array["exists"] = "1";
					$order_receipts_array["id_ricevuta"] = $id_ricevuta;
					$order_receipts_array["numero_fiscale_ricevuta"] = $numero_fiscale_ricevuta;
					
					$push[] = $order_receipts_array;
				}
				
				$query = "SELECT FOUND_ROWS()";
				if ($stmt = $mysqli->prepare($query)) {
					$stmt->execute(); 
					if($stmt->errno!=0){
						$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
						return $push;
					} else {				
						$stmt->store_result();
						$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
						$stmt->bind_result($record_count);
						$stmt->fetch();
						$order_receipts_array["record_count"] = $record_count;
						$push[] = $order_receipts_array;
					}
				} else {
					$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
					return $push;
				}

				return $push;
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return $push;
		}
	}
	
	function edit_order_data($id_lavorazione, $id_cliente, $data_consegna, $stato, $note, $presa_visione, $articoli, $servizi, $prezzi_servizi, $sms, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$mysqli->autocommit(FALSE);
		$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Avvio transazione DB");
		try {
			$query = "UPDATE lavorazioni SET id_cliente = ?, data_consegna = ?, stato = ?, note_lavorazione = ?, presa_visione = ?, sms = ? WHERE id_lavorazione = ?";
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
			if ($insert_stmt = $mysqli->prepare($query)) {
				$fmtDate_data_consegna = $data_consegna;
				if (!empty($data_consegna)) {
					$fmtDate_data_consegna = DateTime::createFromFormat('d/m/Y', trim($data_consegna));
					$fmtDate_data_consegna = $fmtDate_data_consegna->format('Y-m-d');
				}
				
				#*** Query parameters ***
				$params = Array('isssssi', $id_cliente, $fmtDate_data_consegna, $stato, $note, $presa_visione, $sms, $id_lavorazione);
				$tmp = array();
				foreach($params as $key => $value) $tmp[$key] = &$params[$key];
				call_user_func_array(array($insert_stmt, 'bind_param'), $tmp);
				#*** Query parameters ***
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
				$insert_stmt->execute();
				if($insert_stmt->errno!=0){
					$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
					$mysqli->rollback();
					$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
					$mysqli->autocommit(TRUE);
					$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
					return 0;
				} else {
					for($i=0; $i < count($articoli); $i++) {
						$id_articolo = array_keys($articoli[$i])[0];
						$quantita = array_values($articoli[$i])[0];				

						$tot_records = 0;
						$query = "SELECT COUNT(1) FROM lavorazioni_articoli WHERE id_lavorazione = ? AND id_articolo = ?";
						$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
						if ($check_stmt = $mysqli->prepare($query)) {
							#*** Query parameters ***
							$params = Array('ii', $id_lavorazione, $id_articolo);
							$tmp = array();
							foreach($params as $key => $value) $tmp[$key] = &$params[$key];
							call_user_func_array(array($check_stmt, 'bind_param'), $tmp);
							#*** Query parameters ***
							$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
							$check_stmt->execute();
							if($check_stmt->errno!=0){
								$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
								$mysqli->rollback();
								$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
								$mysqli->autocommit(TRUE);
								$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
								return 0;
							} else {
								$check_stmt->store_result();
								$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $check_stmt->num_rows));
								$check_stmt->bind_result($tot_records);
								$check_stmt->fetch();
								
								if(!empty($quantita) && $tot_records>0){
									$query = "UPDATE lavorazioni_articoli SET quantita = ? WHERE id_lavorazione = ? AND id_articolo = ?";
									$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
									if ($update_stmt = $mysqli->prepare($query)) {
										#*** Query parameters ***
										$params = Array('iii', $quantita, $id_lavorazione, $id_articolo);
										$tmp = array();
										foreach($params as $key => $value) $tmp[$key] = &$params[$key];
										call_user_func_array(array($update_stmt, 'bind_param'), $tmp);
										#*** Query parameters ***
										$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
										$update_stmt->execute();
										if($update_stmt->errno!=0){
											$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
											$mysqli->rollback();
											$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
											$mysqli->autocommit(TRUE);
											$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
											return 0;
										}									
									} else {
										$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
										$mysqli->rollback();
										$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
										$mysqli->autocommit(TRUE);
										$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
										return 0;
									}
								}

								if(!empty($quantita) && $tot_records==0){
									$query = "INSERT INTO lavorazioni_articoli (id_lavorazione, id_articolo, quantita) VALUES (?, ?, ?)";
									$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
									if ($insert_stmt = $mysqli->prepare($query)) {
										#*** Query parameters ***
										$params = Array('iii', $id_lavorazione, $id_articolo, $quantita);
										$tmp = array();
										foreach($params as $key => $value) $tmp[$key] = &$params[$key];
										call_user_func_array(array($insert_stmt, 'bind_param'), $tmp);
										#*** Query parameters ***
										$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
										$insert_stmt->execute();
										if($insert_stmt->errno!=0){
											$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
											$mysqli->rollback();
											$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
											$mysqli->autocommit(TRUE);
											$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
											return 0;
										}									
									} else {
										$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
										$mysqli->rollback();
										$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
										$mysqli->autocommit(TRUE);
										$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
										return 0;									
									}
								}

								if(empty($quantita) && $tot_records>0){
									$query = "DELETE FROM lavorazioni_articoli WHERE id_lavorazione = ? AND id_articolo = ?";
									$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
									if ($delete_stmt = $mysqli->prepare($query)) {
										#*** Query parameters ***
										$params = Array('ii', $id_lavorazione, $id_articolo);
										$tmp = array();
										foreach($params as $key => $value) $tmp[$key] = &$params[$key];
										call_user_func_array(array($delete_stmt, 'bind_param'), $tmp);
										#*** Query parameters ***
										$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
										$delete_stmt->execute();
										if($delete_stmt->errno!=0){
											$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
											$mysqli->rollback();
											$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
											$mysqli->autocommit(TRUE);
											$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
											return 0;
										}									
									} else {
										$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
										$mysqli->rollback();
										$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
										$mysqli->autocommit(TRUE);
										$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
										return 0;									
									}
								}
							}
						} else {
							$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
							$mysqli->rollback();
							$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
							$mysqli->autocommit(TRUE);
							$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
							return 0;
						}
					}

					for($i=0; $i < count($servizi); $i++) {
						$id_servizio = array_keys($servizi[$i])[0];
						$quantita = array_values($servizi[$i])[0];				
						$prezzo_servizio = array_values($prezzi_servizi[$i])[0];		
						
						$tot_records = 0;
						$query = "SELECT COUNT(1) FROM lavorazioni_servizi WHERE id_lavorazione = ? AND id_servizio = ?";
						$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
						if ($check_stmt = $mysqli->prepare($query)) {
							#*** Query parameters ***
							$params = Array('ii', $id_lavorazione, $id_servizio);
							$tmp = array();
							foreach($params as $key => $value) $tmp[$key] = &$params[$key];
							call_user_func_array(array($check_stmt, 'bind_param'), $tmp);
							#*** Query parameters ***
							$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
							$check_stmt->execute();
							if($check_stmt->errno!=0){
								$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
								$mysqli->rollback();
								$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
								$mysqli->autocommit(TRUE);
								$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
								return 0;
							} else {
								$check_stmt->store_result();
								$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $check_stmt->num_rows));
								$check_stmt->bind_result($tot_records);
								$check_stmt->fetch();

								if(!empty($quantita) && $tot_records>0){
									$query = "UPDATE lavorazioni_servizi SET quantita = ?, prezzo_lavorazione = ? WHERE id_lavorazione = ? AND id_servizio = ?";
									$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
									if ($update_stmt = $mysqli->prepare($query)) {
										#*** Query parameters ***
										$params = Array('idii', $quantita, $prezzo_servizio, $id_lavorazione, $id_servizio);
										$tmp = array();
										foreach($params as $key => $value) $tmp[$key] = &$params[$key];
										call_user_func_array(array($update_stmt, 'bind_param'), $tmp);
										#*** Query parameters ***
										$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
										$update_stmt->execute();
										if($update_stmt->errno!=0){
											$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
											$mysqli->rollback();
											$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
											$mysqli->autocommit(TRUE);
											$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
											return 0;
										}
									} else {
										$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
										$mysqli->rollback();
										$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
										$mysqli->autocommit(TRUE);
										$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
										return 0;																		
									}
								}

								if(!empty($quantita) && $tot_records==0){
									$query = "INSERT INTO lavorazioni_servizi (id_lavorazione, id_servizio, quantita, prezzo_lavorazione) VALUES (?, ?, ?, ?)";
									$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
									if ($insert_stmt = $mysqli->prepare($query)) {
										#*** Query parameters ***
										$params = Array('iiid', $id_lavorazione, $id_servizio, $quantita, $prezzo_servizio);
										$tmp = array();
										foreach($params as $key => $value) $tmp[$key] = &$params[$key];
										call_user_func_array(array($insert_stmt, 'bind_param'), $tmp);
										#*** Query parameters ***
										$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
										$insert_stmt->execute();
										if($insert_stmt->errno!=0){
											$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
											$mysqli->rollback();
											$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
											$mysqli->autocommit(TRUE);
											$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
											return 0;
										}									
									} else {
										$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
										$mysqli->rollback();
										$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
										$mysqli->autocommit(TRUE);
										$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
										return 0;																		
									}
								}

								if(empty($quantita) && $tot_records>0){
									$query = "DELETE FROM lavorazioni_servizi WHERE id_lavorazione = ? AND id_servizio = ?";
									$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
									if ($delete_stmt = $mysqli->prepare($query)) {
										#*** Query parameters ***
										$params = Array('ii', $id_lavorazione, $id_servizio);
										$tmp = array();
										foreach($params as $key => $value) $tmp[$key] = &$params[$key];
										call_user_func_array(array($delete_stmt, 'bind_param'), $tmp);
										#*** Query parameters ***
										$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
										$delete_stmt->execute();
										if($delete_stmt->errno!=0){
											$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
											$mysqli->rollback();
											$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
											$mysqli->autocommit(TRUE);
											$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
											return 0;
										}									
									} else {
										$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
										$mysqli->rollback();
										$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
										$mysqli->autocommit(TRUE);
										$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
										return 0;																											
									}
								}
							}
						} else {
							$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
							$mysqli->rollback();
							$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
							$mysqli->autocommit(TRUE);
							$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
							return 0;						
						}
					}
				}				
			} else {
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				$mysqli->rollback();
				$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
				$mysqli->autocommit(TRUE);
				$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
				return 0;
			}
		} catch (Exception $e) {
			//echo $e->getMessage();
			$mysqli->rollback();
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Exception catched (%s): Rollback transazione DB", $e->getMessage()));
			$mysqli->autocommit(TRUE);
			$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
		}
		$mysqli->autocommit(TRUE);
		$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
		return 1;
	}

	function delete_order_data($id_lavorazione, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$query = "UPDATE lavorazioni SET stato = 'D' WHERE id_lavorazione = ?";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($delete_stmt = $mysqli->prepare($query)) {
			#*** Query parameters ***
			$params = Array('i', $id_lavorazione);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($delete_stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			
			$delete_stmt->execute();
			if($delete_stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return 0;
			} else {
				return 1;
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return 0;
		}
	}
	
	function bill_order($id_lavorazione, $tipo_fiscale_ricevuta, $numero_fiscale_ricevuta, $anno_fiscale_ricevuta, $articoli, $servizi, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		if (!empty($numero_fiscale_ricevuta) && !empty($anno_fiscale_ricevuta)) {
			$mysqli->autocommit(FALSE);
			$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Avvio transazione DB");
			$query = "INSERT INTO lavorazioni_ricevute (id_lavorazione, data_ricevuta, numero_fiscale_ricevuta, tipo_fiscale_ricevuta, anno_fiscale_ricevuta) VALUES (?, ?, ?, ?, ?)";
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
			if ($bill_stmt = $mysqli->prepare($query)) {
				
				$fmtDate = DateTime::createFromFormat('d/m/Y H:i:s', date("d/m/Y H:i:s"));
				$fmtDate = $fmtDate->format('Y-m-d H:i:s');
				
				#*** Query parameters ***
				$params = Array('isisi', $id_lavorazione, $fmtDate, $numero_fiscale_ricevuta, $tipo_fiscale_ricevuta, $anno_fiscale_ricevuta);
				$tmp = array();
				foreach($params as $key => $value) $tmp[$key] = &$params[$key];
				call_user_func_array(array($bill_stmt, 'bind_param'), $tmp);
				#*** Query parameters ***
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			
				$bill_stmt->execute();
				if($bill_stmt->errno!=0){
					$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
					$mysqli->rollback();
					$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
					$mysqli->autocommit(TRUE);
					$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
					return -1;
				} else {
					$id_ricevuta = $mysqli->insert_id;
					for($i=0; $i < count($articoli); $i++) {
						$id_articolo = array_keys($articoli[$i])[0];
						$quantita = array_values($articoli[$i])[0];				

						if ($quantita > 0) {
							$query = "INSERT INTO lavorazioni_ricevute_articoli (id_ricevuta, id_lavorazione, id_articolo, quantita) VALUES (?, ?, ?, ?)";
							$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
							if ($articoli_stmt = $mysqli->prepare($query)) {
								#*** Query parameters ***
								$params = Array('iiii', $id_ricevuta, $id_lavorazione, $id_articolo, $quantita);
								$tmp = array();
								foreach($params as $key => $value) $tmp[$key] = &$params[$key];
								call_user_func_array(array($articoli_stmt, 'bind_param'), $tmp);
								#*** Query parameters ***
								$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
								$articoli_stmt->execute();
								if($articoli_stmt->errno!=0){
									$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
									$mysqli->rollback();
									$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
									$mysqli->autocommit(TRUE);
									$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
									return -2;
								}
							} else {
								$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
								$mysqli->rollback();
								$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
								$mysqli->autocommit(TRUE);
								$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
								return -2;
							}
						}
					}

					for($i=0; $i < count($servizi); $i++) {
						$id_servizio = array_keys($servizi[$i])[0];
						$quantita = array_values($servizi[$i])[0];				

						if ($quantita > 0) {
							$query = "INSERT INTO lavorazioni_ricevute_servizi (id_ricevuta, id_lavorazione, id_servizio, quantita) VALUES (?, ?, ?, ?)";
							$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
							if ($servizi_stmt = $mysqli->prepare($query)) {
								#*** Query parameters ***
								$params = Array('iiii', $id_ricevuta, $id_lavorazione, $id_servizio, $quantita);
								$tmp = array();
								foreach($params as $key => $value) $tmp[$key] = &$params[$key];
								call_user_func_array(array($servizi_stmt, 'bind_param'), $tmp);
								#*** Query parameters ***
								$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
								$servizi_stmt->execute();
								if($servizi_stmt->errno!=0){
									$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
									$mysqli->rollback();
									$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
									$mysqli->autocommit(TRUE);
									$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
									return -2;
								}
							} else {
								$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
								$mysqli->rollback();
								$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
								$mysqli->autocommit(TRUE);
								$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
								return -2;
							}
						}
					}

					$mysqli->autocommit(TRUE);
					$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
					return $id_ricevuta;
				}
			} else {
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				$mysqli->rollback();
				$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
				$mysqli->autocommit(TRUE);
				$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
				return -2;
			}
		} else {
			$GLOBALS['log']->LogError(eval($GLOBALS['logPrefixString']) . "Error: parametri funzione bill_order mancanti!");
			return -2;
		}
	}
	
	function get_order_next_status($document_type, $last_status, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$push = array();
		$query = "SELECT id_stato, descrizione FROM stati WHERE tipo_documento = ? AND ultimo_stato_1 = ? LIMIT 1 ";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) { 
			#*** Query parameters ***
			$params = Array('ss', $document_type, $last_status);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			$stmt->execute();
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return $push;
			} else {
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($id_stato, $descrizione);
				while($stmt->fetch()) {
					$next_status_data_array["exists"] = "1";
					$next_status_data_array["id_stato"] = $id_stato;
					$next_status_data_array["descrizione"] = $descrizione;
					$push[] = $next_status_data_array;
				}

				return $push;
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return $push;
		}
	}

	function get_order_status_list($document_type, $show_change, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$push = array();
		$query = "SELECT id_stato, descrizione FROM stati WHERE tipo_documento = ? ";
		$query = $query . " AND ((show_change = '1' AND ? = '1') OR (? = '0')) ORDER BY ordine_stato ";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) { 
			#*** Query parameters ***
			$params = Array('sss', $document_type, $show_change, $show_change);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			$stmt->execute();
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return $push;
			} else {
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($id_stato, $descrizione);
				while($stmt->fetch()) {
					$order_status_list_array["exists"] = "1";
					$order_status_list_array["id_stato"] = $id_stato;
					$order_status_list_array["descrizione"] = $descrizione;
					$push[] = $order_status_list_array;
				}
				
				$query = "SELECT FOUND_ROWS()";
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
				if ($stmt = $mysqli->prepare($query)) {
					$stmt->execute(); 
					if($stmt->errno!=0){
						$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
						return $push;
					} else {
						$stmt->store_result();
						$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
						$stmt->bind_result($record_count);
						$stmt->fetch();
						$order_status_list_array["record_count"] = $record_count;
						$push[] = $order_status_list_array;
					}
				} else {
					$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
					return $push;					
				}
				
				return $push;
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return $push;
		}		
	}
	
	function get_order_first_status($document_type, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$push = array();
		$query = "SELECT id_stato, descrizione FROM stati WHERE tipo_documento = ? AND ultimo_stato_1 = '@N' ";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) { 
			#*** Query parameters ***
			$params = Array('s', $document_type);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			$stmt->execute();
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return $push;
			} else {
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($id_stato, $descrizione);
				while($stmt->fetch()) {
					$order_status_list_array["exists"] = "1";
					$order_status_list_array["id_stato"] = $id_stato;
					$order_status_list_array["descrizione"] = $descrizione;
					$push[] = $order_status_list_array;
				}
				
				$query = "SELECT FOUND_ROWS()";
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
				if ($stmt = $mysqli->prepare($query)) {
					$stmt->execute(); 
					if($stmt->errno!=0){
						$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
						return $push;
					} else {
						$stmt->store_result();
						$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
						$stmt->bind_result($record_count);
						$stmt->fetch();
						$order_status_list_array["record_count"] = $record_count;
						$push[] = $order_status_list_array;
					}
				} else {
					$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
					return $push;
				}

				return $push;
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return $push;
		}		
	}
	
	function get_order_status_data($document_type, $status, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$push = array();
		$query = "SELECT id_stato, descrizione FROM stati WHERE tipo_documento = ? AND id_stato = ? ";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) { 
			#*** Query parameters ***
			$params = Array('ss', $document_type, $status);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			$stmt->execute();
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return $push;
			} else {
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($id_stato, $descrizione);
				while($stmt->fetch()) {
					$order_status_data_array["exists"] = "1";
					$order_status_data_array["id_stato"] = $id_stato;
					$order_status_data_array["descrizione"] = $descrizione;
					$push[] = $order_status_data_array;
				}
				
				$query = "SELECT FOUND_ROWS()";
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
				if ($stmt = $mysqli->prepare($query)) {
					$stmt->execute(); 
					if($stmt->errno!=0){
						$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
						return $push;
					} else {
						$stmt->store_result();
						$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
						$stmt->bind_result($record_count);
						$stmt->fetch();
						$order_status_data_array["record_count"] = $record_count;
						$push[] = $order_status_data_array;
					}
				} else {
					$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
					return $push;
				}
			}
			
			return $push;
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return $push;
		}		
	}
	
	function get_order_xml_for_print($id_lavorazione, $mysqli, $App_BaseServerPath, $id_ricevuta) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		if (!empty($id_ricevuta)) {
			$query = 			"SELECT		L.id_lavorazione, L.data_consegna, L.note_lavorazione, L.presa_visione, C.nome, C.cognome, C.telefono, C.cellulare, C.indirizzo, ";
			$query = $query . 	"			(SELECT SUM(prezzo_lavorazione*quantita) FROM lavorazioni_servizi LS WHERE LS.id_lavorazione = L.id_lavorazione) AS tot_lavorazione, ";
			$query = $query . 	"			LR.data_ricevuta, LR.anno_fiscale_ricevuta, LR.numero_fiscale_ricevuta ";
			$query = $query . 	"FROM 		lavorazioni L ";
			$query = $query . 	"			INNER JOIN clienti C ON L.id_cliente = C.id_cliente ";
			$query = $query . 	"			LEFT JOIN lavorazioni_ricevute LR ON L.id_lavorazione = LR.id_lavorazione ";
			$query = $query . 	"WHERE		L.id_lavorazione = ? ";
			$query = $query . 	"AND		LR.id_ricevuta = ? ";
		} else {
			$query = 			"SELECT		L.id_lavorazione, L.data_consegna, L.note_lavorazione, L.presa_visione, C.nome, C.cognome, C.telefono, C.cellulare, C.indirizzo, ";
			$query = $query . 	"			(SELECT SUM(prezzo_lavorazione*quantita) FROM lavorazioni_servizi LS WHERE LS.id_lavorazione = L.id_lavorazione) AS tot_lavorazione, ";
			$query = $query . 	"			'1900-01-01 00:00:00' AS data_ricevuta, 0 AS anno_fiscale_ricevuta, 0 AS numero_fiscale_ricevuta ";
			$query = $query . 	"FROM 		lavorazioni L ";
			$query = $query . 	"			INNER JOIN clienti C ON L.id_cliente = C.id_cliente ";
			$query = $query . 	"WHERE		L.id_lavorazione = ? ";
		}
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) { 
			#*** Query parameters ***
			if (!empty($id_ricevuta)) {
				$params = Array('ii', $id_lavorazione, $id_ricevuta);
			} else {
				$params = Array('i', $id_lavorazione);
			}
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			$stmt->execute();
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return "-1";
			} else {
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($id_lavorazione, $data_consegna, $note_lavorazione, $presa_visione, $nome, $cognome, $telefono, $cellulare, $indirizzo, $tot_lavorazione, $data_ricevuta, $anno_fiscale_ricevuta, $numero_fiscale_ricevuta);
				$xml_string = "<?xml version=\"1.0\"?><order>";
				while($stmt->fetch()) {
					$fmtDate = DateTime::createFromFormat('Y-m-d', trim($data_consegna));
					$fmtDate = $fmtDate->format('d/m/Y');
					$data_consegna = $fmtDate;
					
					if (!empty(trim($data_ricevuta))) {
						$fmtDate = DateTime::createFromFormat('Y-m-d H:i:s', trim($data_ricevuta));
						$fmtDate = $fmtDate->format('d/m/Y H:i:s');
						$data_ricevuta = $fmtDate;
					}
					
					$xml_string = $xml_string . "<order_id>" . $id_lavorazione . "</order_id>";
					$xml_string = $xml_string . "<data_consegna>" . $data_consegna . "</data_consegna>";
					$xml_string = $xml_string . "<data_ricevuta>" . $data_ricevuta . "</data_ricevuta>";
					$xml_string = $xml_string . "<anno_fiscale>" . $anno_fiscale_ricevuta . "</anno_fiscale>";
					$xml_string = $xml_string . "<numero_fiscale>" . $numero_fiscale_ricevuta . "</numero_fiscale>";
					$xml_string = $xml_string . "<note_lavorazione>" . html_entity_decode(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $note_lavorazione)) . "</note_lavorazione>";
					//$xml_string = $xml_string . "<presa_visione><![CDATA[" . htmlentities(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $presa_visione)) . "]]></presa_visione>";
					$xml_string = $xml_string . "<nome>" . htmlentities(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nome)) . "</nome>";
					$xml_string = $xml_string . "<cognome>" . htmlentities(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $cognome)) . "</cognome>";
					$xml_string = $xml_string . "<indirizzo>" . htmlentities(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $indirizzo)) . "</indirizzo>";
					$xml_string = $xml_string . "<telefono>" . htmlentities(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $telefono)) . "</telefono>";
					$xml_string = $xml_string . "<cellulare>" . htmlentities(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $cellulare)) . "</cellulare>";
					/*$xml_string = $xml_string . "<tot_lavorazione>" . $tot_lavorazione . "</tot_lavorazione>";*/
					if (!empty($id_ricevuta)) {
						$query_articoli = 						"SELECT		A.descrizione, LRA.quantita ";
						$query_articoli = $query_articoli . 	"FROM 		lavorazioni_articoli LA ";
						$query_articoli = $query_articoli . 	"			INNER JOIN articoli A ON LA.id_articolo = A.id_articolo ";
						$query_articoli = $query_articoli . 	"			INNER JOIN lavorazioni_ricevute_articoli LRA ON LA.id_articolo = LRA.id_articolo AND LRA.id_lavorazione = LA.id_lavorazione ";
						$query_articoli = $query_articoli . 	"WHERE		LA.id_lavorazione = ? ";
						$query_articoli = $query_articoli . 	"AND		LRA.id_ricevuta = ? ";
					} else {
						$query_articoli = 						"SELECT		A.descrizione, LA.quantita ";
						$query_articoli = $query_articoli . 	"FROM 		lavorazioni_articoli LA ";
						$query_articoli = $query_articoli . 	"			INNER JOIN articoli A ON LA.id_articolo = A.id_articolo ";
						$query_articoli = $query_articoli . 	"WHERE		LA.id_lavorazione = ? ";
					}
					$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query_articoli));
					if ($stmt_articoli = $mysqli->prepare($query_articoli)) { 
						#*** Query parameters ***
						if (!empty($id_ricevuta)) {
							$params = Array('ii', $id_lavorazione, $id_ricevuta);
						} else {
							$params = Array('i', $id_lavorazione);
						}
						$tmp = array();
						foreach($params as $key => $value) $tmp[$key] = &$params[$key];
						call_user_func_array(array($stmt_articoli, 'bind_param'), $tmp);
						#*** Query parameters ***
						$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
						$stmt_articoli->execute();
						if($stmt_articoli->errno!=0){
							$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
							return "-1";
						} else {
							$stmt_articoli->store_result();
							$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt_articoli->num_rows));
							$stmt_articoli->bind_result($articoli_descrizione, $articoli_quantita);
							$xml_string = $xml_string . "<articoli>";
							while($stmt_articoli->fetch()) {
								$xml_string = $xml_string . "<articolo>";
								$xml_string = $xml_string . "<articolo_descrizione>" . htmlentities(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $articoli_descrizione)) . "</articolo_descrizione>";
								$xml_string = $xml_string . "<articolo_quantita>" . $articoli_quantita . "</articolo_quantita>";
								$xml_string = $xml_string . "</articolo>";
							}
							$xml_string = $xml_string . "</articoli>";
						}
					} else {
						$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
						return "-1";						
					}

					if (!empty($id_ricevuta)) {
						$query_servizi = 					"SELECT		S.descrizione, LS.prezzo_lavorazione, LRS.quantita ";
						$query_servizi = $query_servizi . 	"FROM 		lavorazioni_servizi LS ";
						$query_servizi = $query_servizi . 	"			INNER JOIN servizi S ON LS.id_servizio = S.id_servizio ";
						$query_servizi = $query_servizi . 	"			INNER JOIN lavorazioni_ricevute_servizi LRS ON LS.id_servizio = LRS.id_servizio AND LRS.id_lavorazione = LS.id_lavorazione ";
						$query_servizi = $query_servizi . 	"WHERE		LS.id_lavorazione = ? ";
						$query_servizi = $query_servizi . 	"AND		LRS.id_ricevuta = ? ";
					} else {
						$query_servizi = 					"SELECT		S.descrizione, LS.prezzo_lavorazione, LS.quantita ";
						$query_servizi = $query_servizi . 	"FROM 		lavorazioni_servizi LS ";
						$query_servizi = $query_servizi . 	"			INNER JOIN servizi S ON LS.id_servizio = S.id_servizio ";
						$query_servizi = $query_servizi . 	"WHERE		LS.id_lavorazione = ? ";
					}
					$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query_servizi));
					if ($stmt_servizi = $mysqli->prepare($query_servizi)) { 
						#*** Query parameters ***
						if (!empty($id_ricevuta)) {
							$params = Array('ii', $id_lavorazione, $id_ricevuta);
						} else {
							$params = Array('i', $id_lavorazione);
						}
						$tmp = array();
						foreach($params as $key => $value) $tmp[$key] = &$params[$key];
						call_user_func_array(array($stmt_servizi, 'bind_param'), $tmp);
						#*** Query parameters ***
						$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
						$stmt_servizi->execute();
						if($stmt_servizi->errno!=0){
							$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
							return "-1";
						} else {
							$stmt_servizi->store_result();
							$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt_servizi->num_rows));
							$stmt_servizi->bind_result($servizi_descrizione, $servizi_prezzo, $servizi_quantita);
							$totale_ricevuta = 0;
							$xml_string = $xml_string . "<servizi>";
							while($stmt_servizi->fetch()) {
								$xml_string = $xml_string . "<servizio>";
								$xml_string = $xml_string . "<servizio_descrizione>" . htmlentities(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $servizi_descrizione)) . "</servizio_descrizione>";
								$xml_string = $xml_string . "<servizio_prezzo>" . $servizi_prezzo . "</servizio_prezzo>";
								$xml_string = $xml_string . "<servizio_quantita>" . $servizi_quantita . "</servizio_quantita>";
								$xml_string = $xml_string . "</servizio>";
								$totale_ricevuta += $servizi_prezzo * $servizi_quantita;
							}
							$xml_string = $xml_string . "</servizi>";
						}
					} else {
						$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
						return "-1";						
					}
				}
				$xml_string = $xml_string . "<tot_lavorazione>" . $GLOBALS['curfmt']->formatCurrency($totale_ricevuta, "EUR") . "</tot_lavorazione>";
				$xml_string = $xml_string . "</order>";
				//$file = $App_BaseServerPath . '\print_output\print_' . uniqid() . '.xml';
				$file_name = 'print_' . uniqid();
				$file_extension = '.xml';
				file_put_contents($App_BaseServerPath . '/print_output/' . $file_name . $file_extension, $xml_string);
				return $file_name;
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return "-1";
		}
		
	}

	function get_receipt_list($data_ricevuta_dal, $data_ricevuta_dal_searchop, $data_ricevuta_al, $data_ricevuta_al_searchop, $search_page, $per_page, $sort_index, $sort_dir, $mysqli) {   
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));

		$param = array();
		$type = array();
		$params = array();

		$push = array();
		$where_added = false;
		$boolean_op = ' and ';
		$query = "SELECT SQL_CALC_FOUND_ROWS DATE(LR.data_ricevuta) AS data_ricevuta, MIN(LR.numero_fiscale_ricevuta) AS da_ricevuta_n, MAX(LR.numero_fiscale_ricevuta) AS a_ricevuta_n, ";
		$query = $query . "SUM(LS.prezzo_lavorazione*LS.quantita) AS totale_importo ";
		$query = $query . "FROM lavorazioni_ricevute LR ";
		$query = $query . "INNER JOIN lavorazioni L ";
		$query = $query . "ON LR.id_lavorazione = L.id_lavorazione ";
		$query = $query . "LEFT JOIN lavorazioni_servizi LS ";
		$query = $query . "ON L.id_lavorazione = LS.id_lavorazione ";
		if(!empty($data_ricevuta_dal)){
			if (!$where_added) {
				$query = $query . 'where ';
				$where_added = true;
			}
			$fmtDate = DateTime::createFromFormat('d/m/Y', trim($data_ricevuta_dal));
			$fmtDate = $fmtDate->format('Y-m-d');
			$query = $query . "DATE(data_ricevuta) " . getWhereCondition($data_ricevuta_dal_searchop, $fmtDate, 't', $param, $type) . $boolean_op;
		}
		if(!empty($data_ricevuta_al)){
			if (!$where_added) {
				$query = $query . 'where ';
				$where_added = true;
			}
			$fmtDate = DateTime::createFromFormat('d/m/Y', trim($data_ricevuta_al));
			$fmtDate = $fmtDate->format('Y-m-d');
			$query = $query . "DATE(data_ricevuta) " . getWhereCondition($data_ricevuta_al_searchop, $fmtDate, 't', $param, $type) . $boolean_op;
		}
		$query = $where_added?substr($query, 0, strlen($query)-strlen($boolean_op)):$query;
		$query = $query . "GROUP BY DATE(data_ricevuta) ";
		$query = $query . " ORDER BY " . $sort_index . " " . $sort_dir;
		$query = $query . " LIMIT " . $per_page . " OFFSET " . strval(($per_page * $search_page)-$per_page) . " ";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) {
			if (count($param)>0) {
				$params[] = implode("", $type);
				$params = array_merge($params, $param);
				call_user_func_array(array(&$stmt, 'bind_param'), refValues($params));
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($params)));
			}
			$stmt->execute(); 
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return $push;
			} else {
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($data_ricevuta, $da_ricevuta_n, $a_ricevuta_n, $totale_importo);

				while($stmt->fetch()) {
					$row["data_ricevuta"] = $data_ricevuta;
					$row["da_ricevuta_n"] = $da_ricevuta_n;
					$row["a_ricevuta_n"] = $a_ricevuta_n;
					$row["importo_totale"] = $totale_importo;
					$row["record_count"] = mysqli_stmt_num_rows($stmt);
					$push[] = $row;
				}
				
				$query = "SELECT FOUND_ROWS()";
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
				if ($stmt = $mysqli->prepare($query)) {
					$stmt->execute(); 
					if($stmt->errno!=0){
						$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
						return $push;
					} else {					
						$stmt->store_result();
						$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
						$stmt->bind_result($record_count);
						$stmt->fetch();
						$row["record_count"] = $record_count;
						$push[] = $row;
					}
				} else {
					$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
					return $push;					
				}
			}
			
			return $push;
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return $push;
		}
	}
	
	function get_receipt_list_grand_total($data_ricevuta_dal, $data_ricevuta_dal_searchop, $data_ricevuta_al, $data_ricevuta_al_searchop, $mysqli) {   
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));

		$param = array();
		$type = array();
		$params = array();

		$where_added = false;
		$boolean_op = ' and ';
		$query = "SELECT SQL_CALC_FOUND_ROWS ";
		$query = $query . "SUM(LS.prezzo_lavorazione*LS.quantita) AS totale_importo ";
		$query = $query . "FROM lavorazioni_ricevute LR ";
		$query = $query . "INNER JOIN lavorazioni L ";
		$query = $query . "ON LR.id_lavorazione = L.id_lavorazione ";
		$query = $query . "LEFT JOIN lavorazioni_servizi LS ";
		$query = $query . "ON L.id_lavorazione = LS.id_lavorazione ";
		if(!empty($data_ricevuta_dal)){
			if (!$where_added) {
				$query = $query . 'where ';
				$where_added = true;
			}
			$fmtDate = DateTime::createFromFormat('d/m/Y', trim($data_ricevuta_dal));
			$fmtDate = $fmtDate->format('Y-m-d');
			$query = $query . "DATE(data_ricevuta) " . getWhereCondition($data_ricevuta_dal_searchop, $fmtDate, 't', $param, $type) . $boolean_op;
		}
		if(!empty($data_ricevuta_al)){
			if (!$where_added) {
				$query = $query . 'where ';
				$where_added = true;
			}
			$fmtDate = DateTime::createFromFormat('d/m/Y', trim($data_ricevuta_al));
			$fmtDate = $fmtDate->format('Y-m-d');
			$query = $query . "DATE(data_ricevuta) " . getWhereCondition($data_ricevuta_al_searchop, $fmtDate, 't', $param, $type) . $boolean_op;
		}
		$query = $where_added?substr($query, 0, strlen($query)-strlen($boolean_op)):$query;
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) {
			if (count($param)>0) {
				$params[] = implode("", $type);
				$params = array_merge($params, $param);
				call_user_func_array(array(&$stmt, 'bind_param'), refValues($params));
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($params)));
			}
			$stmt->execute(); 
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return -1;
			} else {
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($totale_importo);
				$stmt->fetch();
			}
			
			return $totale_importo;
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return -1;
		}
	}
	
	function has_order_open_items($id_lavorazione, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$query = "	SELECT LA.id_lavorazione,LA.id_articolo,LA.quantita,SUM(COALESCE(LRA.quantita,0)) AS quantita_evasa ";
		$query .= "	FROM lavorazioni_articoli LA ";
		$query .= "	LEFT JOIN lavorazioni_ricevute_articoli LRA ";
		$query .= "	ON LA.id_lavorazione = LRA.id_lavorazione ";
		$query .= "	AND LA.id_articolo = LRA.id_articolo ";
		$query .= "	WHERE LA.id_lavorazione = ? ";
		$query .= "	GROUP BY LA.id_lavorazione,LA.id_articolo,LA.quantita ";
		$query .= "	HAVING LA.quantita>SUM(COALESCE(LRA.quantita,0)) ";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) { 
			#*** Query parameters ***
			$params = Array('i', $id_lavorazione);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			$stmt->execute();
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return -1;
			} else {
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($id_lavorazione,$id_servizio,$quantita,$quantita_evasa);
				$stmt->fetch();

				$query = "SELECT FOUND_ROWS()";
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
				if ($stmt = $mysqli->prepare($query)) {
					$stmt->execute(); 
					if($stmt->errno!=0){
						$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
						return -1;
					} else {
						$stmt->store_result();
						$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
						$stmt->bind_result($record_count);
						$stmt->fetch();
						if ($record_count>0) {
							return 1;
						} else {
							return 0;
						}
					}
				} else {
					$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
					return -1;
				}
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return -1;
		}		
	}	

	function has_order_open_services($id_lavorazione, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$query = "	SELECT LS.id_lavorazione,LS.id_servizio,LS.quantita,SUM(COALESCE(LRS.quantita,0)) AS quantita_evasa ";
		$query .= "	FROM lavorazioni_servizi LS ";
		$query .= "	LEFT JOIN lavorazioni_ricevute_servizi LRS ";
		$query .= "	ON LS.id_lavorazione = LRS.id_lavorazione ";
		$query .= "	AND LS.id_servizio = LRS.id_servizio ";
		$query .= "	WHERE LS.id_lavorazione = ? ";
		$query .= "	GROUP BY LS.id_lavorazione,LS.id_servizio,LS.quantita ";
		$query .= "	HAVING LS.quantita>SUM(COALESCE(LRS.quantita,0)) ";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) { 
			#*** Query parameters ***
			$params = Array('i', $id_lavorazione);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			$stmt->execute();
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return -1;
			} else {
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($id_lavorazione,$id_servizio,$quantita,$quantita_evasa);
				$stmt->fetch();
				
				$query = "SELECT FOUND_ROWS()";
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
				if ($stmt = $mysqli->prepare($query)) {
					$stmt->execute(); 
					if($stmt->errno!=0){
						$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
						return -1;
					} else {
						$stmt->store_result();
						$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
						$stmt->bind_result($record_count);
						$stmt->fetch();
						if ($record_count>0) {
							return 1;
						} else {
							return 0;
						}
					}
				} else {
					$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
					return -1;					
				}
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return -1;
		}		
	}	
	//################################################################################################
	//FINE GESTIONE ORDINI
	//################################################################################################
	
	//################################################################################################
	//GESTIONE NUMERATORI
	//################################################################################################
	function get_nextnumber_list($tipo_fiscale, $tipo_fiscale_searchop, $anno, $anno_searchop, $search_page, $per_page, $sort_index, $sort_dir, $mysqli) {   
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));

		$param = array();
		$type = array();
		$params = array();

		$push = array();
		$where_added = false;
		$boolean_op = ' and ';
		$query = "SELECT SQL_CALC_FOUND_ROWS tipo_fiscale, numero_fiscale, prefisso, suffisso, anno FROM numeratori  ";
		
		if(!empty($tipo_fiscale)){
			if (!$where_added) {
				$query = $query . 'where ';
				$where_added = true;
			}
			$query = $query . "tipo_fiscale " . getWhereCondition($tipo_fiscale_searchop, $tipo_fiscale, 't', $param, $type) . $boolean_op;
		}
		if(!empty($anno)){
			if (!$where_added) {
				$query = $query . 'where ';
				$where_added = true;
			}
			$query = $query . "anno " . getWhereCondition($anno_searchop, $descrizione, 'n', $param, $type) . $boolean_op;
		}
		$query = $where_added?substr($query, 0, strlen($query)-strlen($boolean_op)):$query;
		$query = $query . " ORDER BY " . $sort_index . " " . $sort_dir;
		$query = $query . " LIMIT " . $per_page . " OFFSET " . strval(($per_page * $search_page)-$per_page) . " ";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) {
			if (count($param)>0) {
				$params[] = implode("", $type);
				$params = array_merge($params, $param);
				call_user_func_array(array(&$stmt, 'bind_param'), refValues($params));
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($params)));
			}
			$stmt->execute(); 
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return $push;
			} else {
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($tipo_fiscale, $numero_fiscale, $prefisso, $suffisso, $anno);
				while($stmt->fetch()) {
					$row["tipo_fiscale"] = $tipo_fiscale;
					$row["numero_fiscale"] = $numero_fiscale;
					$row["prefisso"] = $prefisso;
					$row["suffisso"] = $suffisso;
					$row["anno"] = $anno;
					$row["record_count"] = mysqli_stmt_num_rows($stmt);
					$push[] = $row;
				}
				
				$query = "SELECT FOUND_ROWS()";
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
				if ($stmt = $mysqli->prepare($query)) {
					$stmt->execute(); 
					if($stmt->errno!=0){
						$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
						return $push;
					} else {
						$stmt->store_result();
						$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
						$stmt->bind_result($record_count);
						$stmt->fetch();
						$row["record_count"] = $record_count;
						$push[] = $row;
					}
				} else {
					$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
					return $push;
				}
			}

			return $push;
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return $push;
		}
	}
	
	function get_nextnumber_data($tipo_fiscale, $anno, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$query = "SELECT tipo_fiscale, prefisso, numero_fiscale, suffisso, anno FROM numeratori WHERE tipo_fiscale = ? AND anno = ? LIMIT 1";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) { 
			#*** Query parameters ***
			$params = Array('si', $tipo_fiscale, $anno);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			$stmt->execute(); 
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return $push;
			} else {
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($tipo_fiscale, $prefisso, $numero_fiscale, $suffisso, $anno); 
				$stmt->fetch();
				if($stmt->num_rows == 1) { 
					$item_data_array["exists"] = "1";
					$item_data_array["tipo_fiscale"] = $tipo_fiscale;
					$item_data_array["prefisso"] = $prefisso;
					$item_data_array["numero_fiscale"] = $numero_fiscale;
					$item_data_array["suffisso"] = $suffisso;
					$item_data_array["anno"] = $anno;
					return $item_data_array;
				} else {
					$item_data_array["exists"] = "0";
					return $item_data_array;
				}
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			$item_data_array["exists"] = "0";
			return $item_data_array;
		}
	}

	function edit_nextnumber_data($tipo_fiscale, $prefisso, $numero_fiscale, $suffisso, $anno, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$query = "UPDATE numeratori SET prefisso = ?, numero_fiscale = ?, suffisso = ? WHERE tipo_fiscale = ? AND anno = ? ";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($update_stmt = $mysqli->prepare($query)) {    
			#*** Query parameters ***
			$params = Array('sissi', $prefisso, $numero_fiscale, $suffisso, $tipo_fiscale, $anno);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($update_stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			
			$update_stmt->execute();
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return -1;
			} else {
				return 1;
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return 0;
		}
	}
	
	function delete_nextnumber_data($tipo_fiscale, $anno, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$query = "DELETE FROM numeratori WHERE tipo_fiscale = ? AND anno = ? ";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($delete_stmt = $mysqli->prepare($query)) {
			#*** Query parameters ***
			$params = Array('si', $tipo_fiscale, $anno);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($delete_stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			
			$delete_stmt->execute();
			if($delete_stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return 0;
			} else {
				return 1;
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return 0;
		}
	}
	
	function add_nextnumber_data($tipo_fiscale, $prefisso, $numero_fiscale, $suffisso, $anno, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$query = "INSERT INTO numeratori (tipo_fiscale, prefisso, numero_fiscale, suffisso, anno) VALUES (?, ?, ?, ?, ?)";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($insert_stmt = $mysqli->prepare($query)) {
			#*** Query parameters ***
			$params = Array('ssisi', $tipo_fiscale, $prefisso, $numero_fiscale, $suffisso, $anno);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($insert_stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			
			$insert_stmt->execute();
			if($insert_stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return -1;
			} else {
				return 1;
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return 0;
		}
	}
	//################################################################################################
	//FINE GESTIONE NUMERATORI
	//################################################################################################

	//################################################################################################
	//GESTIONE STATI ORDINI/DOCUMENTI
	//################################################################################################

	function get_status_list($id_stato, $id_stato_searchop, $descrizione, $descrizione_searchop, $tipo_documento, $tipo_documento_searchop, $search_page, $per_page, $sort_index, $sort_dir, $mysqli) {   
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));

		$param = array();
		$type = array();
		$params = array();

		$push = array();
		$where_added = false;
		$boolean_op = ' and ';
		$query = "SELECT SQL_CALC_FOUND_ROWS id_stato, descrizione, tipo_documento, ordine_stato, ultimo_stato_1, show_change, print_fiscal_receipt, invio_sms, row_color FROM stati ";
		if(!empty($id_stato)){
			if (!$where_added) {
				$query = $query . 'where ';
				$where_added = true;
			}
			$query = $query . "id_stato " . getWhereCondition($id_stato_searchop, $id_stato, 't', $param, $type) . $boolean_op;
		}
		if(!empty($descrizione)){
			if (!$where_added) {
				$query = $query . 'where ';
				$where_added = true;
			}
			$query = $query . "descrizione " . getWhereCondition($descrizione_searchop, $descrizione, 't', $param, $type) . $boolean_op;
		}
		if(!empty($tipo_documento)){
			if (!$where_added) {
				$query = $query . 'where ';
				$where_added = true;
			}
			$query = $query . "tipo_documento " . getWhereCondition($tipo_documento_searchop, $tipo_documento, 't', $param, $type) . $boolean_op;
		}
		$query = $where_added?substr($query, 0, strlen($query)-strlen($boolean_op)):$query;
		$query = $query . " ORDER BY " . $sort_index . " " . $sort_dir . ",tipo_documento,ordine_stato ";
		$query = $query . " LIMIT " . $per_page . " OFFSET " . strval(($per_page * $search_page)-$per_page) . " ";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) {
			if (count($param)>0) {
				$params[] = implode("", $type);
				$params = array_merge($params, $param);
				call_user_func_array(array(&$stmt, 'bind_param'), refValues($params));
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($params)));
			}
			$stmt->execute(); 
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return $push;
			} else {
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($id_stato, $descrizione, $tipo_documento, $ordine_stato, $ultimo_stato_1, $show_change, $print_fiscal_receipt, $invio_sms, $row_color);
				while($stmt->fetch()) {
					$row["id_stato"] = $id_stato;
					$row["descrizione"] = $descrizione;
					$row["tipo_documento"] = $tipo_documento;
					$row["ordine_stato"] = $ordine_stato;
					$row["ultimo_stato_1"] = $ultimo_stato_1;
					$row["show_change"] = $show_change;
					$row["print_fiscal_receipt"] = $print_fiscal_receipt;
					$row["invio_sms"] = $invio_sms;
					$row["row_color"] = $row_color;
					$row["record_count"] = mysqli_stmt_num_rows($stmt);
					$push[] = $row;
				}
				
				$query = "SELECT FOUND_ROWS()";
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
				if ($stmt = $mysqli->prepare($query)) {
					$stmt->execute(); 
					if($stmt->errno!=0){
						$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
						return $push;
					} else {
						$stmt->store_result();
						$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
						$stmt->bind_result($record_count);
						$stmt->fetch();
						$row["record_count"] = $record_count;
						$push[] = $row;
					}
				} else {
					$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
					return $push;
				}
			}

			return $push;
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return $push;
		}
	}
	
	function get_status_data($id_stato, $tipo_documento, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$query = "SELECT id_stato, descrizione, tipo_documento, ordine_stato, ultimo_stato_1, show_change, print_fiscal_receipt, invio_sms, row_color FROM stati WHERE id_stato = ? AND tipo_documento = ? LIMIT 1";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($stmt = $mysqli->prepare($query)) { 
			#*** Query parameters ***
			$params = Array('ss', $id_stato, $tipo_documento);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			$stmt->execute(); 
			if($stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				$status_data_array["exists"] = "0";
				return $status_data_array;
			} else {
				$stmt->store_result();
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
				$stmt->bind_result($id_stato, $descrizione, $tipo_documento, $ordine_stato, $ultimo_stato_1, $show_change, $print_fiscal_receipt, $invio_sms, $row_color); 
				$stmt->fetch();
				if($stmt->num_rows == 1) { 
					$status_data_array["exists"] = "1";
					$status_data_array["id_stato"] = $id_stato;
					$status_data_array["descrizione"] = $descrizione;
					$status_data_array["tipo_documento"] = $tipo_documento;
					$status_data_array["ordine_stato"] = $ordine_stato;
					$status_data_array["ultimo_stato_1"] = $ultimo_stato_1;
					$status_data_array["show_change"] = $show_change;
					$status_data_array["print_fiscal_receipt"] = $print_fiscal_receipt;
					$status_data_array["invio_sms"] = $invio_sms;
					$status_data_array["row_color"] = $row_color;
					return $status_data_array;
				} else {
					$status_data_array["exists"] = "0";
					return $status_data_array;
				}
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			$status_data_array["exists"] = "0";
			return $status_data_array;
		}
	}

	function edit_status_data($id_stato, $descrizione, $tipo_documento, $ordine_stato, $print_fiscal_receipt, $show_change, $ultimo_stato_1, $invio_sms, $row_color, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$query = "UPDATE stati SET descrizione = ?, ordine_stato = ?, print_fiscal_receipt = ?, show_change = ?, ultimo_stato_1 = ?, invio_sms = ?, row_color = ? WHERE id_stato = ? AND tipo_documento = ? ";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($update_stmt = $mysqli->prepare($query)) {
			#*** Query parameters ***
			$params = Array('siiisisss', $descrizione, $ordine_stato, $print_fiscal_receipt, $show_change, $ultimo_stato_1, $invio_sms, $row_color, $id_stato, $tipo_documento);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($update_stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			
			$update_stmt->execute();
			if($update_stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return -1;
			} else {
				return 1;
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return 0;
		}
	}
	
	function delete_status_data($id_stato, $tipo_documento, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$query = "DELETE FROM stati WHERE id_stato = ? AND tipo_documento = ? ";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($delete_stmt = $mysqli->prepare($query)) {    
			#*** Query parameters ***
			$params = Array('ss', $id_stato, $tipo_documento);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($delete_stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			
			$delete_stmt->execute();
			if($delete_stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return 0;
			} else {
				return 1;
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return 0;
		}
	}
	
	function add_status_data($id_stato, $descrizione, $tipo_documento, $ordine_stato, $print_fiscal_receipt, $show_change, $ultimo_stato_1, $invio_sms, $row_color, $mysqli) {
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "Avvio funzione: %s", __FUNCTION__));
		$query = "INSERT INTO stati (id_stato, descrizione, tipo_documento, ordine_stato, print_fiscal_receipt, show_change, ultimo_stato_1, invio_sms, row_color) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $query));
		if ($insert_stmt = $mysqli->prepare($query)) {
			#*** Query parameters ***
			$params = Array('sssiiisis', $id_stato, $descrizione, $tipo_documento, $ordine_stato, $print_fiscal_receipt, $show_change, $ultimo_stato_1, $invio_sms, $row_color);
			$tmp = array();
			foreach($params as $key => $value) $tmp[$key] = &$params[$key];
			call_user_func_array(array($insert_stmt, 'bind_param'), $tmp);
			#*** Query parameters ***
			$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
			
			$insert_stmt->execute();
			if($insert_stmt->errno!=0){
				$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				return -1;
			} else {
				return 1;
			}
		} else {
			$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
			return 0;
		}
	}

	//################################################################################################
	//FINE GESTIONE STATI ORDINI/DOCUMENTI
	//################################################################################################
?>