<?php
	header('Content-Type: application/json');
	include_once('includes/header.php');
	if (isset($_SERVER['HTTP_HOST'])) {
		if (strpos($_SERVER['SERVER_NAME'], $_SERVER['HTTP_HOST']) !== 0) {
			exit(json_encode(['error' => -999]));
		} else {
			if (isset($_POST['id_lavorazione']) && isset($_POST['id_stato']) && !empty($_POST['tipo_documento'])) {
				$result = 0;
				$sql_query = "UPDATE lavorazioni SET stato = ? WHERE id_lavorazione = ?";
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $sql_query));
				if ($stmt = $mysqli->prepare($sql_query)) {
					#*** Query parameters ***
					$params = Array('si', $_POST['id_stato'], $_POST['id_lavorazione']);
					$tmp = array();
					foreach($params as $key => $value) $tmp[$key] = &$params[$key];
					call_user_func_array(array($stmt, 'bind_param'), $tmp);
					#*** Query parameters ***
					$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));	
					$stmt->execute();
					if($mysqli->errno!=0){
						$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
					}
				} else {
					$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
				}
				$result = mysqli_stmt_errno($stmt);
				
				/* INVIO SMS */
				$sql_query = "SELECT invio_sms FROM stati WHERE id_stato = ? AND tipo_documento = ? ";
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $sql_query));
				if ($stmt = $mysqli->prepare($sql_query)) {
					#*** Query parameters ***
					$params = Array('ss', $_POST['id_stato'], $_POST['tipo_documento']);
					$tmp = array();
					foreach($params as $key => $value) $tmp[$key] = &$params[$key];
					call_user_func_array(array($stmt, 'bind_param'), $tmp);
					#*** Query parameters ***
					$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));	
					$stmt->execute();
					if($mysqli->errno!=0){
						$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
						exit(json_encode(['error' => -888]));
					} else {						
						$stmt->store_result();
						$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
						$stmt->bind_result($invio_sms);
						$stmt->fetch();
						
						$sql_query = "SELECT sms,cellulare FROM lavorazioni INNER JOIN clienti ON lavorazioni.id_cliente = clienti.id_cliente WHERE id_lavorazione = ? ";
						$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $sql_query));
						if ($stmt = $mysqli->prepare($sql_query)) {
							#*** Query parameters ***
							$params = Array('i', $_POST['id_lavorazione']);
							$tmp = array();
							foreach($params as $key => $value) $tmp[$key] = &$params[$key];
							call_user_func_array(array($stmt, 'bind_param'), $tmp);
							#*** Query parameters ***
							$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));	
							$stmt->execute(); // esegue la query appena creata.
							if($mysqli->errno!=0){
								$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
								exit(json_encode(['error' => -888]));
							} else {
								$stmt->store_result();
								$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Affected Rows: %s", $stmt->num_rows));
								$stmt->bind_result($invio_sms_lavorazione, $cellulare);
								$stmt->fetch();
							
								if($invio_sms==1 && $invio_sms_lavorazione=='S'){
									try {
										$html_server_call = "http://api.clickatell.com/http/sendmsg?user=" . $Clickatell_API_username . "&password=" . $Clickatell_API_password . "&api_id=" . $Clickatell_API_id . "&to=" . ($SMSDebugMode==1?$SMSDebugNumber:$cellulare) . "&text=";
										//$html_server_call = "http://api.clickatell.com/http/sendmsg?user=" . $Clickatell_API_username . "&password=" . $Clickatell_API_password . "&api_id=" . $Clickatell_API_id . "&to=" . urlencode(trim($cellulare)) . "&text=";
										$html_message = "Gentile cliente, il suo ordine N° " . $_POST['id_lavorazione'] . " e' stato completato. Da questo momento e' disponibile per il ritiro. Saluti. Lavanderia LavaTu Galatina.";
										$html = file_get_contents($html_server_call . urlencode($html_message));
										$current_date = new DateTime();
										file_put_contents($App_BaseServerPath . '/sms_log/sms_' . $_POST['id_lavorazione'] . '_' . $current_date->getTimestamp() . '.txt', $html);
										 
										$sql_query = "UPDATE lavorazioni SET data_sms = ? WHERE id_lavorazione = ?";
										$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $sql_query));
										if ($stmt = $mysqli->prepare($sql_query)) {
											#*** Query parameters ***
											$params = Array('si', $current_date->format('Y-m-d'), $_POST['id_lavorazione']);
											$tmp = array();
											foreach($params as $key => $value) $tmp[$key] = &$params[$key];
											call_user_func_array(array($stmt, 'bind_param'), $tmp);
											#*** Query parameters ***
											$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));	
											$stmt->execute();
											if($mysqli->errno!=0){
												$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
												exit(json_encode(['error' => -888]));
											}
										} else {
											$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
											exit(json_encode(['error' => -888]));
										}
									} catch (Exception $e) {
										//echo $e->getMessage();
										$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "Send SMS Procedure Error: %s", $e->getMessage()));
										exit(json_encode(['error' => -888]));					
									}
								}
							}
						}
					}
				} else {
					$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
					exit(json_encode(['error' => -888]));					
				}
				/* FINE INVIO SMS */ 

			} else {
				$result = 1;
			}
			exit(json_encode(['error' =>  $result]));
		}
	} else {
		exit(json_encode(['error' => -999]));
	}
?>