<?php
	/* LOCALE PER SVILUPPO */	
	define("HOST", "localhost"); // E' il server a cui ti vuoi connettere.
	define("USER", "lavatu"); // E' l'utente con cui ti collegherai al DB.
	define("PASSWORD", "8bs3cPpO6nyezzDkGIatb"); // Password di accesso al DB.
	define("DATABASE", "lavatu"); // Nome del database. lavatu2/sql455718_5

	/* ARUBA CLAUDIO */	
	/*define("HOST", "62.149.150.133"); // E' il server a cui ti vuoi connettere.
	define("USER", "Sql455718"); // E' l'utente con cui ti collegherai al DB.
	define("PASSWORD", "eb463f3b"); // Password di accesso al DB.
	define("DATABASE", "Sql455718_4"); // Nome del database. lavatu2/sql455718_5*/

	$mysqli = new mysqli(HOST, USER, PASSWORD, DATABASE);
	$mysqli->set_charset("utf8");
	// Se ti stai connettendo usando il protocollo TCP/IP, invece di usare un socket UNIX, ricordati di aggiungere il parametro corrispondente al numero di porta.
?>