<?php
// Connexion BDD
require 'config.php';

// Vérifier qu'on vient bien du formulaire
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Méthode non autorisée.");
}

// Récupération des champs du formulaire
$nom            = trim($_POST['nom'] ?? '');
$prenom         = trim($_POST['prenom'] ?? '');
$email          = trim($_POST['email'] ?? '');
$date_naissance = $_POST['date_naissance'] ?? '';
$lieu_naissance = trim($_POST['lieu_naissance'] ?? '');
$niv_diplome    = trim($_POST['niv_diplome'] ?? '');   // name HTML = niv_diplome
$adresse        = trim($_POST['adresse'] ?? '');
$secu           = trim($_POST['secu'] ?? '');
$telephone      = trim($_POST['telephone'] ?? '');

// Mini validation serveur (tu peux renforcer)
if ($nom === '' || $prenom === '' || $email === '') {
    die("Certains champs obligatoires sont manquants.");
}

// ========== Gestion de l'upload du CV ==========

if (!isset($_FILES['cv']) || $_FILES['cv']['error'] !== UPLOAD_ERR_OK) {
    die("Erreur lors de l’upload du CV.");
}

$file = $_FILES['cv'];

// 1. Vérifier le type MIME (PDF uniquement)
$allowedMime = ['application/pdf'];
if (!in_array($file['type'], $allowedMime)) {
    die("Seuls les fichiers PDF sont autorisés.");
}

// 2. Limiter la taille (ex : 5 Mo)
$maxSize = 5 * 1024 * 1024; // 5 Mo
if ($file['size'] > $maxSize) {
    die("Fichier trop volumineux (max 5 Mo).");
}

// 3. Dossier de destination
$uploadDir = 'uploads_cv/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// 4. Nom de fichier unique
$extension = '.pdf';
$filename = 'cv_' . preg_replace('/\W+/', '_', strtolower($nom . '_' . $prenom)) . '_' . time() . $extension;
$targetPath = $uploadDir . $filename;

// 5. Déplacement du fichier
if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    die("Impossible d’enregistrer le CV sur le serveur.");
}

// ========== Insertion dans la table Formulaire ==========

try {
    $sql = "INSERT INTO Formulaire 
            (nom, prenom, email, date_naissance, lieu_naissance, Niv_Diplome, adresse, secu, telephone, cv_path)
            VALUES 
            (:nom, :prenom, :email, :date_naissance, :lieu_naissance, :Niv_Diplome, :adresse, :secu, :telephone, :cv_path)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':nom'            => $nom,
        ':prenom'         => $prenom,
        ':email'          => $email,
        ':date_naissance' => $date_naissance,
        ':lieu_naissance' => $lieu_naissance,
        ':Niv_Diplome'    => $niv_diplome,  // colonne = Niv_Diplome
        ':adresse'        => $adresse,
        ':secu'           => $secu,
        ':telephone'      => $telephone,
        ':cv_path'        => $targetPath
    ]);

} catch (PDOException $e) {
    die("Erreur lors de l’enregistrement en base : " . $e->getMessage());
}
header("Location: Merci.html");
exit;
?>

