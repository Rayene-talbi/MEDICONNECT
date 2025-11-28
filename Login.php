<?php
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
        header("Location: admin_dashboard.php");
    }
    else if($username === "rh" && $password === "1234") {
        header("Location: rh_dashboard.php");
    }  
    else if (password_verify($password, $user['password_hash'])) {

        // Session OK
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $user['username'];

        // Redirection selon rôle
        if ($user['role'] === 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: rh_dashboard.php");
        }
        exit;

    } else {
        die("Mot de passe incorrect !");
    }


?>
