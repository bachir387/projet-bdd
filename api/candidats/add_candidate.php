<?php
// File: candidats/add_candidate.php

require_once '../config/config.php';
require_once '../utils/send_email.php';  // Fichier pour envoyer l’email si besoin

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Lire les données JSON envoyées dans le corps de la requête
$data = json_decode(file_get_contents("php://input"));

// Vérifier que le numéro d'électeur est fourni
if (!isset($data->numero_electeur)) {
    echo json_encode([
        "success" => false,
        "message" => "Le numéro d'électeur est requis."
    ]);
    exit();
}

$numero_electeur = trim($data->numero_electeur);

// ----------------------------------------------------------------------
// 1. Vérifier la période de parrainage
//    Les candidats doivent être enregistrés AVANT le début de la période
// ----------------------------------------------------------------------
$stmtPeriode = $pdo->query("SELECT date_debut, date_fin FROM periode_parrainage ORDER BY created_at DESC LIMIT 1");
$periode = $stmtPeriode->fetch(PDO::FETCH_ASSOC);
if (!$periode) {
    echo json_encode([
        "success" => false,
        "message" => "Aucune période de parrainage n'est définie."
    ]);
    exit();
}

$currentDate = date('Y-m-d');
if ($currentDate >= $periode['date_debut']) {
    // La période de parrainage a déjà commencé => on bloque l'ajout de candidats
    echo json_encode([
        "success" => false,
        "message" => "Impossible d’ajouter un candidat après le début de la période de parrainage."
    ]);
    exit();
}

// ----------------------------------------------------------------------
// 2. Vérifier que l'électeur figure dans la table 'electeurs'
// ----------------------------------------------------------------------
$stmt = $pdo->prepare("SELECT * FROM electeurs WHERE numeroElecteur = :numero_electeur");
$stmt->execute([":numero_electeur" => $numero_electeur]);
$electeur = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$electeur) {
    echo json_encode([
        "success" => false,
        "message" => "L'électeur n'est pas dans la liste des électeurs."
    ]);
    exit();
}

// ----------------------------------------------------------------------
// 3. Vérifier que le candidat n'est pas déjà enregistré
// ----------------------------------------------------------------------
$stmt = $pdo->prepare("SELECT * FROM candidats WHERE numero_electeur = :numero_electeur");
$stmt->execute([":numero_electeur" => $numero_electeur]);
if ($stmt->fetch()) {
    echo json_encode([
        "success" => false,
        "message" => "Ce candidat est déjà enregistré."
    ]);
    exit();
}

// Récupérer les informations de base de l'électeur
$nom = $electeur['nom'];
$prenom = $electeur['prenom'];
$date_naissance = $electeur['date_naissance'];

// ----------------------------------------------------------------------
// 4. Si les informations complémentaires (email, téléphone) ne sont pas fournies
//    renvoyer les infos de base pour compléter le formulaire
// ----------------------------------------------------------------------
if (!isset($data->email) || !isset($data->telephone)) {
    echo json_encode([
        "success" => false,
        "message" => "Informations complémentaires requises (email et téléphone).",
        "candidate_details" => [
            "numero_electeur" => $numero_electeur,
            "nom" => $nom,
            "prenom" => $prenom,
            "date_naissance" => $date_naissance
        ]
    ]);
    exit();
}

// Récupérer les informations complémentaires envoyées
$email = trim($data->email);
$telephone = trim($data->telephone);
$parti_politique = isset($data->parti_politique) ? trim($data->parti_politique) : null;
$slogan = isset($data->slogan) ? trim($data->slogan) : null;
$photo = isset($data->photo) ? trim($data->photo) : null;
// $couleur1 = isset($data->couleur1) ? trim($data->couleur1) : null;
// $couleur2 = isset($data->couleur2) ? trim($data->couleur2) : null;
// $couleur3 = isset($data->couleur3) ? trim($data->couleur3) : null;
$url = isset($data->url) ? trim($data->url) : null;

// ----------------------------------------------------------------------
// 5. Générer un nouveau code d'authentification (6 chiffres)
// ----------------------------------------------------------------------
$code_auth = strval(rand(100000, 999999));

// ----------------------------------------------------------------------
// 6. Insérer le candidat dans la table 'candidats'
// ----------------------------------------------------------------------
$stmt = $pdo->prepare("INSERT INTO candidats 
    (numero_electeur, nom, prenom, date_naissance, email, telephone, parti_politique, slogan, photo, url, code_auth) 
    VALUES 
    (:numero_electeur, :nom, :prenom, :date_naissance, :email, :telephone, :parti_politique, :slogan, :photo, :url, :code_auth)");

$success = $stmt->execute([
    ":numero_electeur" => $numero_electeur,
    ":nom" => $nom,
    ":prenom" => $prenom,
    ":date_naissance" => $date_naissance,
    ":email" => $email,
    ":telephone" => $telephone,
    ":parti_politique" => $parti_politique,
    ":slogan" => $slogan,
    ":photo" => $photo,
    // ":couleur1" => $couleur1,
    // ":couleur2" => $couleur2,
    // ":couleur3" => $couleur3,
    ":url" => $url,
    ":code_auth" => $code_auth
]);

if ($success) {
    // Envoyer le code par email au candidat
    sendEmail($email, "Votre code d'authentification", "Votre code d'authentification est : $code_auth");
    
    echo json_encode([
        "success" => true,
        "message" => "Candidat ajouté avec succès. Un code d'authentification a été envoyé par email."
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Erreur lors de l'ajout du candidat."
    ]);
}
?>
