<?php
require 'config.php'
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord ADMIN</title>
</head>
<body>
    <h1>Tableau de bord Administrateur</h1>
    <p>Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?>.</p>

    <p><a href="rh_dashboard.php">Voir le tableau de bord RH</a></p>
    <p><a href="logout.php">Déconnexion</a></p>
</body>
</html>
