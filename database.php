<?php

$host = "localhost";
$db_name = "finanza";
$username = "root";
$password = "";
$port = "3306";

try {
	$con = new PDO("mysql:host={$host};port={$port};dbname={$db_name}", $username, $password);
}
catch(PDOException $exception){
	echo "Connection error: " . $exception->getMessage();
	die();
}
?>