<?php

$host = '127.0.0.1';
$port = 3307;              // ⚠️ le port MySQL indiqué dans XAMPP
$dbname = 'mediconnect';   // ⚠️ remplace par le VRAI nom de ta base
$user = 'root';
$pass = '';                // mot de passe vide sous XAMPP

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8";
    $pdo = new PDO($dsn, $user, $pass);

    // Affiche les erreurs SQL proprement
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

?>
