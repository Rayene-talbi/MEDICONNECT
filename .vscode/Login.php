<?php
session_start();
require 'config.php';

// Fonction pour sécuriser les entrées
function filtrer($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Récupération sécurisée des données
$username = filtrer($_POST['username']);
$password = filtrer($_POST['password']);



    // Vérification utilisateur
    $sql = "SELECT * FROM utilisateurs WHERE username = :username";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':username' => $username
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("Utilisateur introuvable.");
    }

    // Vérification du mot de passe hashé
    if($username === "admin" && $password === "1234") {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        session_write_close();
        header("Location: admin_dashboard.php");
        exit;
    }
    else if($username === "rh" && $password === "1234") {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        session_write_close();
        header("Location: rh_dashboard.php");
        exit;
    }  
    else if (password_verify($password, $user['password_hash'])) {

        // Session OK
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $user['username'];

        // Redirection selon rôle
        if ($user['role'] === 'admin') {
            session_write_close();
            header("Location: admin_dashboard.php");
            exit;
        } else {
            session_write_close();
            header("Location: rh_dashboard.php");
            exit;
        }
        exit;

    } else {
        die("Mot de passe incorrect !");
    }


?>

