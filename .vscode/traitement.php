<?php
session_start();
if (!empty($_POST['csrf_token'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Erreur CSRF : Jeton de sécurité invalide.");
    }
} else {
    // Si vous voulez être strict :
    // die("Erreur : Jeton CSRF manquant.");
}

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
$niv_diplome    = trim($_POST['niv_diplome'] ?? '');
$adresse        = trim($_POST['adresse'] ?? '');
$secu           = trim($_POST['secu'] ?? '');
$telephone      = trim($_POST['telephone'] ?? '');

// Validation serveur basique
if ($nom === '' || $prenom === '' || $email === '') {
    die("Certains champs obligatoires sont manquants.");
}

// ========== Gestion de l'upload du CV ==========
$targetPath = null; // Par défaut, pas de CV

// On vérifie si un fichier a bien été envoyé sans erreur
if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
    
    $file = $_FILES['cv'];

    // 1. Limiter la taille (5 Mo)
    $maxSize = 5 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        die("Fichier trop volumineux (max 5 Mo).");
    }

    // 2. Vérifier le type MIME réel (Sécurité)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if ($mimeType !== 'application/pdf') {
        die("Seuls les fichiers PDF valides sont autorisés.");
    }

    // 3. Dossier de destination
    $uploadDir = __DIR__ . '/uploads_cv/'; // Chemin absolu recommandé
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // 4. Nom de fichier unique et sécurisé
    $extension = '.pdf';
    // Nettoyage du nom/prénom pour éviter les caractères spéciaux dans le nom de fichier
    $cleanName = preg_replace('/[^a-z0-9]+/', '_', strtolower($nom . '_' . $prenom));
    $filename = 'cv_' . $cleanName . '_' . time() . $extension;
    $fullPath = $uploadDir . $filename;

    // 5. Déplacement du fichier
    if (move_uploaded_file($file['tmp_name'], $fullPath)) {
        // On stocke le chemin relatif ou absolu selon ton besoin en BDD
        $targetPath = 'uploads_cv/' . $filename;
    } else {
        die("Impossible d’enregistrer le CV sur le serveur.");
    }
} elseif (isset($_FILES['cv']) && $_FILES['cv']['error'] !== UPLOAD_ERR_NO_FILE) {
    // Si une erreur autre que "pas de fichier" survient
    die("Erreur lors de l'envoi du fichier code: " . $_FILES['cv']['error']);
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
        ':Niv_Diplome'    => $niv_diplome,
        ':adresse'        => $adresse,
        ':secu'           => $secu,
        ':telephone'      => $telephone,
        ':cv_path'        => $targetPath // Sera NULL si pas de fichier ou 'uploads_cv/...' si succès
    ]);

} catch (PDOException $e) {
    // En production, évite d'afficher l'erreur SQL exacte à l'utilisateur
    error_log("Erreur SQL : " . $e->getMessage());
    die("Une erreur est survenue lors de l'enregistrement.");
}

//--- Gestion des Logs ---
$logFile = "/var/log/mediconnect_app.log";

$date = date('Y-m-d H:i:s');
$ip = $_SERVER['REMOTE_ADDR'];
$message = "[$date] Nouvelle candidature : $nom $prenom (IP: $ip)\n";

// On écrit dedans (le 3 signifie "ajouter à la fin du fichier")
error_log($message, 3, $logFile);

// Redirection
header("Location: Merci.html");
exit;
?>