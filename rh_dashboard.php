<?php
session_start();
require 'config.php';

$sql = "SELECT * FROM formulaire";
$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord RH</title>
</head>
<body>
    <h1>Tableau de bord Ressources Humaines</h1>
    <p>Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?>.</p>

    <h1>Liste des utilisateurs</h1>

<table border="1">
    <tr>
        <?php foreach (array_keys($rows[0]) as $column): ?>
            <th><?= htmlspecialchars($column) ?></th>
        <?php endforeach; ?>
    </tr>

    <?php foreach ($rows as $row): ?>
        <tr>
            <?php foreach ($row as $value): ?>
                <td><?= htmlspecialchars($value) ?></td>
            <?php endforeach; ?>
        </tr>
    <?php endforeach; ?>

</table>
    <p><a href="logout.php">DÃ©connexion</a></p>
</body>
</html>