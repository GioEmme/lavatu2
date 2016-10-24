<?php
	header('Content-Type: application/json');
	include_once('includes/header.php');
	if (isset($_SERVER['HTTP_HOST'])) {
		if (strpos($_SERVER['SERVER_NAME'], $_SERVER['HTTP_HOST']) !== 0) {
			exit(json_encode(['error' => -999]));
		} else {
			if (isset($_POST['name_startsWith'])) {
				$array = array();
				$query = '%' . $_POST['name_startsWith'] . '%';
				$sql_query = "SELECT id_cliente, cognome, nome, indirizzo, telefono, cellulare, invia_sms FROM clienti WHERE cognome LIKE ?";
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $sql_query));
				if ($stmt = $mysqli->prepare($sql_query)) {
					#*** Query parameters ***
					$params = Array('s', $query);
					$tmp = array();
					foreach($params as $key => $value) $tmp[$key] = &$params[$key];
					call_user_func_array(array($stmt, 'bind_param'), $tmp);
					#*** Query parameters ***
					$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
					$stmt->execute(); // esegue la query appena creata.
					if($mysqli->errno!=0){
						$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
						exit(json_encode(['error' => -999]));
					} else {
						$stmt->store_result();
						$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
						$stmt->bind_result($id_cliente, $cognome, $nome, $indirizzo, $telefono, $cellulare, $invia_sms);
						while($stmt->fetch()) {
							$row = $id_cliente . '|' . $nome . ' ' . $cognome . ' [' . $indirizzo . '] Tel.: ' . $telefono . ' Cell.: ' . $cellulare . '|' . $nome . ' ' . $cognome . '|' . $telefono . '|' . $cellulare . '|' . $indirizzo . '|' . $invia_sms;
							array_push($array, $row);
						}
					}
				} else {
					$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
					exit(json_encode(['error' => -999]));
				}
				exit(json_encode($array));
			}
			
			if (isset($_POST['id_cliente'])) {
				$array = array();
				$sql_query = "SELECT id_cliente, cognome, nome, indirizzo, telefono, cellulare, invia_sms FROM clienti WHERE id_cliente = ?";
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $sql_query));
				if ($stmt = $mysqli->prepare($sql_query)) {
					#*** Query parameters ***
					$params = Array('i', $_POST['id_cliente']);
					$tmp = array();
					foreach($params as $key => $value) $tmp[$key] = &$params[$key];
					call_user_func_array(array($stmt, 'bind_param'), $tmp);
					#*** Query parameters ***
					$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
					$stmt->execute(); // esegue la query appena creata.
					if($mysqli->errno!=0){
						$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
						exit(json_encode(['error' => -999]));
					} else {
						$stmt->store_result();
						$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
						$stmt->bind_result($id_cliente, $cognome, $nome, $indirizzo, $telefono, $cellulare, $invia_sms);
						while($stmt->fetch()) {
							$row = $id_cliente . '|' . $nome . ' ' . $cognome . ' [' . $indirizzo . '] Tel.: ' . $telefono . ' Cell.: ' . $cellulare . '|' . $nome . ' ' . $cognome . '|' . $telefono . '|' . $cellulare . '|' . $indirizzo . '|' . $invia_sms;
							array_push($array, $row);
						}
					}
				} else {
					$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
					exit(json_encode(['error' => -999]));
				}
				exit(json_encode($array));
			}
		}
	} else {
		exit(json_encode(['error' => -999]));
	}
?>