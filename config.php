<?php

$host = '127.0.0.1';
$port = 3307;             
$dbname = 'mediconnect';   
$user = 'root';
$pass = '';               

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8";
    $pdo = new PDO($dsn, $user, $pass);

    // Affiche les erreurs SQL proprement
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

?>
