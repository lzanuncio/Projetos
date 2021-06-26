<?php
# Autor: Rafael Fernando Nazatto
session_start();

if ($_SESSION['login'] != ""){
	
error_reporting (E_ALL & ~ E_NOTICE & ~ E_DEPRECATED);

	#Funcao de conexão com o banco de dados
	$host = "localhost";
	$user = "root";
	$password = "footer7889";
	$db_name = "joomlascop";

	//PARA NAO CAGAR NA ACENTUAÇÃO NO BANCO DE DADOS
	header('Content-Type: text/html; charset=utf-8');

	$con = mysql_connect($host,$user,$password) or die ("Não foi possivel conectar ao servidor");
	mysql_select_db ($db_name,$con) or die ("Não foi possivel conectar ao banco");

	//PARA NAO CAGAR NA ACENTUAÇÃO NO BANCO DE DADOS
	mysql_connect($host,$user,$password);
	mysql_select_db($db_name);
	mysql_query("SET NAMES 'utf8'");
	mysql_query('SET character_set_connection=utf8');
	mysql_query('SET character_set_client=utf8');
	mysql_query('SET character_set_results=utf8');	
}else{
	header('Location: index.php');
}
?>