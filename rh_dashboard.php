<?php
session_start();
require 'config.php';


?>
<?php
// On récupère la recherche
$search = $_GET['q'] ?? '';

// Construction de la requête
if (!empty($search)) {

    // Séparation par mots -> "jean dupont" devient ["jean", "dupont"]
    $keywords = explode(" ", trim($search));

    // Colonnes à vérifier
    $columns = ["prenom", "nom", "adresse", "lieu_naissance", "email"];  // adapte selon ta table

    $conditions = [];
    $params = [];

    foreach ($keywords as $index => $word) {
        $wordCondition = [];
        foreach ($columns as $col) {
            $paramName = ":word{$index}_{$col}";
            $wordCondition[] = "$col LIKE $paramName";
            $params[$paramName] = "%$word%";
        }
        // Chaque mot doit apparaitre dans AU MOINS une colonne
        $conditions[] = "(" . implode(" OR ", $wordCondition) . ")";
    }

    // Les conditions des mots doivent toutes être vraies
    $sql = "SELECT * FROM formulaire WHERE " . implode(" AND ", $conditions);

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

} else {
    // Pas de recherche → toute la table
    $stmt = $pdo->query("SELECT * FROM formulaire");
}

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
    <p>Bienvenue, <?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>.</p>


    <h1>Liste des utilisateurs</h1>

<form method="GET">
    <input type="text" name="q" placeholder="Rechercher..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
    <button type="submit">Rechercher</button>
</form>

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
    <p><a href="logout.php">Déconnexion</a></p>
</body>
</html>