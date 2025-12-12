<?php
session_start();
require 'config.php';


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

if ($user['role'] !== 'admin' && $user['role'] !== 'supAdmin') {
    header('Location: Login.html');
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_selected'])) {
    $ids = $_POST['delete_ids'] ?? [];

    if (is_array($ids) && count($ids) > 0) {
        $ids = array_values(array_filter($ids, fn($v) => ctype_digit((string)$v)));

        if (count($ids) > 0) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $del = $pdo->prepare("DELETE FROM Formulaire WHERE id IN ($placeholders)");
            $del->execute($ids);
        }
    }

    // Retour propre (garde la recherche si tu veux : on peut aussi la conserver)
    header('Location: admin_dashboard.php');
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
    <title>Tableau de bord ADMIN</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="dashboard">
<div class="dash-wrap">

    <header class="dash-header">
        <div class="dash-title">
            <h1>Tableau de bord Administrateur</h1>
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
            <form method="POST" class="table-actions" onsubmit="return confirmDelete();">

                <div class="table-top-actions">
                    <button class="btn btn-danger" type="submit" name="delete_selected">Supprimer</button>
                    <span class="hint">Coche une ou plusieurs lignes puis clique sur “Supprimer”.</span>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th class="col-check">
                                    <input type="checkbox" id="checkAll" onclick="toggleAll(this)">
                                </th>

                                <?php foreach (array_keys($rows[0]) as $column): ?>
                                    <th><?= htmlspecialchars($column) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <td class="col-check">
                                        <input type="checkbox" name="delete_ids[]" value="<?= (int)$row['id'] ?>">
                                    </td>

                                    <?php foreach ($row as $value): ?>
                                        <td><?= htmlspecialchars((string)$value) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </form>
        <?php else: ?>
            <p class="empty">Aucun résultat.</p>
        <?php endif; ?>

    </section>

</div>

<script>
function toggleAll(master){
    document.querySelectorAll('input[name="delete_ids[]"]').forEach(cb => cb.checked = master.checked);
}
function confirmDelete(){
    const checked = document.querySelectorAll('input[name="delete_ids[]"]:checked').length;
    if (checked === 0) { alert("Sélectionne au moins une ligne à supprimer."); return false; }
    return confirm("Supprimer les lignes sélectionnées ?");
}
</script>
</body>
</html>
