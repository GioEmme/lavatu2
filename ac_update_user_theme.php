<?php
	header('Content-Type: application/json');
	include_once('includes/header.php');

	if (isset($_SERVER['HTTP_HOST'])) {
		if (strpos($_SERVER['SERVER_NAME'], $_SERVER['HTTP_HOST']) !== 0) {
			exit(json_encode(['error' => -999]));
		} else {
			if (!empty($_POST['pref_userid']) && !empty($_POST['pref_htmltag'])) {
				$result = 0;
				$sql_query = "UPDATE users SET ux_theme = ? WHERE username = ?";
				$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL statement: %s", $sql_query));
				if ($stmt = $mysqli->prepare($sql_query)) {
					#*** Query parameters ***
					$params = Array('ss', $_POST['pref_htmltag'], $_POST['pref_userid']);
					$tmp = array();
					foreach($params as $key => $value) $tmp[$key] = &$params[$key];
					call_user_func_array(array($stmt, 'bind_param'), $tmp);
					#*** Query parameters ***
					$GLOBALS['log']->LogDebug(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Parameters: %s", json_encode($tmp)));
					$stmt->execute();
					if($mysqli->errno!=0){
						$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
						$result = -1;
					}
				} else {
					$GLOBALS['log']->LogError(sprintf(eval($GLOBALS['logPrefixString']) . "SQL Error (%s): %s", $mysqli->errno, $mysqli->error));
					$result = -1;
				}
				$result = mysqli_stmt_errno($stmt);
			} else {
				$GLOBALS['log']->LogWarn(sprintf(eval($GLOBALS['logPrefixString']) . "Variabili _POST non impostate"));
				$result = -1;
			}
			exit(json_encode(['error' => $result]));
		}
	} else {
		exit(json_encode(['error' => -999]));
	}
?>