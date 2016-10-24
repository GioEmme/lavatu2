<?php
	header('Content-Type: application/json');
	include_once('includes/header.php');
	if (isset($_SERVER['HTTP_HOST'])) {
		if (strpos($_SERVER['SERVER_NAME'], $_SERVER['HTTP_HOST']) !== 0) {
			exit(json_encode(['error' => -999]));
		} else {
			if (isset($_POST['tipo_fiscale']) && isset($_POST['anno_fiscale'])) {
				$array = array();
				$mysqli->autocommit(FALSE);
				$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Avvio transazione DB");
				try {
					$sql_query = "SELECT tipo_fiscale, numero_fiscale, prefisso, suffisso, anno FROM numeratori WHERE tipo_fiscale = ? AND anno = ?";
					$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $sql_query));
					if ($stmt = $mysqli->prepare($sql_query)) {
						#*** Query parameters ***
						$params = Array('si', $_POST['tipo_fiscale'], $_POST['anno_fiscale']);
						$tmp = array();
						foreach($params as $key => $value) $tmp[$key] = &$params[$key];
						call_user_func_array(array($stmt, 'bind_param'), $tmp);
						#*** Query parameters ***
						$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
						$stmt->execute();
						if($mysqli->errno!=0){
							$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
							$mysqli->rollback();
							$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
							$mysqli->autocommit(TRUE);
							$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
							exit(json_encode(['error' => -999]));						
						} else {
							$stmt->store_result();
							$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
							$stmt->bind_result($tipo_fiscale, $numero_fiscale, $prefisso, $suffisso, $anno);
							while($stmt->fetch()) {
								$row = $tipo_fiscale . '|' . $numero_fiscale . '|' . $prefisso . '|' . $suffisso . '|' . $anno;
								array_push($array, $row);
							}
							
							if (mysqli_stmt_num_rows($stmt)>0) {
								$sql_query = "UPDATE numeratori SET numero_fiscale = numero_fiscale + 1 WHERE tipo_fiscale = ? AND anno = ?";
								$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $sql_query));
								if ($stmt = $mysqli->prepare($sql_query)) {
									#*** Query parameters ***
									$params = Array('si', $_POST['tipo_fiscale'], $_POST['anno_fiscale']);
									$tmp = array();
									foreach($params as $key => $value) $tmp[$key] = &$params[$key];
									call_user_func_array(array($stmt, 'bind_param'), $tmp);
									#*** Query parameters ***
									$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));						
									$stmt->execute();
									if($mysqli->errno!=0){
										$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
										$mysqli->rollback();
										$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
										$mysqli->autocommit(TRUE);
										$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
										exit(json_encode(['error' => -999]));						
									}
								} else {
									$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
									$mysqli->rollback();
									$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
									$mysqli->autocommit(TRUE);
									$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
									exit(json_encode(['error' => -999]));															
								}
							} else {
								$row = $_POST['tipo_fiscale'] . '||||';
							}
							
							$mysqli->commit();
							$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Commit transazione DB");
						}
					} else {
						$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
						$mysqli->rollback();
						$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Rollback transazione DB");
						$mysqli->autocommit(TRUE);
						$GLOBALS['log']->LogDebug(eval($GLOBALS['logPrefixString']) . "Chiusura transazione DB");
						exit(json_encode(['error' => -999]));						
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
				exit(json_encode($array)); //Return the JSON Array
			}
		}
	} else {
		exit(json_encode(['error' => -999]));
	}
?>