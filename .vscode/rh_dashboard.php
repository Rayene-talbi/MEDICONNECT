<?php
session_start();
require 'config.php';

/* =========================
   AUTH
   ========================= */
if (empty($_SESSION['username'])) {
    header('Location: Login.html');
    exit;
}

$username = $_SESSION['username'];

$sqlRole = "SELECT role FROM utilisateurs WHERE username = :username";
$stmtRole = $pdo->prepare($sqlRole);
$stmtRole->execute([':username' => $username]);
$user = $stmtRole->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header('Location: Login.html');
    exit;
}

if ($user['role'] !== 'rh' && $user['role'] !== 'supAdmin') {
    header('Location: Login.html');
    exit;
}

/* =========================
   SEARCH
   ========================= */
$search = $_GET['q'] ?? '';

if (!empty($search)) {
    $keywords = preg_split('/\s+/', trim($search));
    $columns = ["prenom", "nom", "adresse", "lieu_naissance", "email"];

    $conditions = [];
    $params = [];

    foreach ($keywords as $index => $word) {
        $wordCondition = [];
        foreach ($columns as $col) {
            $paramName = ":word{$index}_{$col}";
            $wordCondition[] = "$col LIKE $paramName";
            $params[$paramName] = "%$word%";
        }
        $conditions[] = "(" . implode(" OR ", $wordCondition) . ")";
    }

    $sql = "SELECT * FROM Formulaire WHERE " . implode(" AND ", $conditions) . " ORDER BY id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
} else {
    $stmt = $pdo->query("SELECT * FROM Formulaire ORDER BY id DESC");
}

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord RH</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="dashboard">
<div class="dash-wrap">

    <header class="dash-header">
        <div class="dash-title">
            <h1>Tableau de bord RH</h1>
            <p class="dash-sub">Bienvenue, <strong><?= htmlspecialchars($username) ?></strong></p>
        </div>

        <form action="logout.php" method="POST">
            <button class="btn btn-logout" type="submit">Déconnexion</button>
        </form>
    </header>

    <section class="dash-card">
        <div class="dash-card-head">
            <h2>Liste des candidatures</h2>

            <form method="GET" class="searchbar">
                <input class="search-input" type="text" name="q" placeholder="Rechercher…" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                <button class="btn btn-search" type="submit">Rechercher</button>
            </form>
        </div>

        <?php if (!empty($rows)): ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <?php foreach (array_keys($rows[0]) as $column): ?>
                                <th><?= htmlspecialchars($column) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <?php foreach ($row as $col => $value): ?>
                                    <td>
                                        <?php if ($col === 'cv_path' && !empty($value)): ?>
                                            <a class="btn btn-mini" href="<?= htmlspecialchars($value) ?>" target="_blank" rel="noopener">
                                            CV
                                            </a>
                                        <?php else: ?>
                                            <?= htmlspecialchars((string)$value) ?>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="empty">Aucun résultat.</p>
        <?php endif; ?>

    </section>

</div>
</body>
</html>
