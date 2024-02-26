<?php
// Dados de conexão
$host = '127.0.0.1';
$port = '5432';
$dbname = 'TesteTelecontrol';
$user = 'postgres';
$password = 'gusta';

    // Conectando ao Banco
	$conn = pg_connect("host=$host	port=$port	dbname=$dbname	user=$user	password=$password");

	if (!$conn) {
	//	echo "Erro ao conectar-se ao banco de dados.";
	} else {
	//	echo "Conectado com Sucesso ao banco de dados!";
	}
?>


