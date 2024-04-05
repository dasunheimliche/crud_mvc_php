<?php
	$dominio_actual = $_SERVER['HTTP_HOST'];
	define('APP_URL', $dominio_actual . "/");
	define('APP_NAME', "CRUD POO MySQL");
	define('APP_SESSION_NAME', "CRUD");


	/*----------  Zona horaria  ----------*/
	date_default_timezone_set("America/Argentina/Buenos_Aires");

	/*
		Configuración de zona horaria de tu país, para más información visita
		http://php.net/manual/es/function.date-default-timezone-set.php
		http://php.net/manual/es/timezones.php
	*/