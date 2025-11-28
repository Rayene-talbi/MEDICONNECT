<?php 

$host = 'localhost';
$dbname = 'Mediconnect';
$user = 'root';
$pass =''

$pdo = new PDO ("mysql:host = $host ; dbname = $dbname ; chaset=utf8", $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>
